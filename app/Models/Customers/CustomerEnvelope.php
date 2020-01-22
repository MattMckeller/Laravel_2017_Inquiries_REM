<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class CustomerEnvelope
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class CustomerEnvelope extends AccessControlledModel
{

    public $table = 'customer_envelopes';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'envelope_id',
        'is_manual'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'envelope_id' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documents()
    {
        return $this->hasMany(\App\Models\Documents\Document::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function lease()
    {
        return $this->hasOne(\App\Models\Lease::class);
    }
}
