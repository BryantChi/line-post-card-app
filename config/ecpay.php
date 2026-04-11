<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 綠界金流設定
    |--------------------------------------------------------------------------
    |
    | 憑證統一由後台「系統設定」頁面管理（加密存入資料庫）。
    | 以下僅保留測試環境預設值作為初始 fallback，正式環境憑證請於後台設定。
    |
    */

    // 測試環境憑證（ECPay 公開沙箱帳號，作為初始 fallback）
    'test' => [
        'merchant_id' => '3002607',
        'hash_key'    => 'pwFHCqoQZGmho4w6',
        'hash_iv'     => 'EkRm7iFT261dpevs',
        'gateway_url' => 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5',
    ],

    // 正式環境憑證（無預設值，須於後台系統設定頁面填寫）
    'production' => [
        'merchant_id' => null,
        'hash_key'    => null,
        'hash_iv'     => null,
        'gateway_url' => 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5',
    ],

    // ECPay 回呼路由（基於 APP_URL，rtrim 避免雙斜線）
    'notify_url' => rtrim(env('APP_URL'), '/') . '/ecpay/notify',
    'return_url' => rtrim(env('APP_URL'), '/') . '/ecpay/return',
];
