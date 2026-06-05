<?php

namespace App\Services\PaymentGateways;

use App\Models\RenewalOrder;
use App\Models\SystemSetting;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Omnipay;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class EcpayGateway extends AbstractPaymentGateway
{
    public function code(): string
    {
        return 'ecpay';
    }

    public function paymentMethodValue(): string
    {
        return 'ecpay_credit';
    }

    public function label(): string
    {
        return '信用卡(綠界金流)';
    }

    public function buildCheckoutForm(RenewalOrder $order): string
    {
        $gateway = $this->makeGateway();

        $request = $gateway->purchase([
            'transactionId'   => $order->order_no,
            'amount'          => (string) $order->amount,
            'description'     => 'LINE AI 數位名片會員續約',
            'ItemName'        => '訂閱方案 ' . $order->plan->name . ' NT$' . $order->amount,
            'returnUrl'       => config('payment.ecpay.return_url'),  // → OrderResultURL (browser)
            'notifyUrl'       => config('payment.ecpay.notify_url'),  // → ReturnURL (server)
            'ChoosePayment'   => 'Credit',
            'CustomField1'    => (string) $order->id,
        ]);

        $response = $request->send();

        return $this->renderAutoSubmitForm($response->getRedirectUrl(), $response->getRedirectData());
    }

    public function extractOrderNo(array $postData): ?string
    {
        return $postData['MerchantTradeNo'] ?? null;
    }

    protected function parseVerifiedData(array $postData): array
    {
        // httpRequest 必須在建構時注入,不可用 initialize() 設定:
        // initialize() 會重置 gateway 的 ParameterBag,清掉 makeGateway 設好的
        // merchant_id/hash_key/hash_iv,導致驗章 (CheckMacValue) 因金鑰為空而失敗。
        $symfonyRequest = SymfonyRequest::create('', 'POST', $postData);
        $gateway = $this->makeGateway($symfonyRequest);

        $response = $gateway->acceptNotification()->send();

        // ECPay 的 CompletePurchaseRequest 會驗證 CheckMacValue,失敗會拋例外
        $data = $response->getData();

        if (!is_array($data) || empty($data['MerchantTradeNo'])) {
            throw new InvalidRequestException('Invalid ECPay notification payload');
        }

        return [
            'order_no'       => $data['MerchantTradeNo'] ?? null,
            'transaction_no' => $data['TradeNo'] ?? null,
            'amount'         => (int) ($data['TradeAmt'] ?? 0),
            'is_success'     => (int) ($data['RtnCode'] ?? 0) === 1,
            'message'        => $data['RtnMsg'] ?? '',
            'raw'            => $data,
        ];
    }

    protected function successResponse(): string
    {
        return '1|OK';
    }

    protected function failureResponse(string $reason): string
    {
        return '0|' . $reason;
    }

    protected function sanitizeGatewayResponse(array $raw): array
    {
        $allowedKeys = [
            'MerchantID',
            'MerchantTradeNo',
            'RtnCode',
            'RtnMsg',
            'TradeNo',
            'TradeAmt',
            'PaymentDate',
            'PaymentType',
            'PaymentTypeChargeFee',
            'TradeDate',
            'SimulatePaid',
        ];

        return array_filter(
            $raw,
            fn($key) => in_array($key, $allowedKeys, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function resolveRefundAction(\App\Models\PaymentTransaction $original): string
    {
        // 階段 2 才查交易狀態;階段 1 預設 refund(實際會被 refund() 旗標擋下)
        return 'refund';
    }

    public function refund(\App\Models\PaymentTransaction $original, int $amount, string $action): array
    {
        if (!config('payment.ecpay_refund_enabled', false)) {
            return [
                'success' => false,
                'txn_no'  => null,
                'action'  => $action,
                'message' => '綠界退款尚未啟用(待正式環境驗證端點後開放)',
                'raw'     => [],
            ];
        }

        // 階段 2 實作:呼叫 DoAction(Action=R 退刷 / N 作廢),此處先保留
        throw new \LogicException('ECPay refund not implemented yet (phase 2).');
    }

    private function makeGateway(?SymfonyRequest $httpRequest = null)
    {
        $mode  = SystemSetting::getEcpayMode();
        $creds = SystemSetting::getEcpayCredentials($mode);

        /** @var \Omnipay\ECPay\Gateway $gateway */
        $gateway = Omnipay::create('ECPay', null, $httpRequest);
        $gateway->setMerchantID($creds['merchant_id']);
        $gateway->setHashKey($creds['hash_key']);
        $gateway->setHashIV($creds['hash_iv']);
        $gateway->setEncryptType('1');
        $gateway->setTestMode($mode === 'test');

        return $gateway;
    }
}
