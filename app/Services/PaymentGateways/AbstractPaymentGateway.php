<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayContract;
use App\Models\PaymentTransaction;
use App\Models\RenewalOrder;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayContract
{
    public function isActive(): bool
    {
        return in_array($this->code(), SystemSetting::getActiveGateways(), true);
    }

    public function isRedirect(): bool
    {
        return true;
    }

    public function buildCheckoutForm(RenewalOrder $order): string
    {
        throw new \LogicException(static::class . ' does not support redirect checkout.');
    }

    /**
     * 共用的 notify 處理流程:驗章 → 找訂單 → 冪等 → 金額一致 → 更新狀態 + 建交易紀錄 + 延長到期日
     *
     * 各子類別僅需實作:
     *  - parseVerifiedData(array $postData): array
     *      回傳 ['order_no'=>..., 'transaction_no'=>..., 'amount'=>int, 'is_success'=>bool, 'raw'=>array, 'message'=>string]
     *      若驗章失敗應拋出例外
     *  - successResponse(): string
     *  - failureResponse(string $reason): string
     *  - sanitizeGatewayResponse(array $raw): array
     */
    public function processNotify(array $postData): string
    {
        $orderNo = null;

        try {
            $parsed = $this->parseVerifiedData($postData);
            $orderNo = $parsed['order_no'];

            $order = RenewalOrder::where('order_no', $orderNo)->first();
            if (!$order) {
                Log::warning(static::class . ' 回呼:找不到訂單', ['order_no' => $orderNo]);
                return $this->failureResponse('OrderNotFound');
            }

            // 冪等:已付款直接返回成功
            if ($order->status === 'paid') {
                Log::info(static::class . ' 回呼:訂單已付款(重複回呼)', ['order_no' => $orderNo]);
                return $this->successResponse();
            }

            // 訂單已在其他終態 (cancelled/expired):不可改寫狀態。
            // 回成功讓金流商停止重送,並依是否真的付款成功記錄不同層級供人工處理
            // (例如:逾期被自動清理後用戶仍完成付款,需人工退款或補延期)。
            if ($order->isTerminal()) {
                $context = ['order_no' => $orderNo, 'status' => $order->status];
                if ($parsed['is_success']) {
                    Log::critical(static::class . ' 回呼:終態訂單收到成功付款,需人工處理', $context);
                } else {
                    Log::warning(static::class . ' 回呼:終態訂單收到付款失敗回呼(略過)', $context);
                }
                return $this->successResponse();
            }

            // 金額一致性檢查
            if ($parsed['amount'] !== $order->amount) {
                Log::critical(static::class . ' 回呼:金額不一致', [
                    'order_no' => $orderNo,
                    'expected' => $order->amount,
                    'received' => $parsed['amount'],
                ]);
                return $this->failureResponse('AmountMismatch');
            }

            // 付款失敗
            if (!$parsed['is_success']) {
                Log::warning(static::class . ' 回呼:付款失敗', [
                    'order_no' => $orderNo,
                    'message'  => $parsed['message'],
                ]);
                PaymentTransaction::create([
                    'order_id'         => $order->id,
                    'transaction_no'   => $parsed['transaction_no'],
                    'payment_method'   => $this->paymentMethodValue(),
                    'amount'           => $parsed['amount'],
                    'status'           => 'failed',
                    'gateway_response' => $this->sanitizeGatewayResponse($parsed['raw']),
                    'note'             => $parsed['message'],
                ]);
                return $this->failureResponse('PaymentFailed');
            }

            // 付款成功:在 transaction 內更新訂單、建交易紀錄、延長到期日 (雙重鎖定保護冪等)
            DB::transaction(function () use ($order, $parsed) {
                $fresh = RenewalOrder::lockForUpdate()->find($order->id);
                // 鎖內二次確認:僅 pending 可轉為 paid,避免並發下已變終態 (paid/cancelled/expired) 仍被改寫
                if ($fresh->status !== 'pending') {
                    return;
                }

                $fresh->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);

                PaymentTransaction::create([
                    'order_id'         => $fresh->id,
                    'transaction_no'   => $parsed['transaction_no'],
                    'payment_method'   => $this->paymentMethodValue(),
                    'amount'           => $fresh->amount,
                    'status'           => 'success',
                    'gateway_response' => $this->sanitizeGatewayResponse($parsed['raw']),
                    'note'             => $this->label() . ' 信用卡付款成功',
                ]);

                $fresh->user->extendExpiration($fresh->plan->duration_days);

                Log::info(static::class . ' 付款成功', [
                    'order_no'       => $fresh->order_no,
                    'amount'         => $fresh->amount,
                    'transaction_no' => $parsed['transaction_no'],
                ]);
            });

            return $this->successResponse();

        } catch (\Throwable $e) {
            Log::error(static::class . ' 回呼處理例外', [
                'order_no' => $orderNo,
                'message'  => $e->getMessage(),
            ]);
            return $this->failureResponse('Exception');
        }
    }

    /**
     * 驗章 + 抽出 normalized 資料
     * @return array{order_no:string, transaction_no:?string, amount:int, is_success:bool, message:string, raw:array}
     */
    abstract protected function parseVerifiedData(array $postData): array;

    abstract protected function successResponse(): string;

    abstract protected function failureResponse(string $reason): string;

    abstract protected function sanitizeGatewayResponse(array $raw): array;

    /**
     * 建立自動送出的 HTML <form>(供 CSP nonce 環境使用)
     * Manager-side script 會以 document.getElementById('payment-gateway-form').submit() 觸發
     */
    protected function renderAutoSubmitForm(string $action, array $fields): string
    {
        $html = sprintf('<form id="payment-gateway-form" method="POST" action="%s">', e($action));
        foreach ($fields as $name => $value) {
            $html .= sprintf(
                '<input type="hidden" name="%s" value="%s">',
                e($name),
                e((string) $value)
            );
        }
        $html .= '<noscript><button type="submit" class="btn btn-primary">繼續付款</button></noscript>';
        $html .= '</form>';

        return $html;
    }
}
