<?php
namespace App\Repositories\Inquiries;

trait AppointmentStatusChecks
{
    /*
     * Returns whether or not the provided appointment has been scheduled.
     */
    function isAppointmentScheduled($appointment){
        if($appointment == null || empty($appointment->scheduled))
            return $this->noAppointment();
        if($appointment->scheduled == 1)
            return $this->appointmentScheduled();
    }

    /*
     * Returns whether or not the provided appointment has been confirmed.
     */
    function isAppointmentConfirmed($appointment){
        if($appointment == null || empty($appointment->confirmed))
            return $this->notConfirmed();
        if($appointment->confirmed == 1)
            return $this->confirmed();
    }
}