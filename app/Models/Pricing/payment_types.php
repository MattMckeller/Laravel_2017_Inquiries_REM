<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class payment_types
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class payment_types extends AccessControlledModel
{

    public $table = 'payment_types';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'type',
        'name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string',
        'name' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function feePaymentsPaymentTypes()
    {
        return $this->hasMany(\App\Models\FeePaymentsPaymentType::class);
    }
}
