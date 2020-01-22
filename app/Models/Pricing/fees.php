<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class fees
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class fees extends AccessControlledModel
{

    public $table = 'fees';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'amount',
        'waived'
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function feePaymentsFees()
    {
        return $this->hasMany(\App\Models\FeePaymentsFee::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function feeTypesFees()
    {
        return $this->hasMany(\App\Models\FeeTypesFee::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function feesLeases()
    {
        return $this->hasMany(\App\Models\FeesLease::class);
    }
}
