<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class events_other
 * @package App\Models
 * @version November 28, 2016, 3:07 pm UTC
 */
class events_other extends AccessControlledModel
{

    public $table = 'events_other';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'class_name',
        'event_type',
        'message',
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
        'class_name' => 'string',
        'event_type' => 'string',
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
