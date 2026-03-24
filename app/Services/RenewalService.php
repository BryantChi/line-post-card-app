<?php

namespace App\Services;

use App\Models\RenewalOrder;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenewalService
{
    /**
     * 建立續約訂單
     *
     * @param User $user 續約的子帳號
     * @param SubscriptionPlan $plan 選擇的方案
     * @param string $paymentMethod ecpay_credit|bank_transfer|cash
     * @param User|null $createdBy 建立者（null=會員自助，有值=管理員代辦）
     * @throws \Exception 若已有 pending 訂單
     */
    public function createOrder(
        User $user,
        SubscriptionPlan $plan,
        string $paymentMethod,
        ?User $createdBy = null,
        ?string $adminNote = null
    ): RenewalOrder {
        return DB::transaction(function () use ($user, $plan, $paymentMethod, $createdBy, $adminNote) {
            // 在 transaction 內檢查（防止 TOCTOU）
            if (RenewalOrder::where('user_id', $user->id)->where('status', 'pending')->lockForUpdate()->exists()) {
                throw new \Exception('該用戶已有待付款訂單');
            }

            $expireHours = config('renewal.order_expire_hours', 72);

            return RenewalOrder::create([
                'order_no'       => RenewalOrder::generateOrderNo(),
                'user_id'        => $user->id,
                'plan_id'        => $plan->id,
                'created_by'     => $createdBy?->id,
                'amount'         => $plan->price, // 從方案複製金額，不信任前端傳入
                'payment_method' => $paymentMethod,
                'status'         => 'pending',
                'expires_at'     => now()->addHours($expireHours),
                'admin_note'     => $adminNote,
            ]);
        });
    }

    /**
     * 確認離線付款（匯款/現金）
     *
     * @throws \Exception 若訂單已在終態
     */
    public function confirmOfflinePayment(RenewalOrder $order, ?string $adminNote = null): bool
    {
        if ($order->isTerminal()) {
            throw new \Exception('該訂單已在終態，無法再次操作');
        }

        return DB::transaction(function () use ($order, $adminNote) {
            // 使用 lockForUpdate 鎖定訂單（冪等保護）
            $lockedOrder = RenewalOrder::lockForUpdate()->find($order->id);

            if ($lockedOrder->isTerminal()) {
                throw new \Exception('該訂單已在終態，無法再次操作');
            }

            // 更新訂單狀態
            $lockedOrder->update([
                'status'     => 'paid',
                'paid_at'    => now(),
                'admin_note' => $adminNote,
            ]);

            // 建立交易紀錄
            PaymentTransaction::create([
                'order_id'       => $lockedOrder->id,
                'transaction_no' => 'MANUAL-' . now()->format('YmdHis') . '-' . $lockedOrder->id,
                'payment_method' => $lockedOrder->payment_method,
                'amount'         => $lockedOrder->amount,
                'status'         => 'success',
                'note'           => $adminNote,
            ]);

            // 延長用戶到期日
            $lockedOrder->load(['user', 'plan']);
            $lockedOrder->user->extendExpiration($lockedOrder->plan->duration_days);

            Log::info('離線付款確認', [
                'order_no' => $lockedOrder->order_no,
                'user_id'  => $lockedOrder->user_id,
                'amount'   => $lockedOrder->amount,
            ]);

            return true;
        });
    }

    /**
     * 取消訂單
     *
     * @throws \Exception 若訂單已在終態
     */
    public function cancelOrder(RenewalOrder $order): bool
    {
        if ($order->isTerminal()) {
            throw new \Exception('訂單已在終態，無法取消');
        }

        return DB::transaction(function () use ($order) {
            $fresh = RenewalOrder::lockForUpdate()->find($order->id);

            if ($fresh->isTerminal()) {
                return false;
            }

            $fresh->update(['status' => 'cancelled']);
            Log::info('訂單已取消', ['order_no' => $fresh->order_no, 'order_id' => $fresh->id]);
            return true;
        });
    }

    /**
     * 延長用戶到期日（手動，不建立訂單）
     * 使用 User::extendExpiration()
     */
    public function extendUserExpiration(User $user, int $days): bool
    {
        return $user->extendExpiration($days);
    }

    /**
     * 清理逾期未付訂單（pending 超過設定時間）
     * 將符合條件的訂單狀態改為 expired
     *
     * @return int 被清理的訂單數量
     */
    public function expireStaleOrders(): int
    {
        $expireHours = config('renewal.order_expire_hours', 72);
        $threshold   = now()->subHours($expireHours);

        $count = RenewalOrder::where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->update(['status' => 'expired']);

        return $count;
    }
}
