<?php

namespace App\Repositories\Appointments;

use App\Models\Appointments\Appointment;
use App\Models\Appointments\Group;
use Illuminate\Support\Facades\App;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Auth\Team\WorkingLocation;
use App\Models\Customers\Customer;
use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Support\Facades\DB;

class AppointmentsRepository
{
    private $AppointmentEventsRepository;
    private $AgentSchedulesRepository;

    function __construct()
    {
        /** @var  AppointmentEventsRepository */
        $this->AppointmentEventsRepository = App::make('AppointmentEventsRepository');
        /** @var  AgentSchedulesRepository */
        $this->AgentSchedulesRepository = App::make('AgentSchedulesRepository');
    }

    //Returns filtered appointments
    public function getAppointments($appointmentInformation) {
        $with_user_account_information = array_key_exists('with_agent', $appointmentInformation) ? $appointmentInformation['with_agent'] : false;
        $with_customer = array_key_exists('with_customer', $appointmentInformation) ? $appointmentInformation['with_customer'] : false;
        $with_working_location = array_key_exists('with_working_location', $appointmentInformation) ? $appointmentInformation['with_working_location'] : false;
        $sort = array_key_exists('sort', $appointmentInformation) ? $appointmentInformation['sort'] : false;
        $direction = array_key_exists('direction', $appointmentInformation) ? $appointmentInformation['direction'] : false;
        $appointment_id = array_key_exists('appointment_id', $appointmentInformation) ? $appointmentInformation['appointment_id'] : false;
        $inquiry_id = array_key_exists('inquiry_id', $appointmentInformation) ? $appointmentInformation['inquiry_id'] : false;
        $working_location_id = array_key_exists('working_location_id', $appointmentInformation) ? $appointmentInformation['working_location_id'] : false;
        $start = array_key_exists('start', $appointmentInformation) ? $appointmentInformation['start'] : false;
        $end = array_key_exists('end', $appointmentInformation) ? $appointmentInformation['end'] : false;
        $agent_id = array_key_exists('user_account_information_id', $appointmentInformation) ? $appointmentInformation['user_account_information_id'] : false;
        $offset = array_key_exists('offset', $appointmentInformation) ? $appointmentInformation['offset'] : false;
        $limit = array_key_exists('limit', $appointmentInformation) ? $appointmentInformation['limit'] : false;

        if($appointmentInformation['filters']) {
            $filters = $appointmentInformation['filters'];
            $dateRangeStart = $filters['dateRange']['start'];
            $dateRangeEnd = $filters['dateRange']['end'];
            $filtersStatus = array_key_exists('status', $filters) ? $filters['status'] : false;
            $workingLocations = array_key_exists('working_locations', $filters) ? $filters['working_locations'] : false;
        } else {
            $dateRangeStart = false;
            $dateRangeEnd = false;
            $filtersStatus = false;
            $workingLocations = false;
        }

        $appointments = Appointment::
              when($with_user_account_information, function ($query) {
                  return $query->with('userAccountInformation');
            })
            ->when($with_customer, function ($query) {
                return $query->with('customers.emailAddresses');
            })
            ->when($with_customer, function ($query) {
                return $query->with('customers.phoneNumbers');
            })
            ->when($with_working_location, function ($query) {
                return $query->with('workingLocation');
            })
            ->when($sort && $direction, function ($query) use ($sort, $direction) {
                return $query->orderBy($sort, $direction);
            })
            ->when($appointment_id, function ($query) use ($appointment_id) {
                return $query->where('id', $appointment_id);
            })
            ->when($inquiry_id, function ($query) use ($inquiry_id) {
                return $query->where('inquiry_id', $inquiry_id);
            })
            ->when($working_location_id, function ($query) use ($working_location_id) {
                return $query->where('working_location_id', $working_location_id);
            })
            ->when($start, function ($query) use ($start) {
                return $query->where ('start', '>=', $start);
            })
            ->when($end, function ($query) use ($end) {
                return $query->where('end', '<=', $end);
            })
            ->when($agent_id, function ($q) use ($agent_id){
                return $q->whereHas('userAccountInformation', function($q) use ($agent_id){
                    $q->where('user_account_information_id', $agent_id);
               });
            })
            ->when($offset, function ($query) use ($offset) {
                return $query->offset($offset);
            })
            ->when($limit, function($query) use ($limit) {
                return $query->limit($limit);
            })
            ->when($dateRangeStart && $dateRangeEnd, function ($query) use ($dateRangeStart, $dateRangeEnd) {
                return $query->where('start', '>=', $dateRangeStart)->where('end', '<=', $dateRangeEnd);
            })
            ->when($workingLocations, function ($query) use ($workingLocations) {
                return $query->whereIn('working_location_id', $workingLocations);
            })
            ->get();

        if($filtersStatus != false && $filtersStatus != "false") {
            $newAppointments = array();
            foreach($appointments as $appointment) {
                $appointment->toArray();
                $appointment['status'] = $this->AppointmentEventsRepository->determineAppointmentStatus($appointment['id']);
                if (in_array($appointment['status'], $filtersStatus)) {
                    array_push($newAppointments, $appointment);
                }
            }
            return $newAppointments;
        }
        return $appointments;
    }

