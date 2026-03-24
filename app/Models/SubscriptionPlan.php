<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'integer',
        'duration_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function renewalOrders()
    {
        return $this->hasMany(RenewalOrder::class, 'plan_id');
    }

    /**
     * 只取啟用中的方案
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
