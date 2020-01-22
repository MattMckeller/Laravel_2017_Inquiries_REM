<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class logs
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class logs extends AccessControlledModel
{

    public $table = 'logs';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'log_type',
        'class',
        'message',
        'session_data',
        'timestamp'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'log_type' => 'string',
        'class' => 'string',
        'message' => 'string',
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