    //Creates a new appointment
    public function createNewAppointment($start, $end, $working_location_id, $agent_ids, $customer_ids)
    {
        $appointmentSaveStatus = array(
            'success' => 'true',
            'appointmentID' => null,
            'errors' => []
        );

        //Check for available agents during time frame provided
        foreach($agent_ids as $agent_id) {
            if (!$availableAgent = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($start, $end, $working_location_id, $agent_id)) {
                $agent = UserAccountInformation::find($agent_id);
                $appointmentSaveStatus['success'] = 'false';
                $appointmentSaveStatus['errors'][] = 'Agents are not available for the specified time';
                return $appointmentSaveStatus;
            }
        }

        $customers = array();
        foreach ($customer_ids as $customer_id) {
            array_push($customers, Customer::find($customer_id));
        }
        if (empty($customers)) {
            $appointmentSaveStatus['success'] = 'false';
            $appointmentSaveStatus['errors'][] = 'The selected customer was not found. Please try again.';
            return $appointmentSaveStatus;
        }
        $appointment = new Appointment();
        $appointment->start = $start;
        $appointment->end = $end;

        //todo appointment is allowed to save multiple times if submitted from same page w/o refreshing
        try {
            DB::beginTransaction();
            $appointment->save();
            $appointment->workingLocation()->associate(WorkingLocation::findOrFail($working_location_id));
            $appointment->userAccountInformation()->sync($agent_ids);
            $appointment->customers()->sync($customer_ids);
            $appointmentSaveStatus['appointmentID'] = $appointment->id;
            $appointment->save();
            DB::commit();
        } catch (PDOException $e) {
            DB::rollback();
            $appointmentSaveStatus['success'] = 'false';
            $appointmentSaveStatus['errors'][] = 'The appointment was not successfully created';
            return $appointmentSaveStatus;
        }
        return $appointmentSaveStatus;
    }

