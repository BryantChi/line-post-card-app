<?php

/**
 * ECPay 金流回呼路由
 *
 * 使用 'ecpay' middleware 群組（不含 StartSession / VerifyCsrfToken），
 * 避免 ECPay 跨域 POST 建立新空白 session，覆蓋用戶原本的登入 cookie。
 *
 * notify  → 伺服器端回呼，更新訂單，回傳 "1|OK"
 * return  → 瀏覽器回跳，PRG redirect 到後台結果頁（GET，有 auth）
 */

use App\Http\Controllers\EcpayCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('/ecpay/notify', [EcpayCallbackController::class, 'notify'])
    ->name('ecpay.notify')
    ->middleware('throttle:60,1');

Route::post('/ecpay/return', [EcpayCallbackController::class, 'returnResult'])
    ->name('ecpay.return')
    ->middleware('throttle:30,1');
