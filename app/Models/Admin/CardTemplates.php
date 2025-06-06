<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class CardTemplates extends Model
{
    public $table = 'card_templates';

    public $fillable = [
        'name',
        'description',
        'preview_image',
        'template_schema'
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'preview_image' => 'string'
    ];

    public static array $rules = [
        'name' => 'nullable',
        'description' => 'nullable',
        'preview_image' => 'nullable',
        'template_schema' => 'nullable'
    ];

    
}
