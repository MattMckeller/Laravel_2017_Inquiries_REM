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
class TimerOverrideType extends AccessControlledModel
{
    public $table = 'timer_override_types';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'name'
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
    public function timerOverride()
    {
        return $this->hasMany(TimerOverride::class);
    }
}
