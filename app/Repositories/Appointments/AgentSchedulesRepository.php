<?php

namespace App\Repositories\Appointments;

use App\Models\Appointments\AgentSchedule;
use App\Models\Auth\Team\UserAccountInformation;
use Illuminate\Foundation\Auth\User;
use Mockery\Exception;
use Carbon\Carbon;
use App\Models\Auth\Team\WorkingLocation;

class AgentSchedulesRepository
{
    private $AppointmentEventsRepository;
    public function __construct()
    {
        $this->AppointmentEventsRepository = \App::make(AppointmentEventsRepository::class);
    }

    public function agentSchedules($agentScheduleInformation) {
        if ($agentScheduleInformation) {
            $user_account_information_id = array_key_exists('user_account_information_id', $agentScheduleInformation) ? $agentScheduleInformation['user_account_information_id'] : false;
            $with_agent = array_key_exists('with_agent', $agentScheduleInformation) ? $agentScheduleInformation['with_agent'] : false;
            $working_location_id = array_key_exists('working_location_id', $agentScheduleInformation) ? $agentScheduleInformation['working_location_id'] : false;
            $start = array_key_exists('start', $agentScheduleInformation) ? $agentScheduleInformation['start'] : false;
            $end = array_key_exists('end', $agentScheduleInformation) ? $agentScheduleInformation['end'] : false;
            $offset = array_key_exists('offset', $agentScheduleInformation) ? $agentScheduleInformation['offset'] : false;
            $limit = array_key_exists('limit', $agentScheduleInformation) ? $agentScheduleInformation['limit'] : false;
        }
        else {
            $user_account_information_id = false;
            $with_agent = false;
            $working_location_id = false;
            $start = false;
            $end = false;
            $offset = false;
            $limit = false;
        }

        $agentSchedule = AgentSchedule::
              when($user_account_information_id, function ($query) use ($user_account_information_id) {
                return $query->where('user_account_information_id', $user_account_information_id);
            })
            ->when($with_agent, function ($query) {
                return $query->with('userAccountInformation');
            })
            ->when($working_location_id, function ($query) use ($working_location_id) {
                return $query->where('working_location_id', $working_location_id)->groupby('start');
            })
            ->when(($start), function ($query) use ($end) {
                return $query->where('start', '<=', $end);
            })
            ->when(($end), function ($query) use ($start) {
                return $query->where('end', '>=', $start);
            })
            ->when($offset, function ($query) use ($offset) {
                return $query->offset($offset);
            })
            ->when($limit, function ($query) use ($limit) {
                return $query->limit($limit);
            })
            ->get();

        return $agentSchedule;
    }

    public function checkExistingSchedules($userAccountInformationID, $start, $end, $working_location_id = false) {
        $agent['user_account_information_id'] = $userAccountInformationID;
        $agent['start'] = new Carbon($start);
        $agent['end'] = new Carbon($end);
        $agent['working_location_id'] = $working_location_id ?: false;

        $existingAgentSchedules = self::agentSchedules($agent);
        return $existingAgentSchedules;
    }


    public function createAgentSchedule($start, $end, $userAccountInformationID, $workingLocationID) {
        $agentSchedule = new AgentSchedule();
        $agentSchedule->start = $start;
        $agentSchedule->end = $end;
        $agentSchedule->working_location_id = ($workingLocationID);
        $agentSchedule->userAccountInformation()->associate($userAccountInformationID);
        $agentSchedule->save();

        return $agentSchedule;
    }

    public function updateAgentSchedules($start, $end, $userAccountInformationID, $scheduleID, $workingLocationID = false) {
        $schedule = AgentSchedule::findOrFail($scheduleID);
        $schedule->start = $start ?: $schedule->start;
        $schedule->end = $end ?: $schedule->end;
        $schedule->userAccountInformation()->associate(UserAccountInformation::findOrFail($userAccountInformationID));
        if ($workingLocationID) {
            $schedule->workingLocation()->associate(WorkingLocation::findOrFail($workingLocationID));
        }

        return $schedule->save();
    }

    public function delete($id) {
        $agentSchedule = AgentSchedule::findOrFail($id);
        return $agentSchedule->delete();
    }

    public function getAvailableAgentsForTimeFrame($start, $end, $workingLocationID, $agentID = false) {
        $agentIsAvailable = true;
        $availableAgents = array();

        //Retrieve all agent schedules for a given time frame

        //create a callback that is used to retrieve only the agents schedules that overlap with the requested appointment time
        $agentSchedulesCallback = function($query) use ($start, $end) {
            $query->where('start', '<=', new Carbon($start))->where('end', '>=', new Carbon($end));
        };
        //retrieve all available agents with their associated appointments and schedules
        $agents = UserAccountInformation::with(['appointments', 'agentSchedules' => $agentSchedulesCallback])
            ->whereHas('agentSchedules', $agentSchedulesCallback)
            ->whereHas('workingLocation', function($query) use ($workingLocationID) {
                $query->where('id', $workingLocationID);
            })
            ->get();
        //Iterate through agents available in the given time frame
        foreach($agents as $agent) {
            //Iterate through each agent's appointments associated with the schedule
            $appointments = $agent->appointments;
            foreach ($appointments as $appointment) {
                $appointment->status = $this->AppointmentEventsRepository->determineAppointmentStatus($appointment->id);
                //Check for appointments for the given time frame
                if ($appointment->start <= $end && $appointment->end >= $start) {
                    $agentIsAvailable = false;
                }
            }
            //If the agent doesn't have any appointment in the given time frame, the agent is available
            if ($agentIsAvailable == true) {
                if ($agentID != false && $agent->id != false && $agentID == $agent->id) {
                    array_push($availableAgents, array(
                        'first_name' => $agent->first_name,
                        'last_name' => $agent->last_name,
                        'work_phone' => $agent->work_phone,
                        'email_address' => $agent->email_address,
                        'user_account_information_id' => $agent->id,
                        'available_schedule_start' => $agent->agentSchedules->first()->start,
                        'available_schedule_end' => $agent->agentSchedules->first()->end
                    ));
                } else if ($agentID == false) {
                    array_push($availableAgents, array(
                        'first_name' => $agent->first_name,
                        'last_name' => $agent->last_name,
                        'work_phone' => $agent->work_phone,
                        'email_address' => $agent->email_address,
                        'user_account_information_id' => $agent->id,
                        'available_schedule_start' => $agent->agentSchedules->first()->start,
                        'available_schedule_end' => $agent->agentSchedules->first()->end
                    ));
                }
            }
            $agentIsAvailable = true;
        }
        return $availableAgents;
    }
}
