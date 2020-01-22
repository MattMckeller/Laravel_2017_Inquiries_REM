<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class lease_movement_logs
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class lease_movement_logs extends AccessControlledModel
{

    public $table = 'lease_movement_logs';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'to_bedroom_id',
        'from_bedroom_id',
        'timestamp',
        'lease_id',
        'moving_user_id'
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
