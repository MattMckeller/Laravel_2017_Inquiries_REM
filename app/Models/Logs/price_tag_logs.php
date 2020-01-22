<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class price_tag_logs
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class price_tag_logs extends AccessControlledModel
{

    public $table = 'price_tag_logs';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'price_tag_id',
        'previous_payment',
        'new_payment',
        'update_datetime'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'previous_payment' => 'string',
        'new_payment' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
