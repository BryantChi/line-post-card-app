<?php

namespace App\Models\Admin;

use App\Models\BusinessCard;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Class CaseInfo
 * @package App\Models\Admin
 * @version October 12, 2024, 5:48 pm UTC
 *
 * @property string $name
 * @property string $image
 * @property string $bs_card
 * @property string $case_content
 * @property boolean $status
 */
class CaseInfo extends EloquentModel
{
    use SoftDeletes;


    public $table = 'case_infos';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'name',
        'case_content',
        'image',
        'bs_card',
        'status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'image' => 'json',
        'case_content' => 'string',
        'bs_card' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'nullable|string|max:255',
        'case_content' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'bs_card' => 'nullable|string|max:255',
        'status' => 'boolean'
    ];

    // 取得此案例所使用的商業名片
    public function businessCard()
    {
        return $this->belongsTo(BusinessCard::class, 'bs_card');
    }

}
