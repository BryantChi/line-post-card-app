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
        'refunded_amount',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'integer',
        'refunded_amount' => 'integer',
    ];

    // 終態：不可再變更的狀態
    const TERMINAL_STATUSES = ['paid', 'cancelled', 'expired', 'refunded'];

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

    /** 原始付款交易(成功) */
    public function payments()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id')
            ->where('type', 'payment')->where('status', 'success');
    }

    /** 退款交易(成功) */
    public function refunds()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id')
            ->where('type', 'refund')->where('status', 'success');
    }

    /** 此訂單是否可退款 */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['paid', 'partially_refunded'], true)
            && $this->refundableAmount() > 0;
    }

    /** 尚可退款的餘額 = 原金額 - 已退金額 */
    public function refundableAmount(): int
    {
        return max(0, (int) $this->amount - (int) $this->refunded_amount);
    }

    /**
     * 檢查訂單是否已在終態（不可變更）
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    /**
     * 將 payment_method 欄位轉換為人類可讀的標籤 (供 Blade 顯示用)
     * 同時涵蓋啟用中與已停用的歷史值
     */
    public function getPaymentMethodLabel(): string
    {
        $methodMap = config('payment.method_to_gateway', []);

        if (isset($methodMap[$this->payment_method])) {
            try {
                $driver = app(\App\Services\PaymentGateways\PaymentGatewayManager::class)
                    ->driver($methodMap[$this->payment_method]);
                return $driver->label();
            } catch (\Throwable $e) {
                // 落空交由下面的 static map
            }
        }

        return match ($this->payment_method) {
            'ecpay_credit'    => '信用卡(綠界金流)',
            'newebpay_credit' => '信用卡(藍新金流)',
            'bank_transfer'   => '銀行轉帳',
            'cash'            => '現金',
            default           => $this->payment_method,
        };
    }

    /**
     * 判斷此訂單的付款方式是否為需要跳轉的線上信用卡
     */
    public function isRedirectPayment(): bool
    {
        return in_array($this->payment_method, ['ecpay_credit', 'newebpay_credit'], true);
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
