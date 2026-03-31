<?php

namespace App\Http\Controllers;

use App\Models\RenewalOrder;
use App\Models\SubscriptionPlan;
use App\Services\EcpayService;
use App\Services\RenewalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laracasts\Flash\Flash;

class RenewalController extends Controller
{
    public function __construct(
        protected RenewalService $renewalService,
        protected EcpayService $ecpayService
    ) {}

    /**
     * 續約主頁：顯示到期資訊 + 方案卡片 + 選擇付款方式
     */
    public function index()
    {
        $user = Auth::user();
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $pendingOrder = RenewalOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('plan')
            ->latest()
            ->first();
        $daysUntilExpiry = $user->expires_at ? now()->diffInDays($user->expires_at, false) : null;
        return view('renewal.index', compact('user', 'plans', 'pendingOrder', 'daysUntilExpiry'));
    }

    /**
     * 建立續約訂單（POST）
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'plan_id'        => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|in:ecpay_credit,bank_transfer',
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        try {
            $order = $this->renewalService->createOrder($user, $plan, $request->payment_method);
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
            return redirect()->route('renewal.index');
        }

        if ($request->payment_method === 'ecpay_credit') {
            return redirect()->route('renewal.ecpay-redirect', $order->id);
        }

        return redirect()->route('renewal.bank-transfer', $order->id);
    }

    /**
     * ECPay 付款跳轉（顯示自動送出表單）
     */
    public function ecpayRedirect($orderId)
    {
        $user = Auth::user();
        $order = RenewalOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('plan')
            ->firstOrFail();

        if ($order->payment_method !== 'ecpay_credit') {
            Flash::error('此訂單的付款方式不是信用卡');
            return redirect()->route('renewal.index');
        }

        $formHtml = $this->ecpayService->buildCheckoutForm($order);

        return view('renewal.ecpay_redirect', compact('formHtml'));
    }

    /**
     * 匯款資訊頁面
     */
    public function bankTransfer($orderId)
    {
        $user = Auth::user();
        $order = RenewalOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('plan')
            ->firstOrFail();

        return view('renewal.bank_transfer', compact('order'));
    }

    /**
     * 上傳匯款收據（POST）
     */
    public function uploadReceipt(Request $request, $orderId)
    {
        $request->validate([
            'receipt_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = Auth::user();
        $order = RenewalOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $file = $request->file('receipt_image');
        $path = $file->store('receipts', 'local');

        $order->update(['receipt_image' => $path]);

        Flash::success('收據已上傳，請等待管理員確認');
        return redirect()->route('renewal.bank-transfer', $order->id);
    }

    /**
     * 信用卡付款結果頁（GET，需要登入）
     * 由 /ecpay/return POST-Redirect-Get 後到達此頁
     * 此時 session 已恢復，可正常顯示後台 layout
     */
    public function paymentResult(Request $request)
    {
        $orderNo = $request->input('order_no');
        $order   = null;

        if ($orderNo) {
            $order = RenewalOrder::where('order_no', $orderNo)
                ->where('user_id', Auth::id())   // 防止 IDOR：只能查自己的訂單
                ->with('plan', 'user')
                ->first();
        }

        // 以資料庫訂單狀態為準，不信任 URL 參數
        $success = $order && $order->status === 'paid';

        return view('renewal.payment_result', compact('success', 'order'));
    }

    /**
     * 訂單歷史
     */
    public function history()
    {
        $user = Auth::user();
        $orders = RenewalOrder::where('user_id', $user->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('renewal.history', compact('orders'));
    }

    /**
     * 單筆訂單詳情
     */
    public function orderDetail($orderId)
    {
        $user = Auth::user();
        $order = RenewalOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->with(['plan', 'transactions'])
            ->firstOrFail();

        return view('renewal.order_detail', compact('order'));
    }

    /**
     * 取消訂單（POST）
     */
    public function cancelOrder($orderId)
    {
        $user = Auth::user();
        $order = RenewalOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $this->renewalService->cancelOrder($order);
            Flash::success('訂單已取消');
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
        }

        return redirect()->route('renewal.history');
    }
}
