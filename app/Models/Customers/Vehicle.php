<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class vehicles
 * @package App\Models
 * @version November 23, 2016, 9:25 pm UTC
 */
class Vehicle extends AccessControlledModel
{

    public $table = 'vehicles';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'make',
        'model',
        'year',
        'license_plate'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'make' => 'string',
        'model' => 'string',
        'license_plate' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
