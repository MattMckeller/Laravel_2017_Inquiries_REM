<?php

namespace App\Models\Appointments;

use App\Models\Appointments\Appointment;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customers\Customer;
use App\Models\AccessControlledModel;

class Group extends AccessControlledModel
{
    protected $table = 'groups';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $timestamps = true;

    public $fillable = [
        'appointment_id',
        'customer_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function customer() {
        return $this->belongsTo(Customer::class);
    }
}