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
        'logged_out_at',
        'session_duration',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
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

    /**
     * 記錄用戶登出
     */
    public function recordLogout()
    {
        $this->logged_out_at = now();

        // 計算線上時長（秒）
        if ($this->logged_in_at) {
            $this->session_duration = $this->logged_out_at->diffInSeconds($this->logged_in_at);
        }

        $this->save();

        return $this;
    }

    /**
     * 取得格式化的線上時長
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->session_duration) {
            return '-';
        }

        $hours = floor($this->session_duration / 3600);
        $minutes = floor(($this->session_duration % 3600) / 60);
        $seconds = $this->session_duration % 60;

        if ($hours > 0) {
            return sprintf('%d 小時 %d 分 %d 秒', $hours, $minutes, $seconds);
        } elseif ($minutes > 0) {
            return sprintf('%d 分 %d 秒', $minutes, $seconds);
        } else {
            return sprintf('%d 秒', $seconds);
        }
    }

    /**
     * Scope: 查詢未登出的紀錄
     */
    public function scopeActive($query)
    {
        return $query->whereNull('logged_out_at');
    }
}
