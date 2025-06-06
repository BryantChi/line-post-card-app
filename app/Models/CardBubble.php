<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardBubble extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'card_id',
        'template_id',
        'title',
        'subtitle',
        'image',
        'content',
        'bubble_data',
        'json_content',
        'order',
        'active'
    ];

    protected $casts = [
        'bubble_data' => 'array',
        'json_content' => 'array',
        'active' => 'boolean',
    ];

    /**
     * 氣泡卡片所屬的電子名片
     */
    public function businessCard()
    {
        return $this->belongsTo(BusinessCard::class, 'card_id');
    }

    /**
     * 氣泡卡片使用的模板
     */
    public function template()
    {
        return $this->belongsTo(CardTemplate::class, 'template_id');
    }

    /**
     * 檢查用戶是否有權限編輯此氣泡卡片
     */
    public function canBeEditedBy($user)
    {
        if (!$user) return false;

        // 如果該用戶可以編輯對應的名片，則也可以編輯此氣泡卡片
        return $this->businessCard->canBeEditedBy($user);
    }
}
