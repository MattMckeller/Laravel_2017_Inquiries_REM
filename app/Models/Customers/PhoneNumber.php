<?php

namespace App\Models\Customers;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Scopes\PrimaryActiveScope;
use Monolog\Processor\GitProcessor;

/**
 * Class phone_numbers
 * @package App\Models
 * @version November 23, 2016, 9:29 pm UTC
 */
class PhoneNumber extends AccessControlledModel
{

    public $table = 'phone_numbers';
    public $timestamps = false;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'phone_number',
        'is_primary',
        'is_active'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone_number' => 'string'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function emergencyContact()
    {
        return $this->belongsTo(EmergencyContact::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function guarantor()
    {
        return $this->belongsTo(Guarantor::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function phoneNumberTypes()
    {
        return $this->belongsTo(PhoneNumberType::class);
    }
}
