<?php

namespace App\Models\Admin;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Class SeoSetting
 * @package App\Models\Admin
 * @version September 24, 2024, 5:04 pm UTC
 *
 * @property string $url
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property string $og_title
 * @property string $og_description
 * @property string $og_site_name
 * @property string $ga_header
 * @property string $ga_body
 */
class SeoSetting extends EloquentModel
{
    use SoftDeletes;


    public $table = 'seo_setting_infos';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'url',
        'title',
        'description',
        'keywords',
        'og_title',
        'og_description',
        'og_site_name',
        'ga_header',
        'ga_body'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'url' => 'nullable',
        'title' => 'nullable',
        'description' => 'nullable',
        'keywords' => 'nullable',
        'og_title' => 'nullable',
        'og_description' => 'nullable',
        'og_site_name' => 'nullable',
        'ga_header' => 'nullable',
        'ga_body' => 'nullable'
    ];


}
