<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RenewalOrder extends Model
{
    protected $fillable = [
        'order_no',
        'user_id',
        'plan_id',
        'created_by',
        'amount',
        'payment_method',
        'status',
        'paid_at',
        'expires_at',
        'receipt_image',
        'admin_note',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'integer',
    ];

    // 終態：不可再變更的狀態
    const TERMINAL_STATUSES = ['paid', 'cancelled', 'expired'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id');
    }

    /**
     * 檢查訂單是否已在終態（不可變更）
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    /**
     * 生成唯一訂單編號（格式：RN + 年月日時分秒 + 4位亂數，共20碼）
     */
    public static function generateOrderNo(): string
    {
        do {
            $orderNo = 'RN' . now()->format('YmdHis') . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('order_no', $orderNo)->exists());

        return $orderNo;
    }
}
