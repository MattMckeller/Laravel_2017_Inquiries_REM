<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class parking_lots
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class parking_lots extends AccessControlledModel
{

    public $table = 'parking_lots';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'name',
        'price',
        'short_name',
        'total_resident_spots'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'short_name' => 'string',
        'total_resident_spots' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
