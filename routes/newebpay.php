<?php

/**
 * 藍新金流 (NewebPay) 回呼路由
 *
 * 使用與 ECPay 共用的 'ecpay' middleware 群組 (不含 StartSession / VerifyCsrfToken),
 * 避免藍新跨域 POST 建立新空白 session,覆蓋用戶原本的登入 cookie。
 *
 * notify  → 伺服器端回呼,更新訂單,回傳 "1|OK"
 * return  → 瀏覽器回跳,PRG redirect 到後台結果頁 (GET,有 auth)
 */

use App\Http\Controllers\NewebpayCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('/newebpay/notify', [NewebpayCallbackController::class, 'notify'])
    ->name('newebpay.notify')
    ->middleware('throttle:60,1');

Route::post('/newebpay/return', [NewebpayCallbackController::class, 'returnResult'])
    ->name('newebpay.return')
    ->middleware('throttle:30,1');
