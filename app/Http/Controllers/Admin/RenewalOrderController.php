<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Admin\RefundRequest;
use App\Models\RenewalOrder;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\PaymentGateways\PaymentGatewayManager;
use App\Services\RefundService;
use App\Services\RenewalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Flash;

class RenewalOrderController extends AppBaseController
{
    /** @var RenewalService */
    protected $renewalService;

    /** @var PaymentGatewayManager */
    protected $paymentManager;

    public function __construct(RenewalService $renewalService, PaymentGatewayManager $paymentManager)
    {
        $this->renewalService = $renewalService;
        $this->paymentManager = $paymentManager;
    }

    /**
     * 訂單列表，支援 keyword、status、user_id 篩選
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            // 超級管理員看全部
            $subUserIds = [];
            $query = RenewalOrder::with(['user', 'plan', 'createdBy']);
        } else {
            // 主帳號只看自己子帳號的訂單
            $subUserIds = $currentUser->subUsers()->pluck('id')->toArray();
            $query = RenewalOrder::with(['user', 'plan', 'createdBy'])
                ->whereIn('user_id', $subUserIds);
        }

        // keyword 篩選（訂單編號或會員名稱）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('order_no', 'like', '%' . $keyword . '%')
                  ->orWhereHas('user', function ($uq) use ($keyword) {
                      $uq->where('name', 'like', '%' . $keyword . '%');
                  });
            });
        }

        // status 篩選
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // user_id 篩選（管理員用）
        if ($request->filled('user_id')) {
            // 主帳號只能篩選自己子帳號的 user_id（防禦性 IDOR 保護）
            if (!$currentUser->isSuperAdmin() && !in_array((int)$request->user_id, $subUserIds)) {
                abort(403, '無權限篩選此用戶的訂單');
            }
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->all());

        // 取得可篩選的子帳號列表
        if ($currentUser->isSuperAdmin()) {
            $subUsers = User::where('role', 'sub_user')->orderBy('name')->get();
        } else {
            $subUsers = $currentUser->subUsers()->orderBy('name')->get();
        }

        return view('admin.renewal_orders.index', compact('orders', 'subUsers'));
    }

    /**
     * 訂單詳情
     */
    public function show($id)
    {
        $order = $this->getOrderForCurrentUser($id);

        $order->load(['user', 'plan', 'createdBy', 'transactions']);

        return view('admin.renewal_orders.show', compact('order'));
    }

    /**
     * 為子帳號建立訂單的表單（GET）
     */
    public function createForUser($userId)
    {
        $subUser = $this->getSubUserForCurrentUser($userId);
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();

        // 後台代辦訂單允許的付款方式 = 啟用中的線上金流 + 銀行轉帳 + 現金
        $paymentOptions = [];
        foreach ($this->paymentManager->activeDrivers() as $driver) {
            $paymentOptions[] = [
                'value' => $driver->paymentMethodValue(),
                'label' => $driver->label(),
            ];
        }
        $paymentOptions[] = ['value' => 'cash', 'label' => '現金'];

        return view('admin.renewal_orders.create_for_user', compact('subUser', 'plans', 'paymentOptions'));
    }

