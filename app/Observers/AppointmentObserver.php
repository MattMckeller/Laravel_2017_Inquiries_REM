<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 3/8/17
 * Time: 4:23 PM
 */

namespace app\Observers;
use App\Models\Appointments\Appointment;
use App\Events\AppointmentCreatedEvent;
use App\Events\AppointmentCanceledEvent;
use App\Events\AppointmentUpdatedEvent;


class AppointmentObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Appointment  $appointment
     * @return void
     */
    public function created(Appointment $appointment)
    {
        event(new AppointmentCreatedEvent($appointment));
    }

    /**
     * Listen to the User created event.
     *
     * @param  Appointment  $appointment
     * @return void
     */
    public function updated(Appointment $appointment)
    {
        event(new AppointmentUpdatedEvent($appointment));
    }

}