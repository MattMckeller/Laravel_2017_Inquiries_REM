<?php

namespace App\Models;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class fee_payments
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class fee_payments extends AccessControlledModel
{

    public $table = 'fee_payments';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'description',
        'received',
        'received_datetime',
        'received_user_id',
        'recorded',
        'recorded_datetime',
        'recorded_user_id',
        'canceled',
        'canceled_datetime',
        'canceled_user_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'description' => 'string',
        'recorded_user_id' => 'integer'
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
    public function feePaymentsPaymentTypes()
    {
        return $this->hasMany(\App\Models\FeePaymentsPaymentType::class);
    }
}
