<?php

namespace App\Models;

use App\Models\Admin\BusinessCards;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id',
        'max_business_cards',
        'max_card_bubbles',
        'expires_at',
        'active',
        'remarks',
        'signature',
        'login_count',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'expires_at' => 'datetime',
        'active' => 'boolean',
        'max_business_cards' => 'integer',
        'max_card_bubbles' => 'integer',
        'login_count' => 'integer',
        'last_login_at' => 'datetime',
    ];

    public static $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:6', 'confirmed'],
        'remarks' => ['nullable', 'string'],
        'signature' => ['nullable', 'string', 'max:100'],
        'max_business_cards' => ['nullable', 'integer', 'min:1'],
        'max_card_bubbles' => ['nullable', 'integer', 'min:1', 'max:10'],
    ];

    public static $update_rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['string', 'email', 'max:255'],
        'password' => ['nullable','string', 'min:6', 'confirmed'],
        'remarks' => ['nullable', 'string'],
        'signature' => ['nullable', 'string', 'max:100'],
        'max_business_cards' => ['nullable', 'integer', 'min:1'],
        'max_card_bubbles' => ['nullable', 'integer', 'min:1', 'max:10'],
    ];

    /**
     * 取得用戶的所有子帳號
     */
    public function subUsers()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * 取得用戶的父帳號
     */
    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * 取得用戶的所有AI數位名片
     */
    public function businessCards()
    {
        return $this->hasMany(BusinessCards::class);
    }

    /**
     * 取得用戶的所有登入紀錄
     */
    public function loginLogs()
    {
        return $this->hasMany(UserLoginLog::class);
    }

    /**
     * 檢查是否為超級管理員
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * 檢查是否為主帳號
     */
    public function isMainUser()
    {
        return $this->role === 'main_user';
    }

    /**
     * 檢查是否為子帳號
     */
    public function isSubUser()
    {
        return $this->role === 'sub_user';
    }

    /**
     * 檢查帳號是否有效
     */
    public function isActive()
    {
        // 超級管理員和主帳號永遠有效
        if ($this->isSuperAdmin() || $this->isMainUser()) {
            return true;
        }

        // 子帳號需檢查active狀態和過期日期
        return $this->active && ($this->expires_at === null || $this->expires_at > now());
    }

    /**
     * 增加登入次數並更新最後登入時間
     */
    public function incrementLoginCount()
    {
        $this->increment('login_count');
        $this->update(['last_login_at' => now()]);
    }

    /**
     * 取得該用戶的名片數量上限
     * 超級管理員和主帳號無限制,子帳號依設定
     */
    public function getMaxBusinessCards()
    {
        if ($this->isSuperAdmin() || $this->isMainUser()) {
            return PHP_INT_MAX; // 無限制
        }
        return $this->max_business_cards ?? 1;
    }

    /**
     * 取得該用戶的卡片數量上限
     * 超級管理員和主帳號無限制,子帳號依設定(最大10)
     */
    public function getMaxCardBubbles()
    {
        if ($this->isSuperAdmin() || $this->isMainUser()) {
            return 10; // 系統硬性上限
        }
        return min($this->max_card_bubbles ?? 10, 10); // 確保不超過10
    }

    /**
     * 檢查是否可以建立新名片
     */
    public function canCreateBusinessCard()
    {
        $currentCount = $this->businessCards()->count();
        return $currentCount < $this->getMaxBusinessCards();
    }

    /**
     * 檢查特定名片是否可以新增卡片
     */
    public function canAddCardBubble($businessCardId)
    {
        $card = $this->businessCards()->find($businessCardId);
        if (!$card) return false;

        $currentCount = $card->bubbles()->count();
        return $currentCount < $this->getMaxCardBubbles();
    }

    /**
     * 取得剩餘可建立的名片數量
     */
    public function getRemainingBusinessCards()
    {
        $max = $this->getMaxBusinessCards();
        if ($max === PHP_INT_MAX) return '無限制';

        $current = $this->businessCards()->count();
        return max(0, $max - $current);
    }

    /**
     * 取得署名文字
     * 子帳號會繼承父帳號的署名,沒有設定則使用預設值
     */
    public function getSignature()
    {
        // 如果本身有設定署名,直接使用
        if (!empty($this->signature)) {
            return 'Design by ' . $this->signature;
        }

        // 如果是子帳號且沒有設定,使用父帳號的署名
        if ($this->isSubUser() && $this->parentUser) {
            return $this->parentUser->getSignature();
        }

        // 預設署名
        return 'Design by 誠翊資訊網路應用事業';
    }
}
