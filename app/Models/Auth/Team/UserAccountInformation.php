<?php

namespace App\Models\Auth\Team;

use App\Models\Appointments\AgentSchedule;
use App\Models\Appointments\Appointment;
use App\Models\Appointments\AppointmentEvent;
use \App\Models\Auth\Team\UserAccount;
use App\Models\AccessControlledModel;
use App\Models\Inquiries\InquiryNote;

/**
 * Class user_account_information
 * @package App\Models
 * @version November 28, 2016, 3:07 pm UTC
 */
class UserAccountInformation extends AccessControlledModel
{
    public $table = 'user_account_information';
    
    public $timestamps = false;
    
    public $fillable = [
        'first_name',
        'last_name',
        'email_address',
        'work_phone',
        'phone_ext',
        'position'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'first_name' => 'string',
        'last_name' => 'string',
        'email_address' => 'string',
        'work_phone' => 'string',
        'phone_ext' => 'integer',
        'position' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function appointmentEvents()
    {
        return $this->hasMany(AppointmentEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function workingLocation() {
        return $this->belongsToMany(WorkingLocation::class, 'user_account_information_working_locations');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function agentSchedules() {
        return $this->hasMany(AgentSchedule::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function userAccount()
    {
        return $this->hasOne(UserAccount::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function inquiryNote()
    {
        return $this->hasMany(InquiryNote::class);
    }
}
