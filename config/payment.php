<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 多金流統一設定
    |--------------------------------------------------------------------------
    |
    | 統一管理「藍新金流 (NewebPay) / 綠界金流 (ECPay) / 銀行轉帳」三種付款方式。
    | 主要憑證由後台「系統設定」加密儲存 (SystemSetting)；
    | 此檔僅保留 endpoint URL 與 fallback 預設值。
    |
    */

    // 金流驅動類別對應表
    'gateways' => [
        'newebpay'      => App\Services\PaymentGateways\NewebpayGateway::class,
        'ecpay'         => App\Services\PaymentGateways\EcpayGateway::class,
        'bank_transfer' => App\Services\PaymentGateways\BankTransferGateway::class,
    ],

    // payment_method 欄位值 → gateway code 對應 (用於 RenewalOrder.payment_method 反查 driver)
    'method_to_gateway' => [
        'newebpay_credit' => 'newebpay',
        'ecpay_credit'    => 'ecpay',
        'bank_transfer'   => 'bank_transfer',
    ],

    // 藍新金流 (NewebPay)
    'newebpay' => [
        // 測試環境 (官方公開測試帳號,僅供開發初期 fallback)
        'test' => [
            'merchant_id' => env('NEWEBPAY_TEST_MERCHANT_ID'),
            'hash_key'    => env('NEWEBPAY_TEST_HASH_KEY'),
            'hash_iv'     => env('NEWEBPAY_TEST_HASH_IV'),
            'gateway_url' => 'https://ccore.newebpay.com/MPG/mpg_gateway',
        ],
        // 正式環境
        'production' => [
            'merchant_id' => env('NEWEBPAY_PROD_MERCHANT_ID'),
            'hash_key'    => env('NEWEBPAY_PROD_HASH_KEY'),
            'hash_iv'     => env('NEWEBPAY_PROD_HASH_IV'),
            'gateway_url' => 'https://core.newebpay.com/MPG/mpg_gateway',
        ],
        // 回呼路由
        'notify_url' => rtrim(env('APP_URL'), '/') . '/newebpay/notify',
        'return_url' => rtrim(env('APP_URL'), '/') . '/newebpay/return',
    ],

    // 綠界退款階段旗標:階段 1 關閉,正式環境驗證端點後再開
    'ecpay_refund_enabled' => env('ECPAY_REFUND_ENABLED', false),

    // 綠界金流 (ECPay) - 沿用既有 config/ecpay.php 的設定來源
    'ecpay' => [
        'notify_url' => rtrim(env('APP_URL'), '/') . '/ecpay/notify',
        'return_url' => rtrim(env('APP_URL'), '/') . '/ecpay/return',
    ],

    /*
    |--------------------------------------------------------------------------
    | 注意
    |--------------------------------------------------------------------------
    | 1. 正式環境上線前需在藍新金流後台設定「我方 server IP 白名單」
    | 2. 所有回呼路由須使用 HTTPS
    | 3. 後台未設定憑證時,SystemSetting 會優先讀 .env (透過 config()),
    |    config 未設定時返回 null
    */
];
