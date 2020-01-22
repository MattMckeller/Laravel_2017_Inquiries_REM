<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointments\AgentSchedule;
use App\Http\Controllers\Controller;
use App\Models\Auth\Team\WorkingLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\Appointments\AgentSchedulesRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class AgentSchedulesResource extends Controller
{
    private $AgentSchedulesRepository;

    function __construct()
    {
        $this->AgentSchedulesRepository = App::make(AgentSchedulesRepository::class);
    }

    /**
     * Return a list of agent schedules.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $agentScheduleInformation = array();
        $agentScheduleInformation['user_account_information_id'] = $request->input('user_account_information_id', false);
        $agentScheduleInformation['with_working_location'] = $request->input('with_working_location', false);
        $agentScheduleInformation['working_location_id'] = $request->input('working_location_id', false);
        $agentScheduleInformation['start'] = $request->input('start', false);
        $agentScheduleInformation['end'] = $request->input('end', false);
        $agentScheduleInformation['with_agent'] = $request->input('with_agent', false);
        $agentScheduleInformation['offset'] = $request->input('offset', false);
        $agentScheduleInformation['limit'] = $request->input('limit', false);

        if ($schedules = $this->AgentSchedulesRepository->agentSchedules($agentScheduleInformation))
            return response(json_encode($schedules), 200);
        else
            return response('Failure', 500);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return response("No create method currently available", 500);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $agents = $request->input('agents', false);
        if($agents && !empty($agents)) {
            $agentSchedule = array();
            foreach($agents as $agent) {
                $agent_id = $agent['id'];
                foreach ($agent['schedules'] as $schedule) {
                    $start = $schedule['start'];
                    $end = $schedule['end'];
                    $locationID = $schedule['locationID'];

                    //Check for properly provided start and end
                    $carbonStartDateTime = new Carbon($start);
                    $carbonEndDateTime = new Carbon($end);
                    if($carbonStartDateTime->gt($carbonEndDateTime)) {
                        return response(array('success' => 'false', 'errors' => ['The agent schedule start date must be before the end date.']), 400);
                    }

                    $existingSchedules = $this->AgentSchedulesRepository->checkExistingSchedules($agent_id, $start, $end);

                    if($existingSchedules->isNotEmpty()) {
                        return response(array('success' => 'false', 'errors' => ['This agent already has a schedule at this time.']), 409);
                    }
                    if($schedule = $this->AgentSchedulesRepository->createAgentSchedule($start, $end, $agent_id, $locationID)) {
                        array_push($agentSchedule, $schedule);
                    }
                    else {
                        return response(array('success' => 'false', 'errors' => ['Unable to add the schedule for the agent.']), 409);
                    }
                }
            }
            return response(array('success' => 'true', 'errors' => []), 200);
        }
        else {
            return response(array('success' => 'false', 'errors' => ['Required information is missing.']), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Object $allSchedules
     */
    public function show() {
        $allSchedules = $this->AgentSchedulesRepository->agentSchedules(['with_agent' => true]);
        return $allSchedules;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request) {
        $start = $request->input('start', false);
        $end = $request->input('end', false);
        $user_account_information_id = $request->input('user_account_information_id', false);

        $existingSchedules = $this->AgentSchedulesRepository->checkExistingSchedules($user_account_information_id, $start, $end);
        return response($existingSchedules, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        $scheduleEdits = $request->input('schedules', false);

        if ($scheduleEdits) {
            $shouldSave = true;
            $existingSchedules = array();
            //this foreach is for checking to see that can be saved
            foreach ($scheduleEdits as $scheduleEdit) {
                $schedule_id = $scheduleEdit['id'];
                $schedule_start = $scheduleEdit['start'];
                $schedule_end = $scheduleEdit['end'];
                $schedule = AgentSchedule::findOrFail($schedule_id);
                $agent_id = $schedule->user_account_information_id;
                $working_location_id = array_key_exists('working_location_id', $scheduleEdit) ? $scheduleEdit['working_location_id'] : $schedule->working_location_id;

                $conflictingSchedules = $this->AgentSchedulesRepository->checkExistingSchedules($agent_id, $schedule_start, $schedule_end);
                if (($conflictingSchedules->count() == 1 && $conflictingSchedules[0]->id == $schedule_id) || !$conflictingSchedules->isNotEmpty()) {
                    continue;
                }
                elseif ($conflictingSchedules->isNotEmpty()) {
                    $existingSchedules[] = $conflictingSchedules;
                }
            }
            if (!empty($existingSchedules)) {
                return response(array('success' => 'false', 'errors' => ['This agent already has a schedule during one of these times.']), 409);
            }

            //this foreach is for actually saving
            foreach ($scheduleEdits as $scheduleEdit) {
                $schedule_id = $scheduleEdit['id'];
                $schedule_start = $scheduleEdit['start'];
                $schedule_end = $scheduleEdit['end'];
                $schedule = AgentSchedule::findOrFail($schedule_id);
                $agent_id = $schedule->user_account_information_id;
                $working_location_id = array_key_exists('working_location_id', $scheduleEdit) ? $scheduleEdit['working_location_id'] : $schedule->working_location_id;
                $saveAttempt = true;

                $existingSchedule = $this->AgentSchedulesRepository->checkExistingSchedules($agent_id, $schedule_start, $schedule_end);
                //check to see if the only schedule returned is the one we're attempting to modify
                $saveAttempt &= $this->AgentSchedulesRepository->updateAgentSchedules($schedule_start, $schedule_end, $agent_id, $schedule_id, $working_location_id);
            }
            return response(array('success' => 'true', 'errors' => []), 200);
        }
        else {
            return response(array('success' => 'false', 'errors' => ['Nothing was submitted.']), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return int $id
     */
    public function destroy($id) {
        $agentSchedule = AgentSchedule::find($id);
        if ($agentSchedule->delete()) {
            return response(array(
                'success' => 'true',
                'errors' => ''
            ), 200);
        }
        else {
            return response(array(
                'success' => 'false',
                'errors' => ['Unable to delete the schedule.']
            ), 400);
        }
    }

    //Returns count of working agents for a given time frame
    //todo create a repository function that will return me a list of start/end DateTime's for a given property
    public function createCalendar($working_location_id) {
        //Get all the start times for agents at working location
        $foundStartTimes = WorkingLocation::find($working_location_id)->agentSchedules()->select(DB::raw('start, count(start) as count'))->groupBy('start')->get();
        //Get all the end times for agents at working location
        $foundEndTimes = WorkingLocation::find($working_location_id)->agentSchedules()->select(DB::raw('end, count(end) as count'))->groupBy('end')->get();

        // creating temp arrays to hold the start and end times and how many schedules start at that specific time
        // each array addition will have a time and a count. The count is in reference to how many agent_schedules start/end
        // at that specific time
        $start_times = array();
        $end_times = array();

        foreach($foundStartTimes as $foundStartTime) {
            $temp_start = array(
                'time' => $foundStartTime->start,
                'count' => $foundStartTime->count
            );
            array_push($start_times, $temp_start);
        }
        foreach($foundEndTimes as $foundEndTime) {
            $temp_end = array(
                'time' => $foundEndTime->end,
                'count' => $foundEndTime->count

            );
            array_push($end_times, $temp_end);
        }

        $j = 0;
        $i = 0;
        // this array will hold the compiled list of start and end times with the number
        // of agents that will be working during that given time period
        $time_slots = array();
        $count = 0;

        //temp variable because the length of the array changes when you use 'array_shift'
        $holder_start = count($start_times);
        $holder_end = count($end_times);
        while ($i < $holder_start + 1 && $j < $holder_end + 1) {
            $new_time_slot = array(
                'start' => '',
                'end' => '',
                'count' => 0
            );
            $date1 = new Carbon(current($start_times)['time']);
            $date2 = new Carbon(current($end_times)['time']);
            // if the day of the year is different between the first element on the start list and the first element on the end list,
            // we will have to create new events for the remaining elements that exist for that day.
            if ($date1->dayOfYear > $date2->dayOfYear) {
                //creating an event for the remaining elements
                $count = $count - current($end_times)['count'];
                $new_time_slot['count'] = $count;
                $new_time_slot['start'] = array_shift($end_times)['time'];
                $new_time_slot['end'] = current($end_times)['time'];

                array_push($time_slots, $new_time_slot);
                // pushing the newly created element onto the array of compiled 'event' times
                $j++;
            }
            // if the days on top of both arrays are the same I see if the first element in each array is the same.
            // In this case, count = count + (num start events) - (num end events)
            else if (current($start_times)['time'] == current($end_times)['time']) {
                $count = $count + current($start_times)['count'] - array_shift($end_times)['count'];
                $new_time_slot['count'] = $count;
                $new_time_slot['start'] = array_shift($start_times)['time'];
                $date1 = new Carbon(current($start_times)['time']);
                $date2 = new Carbon(current($end_times)['time']);
                if ($date1->min($date2) === $date1 && current($start_times)['time'] != NULL) {
                    $new_time_slot['end'] = current($start_times)['time'];
                }
                else {
                    $new_time_slot['end'] = current($end_times)['time'];
                }
                $i++;
                $j++;
                array_push($time_slots, $new_time_slot);
            }
            // the other case is that the time at the top of the arrays are different. We first find the time
            // that is earlier. Depending on that, we do some logic to decide what the count needs to be (subtract if
            // on end array & add if on start array)
            else {
                $date1 = new Carbon(current($start_times)['time']);
                $date2 = new Carbon(current($end_times)['time']);
                // case where the start time is earlier than the end time
                if ($date1->min($date2) === $date1 && $i < $foundStartTimes->count() && current($start_times)['time'] != NULL) {
                    $new_time_slot['start'] = current($start_times)['time'];
                    $count = $count + array_shift($start_times)['count'];
                    $new_time_slot['count'] = $count;
                    $date1 = new Carbon(current($start_times)['time']);
                    $i++;
                } // case where the end time is earlier than the start time
                else {
                    $new_time_slot['start'] = current($end_times)['time'];
                    $count = $count - array_shift($end_times)['count'];
                    $new_time_slot['count'] = $count;

                    $date2 = new Carbon(current($end_times)['time']);

                    $j++;
                }
                // adding the next most current time to the end time of the current array element that will
                // get added to the compiled array.
                if ($date1->min($date2) === $date1 && current($start_times)['time'] != NULL) {
                    $new_time_slot['end'] = current($start_times)['time'];
                }
                else {
                    $new_time_slot['end'] = current($end_times)['time'];
                }
                //pushing the newly created 'event' onto the compiled array
                array_push($time_slots, $new_time_slot);
            }
        }
        // returning the compiled list
        return $time_slots;
    }

    public function getAvailableAgentsForTimeFrame(Request $request)
    {
        $startDateString = $request->input('start_datetime', false);
        $endDateString = $request->input('end_datetime', false);
        $locationID = $request->input('working_location_id', false);
        //Format dates for call to repository
        $start = new Carbon($startDateString);
        $end = new Carbon($endDateString);

        $agents = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($start, $end, $locationID);
        return $agents;
    }
}
