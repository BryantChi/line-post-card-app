<?php
namespace App\Http\Controllers;

use App\Services\EcpayService;
use Illuminate\Http\Request;

class EcpayCallbackController extends Controller
{
    protected EcpayService $ecpayService;

    public function __construct(EcpayService $ecpayService)
    {
        $this->ecpayService = $ecpayService;
    }

    /**
     * ECPay 伺服器端回呼（notify）
     * 這裡才是真正更新訂單的地方
     * 必須回傳純文字 "1|OK"，不可重定向
     */
    public function notify(Request $request)
    {
        $result = $this->ecpayService->processCallback($request->post());
        return response($result, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * ECPay 瀏覽器回跳（return）
     * 因 ECPay 使用跨域 POST（SameSite=lax），此時 session cookie 不會被帶回，
     * 採用 PRG 模式：僅將 order_no 存入 session flash 後立即 redirect 到 GET 路由，
     * GET 路由有 auth middleware，session 會正常恢復，才能顯示後台 layout。
     */
    public function returnResult(Request $request)
    {
        $orderNo = $request->input('MerchantTradeNo');
        return redirect()->route('renewal.payment-result', ['order_no' => $orderNo]);
    }
}
