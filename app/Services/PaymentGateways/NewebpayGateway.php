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
        $gateway = $this->makeGateway();
        $symfonyRequest = SymfonyRequest::create('', 'POST', $postData);
        $gateway->initialize(['httpRequest' => $symfonyRequest]);

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

    /**
     * 建立 Omnipay\NewebPay\Gateway 實例,套用後台憑證
     */
    private function makeGateway()
    {
        $mode  = SystemSetting::getNewebpayMode();
        $creds = SystemSetting::getNewebpayCredentials($mode);

        /** @var \Omnipay\NewebPay\Gateway $gateway */
        $gateway = Omnipay::create('NewebPay');
        $gateway->setMerchantID($creds['merchant_id']);
        $gateway->setHashKey($creds['hash_key']);
        $gateway->setHashIV($creds['hash_iv']);
        $gateway->setTestMode($mode === 'test');

        return $gateway;
    }
}
