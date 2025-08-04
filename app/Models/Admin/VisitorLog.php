<?php

namespace App\Models\Admin;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * Class VisitorLog
 * @package App\Models\Admin
 * @version March 14, 2025, 8:30 am UTC
 *
 * @property string $ip
 * @property string $visit_date
 */
class VisitorLog extends EloquentModel
{
    use SoftDeletes;


    public $table = 'visitor_logs';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'ip',
        'visit_date'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'ip' => 'string',
        'visit_date' => 'date'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];


}