    /**
     * 儲存管理員代辦訂單（POST）
     */
    public function storeForUser(Request $request, $userId)
    {
        $subUser = $this->getSubUserForCurrentUser($userId);

        // 後台代辦訂單:允許所有啟用中的金流 + cash (現金)
        $activeMethods = array_map(
            fn($d) => $d->paymentMethodValue(),
            $this->paymentManager->activeDrivers()
        );
        $allowedMethods = array_unique(array_merge($activeMethods, ['cash']));

        $validated = $request->validate([
            'plan_id'        => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|in:' . implode(',', $allowedMethods),
            'admin_note'     => 'nullable|string|max:500',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        try {
            $order = $this->renewalService->createOrder(
                $subUser,
                $plan,
                $validated['payment_method'],
                Auth::user(),
                $request->filled('admin_note') ? $validated['admin_note'] : null
            );

            Flash::success('訂單已建立，訂單編號：' . $order->order_no);
            return redirect(route('admin.renewalOrders.show', $order->id));
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * 確認付款（PATCH）
     */
    public function confirm(Request $request, $id)
    {
        $order = $this->getOrderForCurrentUser($id);

        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        try {
            $this->renewalService->confirmOfflinePayment($order, $request->admin_note);

            Flash::success('已確認付款，用戶到期日已更新');
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
        }

        return redirect(route('admin.renewalOrders.show', $id));
    }

    /**
     * 取消訂單（PATCH）
     */
    public function cancel($id)
    {
        $order = $this->getOrderForCurrentUser($id);

        try {
            $this->renewalService->cancelOrder($order);

            Flash::success('訂單已取消');
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
        }

        return redirect(route('admin.renewalOrders.show', $id));
    }

    /**
     * 手動延長到期日表單（GET）
     */
    public function showManualExtend($userId)
    {
        $subUser = $this->getSubUserForCurrentUser($userId);

        return view('admin.renewal_orders.manual_extend', compact('subUser'));
    }

    /**
     * 處理手動延長（POST）
     */
    public function manualExtend(Request $request, $userId)
    {
        $subUser = $this->getSubUserForCurrentUser($userId);

        $validated = $request->validate([
            'days'   => 'required|integer|min:1|max:3650',
            'reason' => 'required|string|max:500',
        ]);

        $result = $this->renewalService->extendUserExpiration($subUser, $validated['days']);

        if ($result) {
            Flash::success('已成功延長 ' . $validated['days'] . ' 天，新到期日：' . $subUser->fresh()->expires_at->format('Y-m-d'));
        } else {
            Flash::warning('延長失敗：帳號已過期太久，新的到期日仍在過去，請聯繫管理員手動調整或選擇更長的方案天數');
        }

        return redirect(route('sub-users.edit', $subUser->id));
    }

    /**
     * 取得當前用戶有權存取的訂單
     */
    private function getOrderForCurrentUser($id): RenewalOrder
    {
        $currentUser = Auth::user();
        $order = RenewalOrder::findOrFail($id);

        if ($currentUser->isSuperAdmin()) {
            return $order;
        }

        // 主帳號只能存取自己子帳號的訂單
        $subUserIds = $currentUser->subUsers()->pluck('id')->toArray();
        if (!in_array($order->user_id, $subUserIds)) {
            abort(403, '您沒有權限存取此訂單');
        }

        return $order;
    }

    /**
     * 退款表單頁。
     */
    public function refundForm($id)
    {
        $order = RenewalOrder::with(['plan', 'user', 'transactions'])->findOrFail($id);
        $this->authorizeRefund($order);

        if (!$order->canBeRefunded()) {
            Flash::error('此訂單目前不可退款');
            return redirect()->route('admin.renewalOrders.show', $order->id);
        }

        // 不在頁面 GET 載入時呼叫金流查詢 API(會卡頁)。
        // 「自動判斷」改在送出退款時由 RefundService 執行(action 為空 → resolveRefundAction);
        // 退款表單下拉預設「自動判斷」,管理員亦可手動指定 refund/void。
        $refundableAmount = $order->refundableAmount();
        $refunds = $order->transactions->where('type', 'refund');

        return view('admin.renewal_orders.refund', compact('order', 'refundableAmount', 'refunds'));
    }

    /**
     * 執行退款。
     */
    public function refund($id, RefundRequest $request, RefundService $refundService)
    {
        $order = RenewalOrder::findOrFail($id);
        $this->authorizeRefund($order);

        if ($request->integer('amount') > $order->refundableAmount()) {
            Flash::error('退款金額超過可退餘額');
            return redirect()->route('admin.renewalOrders.refundForm', $order->id);
        }

        $result = $refundService->refund(
            $order,
            $request->integer('amount'),
            $request->input('reason'),
            $request->boolean('rollback_expiration'),
            $request->input('action') ?: null
        );

        $result['success']
            ? Flash::success($result['message'])
            : Flash::error('退款失敗:' . $result['message']);

        return redirect()->route('admin.renewalOrders.show', $order->id);
    }

    /**
     * 退款權限歸屬:超管不限;主帳號只能退自己旗下子帳號的訂單。
     */
    private function authorizeRefund(RenewalOrder $order): void
    {
        $actor = Auth::user();
        if ($actor->isSuperAdmin()) {
            return;
        }
        if ($actor->isMainUser() && $order->user && (int) $order->user->parent_id === (int) $actor->id) {
            return;
        }
        abort(403, '無權對此訂單退款');
    }

    /**
     * 取得當前用戶有權管理的子帳號
     */
    private function getSubUserForCurrentUser($userId): User
    {
        $currentUser = Auth::user();
        $subUser = User::where('id', $userId)->where('role', 'sub_user')->firstOrFail();

        if ($currentUser->isSuperAdmin()) {
            return $subUser;
        }

        // 主帳號只能管理自己的子帳號
        if ($subUser->parent_id !== $currentUser->id) {
            abort(403, '您沒有權限管理此子帳號');
        }

        return $subUser;
    }
}
