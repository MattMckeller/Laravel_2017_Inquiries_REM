<?php

namespace App\Models\Auth\Customer;

use \App\Models\RestrictedModel as RestrictedModel;
use App\Models\Customers\Customer;
/**
 * Class customer_accounts
 * @package App\Models
 * @version November 23, 2016, 9:27 pm UTC
 */
class CustomerAccount extends RestrictedModel
{

    public $table = 'customer_accounts';
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'email_address',
        'pin',
        'birthday',
        'last_name',
        'password',
        'salt',
        'ip_address',
        'activated',
        'forgot_password_expiriation',
        'last_login_date',
        'failed_logins',
        'locked',
        'banned',
        'ban_expiration',
        'account_creation_date',
        'update_email_address',
        'activation_token',
        'forgot_password_token',
        'last_forgot_password_datetime',
        'is_active'
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
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
