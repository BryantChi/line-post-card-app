<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessCards extends Model
{
    public $table = 'business_cards';

    public $fillable = [
        'user_id',
        'template_id',
        'title',
        'subtitle',
        'profile_image',
        'content',
        'flex_json'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'template_id' => 'integer',
        'title' => 'string',
        'subtitle' => 'string',
        'profile_image' => 'json',
    ];

    public static array $rules = [
        'user_id' => 'nullable',
        'template_id' => 'required|exists:card_templates,id',
        'title' => 'required|string|max:255',
        'subtitle' => 'nullable|string|max:255',
        'profile_image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,gif,webp',
        'content' => 'nullable|string',
        'flex_json' => 'nullable'
    ];

    // 關聯：此名片屬於哪個使用者
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 關聯：使用的模板
    public function template()
    {
        return $this->belongsTo(CardTemplates::class, 'template_id');
    }
}
