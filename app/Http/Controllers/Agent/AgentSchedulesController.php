<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Api\AgentSchedulesResource;
use App\Http\Controllers\Controller;
use App\Models\Auth\Team\WorkingLocation;
use App\Repositories\Appointments\AgentSchedulesRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\App;

class AgentSchedulesController extends Controller
{
    private $AgentSchedulesRepository;
    private $AgentSchedulesResource;

    function __construct()
    {
        $this->AgentSchedulesRepository = App::make(AgentSchedulesRepository::class);
        $this->AgentSchedulesResource = App::make(AgentSchedulesResource::class);
    }

    public function agentScheduleList() {
        $allAgents = $this->AgentSchedulesResource->show();
        return view('agentScheduling/agentScheduleList', ['allSchedules' => $allAgents]);
    }

    public function getExistingSchedules(Request $request) {
        $userAccountInformationID = $request->input('user_account_information_id');
        $start = $request->input('start');
        $end = $request->input('end');
        $working_location_id = $request->input('working_location_id', false);

        $existingSchedules = $this->AgentSchedulesRepository->checkExistingSchedules($userAccountInformationID, $start, $end);

        return $existingSchedules;
    }

    public function agentSchedulesFromDates(Request $request) {
        $userAccountInformationID = $request->input('user_account_information_id', false);
        $start = $request->input('start');
        $end = $request->input('end');
//        $working_location_id = $request->input('working_location_id');
        //This is defaulted to false because the schedules for an agent should be returned for every property they work at
        $working_location_id = false;

        $existingSchedules = $this->AgentSchedulesRepository->checkExistingSchedules($userAccountInformationID, $start, $end, $working_location_id);

//        $existingSchedules = is_array($existingSchedules) ?: array($existingSchedules);

        //Format and add information to existing schedules for return
        $agentSchedules = array();
        foreach($existingSchedules as $schedule) {
            $schedule_id = $schedule->id;
            $startDate = new DateTime($schedule->start);
            $endDate = new DateTime($schedule->end);

            $shiftStart = $startDate->format('h:i a');
            $shiftEnd = $endDate->format('h:i a');

            //Format start day name and index for array
            $startDayName = $startDate->format('l');
            $startDateFormatted = $startDate->format('m/d');

            $workingLocation = $schedule->workingLocation->short_name;
            $workingLocationID = $schedule->workingLocation->id;

            $agentSchedules[$schedule_id] = array(
                'id' => $schedule_id,
                'dayName' => $startDayName,
                'startDateFormatted' => $startDateFormatted,
                'dates' => array(),
                'startDateTime' => $startDate,
                'endDateTime' =>$endDate,
                'workingLocation' => $workingLocation,
                'workingLocationID' => $workingLocationID
            );
            //Create array for shift to add to the day's schedules
            $shift = array(
                'start' => $shiftStart,
                'end' => $shiftEnd
            );
            //Add shift onto agent schedule
            array_push($agentSchedules[$schedule_id]['dates'], $shift);
        }
        return $agentSchedules;
    }

    public function agentScheduleCalendar() {
        $workingLocations = WorkingLocation::all();
        return view('agentScheduling.agentScheduleCalendar', ['working_locations' => $workingLocations]);
    }

    public function agentScheduleForm() {
        $workingLocations = WorkingLocation::all();
        return view('agentScheduling.agentScheduleForm', ['working_locations' => $workingLocations]);
    }
}