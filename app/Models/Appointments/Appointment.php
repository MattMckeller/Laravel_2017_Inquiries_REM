<?php

namespace App\Models\Appointments;

use App\Models\AccessControlledModel;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Customers\Customer;
use App\Models\Inquiries\Inquiry;
use App\Models\Auth\Team\WorkingLocation;

use Illuminate\Database\Eloquent\Model as Model;

class Appointment extends AccessControlledModel
{

    public $table = 'appointments';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $timestamps = true;

    public $fillable = [
        'user_account_information_id',
        'working_location_id',
        'inquiry_id',
        'start',
        'end',
        'scheduled',
        'confirmed'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_account_information_id' => 'bigint',
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
    public function userAccountInformation() {
        return $this->belongsToMany(UserAccountInformation::class);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function appointmentEvents() {
        return $this->hasMany(AppointmentEvent::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customers() {
        return $this->belongsToMany(Customer::class, 'groups')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function groups()
    {
        return $this->hasMany(group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function workingLocation() {
        return $this->belongsTo(WorkingLocation::class);
    }
}