    public function updateAppointment($appointment_id, $start, $end, $working_location_id, $agent_ids, $customer_ids)
    {
        $appointmentSaveStatus = array(
            'success' => 'true',
            'errors' => []
        );
        if (!$appointment = Appointment::find($appointment_id)) {
            $appointmentSaveStatus['success'] = 'false';
            $appointmentSaveStatus['errors'][] = 'Could not find the appointment';
            return $appointmentSaveStatus;
        }

        $existingAgents = array();

        foreach($appointment->userAccountInformation as $existingAgent) {
            array_push($existingAgents, $existingAgent->id);
        }

        foreach($agent_ids as $agent_id) {
            if (!in_array($agent_id, $existingAgents)) {
                if (!$availableAgent = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($start, $end, $working_location_id, $agent_id)) {
                    $appointmentSaveStatus['success'] = 'false';
                    $appointmentSaveStatus['errors'][] = $availableAgent->first_name . ' ' . $availableAgent->last_name . ' is not available for the specified time';
                    return $appointmentSaveStatus;
                }
            }
        }

        $appointment->start = $start ?: $appointment->start;
        $appointment->end = $end ?: $appointment->end;
        try {
            DB::beginTransaction();
            $previousAppointmentCustomerIDs = array();
            $previousAppointmentCustomers = $appointment->customers;
            foreach($previousAppointmentCustomers as $previousAppointmentCustomer) {
                array_push($previousAppointmentCustomerIDs, $previousAppointmentCustomer->id);
            }

            //If a customer ID is not present in customer_ids and was in previousCustomerAppointmentID's, store an event that the customer has been removed
            foreach($previousAppointmentCustomerIDs as $previousAppointmentCustomerID) {
                if(!in_array($previousAppointmentCustomerID, $customer_ids)) {
                    $appointmentEventSaved = $this->AppointmentEventsRepository->addNewAppointmentEvent($appointment->id, 'withdrawal', 'customer', false, $previousAppointmentCustomerID);
                    if($appointmentEventSaved['success'] == 'false') {
                        $appointmentSaveStatus['success'] = 'false';
                        $appointmentSaveStatus['errors'][] = 'The appointment event for a removed customer was not able to be saved';
                    }
                }
            }

            //If a customer ID is present in customer_ids ans was not in previousCustomerAppointmentID's, store an event that the customer has been added
            foreach($customer_ids as $providedCustomerID) {
                if(!in_array($providedCustomerID, $previousAppointmentCustomerIDs)) {
                    $appointmentEventSaved = $this->AppointmentEventsRepository->addNewAppointmentEvent($appointment->id, 'addition', 'customer', false, $providedCustomerID);
                    if($appointmentEventSaved['success'] == 'false') {
                        $appointmentSaveStatus['success'] = 'false';
                        $appointmentSaveStatus['errors'][] = 'The appointment event for a new customer was not able to be saved';
                    }
                }
            }

            $previousAppointmentAgentIDs = array();
            $previousAppointmentAgents = $appointment->userAccountInformation;
            foreach($previousAppointmentAgents as $previousAppointmentAgent) {
                array_push($previousAppointmentAgentIDs, $previousAppointmentAgent->id);
            }
            //store an event indicating whether or not
            foreach($previousAppointmentAgentIDs as $previousAppointmentAgentID) {
                if (!in_array($previousAppointmentAgentID, $agent_ids)) {
                    $appointmentEventSaved = $this->AppointmentEventsRepository->addNewAppointmentEvent($appointment->id, 'withdrawal', 'agent', false, $previousAppointmentAgentID);
                    if($appointmentEventSaved['success'] == 'false') {
                        $appointmentSaveStatus['success'] = 'false';
                        $appointmentSaveStatus['errors'][] = 'The appointment event for a removed agent was not able to be saved';
                    }
                }
            }

            foreach($agent_ids as $providedAgentID) {
                if (!in_array($providedAgentID, $previousAppointmentAgentIDs)) {
                    $appointmentEventSaved = $this->AppointmentEventsRepository->addNewAppointmentEvent($appointment->id, 'addition', 'agent', false, $providedAgentID);
                    if($appointmentEventSaved['success'] == 'false') {
                        $appointmentSaveStatus['success'] = 'false';
                        $appointmentSaveStatus['errors'][] = 'The appointment event for a new agent was not able to be saved';
                    }
                }
            }

            $appointment->workingLocation()->associate(WorkingLocation::findOrFail($working_location_id));
            $appointment->userAccountInformation()->sync($agent_ids);
            $appointment->customers()->sync($customer_ids);
            $appointment->save();
            DB::commit();
        } catch (PDOException $e) {
            DB::rollback();
            $appointmentSaveStatus['success'] = 'false';
            $appointmentSaveStatus['errors'][] = 'The appointment was not successfully updated';
            return $appointmentSaveStatus;
        }
        return $appointmentSaveStatus;
    }

    public function getAppointmentsList() {
        $appointments = Appointment::with('workingLocation','userAccountInformation','customers')->OrderBy('start','desc')->paginate(15);
        foreach($appointments as $appointment) {
            $appointment->status = $this->AppointmentEventsRepository->determineAppointmentStatus($appointment->id);
        }
        return $appointments;
    }
}