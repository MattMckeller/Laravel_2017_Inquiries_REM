<?php

namespace App\Models\Auth\Customer;

use App\Models\RestrictedModel;

/**
 * Class customer_account_logins
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class customer_account_logins extends RestrictedModel
{

    public $table = 'customer_account_logins';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'datetime',
        'ip_address',
        'user_agent',
        'success'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'ip_address' => 'string',
        'user_agent' => 'string'
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
    public function customerAccountLoginsCustomerAccounts()
    {
        return $this->hasMany(\App\Models\CustomerAccountLoginsCustomerAccount::class);
    }
}
