<?php

namespace App\Http\Controllers;

use App\Models\RenewalOrder;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Services\PaymentGateways\PaymentGatewayManager;
use App\Services\RenewalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laracasts\Flash\Flash;

class RenewalController extends Controller
{
    public function __construct(
        protected RenewalService $renewalService,
        protected PaymentGatewayManager $paymentManager
    ) {}

    /**
     * 續約主頁:顯示到期資訊 + 方案卡片 + 選擇付款方式
     */
    public function index()
    {
        if (!SystemSetting::canUserAccessRenewal(Auth::id())) {
            return view('renewal.disabled');
        }

        $user = Auth::user();
        $plans = SubscriptionPlan::active()
            ->orderBy('sort_order')
            ->orderBy('duration_days')
            ->orderBy('id')
            ->get();

        // 依層級分組(無 plan_tier 的方案歸入 'other',前台不渲染於分層卡片)
        $plansByTier = $plans->groupBy(function ($plan) {
            return $plan->plan_tier ?: 'other';
        });

        // 提供前台依固定順序渲染的層級鍵
        $tierOrder = array_keys(SubscriptionPlan::TIER_OPTIONS);

        $pendingOrder = RenewalOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('plan')
            ->latest()
            ->first();
        $daysUntilExpiry = $user->expires_at ? now()->diffInDays($user->expires_at, false) : null;

        // 系統設定:費用 / 保留天數 (前台備註用)
        $designFee       = SystemSetting::getFirstTimeDesignFee();
        $reactivationFee = SystemSetting::getReactivationSetupFee();
        $retentionDays   = SystemSetting::getCardRetentionDays();

        // 取得啟用中的金流(供前台付款方式下拉動態渲染)
        $paymentOptions = $this->buildPaymentOptions();
        $defaultPaymentMethod = $this->buildDefaultPaymentMethod($paymentOptions);

        // 續約開放窗口:僅在到期前 N 天內(含已過期)才開放建單,未達標準只能查看紀錄
        $canRenewNow = $user->isWithinRenewalWindow();
        $renewalOpenDays = (int) config('renewal.open_days_before_expiry', 30);

        return view('renewal.index', compact(
            'user',
            'plans',
            'plansByTier',
            'tierOrder',
            'pendingOrder',
            'daysUntilExpiry',
            'designFee',
            'reactivationFee',
            'retentionDays',
            'paymentOptions',
            'defaultPaymentMethod',
            'canRenewNow',
            'renewalOpenDays'
        ));
    }

    /**
     * 建立續約訂單 (POST)
     */
    public function createOrder(Request $request)
    {
        if (!SystemSetting::canUserAccessRenewal(Auth::id())) {
            Flash::error('續約功能目前暫停開放,請聯繫管理員');
            return redirect()->route('renewal.index');
        }

        // 後端再次把關續約開放窗口(防止繞過前端直接 POST)
        if (!Auth::user()->isWithinRenewalWindow()) {
            Flash::error('目前尚未開放續約,將於到期前 ' . config('renewal.open_days_before_expiry', 30) . ' 天開放');
            return redirect()->route('renewal.index');
        }

        $allowedMethods = collect($this->buildPaymentOptions())->pluck('value')->all();

        $request->validate([
            'plan_id'        => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|in:' . implode(',', $allowedMethods),
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        try {
            $order = $this->renewalService->createOrder($user, $plan, $request->payment_method);
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
            return redirect()->route('renewal.index');
        }

        $gateway = $this->paymentManager->driverForPaymentMethod($order->payment_method);

        if ($gateway->isRedirect()) {
            return redirect()->route('renewal.payment-redirect', $order->id);
        }

        // bank_transfer 走匯款流程
        return redirect()->route('renewal.bank-transfer', $order->id);
    }

    /**
     * 信用卡付款跳轉頁 (通用,依訂單的 payment_method 取對應 driver)
     */
    public function paymentRedirect($orderId)
    {
        $user = Auth::user();
        $order = RenewalOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['plan', 'user'])
            ->firstOrFail();

        try {
            $gateway = $this->paymentManager->driverForPaymentMethod($order->payment_method);
        } catch (\InvalidArgumentException $e) {
            Flash::error('此訂單的付款方式無效');
            return redirect()->route('renewal.index');
        }

        if (!$gateway->isRedirect()) {
            Flash::error('此訂單的付款方式不需要跳轉');
            return redirect()->route('renewal.index');
        }

        if (!$gateway->isActive()) {
            Flash::error('此金流已停用,請取消訂單後重新建立');
            return redirect()->route('renewal.history');
        }

        $formHtml = $gateway->buildCheckoutForm($order);
        $gatewayLabel = $gateway->label();

        return view('renewal.payment_redirect', compact('formHtml', 'gatewayLabel'));
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
     * 上傳匯款收據 (POST)
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

        Flash::success('收據已上傳,請等待管理員確認');
        return redirect()->route('renewal.bank-transfer', $order->id);
    }

    /**
     * 信用卡付款結果頁 (GET,需要登入)
     * 由 /ecpay/return 或 /newebpay/return POST-Redirect-Get 後到達此頁
     * 此時 session 已恢復,可正常顯示後台 layout
     */
    public function paymentResult(Request $request)
    {
        $orderNo = $request->input('order_no');
        $order   = null;

        if ($orderNo) {
            $order = RenewalOrder::where('order_no', $orderNo)
                ->where('user_id', Auth::id())   // 防止 IDOR:只能查自己的訂單
                ->with('plan', 'user')
                ->first();
        }

        // 以資料庫訂單狀態為準,不信任 URL 參數
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
     * 取消訂單 (POST)
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

    /**
     * 組成前台付款方式下拉的 options
     * @return array<int,array{value:string,label:string,is_redirect:bool}>
     */
    private function buildPaymentOptions(): array
    {
        $options = [];
        foreach ($this->paymentManager->activeDrivers() as $driver) {
            $options[] = [
                'value'       => $driver->paymentMethodValue(),
                'label'       => $driver->label(),
                'is_redirect' => $driver->isRedirect(),
            ];
        }
        return $options;
    }

    private function buildDefaultPaymentMethod(array $paymentOptions): ?string
    {
        $defaultDriver = $this->paymentManager->default();
        if ($defaultDriver) {
            $defaultValue = $defaultDriver->paymentMethodValue();
            foreach ($paymentOptions as $opt) {
                if ($opt['value'] === $defaultValue) {
                    return $defaultValue;
                }
            }
        }
        return $paymentOptions[0]['value'] ?? null;
    }
}
