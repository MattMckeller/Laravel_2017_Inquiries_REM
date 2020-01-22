<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Auth\Team\UserAccount;

/**
 * Class addresses
 * @package App\Models
 * @version November 23, 2016, 9:26 pm UTC
 */
class Note extends AccessControlledModel
{
    public $table = 'notes';
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'text'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'text' => 'string'
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
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }
}
