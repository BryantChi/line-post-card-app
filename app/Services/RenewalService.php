<?php

namespace App\Services;

use App\Mail\RenewalOrderCreated;
use App\Mail\RenewalPaymentConfirmed;
use App\Models\PaymentTransaction;
use App\Models\RenewalOrder;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $newOrder = DB::transaction(function () use ($user, $plan, $paymentMethod, $createdBy, $adminNote) {
            // 先鎖定 user row,序列化同一用戶的並發建單請求。
            // (不能只靠下方 pending 查詢的 lockForUpdate：當尚無 pending row 時，
            //  空集合的 FOR UPDATE 無法阻擋另一交易插入新 row，仍會產生重複 pending 訂單)
            User::whereKey($user->id)->lockForUpdate()->first();

            // 鎖定 user 後再檢查（此時同一用戶的請求已序列化，防止 TOCTOU）
            if (RenewalOrder::where('user_id', $user->id)->where('status', 'pending')->exists()) {
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

        // 匯款訂單：通知管理員審核
        if ($newOrder->payment_method === 'bank_transfer') {
            $adminEmail = config('mail.admin_email', config('mail.from.address'));
            try {
                Mail::to($adminEmail)->send(new RenewalOrderCreated($newOrder));
            } catch (\Exception $e) {
                Log::warning('發送管理員通知 Email 失敗', ['order_no' => $newOrder->order_no, 'error' => $e->getMessage()]);
            }
        }

        return $newOrder;
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

        // 用參考變數在 closure 外捕捉已確認的訂單，供 transaction 後發送 Email 使用
        $confirmedOrder = null;

        DB::transaction(function () use ($order, $adminNote, &$confirmedOrder) {
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

            // 捕捉到外部變數（transaction commit 後才發 Email，避免 DB rollback 後 Email 已寄出）
            $confirmedOrder = $lockedOrder;
        });

        // Email 在 transaction commit 之後發送（失敗只記 Log，不影響主流程）
        if ($confirmedOrder) {
            try {
                Mail::to($confirmedOrder->user->email)->send(new RenewalPaymentConfirmed($confirmedOrder));
            } catch (\Exception $e) {
                Log::warning('發送付款確認 Email 失敗', ['order_no' => $confirmedOrder->order_no, 'error' => $e->getMessage()]);
            }
        }

        return true;
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

            // 二次鎖定後確認：並發情況下可能已變為終態（拋出例外讓 controller 正確顯示錯誤訊息）
            if ($fresh->isTerminal()) {
                throw new \Exception('訂單狀態已變更，無法取消');
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
        // 使用 expires_at 欄位進行比較，語意更精確且能感知未來可能的人工延長
        $count = RenewalOrder::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        return $count;
    }
}
