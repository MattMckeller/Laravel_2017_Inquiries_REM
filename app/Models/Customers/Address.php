<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class addresses
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class Address extends AccessControlledModel
{
    public $table = 'addresses';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    


    public $fillable = [
        'street_address_1',
        'street_address_2',
        'city',
        'state',
        'zip',
        'country',
        'is_international',
        'is_primary',
        'is_active'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'street_address_1' => 'string',
        'street_address_2' => 'string',
        'city' => 'string',
        'state' => 'string',
        'zip' => 'string',
        'country' => 'string'
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
    public function addressType()
    {
        return $this->belongsTo(AddressType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function guarantor()
    {
        return $this->belongsTo(Guarantor::class);
    }
}
