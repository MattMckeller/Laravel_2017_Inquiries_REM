<?php

namespace App\Http\Controllers\Appointment;

use Illuminate\Support\Facades\App;
use App\Http\Controllers\Api\AppointmentResource;
use App\Repositories\Appointments\AppointmentEventsRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointments\Appointment;
use App\Models\Auth\Team\WorkingLocation;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Marketing\BrandExposure;
use App\Repositories\Appointments\AgentSchedulesRepository;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    private $AppointmentEventsRepository;
    private $AgentSchedulesRepository;
    private $AppointmentsResource;

    function __construct()
    {
        $this->AppointmentEventsRepository = App::make(AppointmentEventsRepository::class);
        $this->AgentSchedulesRepository = App::make(AgentSchedulesRepository::class);
        $this->AppointmentsResource = App::make(AppointmentResource::class);
    }

    public function appointmentsList() {
        $appointments = $this->AppointmentsResource->getAppointmentList();
        return view('appointment/appointmentsList', ['appointments' => $appointments]);
    }

    public function showAppointment($AID = false) {
        $requestedAppointment = Appointment::findOrFail($AID)->load('customers')->load('userAccountInformation');
        $requestedAppointment->status = $this->AppointmentEventsRepository->determineAppointmentStatus($requestedAppointment->id);
        return view('appointment/appointmentInfo')->with(['appointment' => $requestedAppointment]);
    }

    public function completeAppointmentForm($AID = false) {
        $appointment = Appointment::findOrFail($AID);
        $appointment->start = Carbon::parse($appointment->start);
        $appointment->end = Carbon::parse($appointment->end);
        $workingLocations = WorkingLocation::select('id', 'name')->get();
        $agentsForInitialLocation = WorkingLocation::find($appointment->working_location_id)->userAccountInformation()->select('id', 'first_name', 'last_name')->get();
        $brandExposures = BrandExposure::get();

        foreach ($appointment->customers as $customer) {
            $customer->phoneNumber = $customer->phoneNumbers->first()->phone_number;
            $customer->email = $customer->emailAddresses->first()->email_address;
            $birthday = Carbon::parse($customer->birthday);
            $customer->birthdate = $birthday->toDateString();
        }
        return view('appointment/completeAppointment')->with(array(
                'workingLocations' => $workingLocations,
                'appointment' => $appointment,
                'agents' => $agentsForInitialLocation,
                'brandExposures' => $brandExposures
            )
        );
    }

    public function completeAppointment(Request $request) {
        $appointmentID = $request->input('appointment_id');
        $completedAppointmentCustomerIDs = $request->input('customer_ids');

        $previousAppointmentCustomers = Appointment::findOrFail($appointmentID)->customers()->get();
        $previousAppointmentCustomerIDs = array();
        foreach($previousAppointmentCustomers as $previousAppointmentCustomer) {
            array_push($previousAppointmentCustomerIDs, $previousAppointmentCustomer->id);
        }

        //If a previous customer id's with the appointment is not present in the provided completed appointment id's, add event for withdrawal
        foreach($previousAppointmentCustomerIDs as $previousAppointmentCustomerID) {
            if(!in_array($previousAppointmentCustomerID, $completedAppointmentCustomerIDs)) {
                $saveAttempt = $this->AppointmentEventsRepository->addNewAppointmentEvent($appointmentID, 'withdrawal', 'customer', false, $previousAppointmentCustomerID);
                if($saveAttempt['success'] == 'false') {
                    return response("Unable to create appointment event for customer not present in appointment.", 500);
                }
            }
        }
        //todo Temporarily store that the system has completed the appointment until authentication is placed in project
        $saveAttempt = $this->AppointmentEventsRepository->addNewAppointmentEvent($appointmentID, 'completed', 'system');
        if($saveAttempt['success'] == 'true') {
            return response($saveAttempt, 200);
        }
        return response("Unable to complete appointment for customer", 500);
    }

    public function updateAppointmentForm($AID = false) {
        $appointment = Appointment::findOrFail($AID);
        $workingLocations = WorkingLocation::select('id', 'name')->get();
        $agentsForInitialLocation = WorkingLocation::find($appointment->working_location_id)->userAccountInformation()->select('id', 'first_name', 'last_name')->get();
        $brandExposures = BrandExposure::get();

        foreach ($appointment->customers as $customer) {
            $customer->phoneNumber = $customer->phoneNumbers->first()->phone_number;
            $customer->email = $customer->emailAddresses->first()->email_address;
            $birthday = Carbon::parse($customer->birthday);
            $customer->birthdate = $birthday->toDateString();
        }
        return view('appointment/updateAppointment')->with(array(
                'workingLocations' => $workingLocations,
                'appointment' => $appointment,
                'agents' => $agentsForInitialLocation,
                'brandExposures' => $brandExposures
            )
        );
    }

    public function submitAppointmentUpdate(Request $request) {
        $appointmentRequest = $request->input('appointment');
        $appointmentRequest['customers_to_remove'] = $appointmentRequest['customers_to_remove'] ? $appointmentRequest['customers_to_remove'] : false;

        $appointmentInformation = array(
            'user_account_information_id' => $appointmentRequest['user_account_information_id'],
            'id' => $appointmentRequest['appointmentID'],
            'customer_info' => $appointmentRequest['customer_info'],
            'end' => $appointmentRequest['end'],
            'working_location_id' => $appointmentRequest['working_location_id'],
            'start' => $appointmentRequest['start'],
            'customers_to_remove' => $appointmentRequest['customers_to_remove']
        );

        $updatedAppointment = $this->AppointmentResource->update($appointmentInformation);
        return $updatedAppointment;
    }

    public function getUnclaimed(Request $request)
    {
        $agentID = $request->input('user_account_information_id', false);
        $workingLocationID = $request->input('working_location_id', false);

        $unclaimed_appointments = $this->AppointmentResource->getUnclaimedAppointments($agentID, $workingLocationID);
        $appointments = array();
        foreach ($unclaimed_appointments as $unclaimed_appointment) {
            $available_agents = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($unclaimed_appointment->start, $unclaimed_appointment->end, $unclaimed_appointment->working_location_id, false);
            $unclaimed_appointment = $unclaimed_appointment->toArray();
            array_push($unclaimed_appointment, $available_agents);
            array_push($appointments, $unclaimed_appointment);
        }

        return response(json_encode(array('appointments' => $appointments)), 201);
    }
}