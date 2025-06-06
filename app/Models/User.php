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
        'expires_at',
        'active',
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
    ];

    public static $rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:6', 'confirmed']
    ];

    public static $update_rules = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['string', 'email', 'max:255'],
        'password' => ['nullable','string', 'min:6', 'confirmed']
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
     * 取得用戶的所有電子名片
     */
    public function businessCards()
    {
        return $this->hasMany(BusinessCards::class);
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
}
