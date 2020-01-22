<?php

namespace App\Models\Appointments;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Customers\Customer;
use App\Models\AccessControlledModel;

class AppointmentEvent extends AccessControlledModel
{

    public $table = 'appointment_events';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'appointment_id',
        'user_account_information_id',
        'customer_id',
        'user_type',
        'event_type',
        'ip_address',
        'user_agent'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function appointment () {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function userAccountInformation() {
        return $this->belongsTo(UserAccountInformation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function customer() {
        return $this->belongsTo(Customer::class);
    }
}
