<?php

namespace App\Models\Other;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class ci_sessions_api
 * @package App\Models
 * @version November 28, 2016, 3:07 pm UTC
 */
class SessionList extends AccessControlledModel
{

    public $table = 'laravel_sessions';
    var $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
