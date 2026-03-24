<?php
namespace App\Http\Controllers;

use App\Services\EcpayService;
use App\Models\RenewalOrder;
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
     * 僅顯示結果，不更新訂單（訂單已在 notify 中更新）
     */
    public function returnResult(Request $request)
    {
        $orderNo = $request->input('MerchantTradeNo');
        $rtnCode = (int) $request->input('RtnCode', 0);

        $order = null;
        if ($orderNo) {
            $order = RenewalOrder::where('order_no', $orderNo)
                ->with('plan', 'user')
                ->first();
        }

        // 以資料庫中的訂單狀態為準，不信任瀏覽器傳入的 RtnCode
        $success = $order && $order->status === 'paid';

        return view('ecpay.result', compact('success', 'order', 'rtnCode'));
    }
}
