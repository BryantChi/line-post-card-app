<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 綠界金流設定
    |--------------------------------------------------------------------------
    */

    'merchant_id' => env('ECPAY_MERCHANT_ID'),  // 無 fallback，強制由 .env 提供
    'hash_key'    => env('ECPAY_HASH_KEY'),
    'hash_iv'     => env('ECPAY_HASH_IV'),

    'gateway_url' => env(
        'ECPAY_PAYMENT_GATEWAY_URL',
        'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5'  // 預設正式環境
    ),

    'mode' => env('ECPAY_MODE', 'production'), // test | production

    // ECPay 回呼路由（基於 APP_URL，rtrim 避免雙斜線）
    'notify_url' => rtrim(env('APP_URL'), '/') . '/ecpay/notify',
    'return_url' => rtrim(env('APP_URL'), '/') . '/ecpay/return',
];
