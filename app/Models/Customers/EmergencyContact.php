<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class emergency_contacts
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class EmergencyContact extends AccessControlledModel
{

    public $table = 'emergency_contacts';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'relationship',
        'first_name',
        'last_name',
        'is_active',
        'is_primary'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'relationship' => 'string',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customers()
    {
        return $this->belongsToMany(Customer::class, '_customers_emergency_contacts', 'emergency_contact_id', 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function emailAddressesEmergencyContacts()
    {
        return $this->hasMany(\App\Models\EmailAddressesEmergencyContact::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function emergencyContactsPhoneNumbers()
    {
        return $this->hasMany(\App\Models\EmergencyContactsPhoneNumber::class);
    }
}
