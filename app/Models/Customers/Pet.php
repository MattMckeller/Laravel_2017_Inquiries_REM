<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class pets
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class Pet extends AccessControlledModel
{

    public $table = 'pets';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'type',
        'documentation',
        'breed',
        'weight',
        'is_residing'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string',
        'breed' => 'string',
        'weight' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
