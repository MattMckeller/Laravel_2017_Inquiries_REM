<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class parking_spots
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class parking_spots extends AccessControlledModel
{

    public $table = 'parking_spots';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'year',
        'tag',
        'available_datetime',
        'passcode'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'year' => 'string',
        'tag' => 'string',
        'passcode' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
