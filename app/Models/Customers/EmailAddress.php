<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class email_addresses
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class EmailAddress extends AccessControlledModel
{

    public $table = 'email_addresses';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'email_address',
        'is_active',
        'is_primary'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_address' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * The "booting" method of the model
     *
     * @return void
     */

//    protected static function boot() {
//        parent::boot();
//
//        static::addGlobalScope(new PrimaryActiveScope);
//    }

    /**
     * Scope a query to only include active and primary phone numbers
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimaryActive($query) {
        return $query->where('is_primary', 1)->where('is_active', 1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function emailAddressType()
    {
        return $this->hasOne(EmailAddressType::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function emergencyContact()
    {
        return $this->hasOne(EmergencyContact::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function guarantor()
    {
        return $this->belongsTo(Guarantor::class);
    }
}
