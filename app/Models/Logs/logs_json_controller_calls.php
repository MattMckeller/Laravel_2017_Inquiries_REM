<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class logs_json_controller_calls
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class logs_json_controller_calls extends AccessControlledModel
{

    public $table = 'logs_json_controller_calls';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'call',
        'user_id',
        'session_data',
        'timestamp'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'call' => 'string',
        'session_data' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
