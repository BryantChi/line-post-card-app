<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'logged_in_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    /**
     * 取得所屬用戶
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: 依日期範圍篩選
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('logged_in_at', [$startDate, $endDate]);
    }

    /**
     * 記錄用戶登入
     */
    public static function recordLogin($userId, $ipAddress = null, $userAgent = null)
    {
        return self::create([
            'user_id' => $userId,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'logged_in_at' => now(),
        ]);
    }
}
