<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateways\NewebpayGateway;
use Illuminate\Http\Request;

class NewebpayCallbackController extends Controller
{
    public function __construct(protected NewebpayGateway $gateway) {}

    /**
     * 藍新伺服器端回呼 (notify)
     * 這裡才是真正更新訂單的地方。回傳純文字,不可重定向。
     */
    public function notify(Request $request)
    {
        $result = $this->gateway->processNotify($request->post());
        return response($result, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * 藍新瀏覽器回跳 (return)
     * 因藍新使用跨域 POST (SameSite=lax),此時 session cookie 不會被帶回,
     * 採用 PRG 模式:解析 TradeInfo 取得 order_no 後 redirect 到 GET 路由,
     * GET 路由有 auth middleware,session 會正常恢復,才能顯示後台 layout。
     */
    public function returnResult(Request $request)
    {
        $orderNo = $this->gateway->extractOrderNo($request->post());
        return redirect()->route('renewal.payment-result', ['order_no' => $orderNo]);
    }
}
