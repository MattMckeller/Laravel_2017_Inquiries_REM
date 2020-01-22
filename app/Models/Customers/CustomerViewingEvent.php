<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;
use Monolog\Processor\GitProcessor;
use App\Models\Auth\Team\UserAccount;

/**
 * Class phone_numbers
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class CustomerViewingEvent extends AccessControlledModel
{

    public $table = 'customer_viewing_event';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'timestamp',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function userAccount() {
        return $this->belongsTo(UserAccount::class);
    }
}
