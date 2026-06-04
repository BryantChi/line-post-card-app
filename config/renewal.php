<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 續約系統設定
    |--------------------------------------------------------------------------
    */

    /*
     * 過期太久的處理策略
     * admin_only：續約後新到期日仍在過去時，提示聯繫管理員（預設）
     * from_now  ：自動改為從現在起算
     */
    'expired_too_long_policy' => env('RENEWAL_EXPIRED_POLICY', 'admin_only'),

    /*
     * 待付款訂單逾期時間（小時）
     * 超過此時間仍未付款的 pending 訂單將被自動標記為 expired
     */
    'order_expire_hours' => env('RENEWAL_ORDER_EXPIRE_HOURS', 72),

    /*
     * 到期提醒天數
     * 會員帳號在到期前幾天顯示提醒橫幅
     */
    'warn_days_before_expiry' => 30,
    'alert_days_before_expiry' => 7,

    /*
     * 續約開放窗口（天）
     * 僅在帳號「到期前這麼多天內」(含已過期) 才開放續約建單；
     * 未達標準者只能查看續約紀錄。
     */
    'open_days_before_expiry' => env('RENEWAL_OPEN_DAYS_BEFORE_EXPIRY', 30),
];
