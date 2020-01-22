<?php

namespace App\Models\Timers;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;

use \App\Models\AccessControlledModel as AccessControlledModel;

/**
 * Class Inquiry
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class TimerContactType extends AccessControlledModel
{
    public $table = 'timer_contact_types';
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
    public function timerConfigs()
    {
        return $this->hasMany(TimerConfig::class, 'timer_contact_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function timers()
    {
        return $this->hasMany(Timer::class, 'timer_contact_Type_id');
    }
}
