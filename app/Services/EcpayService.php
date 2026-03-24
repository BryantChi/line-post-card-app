<?php
namespace App\Services;

use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Response\VerifiedArrayResponse;
use Ecpay\Sdk\Services\UrlService;
use App\Models\RenewalOrder;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EcpayService
{
    private string $merchantId;
    private ?string $hashKey;
    private ?string $hashIv;
    private string $gatewayUrl;

    public function __construct()
    {
        $this->merchantId = config('ecpay.merchant_id');
        $this->hashKey    = config('ecpay.hash_key');
        $this->hashIv     = config('ecpay.hash_iv');
        $this->gatewayUrl = config('ecpay.gateway_url');
    }

    /**
     * 產生付款表單 HTML（自動送出）
     * 參數 $order 必須是 pending 狀態
     */
    public function buildCheckoutForm(RenewalOrder $order): string
    {
        $factory = new Factory(['hashKey' => $this->hashKey, 'hashIv' => $this->hashIv]);
        $autoSubmitFormService = $factory->create('AutoSubmitFormWithCmvService');

        $input = [
            'MerchantID'        => $this->merchantId,
            'MerchantTradeNo'   => $order->order_no,           // 最多 20 碼，正好符合
            'MerchantTradeDate' => now()->format('Y/m/d H:i:s'),
            'PaymentType'       => 'aio',
            'TotalAmount'       => $order->amount,
            'TradeDesc'         => UrlService::ecpayUrlEncode('LINE AI 數位名片會員續約'),
            'ItemName'          => '訂閱方案 ' . $order->plan->name . ' NT$' . $order->amount,
            'ReturnURL'         => config('ecpay.notify_url'),  // 伺服器回呼
            'OrderResultURL'    => config('ecpay.return_url'),  // 瀏覽器回跳
            'ChoosePayment'     => 'Credit',
            'EncryptType'       => 1,
            'CustomField1'      => (string) $order->id,         // 夾帶內部訂單 ID 供回呼用
        ];

        return $autoSubmitFormService->generate($input, $this->gatewayUrl);
    }

    /**
     * 驗證並解析 ECPay 回呼資料
     * 驗證失敗會拋出例外
     * @return array 驗證通過的回呼資料
     */
    public function verifyCallback(array $postData): array
    {
        $factory = new Factory(['hashKey' => $this->hashKey, 'hashIv' => $this->hashIv]);
        $checkoutResponse = $factory->create(VerifiedArrayResponse::class);
        return $checkoutResponse->get($postData); // 失敗時拋出例外
    }

    /**
     * 處理 ECPay notify 回呼（伺服器端，實際更新訂單）
     * 驗證通過後更新訂單狀態、建立交易記錄、延長到期日
     * @return string "1|OK" 成功，"0|ErrorMessage" 失敗
     */
    public function processCallback(array $postData): string
    {
        $orderNo = $postData['MerchantTradeNo'] ?? null;
        $tradeNo = $postData['TradeNo'] ?? null;

        try {
            // 1. 驗證簽章（失敗拋出例外）
            $verified = $this->verifyCallback($postData);

            // 2. 找到訂單
            $order = RenewalOrder::where('order_no', $orderNo)->first();
            if (!$order) {
                Log::warning('ECPay 回呼：找不到訂單', ['order_no' => $orderNo]);
                return '0|OrderNotFound';
            }

            // 3. 冪等：已付款直接返回 OK
            if ($order->status === 'paid') {
                Log::info('ECPay 回呼：訂單已付款（重複回呼）', ['order_no' => $orderNo]);
                return '1|OK';
            }

            // 4. 確認金額一致（防竄改）
            $returnedAmt = (int) ($verified['TradeAmt'] ?? 0);
            if ($returnedAmt !== $order->amount) {
                Log::critical('ECPay 回呼：金額不一致', [
                    'order_no' => $orderNo,
                    'expected' => $order->amount,
                    'received' => $returnedAmt,
                ]);
                return '0|AmountMismatch';
            }

            // 5. RtnCode === 1 表示付款成功
            $rtnCode = (int) ($verified['RtnCode'] ?? 0);
            if ($rtnCode !== 1) {
                Log::warning('ECPay 回呼：付款失敗', [
                    'order_no' => $orderNo,
                    'rtn_code' => $rtnCode,
                    'rtn_msg'  => $verified['RtnMsg'] ?? '',
                ]);
                // 建立失敗交易記錄
                PaymentTransaction::create([
                    'order_id'         => $order->id,
                    'transaction_no'   => $tradeNo,
                    'payment_method'   => 'ecpay_credit',
                    'amount'           => $returnedAmt,
                    'status'           => 'failed',
                    'gateway_response' => $this->sanitizeGatewayResponse($verified),
                    'note'             => 'RtnCode: ' . $rtnCode . ' / ' . ($verified['RtnMsg'] ?? ''),
                ]);
                return '0|PaymentFailed';
            }

            // 6. 在 Transaction 中更新訂單狀態、建交易記錄、延長到期日
            DB::transaction(function () use ($order, $verified, $tradeNo) {
                $fresh = RenewalOrder::lockForUpdate()->find($order->id);

                if ($fresh->status === 'paid') {
                    return; // 冪等保護（雙重鎖定）
                }

                $fresh->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);

                PaymentTransaction::create([
                    'order_id'         => $fresh->id,
                    'transaction_no'   => $tradeNo,
                    'payment_method'   => 'ecpay_credit',
                    'amount'           => $fresh->amount,
                    'status'           => 'success',
                    'gateway_response' => $this->sanitizeGatewayResponse($verified),
                    'note'             => 'ECPay 信用卡付款成功',
                ]);

                $fresh->user->extendExpiration($fresh->plan->duration_days);

                Log::info('ECPay 付款成功', [
                    'order_no'       => $fresh->order_no,
                    'amount'         => $fresh->amount,
                    'transaction_no' => $tradeNo,
                ]);
            });

            return '1|OK';

        } catch (\Exception $e) {
            Log::error('ECPay 回呼處理例外', [
                'order_no' => $orderNo,
                'message'  => $e->getMessage(),
            ]);
            return '0|Exception';
        }
    }

    /**
     * 移除 gateway_response 中不需記錄的敏感欄位
     */
    private function sanitizeGatewayResponse(array $data): array
    {
        // 移除 CheckMacValue（金鑰衍生值）
        unset($data['CheckMacValue']);
        return $data;
    }
}
