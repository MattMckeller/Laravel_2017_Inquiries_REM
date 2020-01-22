<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class phone_number_types
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class PhoneNumberType extends AccessControlledModel
{

    public $table = 'phone_number_types';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'type',
        'description'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string',
        'description' => 'string'
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
    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class);
    }
}
