<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayContract;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function __construct(protected Container $container) {}

    /**
     * 取得指定 code 的 driver 實例
     * @param string $code 'newebpay' / 'ecpay' / 'bank_transfer'
     */
    public function driver(string $code): PaymentGatewayContract
    {
        $map = config('payment.gateways', []);

        if (!isset($map[$code])) {
            throw new InvalidArgumentException("Unknown payment gateway: {$code}");
        }

        return $this->container->make($map[$code]);
    }

    /**
     * 依 RenewalOrder.payment_method 欄位值反查對應 driver
     */
    public function driverForPaymentMethod(string $paymentMethod): PaymentGatewayContract
    {
        $methodMap = config('payment.method_to_gateway', []);

        if (!isset($methodMap[$paymentMethod])) {
            throw new InvalidArgumentException("Unknown payment_method: {$paymentMethod}");
        }

        return $this->driver($methodMap[$paymentMethod]);
    }

    /**
     * 取得所有「目前 active」的 gateway,依設定順序回傳
     * @return PaymentGatewayContract[]
     */
    public function activeDrivers(): array
    {
        $active = \App\Models\SystemSetting::getActiveGateways();
        $drivers = [];

        foreach ($active as $code) {
            try {
                $drivers[] = $this->driver($code);
            } catch (InvalidArgumentException $e) {
                continue;
            }
        }

        return $drivers;
    }

    /**
     * 預設 gateway (前台預選用)
     */
    public function default(): ?PaymentGatewayContract
    {
        $code = \App\Models\SystemSetting::getDefaultGateway();
        if (!$code) {
            return null;
        }

        try {
            return $this->driver($code);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * 列出所有已註冊的 gateway code (含未啟用的)
     * @return string[]
     */
    public function allCodes(): array
    {
        return array_keys(config('payment.gateways', []));
    }
}
