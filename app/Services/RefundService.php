<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\RenewalOrder;
use App\Services\PaymentGateways\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    public function __construct(protected PaymentGatewayManager $gateways) {}

    /**
     * 對訂單退款。
     *
     * @param  RenewalOrder  $order
     * @param  int     $amount             退款金額(>0)
     * @param  string  $reason             退款原因(必填,稽核)
     * @param  bool    $rollbackExpiration 是否扣回到期日(該方案天數)
     * @param  ?string $action             指定動作(refund/void/manual);null=由 gateway 判斷
     * @return array{success:bool, message:string, transaction:?PaymentTransaction}
     */
    public function refund(
        RenewalOrder $order,
        int $amount,
        string $reason,
        bool $rollbackExpiration,
        ?string $action = null
    ): array {
        $driver = $this->gateways->driverForPaymentMethod($order->payment_method);

        return DB::transaction(function () use ($order, $amount, $reason, $rollbackExpiration, $action, $driver) {
            /** @var RenewalOrder $fresh */
            $fresh = RenewalOrder::lockForUpdate()->find($order->id);

            // 1. 狀態與金額驗證
            if (!in_array($fresh->status, ['paid', 'partially_refunded'], true)) {
                return ['success' => false, 'message' => '此訂單狀態不可退款', 'transaction' => null];
            }
            $refundable = max(0, (int) $fresh->amount - (int) $fresh->refunded_amount);
            if ($amount <= 0 || $amount > $refundable) {
                return ['success' => false, 'message' => "退款金額不合法(可退餘額 {$refundable})", 'transaction' => null];
            }

            // 2. 找原付款交易
            $payment = PaymentTransaction::where('order_id', $fresh->id)
                ->where('type', PaymentTransaction::TYPE_PAYMENT)
                ->where('status', 'success')
                ->latest()->first();
            if (!$payment && $fresh->payment_method !== 'bank_transfer') {
                return ['success' => false, 'message' => '找不到原始付款交易', 'transaction' => null];
            }

            // 3. 決定動作 + 執行
            if ($fresh->payment_method === 'bank_transfer') {
                $result = ['success' => true, 'txn_no' => 'MANUAL-REFUND-' . now()->format('YmdHis') . '-' . $fresh->id,
                           'action' => 'manual', 'message' => '銀行轉帳人工退款記錄', 'raw' => []];
            } else {
                // 預設動作 refund(信用卡續約幾乎都即時請款,refund 幾乎總是正確)。
                // 不在退款流程同步呼叫藍新查詢 API 自動判斷:該 API 在測試環境會 hang,
                // 會卡死單執行緒開發伺服器。未請款需作廢的情況由管理員手動選 void。
                $finalAction = $action ?: 'refund';
                $result = $driver->refund($payment, $amount, $finalAction);
            }

            // 4. 寫退款交易紀錄(成功或失敗都記,稽核留痕)
            $refundTxn = PaymentTransaction::create([
                'order_id'              => $fresh->id,
                'type'                  => PaymentTransaction::TYPE_REFUND,
                'parent_transaction_id' => $payment->id ?? null,
                'refund_action'         => $result['action'],
                'transaction_no'        => $result['txn_no'],
                'payment_method'        => $fresh->payment_method,
                'amount'                => $amount,
                'status'                => $result['success'] ? 'success' : 'failed',
                'gateway_response'      => $result['raw'],
                'note'                  => $reason,
            ]);

            // 5. 失敗 → 不動訂單與到期日
            if (!$result['success']) {
                Log::warning('退款失敗', ['order_no' => $fresh->order_no, 'message' => $result['message']]);
                return ['success' => false, 'message' => $result['message'], 'transaction' => $refundTxn];
            }

            // 6. 成功 → 累加已退、更新狀態、(選擇性)扣回到期日
            $fresh->refunded_amount = (int) $fresh->refunded_amount + $amount;
            $fresh->status = $fresh->refunded_amount >= (int) $fresh->amount ? 'refunded' : 'partially_refunded';
            $fresh->save();

            if ($rollbackExpiration) {
                $fresh->loadMissing(['user', 'plan']);
                $fresh->user->reduceExpiration((int) $fresh->plan->duration_days);
            }

            Log::info('退款成功', [
                'order_no' => $fresh->order_no, 'amount' => $amount,
                'action'   => $result['action'], 'status' => $fresh->status,
            ]);

            return ['success' => true, 'message' => '退款成功', 'transaction' => $refundTxn];
        });
    }
}
