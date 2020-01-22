<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class guarantors
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class Guarantor extends AccessControlledModel
{

    public $table = 'guarantors';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'first_name',
        'last_name',
        'is_active',
        'is_verified'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'first_name' => 'string',
        'last_name' => 'string'
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
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customers()
    {
        return $this->belongsToMany(Customer::class, "_customers_guarantors");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function emailAddresses()
    {
        return $this->hasMany(EmailAddress::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class);
    }
}
