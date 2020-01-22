<?php

namespace App\Repositories\Appointments;

use App\Events\Appointments\AppointmentCustomerAdded;
use App\Events\Appointments\AppointmentCanceledEvent;
use App\Events\Appointments\AppointmentCompletedEvent;
use App\Events\Appointments\AppointmentScheduledEvent;
use App\Events\Appointments\AppointmentUpdatedEvent;
use App\Events\Appointments\AppointmentCustomerWithdrawal;
use App\Models\Appointments\Appointment;
use App\Models\Appointments\AppointmentEvent;


class AppointmentEventsRepository
{
    /**
     * @param int $appointmentID
     * @param string $eventType
     * @param string $userType
     * @param $userAccountInformationID
     * @param $customerID
     * @return array $saveStatus
     */
    public function addNewAppointmentEvent($appointmentID, $eventType, $userType, $userAccountInformationID = null, $customerID = null)
    {
        $saveStatus = array(
            'success' => 'true',
            'errors' => array()
        );

        $appointmentEvent = new AppointmentEvent();
        $appointment = Appointment::find($appointmentID);
        $appointmentEvent->appointment()->associate($appointmentID);
        $appointmentEvent->user_type = $userType;
        //Store the appropriate ID's based on the provided information
        switch($userType) {
            case "customer":
                $appointmentEvent->customer()->associate($customerID);
                break;
            case "agent":
                $appointmentEvent->userAccountInformation()->associate($userAccountInformationID);
                break;
            case "system":
                $appointmentEvent->userAccountInformation()->associate($userAccountInformationID);
                break;
        }
        $event = null;
        //Store the appointment event type provided
        switch ($eventType) {
            case "generated":
            case "generation":
            case "created":
            case "creation":
                $appointmentEvent->event_type = "created";
                $event = new AppointmentScheduledEvent($appointmentEvent);
                break;
            case "cancelled":
            case "canceled":
            case "cancellation":
            case "close":
            case "closed":
                $appointmentEvent->event_type = "closed";
                $event = new AppointmentCanceledEvent($appointmentEvent);
                break;
            case "complete":
            case "completed":
            case "completion":
                $appointmentEvent->event_type = "completed";
                $event = new AppointmentCompletedEvent($appointmentEvent);
                break;
            case "addition":
            case "add":
            case "added":
                $appointmentEvent->event_type = "addition";
                $event = new AppointmentCustomerAdded($appointmentEvent);
                break;
            case "withdraw":
            case "withdrawal":
                $appointmentEvent->event_type = "withdrawal";
                $event = new AppointmentCustomerWithdrawal($appointmentEvent);
                break;
            default:
                $saveStatus['success'] = 'false';
                $saveStatus['errors'][] = 'Invalid event type provided for the appointment event';
                return $saveStatus;
                break;
        }

        $saveAttempt = $appointmentEvent->save();
        if(!$saveAttempt) {
            $saveStatus['success'] = 'false';
            $saveStatus['errors'][] = 'Unable to attach the appointment event to the appointment';
            return $saveStatus;
        }
        event($event);
        return $saveStatus;
    }

    public function determineAppointmentStatus($appointmentID) {
        $appointmentCreated = false;
        $appointmentCustomers = Appointment::find($appointmentID)->customers()->count();
        //Make sure that a customer exists for the appointment
        if($appointmentCustomers <= 0) {
            return "Error: No customers exist for this appointment";
        }

        $appointmentEvents = Appointment::find($appointmentID)->appointmentEvents()->orderBy('created_at', 'desc')->get();
        foreach($appointmentEvents as $appointmentEvent) {
            switch($appointmentEvent->event_type) {
                case "completed":
                    return "completed";
                    break;
                case "closed":
                    return "closed";
                    break;
                case "created":
                    return "created";
                    break;
                default:
                    return "unknown";
                    break;
            }
        }
        return "Error: Appointment status could not be calculated";
    }
}