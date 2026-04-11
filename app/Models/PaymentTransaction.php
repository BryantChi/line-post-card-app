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
    ];

    protected $casts = [
        'amount' => 'integer',
        'gateway_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(RenewalOrder::class, 'order_id');
    }
}
