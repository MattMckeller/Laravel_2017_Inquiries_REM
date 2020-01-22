<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class email_address_types
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class EmailAddressType extends AccessControlledModel
{

    public $table = 'email_address_types';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string'
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
    public function emailAddresses()
    {
        return $this->hasMany(EmailAddress::class);
    }
}
