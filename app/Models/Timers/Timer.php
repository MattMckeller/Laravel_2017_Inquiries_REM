<?php

namespace App\Models\Timers;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Inquiries\Inquiry;
use App\Models\Inquiries\InquiryEvent;

/**
 * Class Inquiry
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class Timer extends AccessControlledModel
{
    public $table = 'timers';
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    public $fillable = [
        'timer_expiration_datetime',
        'valid_start_date',
        'completed',
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
    public function timerConfig()
    {
        return $this->belongsTo(TimerConfig::class, 'timer_config_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function timerOverrides()
    {
        return $this->hasMany(TimerOverride::class, 'timer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function timerDelays()
    {
        return $this->hasMany(TimerDelay::class, 'timer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function timerContactType()
    {
        return $this->belongsTo(TimerContactType::class, 'timer_contact_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function inquiryEvent()
    {
        return $this->belongsTo(InquiryEvent::class, 'inquiry_event_id');
    }
}
