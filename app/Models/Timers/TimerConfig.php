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
class TimerConfig extends AccessControlledModel
{
    public $table = 'timer_config';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'type',
        'display_name',
        'timer_expiration',
        'duration',
        'earliest_time',
        'role_type'
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
     * @return mixed|TimerConfig
     */
    public function getLeftCustomerVoicemailConfig(){
        return $this->where('display_name', 'Left Customer Voicemail Timer')->first();
    }

    /**
     * @return mixed|TimerConfig
     */
    public function getCustomerLeftVoicemailConfig(){
        return $this->where('display_name', 'Customer left voicemail timer')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function timerContactType()
    {
        return $this->belongsTo(TimerContactType::class, 'timer_contact_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function timers()
    {
        return $this->hasMany(Timer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function timerConfigGroups()
    {
        return $this->belongsToMany(TimerConfigGroup::class, "_timer_config_timer_config_groups");
    }
}
