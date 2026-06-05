<?php

namespace App\Services\PaymentGateways;

use App\Models\RenewalOrder;
use App\Models\SystemSetting;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Omnipay;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class NewebpayGateway extends AbstractPaymentGateway
{
    public function code(): string
    {
        return 'newebpay';
    }

    public function paymentMethodValue(): string
    {
        return 'newebpay_credit';
    }

    public function label(): string
    {
        return '信用卡(藍新金流)';
    }

    public function buildCheckoutForm(RenewalOrder $order): string
    {
        $gateway = $this->makeGateway();

        $request = $gateway->purchase([
            'transactionId' => $order->order_no,
            'amount'        => (string) $order->amount,
            'description'   => 'LINE AI 數位名片會員續約 - ' . $order->plan->name,
            'returnUrl'     => config('payment.newebpay.return_url'),
            'notifyUrl'     => config('payment.newebpay.notify_url'),
            'CREDIT'        => '1', // 啟用信用卡支付
            'Email'         => $order->user->email,
        ]);

        $response = $request->send();

        $action = $response->getRedirectUrl();
        $fields = $response->getRedirectData();

        return $this->renderAutoSubmitForm($action, $fields);
    }

    public function extractOrderNo(array $postData): ?string
    {
        $tradeInfo = $postData['TradeInfo'] ?? null;
        if (!$tradeInfo) {
            return null;
        }

        try {
            $parsed = $this->parseVerifiedData($postData);
            return $parsed['order_no'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseVerifiedData(array $postData): array
    {
        // httpRequest 必須在建構時注入,不可用 initialize() 設定:
        // initialize() 會重置 gateway 的 ParameterBag,清掉 makeGateway 設好的
        // merchant_id/hash_key/hash_iv,導致驗章時 HashKey 為空 (Key of size 0)。
        $symfonyRequest = SymfonyRequest::create('', 'POST', $postData);
        $gateway = $this->makeGateway($symfonyRequest);

        // acceptNotification 內部會驗證 TradeSha 與解密 TradeInfo
        $response = $gateway->acceptNotification()->send();

        if (!$response->isSuccessful() && !isset($response->getData()['Status'])) {
            throw new InvalidRequestException('Invalid notification payload');
        }

        $data = $response->getData();

        return [
            'order_no'       => $data['MerchantOrderNo'] ?? null,
            'transaction_no' => $data['TradeNo'] ?? null,
            'amount'         => (int) ($data['Amt'] ?? 0),
            'is_success'     => ($data['Status'] ?? '') === 'SUCCESS',
            'message'        => $data['Message'] ?? '',
            'raw'            => $data,
        ];
    }

    protected function successResponse(): string
    {
        // 藍新對回應內容無強制格式,回 1|OK 與綠界一致便於監控
        return '1|OK';
    }

    protected function failureResponse(string $reason): string
    {
        return '0|' . $reason;
    }

    protected function sanitizeGatewayResponse(array $raw): array
    {
        // 白名單:只保留稽核所需欄位,移除卡號等敏感資訊
        $allowedKeys = [
            'MerchantID',
            'MerchantOrderNo',
            'TradeNo',
            'Amt',
            'Status',
            'Message',
            'PaymentType',
            'RespondType',
            'PayTime',
            'IP',
            'EscrowBank',
            'AuthBank',
            'TokenUseStatus',
        ];

        return array_filter(
            $raw,
            fn($key) => in_array($key, $allowedKeys, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function resolveRefundAction(\App\Models\PaymentTransaction $original): string
    {
        try {
            $gateway = $this->makeGateway();
            $response = $gateway->fetchTransaction([
                'transactionId' => $original->order->order_no,
                'amount'        => (string) $original->amount, // QueryTradeInfo 的 CheckValue 需含金額(MerchantID+Amt+MerchantOrderNo),漏傳會驗證失敗
            ])->send();

            $data = $response->getData();
            // 藍新 CloseStatus: 0=未請款, 1=等待批次關帳, 2=請款失敗, 3=已關帳請款
            $closeStatus = $data['Result']['CloseStatus'] ?? ($data['CloseStatus'] ?? null);

            if ($closeStatus !== null && (string) $closeStatus === '0') {
                return 'void'; // 尚未請款 → 取消授權
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('藍新查交易狀態失敗,退款動作預設 refund', [
                'order_no' => $original->order->order_no ?? null,
                'error'    => $e->getMessage(),
            ]);
        }

        return 'refund'; // 已請款或查不到 → 預設退款(管理員可手動覆寫)
    }

    public function refund(\App\Models\PaymentTransaction $original, int $amount, string $action): array
    {
        $gateway = $this->makeGateway();
        $orderNo = $original->order->order_no;
        $tradeNo = $original->transaction_no; // 原付款的藍新 TradeNo

        $options = [
            'transactionId'        => $orderNo,
            'transactionReference' => $tradeNo,
            'amount'               => (string) $amount,
        ];

        try {
            // action=void → CreditCard/Cancel(取消授權); 否則 → CreditCard/Close CloseType=2(退款)
            $request  = $action === 'void' ? $gateway->void($options) : $gateway->refund($options);
            $response = $request->send();
            $data     = $response->getData();

            $success = method_exists($response, 'isSuccessful')
                ? $response->isSuccessful()
                : (($data['Status'] ?? '') === 'SUCCESS');

            return [
                'success' => (bool) $success,
                'txn_no'  => $data['Result']['TradeNo'] ?? ($data['TradeNo'] ?? $tradeNo),
                'action'  => $action,
                'message' => $data['Message'] ?? ($success ? 'OK' : 'FAILED'),
                'raw'     => is_array($data) ? $data : [],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'txn_no'  => null,
                'action'  => $action,
                'message' => $e->getMessage(),
                'raw'     => [],
            ];
        }
    }

    /**
     * 建立 Omnipay\NewebPay\Gateway 實例,套用後台憑證
     */
    private function makeGateway(?SymfonyRequest $httpRequest = null)
    {
        $mode  = SystemSetting::getNewebpayMode();
        $creds = SystemSetting::getNewebpayCredentials($mode);

        // 帶逾時的 HTTP client:避免藍新 API(交易查詢/退款)無回應時 hang,
        // 卡死單執行緒開發伺服器。purchase/notify 不發外部請求,不受影響。
        $httpClient = new \Omnipay\Common\Http\Client(
            new \Http\Adapter\Guzzle7\Client(
                new \GuzzleHttp\Client(['timeout' => 8, 'connect_timeout' => 5])
            )
        );

        /** @var \Omnipay\NewebPay\Gateway $gateway */
        $gateway = Omnipay::create('NewebPay', $httpClient, $httpRequest);
        $gateway->setMerchantID($creds['merchant_id']);
        $gateway->setHashKey($creds['hash_key']);
        $gateway->setHashIV($creds['hash_iv']);
        $gateway->setTestMode($mode === 'test');

        return $gateway;
    }
}
