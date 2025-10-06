<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessCardStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_card_id',
        'date',
        'views',
        'shares',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
        'shares' => 'integer',
    ];

    /**
     * 關聯到 BusinessCard
     */
    public function businessCard()
    {
        return $this->belongsTo(BusinessCard::class);
    }

    /**
     * Scope: 本週數據
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope: 本月數據
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('date', Carbon::now()->year)
                     ->whereMonth('date', Carbon::now()->month);
    }

    /**
     * Scope: 指定月份數據
     */
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    /**
     * Scope: 指定週數據
     */
    public function scopeForWeek($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: 自訂日期區間
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * 記錄點閱 (增加當日點閱數)
     */
    public static function recordView($businessCardId, $date = null)
    {
        $date = $date ?? Carbon::today();

        return static::updateOrCreate(
            [
                'business_card_id' => $businessCardId,
                'date' => $date,
            ],
            []
        )->increment('views');
    }

    /**
     * 記錄分享 (增加當日分享數)
     */
    public static function recordShare($businessCardId, $date = null)
    {
        $date = $date ?? Carbon::today();

        return static::updateOrCreate(
            [
                'business_card_id' => $businessCardId,
                'date' => $date,
            ],
            []
        )->increment('shares');
    }
}
