<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 綠界金流設定
    |--------------------------------------------------------------------------
    */

    'mode' => env('ECPAY_MODE', 'production'), // test | production

    // 測試環境憑證
    'test' => [
        'merchant_id' => env('ECPAY_TEST_MERCHANT_ID', '3002607'),
        'hash_key'    => env('ECPAY_TEST_HASH_KEY', 'pwFHCqoQZGmho4w6'),
        'hash_iv'     => env('ECPAY_TEST_HASH_IV', 'EkRm7iFT261dpevs'),
        'gateway_url' => env('ECPAY_TEST_GATEWAY_URL', 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5'),
    ],

    // 正式環境憑證
    'production' => [
        'merchant_id' => env('ECPAY_PROD_MERCHANT_ID'),
        'hash_key'    => env('ECPAY_PROD_HASH_KEY'),
        'hash_iv'     => env('ECPAY_PROD_HASH_IV'),
        'gateway_url' => env('ECPAY_PROD_GATEWAY_URL', 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5'),
    ],

    // 舊版單組 key（向下相容）
    'merchant_id' => env('ECPAY_MERCHANT_ID'),
    'hash_key'    => env('ECPAY_HASH_KEY'),
    'hash_iv'     => env('ECPAY_HASH_IV'),
    'gateway_url' => env('ECPAY_PAYMENT_GATEWAY_URL', 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5'),

    // ECPay 回呼路由（基於 APP_URL，rtrim 避免雙斜線）
    'notify_url' => rtrim(env('APP_URL'), '/') . '/ecpay/notify',
    'return_url' => rtrim(env('APP_URL'), '/') . '/ecpay/return',
];
