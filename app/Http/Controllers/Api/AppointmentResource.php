<?php

namespace App\Http\Controllers\Api;

use App\Events\Appointments\AppointmentClaimedEvent;
use App\Http\Controllers\Controller;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Auth\Team\WorkingLocation;
use App\Models\Inventory\Location;
use App\Models\Marketing\BrandExposure;
use App\Repositories\Appointments\AgentSchedulesRepository;
use App\Repositories\Appointments\AppointmentEventsRepository;
use App\Repositories\Appointments\AppointmentsRepository;
use App\Repositories\Customers\CustomerRepository;
use REM\Authentication\TeamAuth;
use Illuminate\Http\Request;
use App\Models\Appointments\Appointment;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;

class AppointmentResource extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */

    private $AppointmentEventsRepository;
    private $AgentSchedulesRepository;
    private $AppointmentsRepository;
    private $CustomerRepository;
    private $TeamAuth;

    function __construct()
    {
        /** @var  AppointmentEventsRepository */
        $this->AppointmentEventsRepository = App::make(AppointmentEventsRepository::class);
        /** @var  AgentSchedulesRepository */
        $this->AgentSchedulesRepository = App::make(AgentSchedulesRepository::class);
        /** @var  AppointmentsRepository */
        $this->AppointmentsRepository = App::make(AppointmentsRepository::class);
        /** @var  CustomerRepository */
        $this->CustomerRepository = App::make(CustomerRepository::class);
        /** @var  TeamAuth */
        $this->TeamAuth = App::make('TeamAuth');

    }

    public function index(Request $request) {
        $appointmentInformation = array();
        $appointmentInformation['appointment_id'] = $request->input('appointment_id', false);
        $appointmentInformation['user_account_information_id'] = $request->input('user_account_information_id', false);
        $appointmentInformation['working_location_id'] = $request->input('working_location_id', false);
        $appointmentInformation['start'] = $request->input('start', false);
        $appointmentInformation['status'] = $request->input('status', false);
        $appointmentInformation['end'] = $request->input('end', false);
        $appointmentInformation['inquiry_id'] = $request->input('inquiry_id', false);
        $appointmentInformation['sort'] = $request->input('sort', false);
        $appointmentInformation['direction'] = $request->input('direction', false);
        $appointmentInformation['offset'] = $request->input('offset', false);
        $appointmentInformation['limit'] = $request->input('limit', false);
        $appointmentInformation['with_agent'] = $request->input('with_agent', true);
        $appointmentInformation['with_customer'] = $request->input('with_customer', true);
        $appointmentInformation['with_working_location'] = $request->input('with_working_location', true);
        $appointmentInformation['filters'] = $request->input('filters', false);

        $appointments = $this->AppointmentsRepository->getAppointments($appointmentInformation);
        return response(json_encode(array('appointments' => $appointments)), 201);
    }

    /**
     * Retrieve available appointments based on time frame, agent and location id's, and slot information
     * @param Request $request
     * @return array $appointmentInformation
     */
    public function getAvailable(Request $request) {
        //$time_start = microtime(true);
        $appointmentInformation = array();
        $locationID = $request->input('location_id', false);
        $agentID = $request->input('agent_id', false);
        $slotDuration = $request->input('slot_duration', '30');
        $slotDurationUnitType = $request->input('slot_duration_unit_type', 'minute');
        $startDateTimeString = $request->input('start_datetime', false);
        $endDateTimeString = $request->input('end_datetime', false);
        $appointmentCount = $request->input('appointment_count', 10);

        //Convert provided datetime strings to datetime
        $today = new Carbon();
        $startDateTime = new Carbon($startDateTimeString);
        $endDateTime = $endDateTimeString == true ? new Carbon($endDateTimeString) : new Carbon($today->addDays(7));

        if($slotDuration) {
            $availableSlotsToCheckForAgents = $this->parseDatesWithProvidedSlot($startDateTime, $endDateTime, $slotDuration, $slotDurationUnitType);
        }
        else {
            $availableSlotsToCheckForAgents[] = array(
                'start' => $startDateTime,
                'end' => $endDateTime
            );
        }

        if($locationID) {
            $location = Location::findOrFail($locationID);
            $appointmentInformation[] = array(
                'location_name' => $location->name,
                'location' => $location,
                'available_appointments' => $this->findAvailableAppointmentsForLocation($location, $availableSlotsToCheckForAgents, $agentID, $appointmentCount)
            );
        }
        else {
            $locations = Location::all();
            foreach($locations as $location) {
                $appointmentInformation[] = array(
                    'location_name' => $location->name,
                    'location' => $location,
                    'available_appointments' => $this->findAvailableAppointmentsForLocation($location, $availableSlotsToCheckForAgents, $agentID, $appointmentCount)
                );
            }
        }

        //If appointment count has been specified, only return specified amount of appointments
        if($appointmentCount) {
            $countedAppointmentArray = array();
            foreach($appointmentInformation as $locationID => $locationInformation) {
                $countedAppointmentArray[$locationID] = array(
                    'location' => $locationInformation['location'],
                    'available_appointments' => array_slice($locationInformation['available_appointments'], 0, $appointmentCount)
                );
            }
            //$time_end = microtime(true);
            //$execution_time = $time_end - $time_start;
            //echo "Process time: " . $execution_time;
            //echo_newline();
            return $countedAppointmentArray;
        }
        return $appointmentInformation;
    }

    public function getUnclaimedAppointments($agentID = false, $workingLocationID = false)
    {
        $availableAppointments = array();

        $appointments = Appointment::
            when($workingLocationID, function ($query) use ($workingLocationID) {
                return $query->where('working_location_id', '=', $workingLocationID);
            })
            ->doesntHave('userAccountInformation')
            ->get();
        if($agentID) {
            foreach ($appointments as $appointment) {
                $availableAgents = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($appointment->start, $appointment->end, $appointment->working_location_id, $agentID);
                foreach ($availableAgents as $availableAgent) {
                    if ($availableAgent['user_account_information_id'] == $agentID) {
                        array_push($availableAppointments, $appointment);
                    }
                }
            }
            return $availableAppointments;
        }
        else {
            return $appointments;
        }
    }

    public function claimAppointment(Request $request) {
        $saveStatus = array(
            'success' => 'true',
            'errors' => []
        );
        $appointmentID = $request->input('appointment_id', false);
        $agentID = $request->input('agent_id', false);

        $appointment = Appointment::findOrFail($appointmentID);
        $agent = UserAccountInformation::findOrFail($agentID);

        $appointment->userAccountInformation()->attach($agent);
        $appointment->save();
        $updatedAppointment = Appointment::findOrFail($appointmentID);
        if($updatedAppointment->user_account_information_id == $agentID) {
            event(new AppointmentClaimedEvent($updatedAppointment));
            return $saveStatus;
        }
        else {
            $saveStatus['success'] = false;
            $saveStatus['errors'][] = 'Unable to associate agent and appointment';
            return $saveStatus;
        }
    }

    public function findAvailableAppointmentsForLocation($location, $availableSlotsToCheckForAgents, $agentID, $appointmentCount) {
        $appointmentCountCounter = 0;
        //Add location to return array
        $availableAppointments = array();

        //Query for available agents for the provided location and time frame
        foreach($availableSlotsToCheckForAgents as $currentDateRange) {
            if ($appointmentCountCounter == $appointmentCount) {
                return $availableAppointments;
            }
            $currentStartDate = $currentDateRange['start'];
            $currentEndDate = $currentDateRange['end'];
            $workingLocations = new WorkingLocation();
            $workingLocations = $workingLocations::whereHas('location', function ($query) use ($location) {
                $query->where('id', '=', $location->id);
            })->get();
            foreach ($workingLocations as $workingLocation) {
                $availableAgents = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($currentStartDate, $currentEndDate, $workingLocation->id, $agentID);

                if (!empty($availableAgents)) {
                    $availableAppointments[] = array(
                        'start_date' => $currentStartDate,
                        'end_date' => $currentEndDate,
                        'available_agents' => $availableAgents
                    );
                    //Increment the counter until the appointment count has been filled
                    $appointmentCountCounter++;
                }
            }
        }
        return $availableAppointments;
    }

    /*
     * Create an array of all date ranges broken up by slot duration and unit type
     *
     */
    public function parseDatesWithProvidedSlot($originalStartDate, $originalEndDate, $slotDuration, $slotUnitType) {
        $slotIntervalFormat = new DateInterval("P1D");
        switch($slotUnitType) {
            case "day":
            case "days":
            case "d":
                $slotIntervalFormat = new DateInterval("P" . $slotDuration . "D");
                break;
            case "minute":
            case "minutes":
            case "m":
                $slotIntervalFormat = new DateInterval("PT" . $slotDuration . "M");
                break;
        }
        //Round the start date to the closest fifteen minute interval
        $originalStartDate = Carbon::createFromTimestamp(round(Carbon::parse($originalStartDate)->timestamp / 900) * 900);
        //Convert start and end dates to intervals
        $originalEndDate->add($slotIntervalFormat);
        //Calculate all date periods between the start and end with the provided interval
        $dateRanges = new DatePeriod($originalStartDate, $slotIntervalFormat, $originalEndDate);

        //Convert date ranges to string array for array formatting
        $simpleDatesArray = array();
        foreach($dateRanges as $dateRange) {
            array_push($simpleDatesArray, $dateRange->format('Y-m-d H:i:s'));
        }

        //Chunk start and end dates from date periods
        $dateSlots = array();
        for($i = 0; $i < count($simpleDatesArray) - 1; $i++) {
            $dateSlots[] = array(
                'start' => $simpleDatesArray[$i],
                'end' => $simpleDatesArray[$i + 1]
            );
        }
        return $dateSlots;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $customerID = $request->input('customers', false);
        $start = $request->input('start', false);
        $end = $request->input('end', false);
        $workingLocationID = $request->input('property', false);
        $agentID = $request->input('agent', false);

        $saveStatus = $this->AppointmentsRepository->createNewAppointment($start, $end, $workingLocationID, $agentID, $customerID);
        //If the appointment has been successfully saved, attempt to store an associated appointment event
        if($saveStatus['success'] == 'true') {
            $createdAppointmentID = $saveStatus['appointmentID'];
            $appointmentEventSaved = $this->AppointmentEventsRepository->addNewAppointmentEvent($createdAppointmentID, 'generation', 'agent', $agentID);
            if($appointmentEventSaved['success'] == true) {
                //Retrieve the created appointment with appointment events
                $createdAppointment = Appointment::findOrFail($createdAppointmentID)->load('appointmentEvents');
                $customerAddedToAppointmentEvent = $this->AppointmentEventsRepository->addNewAppointmentEvent($createdAppointmentID, 'addition', 'customer', false, $customerID);
                if($customerAddedToAppointmentEvent['success'] == 'true') {
                    return response($createdAppointment, 200);
                }
            }
            else {
                return response($appointmentEventSaved, 400);
            }
        }
        return response($saveStatus, 400);
    }

    public function schedule(Request $request) {
        $workingLocationID = $request->input('working_location_id', false);
        $agentIDs = $request->input('agent_ids', false);
        $startDateTime = $request->input('start_datetime', false);
        $endDateTime = $request->input('end_datetime', false);
        $customerIDs = $request->input('customer_ids', false);
        $user_id = $this->TeamAuth->get_user()->id;

        //Check that all required information has been provided
        if(empty($workingLocationID)) {
            return response(array('success' => 'false', 'errors' => ['A location must be selected before creating an appointment.']), 400);
        }

        if(empty($agentIDs)) {
            return response(array('success' => 'false', 'errors' => ['An agent must be selected for the appointment.']), 400);
        }
        //Default end date time to be 30 minutes from start date time
        if(empty($startDateTime) || empty($endDateTime)) {
            return response(array('success' => 'false', 'errors' => ['Start and end dates must be provided before creating an appointment.']), 400);
        }

        $carbonStartDateTime = new Carbon($startDateTime);
        $carbonEndDateTime = new Carbon($endDateTime);
        if($carbonStartDateTime->greaterThanOrEqualTo($carbonEndDateTime)) {
            return response(array('success' => 'false', 'errors' => ['The appointment start date must be before the end date.']), 400);
        }

        if($customerIDs) {
            $saveStatus = $this->AppointmentsRepository->createNewAppointment($startDateTime, $endDateTime, $workingLocationID, $agentIDs, $customerIDs);
            if ($saveStatus['success'] == 'true') {
                $createdAppointmentID = $saveStatus['appointmentID'];
                $appointmentEventSaved = $this->AppointmentEventsRepository->addNewAppointmentEvent($createdAppointmentID, 'generation', 'agent', $user_id);
                if ($appointmentEventSaved['success'] == 'true') {
                    //Retrieve the created appointment with appointment events
                    $createdAppointment = Appointment::findOrFail($createdAppointmentID)->load('appointmentEvents');
                    //Create addition events for each customer to the appointment
                    foreach ($customerIDs as $customerID) {
                        $saveStatus = $this->AppointmentEventsRepository->addNewAppointmentEvent($createdAppointmentID, 'addition', 'customer', false, $customerID);
                        if ($saveStatus['success'] == 'false') {
                            return response($saveStatus, 500);
                        }
                    }
                    return response($saveStatus, 200);
                } else {
                    return response($saveStatus, 400);
                }
            }
            else {
                return response($saveStatus, 500);
            }
        }
        if(!empty($saveStatus)) {
            return response($saveStatus, 400);
        }
        return response("Unable to schedule appointment", 400);
    }

    public function updateSchedule(Request $request)
    {
        $appointment_id = $request->input('appointment_id', false);
        $working_location_id = $request->input('working_location_id', false);
        $user_account_information_ids = $request->input('agent_ids', false);
        $startDateTime = $request->input('start_datetime', false);
        $endDateTime = $request->input('end_datetime', false);
        $customerIDs = $request->input('customer_ids', false);

        //Check that all required information has been provided
        if (empty($appointment_id)) {
            return response(array('success' => 'false', 'errors' => ['Appointment not properly selected or submitted.']), 400);
        }
        if (empty($working_location_id) && empty($user_account_information_ids) && empty($startDateTime) && empty($endDateTime) && empty($customerIDs)) {
            return response(array('success' => 'false', 'errors' => ['Something must be changed for the appointment to be updated!']));
        }
        if (empty($user_account_information_ids)) {
            return response(array('success' => 'false', 'errors' => ['You must provide an agent for the appointment.']));
        }
        $carbonStartDateTime = new Carbon($startDateTime);
        $carbonEndDateTime = new Carbon($endDateTime);
        if($carbonStartDateTime->gt($carbonEndDateTime)) {
            return response(array('success' => 'false', 'errors' => ['The appointment start date must be before the end date.']), 400);
        }

        $saveAttempt = $this->AppointmentsRepository->updateAppointment($appointment_id, $startDateTime, $endDateTime, $working_location_id, $user_account_information_ids, $customerIDs);
        if ($saveAttempt['success'] == 'true') {
            return response($saveAttempt, 200);
        }
        return response($saveAttempt, 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  Appointment $appointment
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment) {
        $appointment->status = $this->AppointmentEventsRepository->determineAppointmentStatus($appointment->id);
        foreach ($appointment->customers as $customer) {
            $birthday = Carbon::parse($customer->birthday);
            $customer->birthdate = $birthday->toDateString();
        }
        return response(json_encode(array('appointment', $appointment)), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Appointment $appointment
     * @return \Illuminate\Http\Response
     */
    public function edit(Appointment $appointment) {
        $availableAgents = null;
        //todo Call for all locations to retrieve available locations based on agent availability
        $workingLocations = WorkingLocation::select('id', 'name')->get();
        $brandExposures = BrandExposure::all();

        foreach ($appointment->customers as $customer) {
            $customer->phoneNumber = $customer->phoneNumbers->first()->phone_number;
            $customer->email = $customer->emailAddresses->first()->email_address;
            $birthday = Carbon::parse($customer->birthday);
            $customer->birthdate = $birthday->toDateString();

            $availableAgents = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($appointment->start, $appointment->end, $appointment->working_location_id);
        }
        return response(array(
            'workingLocations' => $workingLocations,
            'appointment' => $appointment,
            'agents' => $availableAgents,
            'brandExposures' => $brandExposures
        ), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  array $updatedAppointment
     * @return \Illuminate\Http\Response
     */
    //should take in the request and update the appointment via the
    // event_type supplied by the request.
    // todo send the event type to createNewAppointmentEvent function
    // todo if they attempt to delete the last customer from appointment, don't let them
    public function update($updatedAppointment) {
        //For whatever reason a single customer is not returning as an array, so this checks if the customer_info section is an array and, if not, makes it one
        $customerInfo = is_array($updatedAppointment['customer_info']) ? $updatedAppointment['customer_info'] : array($updatedAppointment['customer_info']);
        $appointment = Appointment::findOrFail($updatedAppointment['id']);

        if (isset($updatedAppointment['customers_to_remove'])) {
            $customers_to_remove = is_array($updatedAppointment['customers_to_remove']) ? $updatedAppointment['customers_to_remove'] : array($updatedAppointment['customers_to_remove']);
            foreach ($customers_to_remove as $customer_id) {
                $appointment->customers()->where('id', $customer_id)->detach($customer_id);
            }
        }

        //Retrieve all columns for the model
        $appointmentColumns = Schema::getColumnListing($appointment->getTable());

        //Prepare array to be sent for model save
        foreach($appointmentColumns as $appointmentColumn) {
            if (!array_key_exists($appointmentColumn, $updatedAppointment)) {
                continue;
            }
            $appointment->$appointmentColumn = $updatedAppointment[$appointmentColumn];
        }

        $appointment->user_account_information_id = $updatedAppointment['user_account_information_id'];
        $appointment->working_location_id = $updatedAppointment['working_location_id'];
        $appointment->start = $updatedAppointment['start'];
        $appointment->end = $updatedAppointment['end'];
        $appointment->save();

        foreach ($customerInfo as $currentCustomer) {
            $customer_id = $currentCustomer['id'];
            if ($customer_id == -1) {
                $customer = $this->CustomerRepository->createNewCustomer($currentCustomer);
                $this->CustomerRepository->associateAppointmentAndCustomer($customer, $appointment);
            }
        }
        $appointment = Appointment::with('customers')->findOrFail($appointment->id);
        return response(json_encode($appointment), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $appointment_id = $request->input('appointment_id');
        $user_account_information_id = $this->TeamAuth->get_user()->userAccountInformation->id;
        $status = $this->AppointmentEventsRepository->addNewAppointmentEvent($appointment_id, 'canceled', 'agent', $user_account_information_id, null);
        if ($status['success'] == 'true') {
            return response($status, 200);
        }
        else {
            return response($status, 400);
        }
    }

    //Return all appointments to appointmentList.blade.php
    public function getAppointmentList() {
        $appointments = $this->AppointmentsRepository->getAppointmentsList();
        return $appointments;
    }

    public function determineAppointmentStatus($appointmentID) {
        return $this->AppointmentEventsRepository->determineAppointmentStatus($appointmentID);
    }
}