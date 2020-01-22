<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class payment_option
 * @package App\Models
 * @version November 28, 2016, 3:07 pm UTC
 */
class payment_option extends AccessControlledModel
{

    public $table = 'payment_options';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'selected_payment_method',
        'selection_datetime',
        'wants_checks',
        'wants_ach',
        'ach_account_holder_type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'ach_account_holder_type' => 'string'
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
    public function leasesPaymentOptions()
    {
        return $this->hasMany(\App\Models\LeasesPaymentOption::class);
    }
}
