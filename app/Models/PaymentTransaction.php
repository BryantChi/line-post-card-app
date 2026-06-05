<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_no',
        'payment_method',
        'amount',
        'status',
        'gateway_response',
        'note',
        'type',
        'parent_transaction_id',
        'refund_action',
    ];

    protected $casts = [
        'amount' => 'integer',
        'gateway_response' => 'array',
    ];

    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND  = 'refund';

    public function order()
    {
        return $this->belongsTo(RenewalOrder::class, 'order_id');
    }

    /** 退款交易指向的原付款交易 */
    public function parent()
    {
        return $this->belongsTo(PaymentTransaction::class, 'parent_transaction_id');
    }

    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }
}
