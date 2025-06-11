<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BusinessCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'profile_image',
        'content',
        'flex_json',
        'uuid',
        'active',
    ];

    protected $casts = [
        'flex_json' => 'array',
        'active' => 'boolean',
    ];

    /**
     * 模型引導啟動時自動產生UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * 取得數位名片所屬用戶
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 取得所有氣泡卡片
     */
    public function bubbles()
    {
        return $this->hasMany(CardBubble::class, 'card_id')->orderBy('order');
    }

    /**
     * 取得啟用狀態的氣泡卡片
     */
    public function activeBubbles()
    {
        return $this->bubbles()->where('active', true);
    }

    /**
     * 檢查用戶是否有權限編輯此卡片
     */
    public function canBeEditedBy($user)
    {
        if (!$user) return false;

        // 超級管理員可以編輯所有卡片
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 主帳號可以編輯自己的卡片和子帳號的卡片
        if ($user->isMainUser()) {
            return $this->user_id === $user->id ||
                  ($this->user && $this->user->parent_id === $user->id);
        }

        // 子帳號只能編輯自己的卡片
        return $this->user_id === $user->id;
    }

    /**
     * 檢查用戶是否有權限檢視此卡片
     */
    public function canBeViewedBy($user)
    {
        if (!$user) return false;

        // 檢視權限通常比編輯權限寬鬆一些
        return $this->canBeEditedBy($user);
    }

    /**
     * 更新 Flex JSON 基於所有啟用的氣泡卡片
     */
    public function updateFlexJson()
    {
        // 獲取所有啟用的氣泡卡片
        $bubbles = $this->activeBubbles()->orderBy('order')->get();

        // 如果沒有氣泡，清空 flex_json
        if ($bubbles->isEmpty()) {
            $this->flex_json = null;
            $this->save();
            return;
        }

        // 建立 Carousel 容器
        $carouselJson = [
            'type' => 'carousel',
            'contents' => []
        ];

        // 添加每個氣泡的 JSON
        foreach ($bubbles as $bubble) {
            if (is_array($bubble->json_content)) {
                $carouselJson['contents'][] = $bubble->json_content;
            } elseif (is_string($bubble->json_content) && !empty($bubble->json_content)) {
                try {
                    $bubbleJson = json_decode($bubble->json_content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $carouselJson['contents'][] = $bubbleJson;
                    }
                } catch (\Exception $e) {
                    // 忽略無效的 JSON
                }
            }
        }

        // 如果只有一個氣泡，直接使用該氣泡的 JSON
        if (count($carouselJson['contents']) === 1) {
            $this->flex_json = $carouselJson['contents'][0];
        } else {
            $this->flex_json = $carouselJson;
        }

        $this->save();
        return $this->flex_json;
    }

    /**
     * 獲取名片分享 URL
     */
    public function getShareUrl()
    {
        return url('/share/' . $this->uuid);
    }


}
