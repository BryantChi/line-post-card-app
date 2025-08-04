<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class LesssonInfo extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public $table = 'lesson_infos';

    public $fillable = [
        'title',
        'content',
        'image',
        'views',
        'num',
        'status'
    ];

    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'views' => 'integer',
        'num' => 'integer',
        'status' => 'boolean'
    ];

    protected $dates = ['deleted_at'];

    public static array $rules = [
        'title' => 'nullable',
        'content' => 'nullable',
        'image' => 'nullable|image|max:1024|mimes:jpg,jpeg,png,gif,webp',
        'views' => 'nullable',
        'num' => 'nullable',
        'status' => 'nullable|boolean'
    ];


}
