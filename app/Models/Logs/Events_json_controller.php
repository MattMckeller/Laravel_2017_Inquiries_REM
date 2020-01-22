<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class events_json_controller
 * @package App\Models
 * @version November 28, 2016, 3:07 pm UTC
 */
class events_json_controller extends AccessControlledModel
{

    public $table = 'events_json_controller';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'event_type',
        'message',
        'user_id',
        'data',
        'session_data',
        'timestamp'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'event_type' => 'string',
        'message' => 'string',
        'data' => 'string',
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
