<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 綠界金流設定
    |--------------------------------------------------------------------------
    */

    'merchant_id' => env('ECPAY_MERCHANT_ID', '3002607'),
    'hash_key'    => env('ECPAY_HASH_KEY'),
    'hash_iv'     => env('ECPAY_HASH_IV'),

    'gateway_url' => env(
        'ECPAY_PAYMENT_GATEWAY_URL',
        'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5'
    ),

    'mode' => env('ECPAY_MODE', 'test'), // test | production

    // ECPay 回呼路由（由系統自動設定，基於 APP_URL）
    'notify_url' => env('APP_URL') . '/ecpay/notify',
    'return_url' => env('APP_URL') . '/ecpay/return',
];
