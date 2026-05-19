<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    public const TIER_BASIC    = 'basic';
    public const TIER_ADVANCED = 'advanced';
    public const TIER_BUSINESS = 'business';

    public const TIER_OPTIONS = [
        self::TIER_BASIC    => 'BASIC 初級方案',
        self::TIER_ADVANCED => 'ADVANCED 進階方案',
        self::TIER_BUSINESS => 'BUSINESS 商業方案',
    ];

    protected $fillable = [
        'name',
        'plan_tier',
        'page_limit',
        'icon',
        'description',
        'target_audience',
        'price',
        'duration_days',
        'active',
        'is_popular',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'active'        => 'boolean',
        'is_popular'    => 'boolean',
        'is_featured'   => 'boolean',
        'price'         => 'integer',
        'duration_days' => 'integer',
        'page_limit'    => 'integer',
        'sort_order'    => 'integer',
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

    /**
     * 依層級篩選
     */
    public function scopeByTier($query, string $tier)
    {
        return $query->where('plan_tier', $tier);
    }

    /**
     * 平均每月金額（依據訂閱天數換算為 30 天/月）
     */
    public function getAveragePerMonth(): int
    {
        if (!$this->duration_days || $this->duration_days <= 0) {
            return 0;
        }
        return (int) round($this->price / ($this->duration_days / 30));
    }

    /**
     * 訂閱期顯示文字（例：1 個月 / 6 個月 / 1 年 / 2 年 / 3 年）
     */
    public function getDurationLabel(): string
    {
        $days = (int) $this->duration_days;
        if ($days >= 365 && $days % 365 === 0) {
            return ($days / 365) . ' 年';
        }
        if ($days >= 30 && $days % 30 === 0) {
            return ($days / 30) . ' 個月';
        }
        return $days . ' 天';
    }

    /**
     * 層級中文名稱
     */
    public function getTierLabel(): ?string
    {
        return $this->plan_tier ? (self::TIER_OPTIONS[$this->plan_tier] ?? null) : null;
    }
}
