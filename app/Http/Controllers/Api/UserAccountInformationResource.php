<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auth\Team\UserAccountInformation;
use App\Repositories\Auth\UserAccountInformationRepository;
use App\Repositories\Appointments\AppointmentEventsRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;

class UserAccountInformationResource extends Controller {

    private $AppointmentEventsRepository;
    private  $UserAccountInformationRepository;

    function __construct()
    {
        $this->AppointmentEventsRepository = App::make(AppointmentEventsRepository::class);
        $this->UserAccountInformationRepository = App::make(UserAccountInformationRepository::class);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request = NULL) {
        $agentInformation = array();
        $agentInformation['user_account_information_id'] = $request->input('user_account_information_id', false);
        $agentInformation['working_location_id'] = $request->input('working_location_id', false);
        $agentInformation['email_address'] = $request->input('email_address', false);
        $agentInformation['work_phone'] = $request->input('work_phone', false);
        $agentInformation['phone_ext'] = $request->input('phone_ext', false);
        $agentInformation['position'] = $request->input('position', false);
        $agentInformation['order'] = $request->input('order', false);
        $agentInformation['offset'] = $request->input('offset', false);
        $agentInformation['limit'] = $request->input('limit', false);
        $agentInformation['with_appointments'] = $request->input('with_appointments', false);
        $agentInformation['with_working_location'] = $request->input('with_working_location', false);
        $agentInformation['agentInfo'] = $request->input('agentInfo', false);

        if ($result = $this->UserAccountInformationRepository->getAgents($agentInformation))
            return response($result, 200);
        else
            return response(json_encode(array('agents' => 'There are no agents of that description')), 500);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        return response("No create method currently available", 500);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response Response
     */
    public function store(Request $request) {
        $agent = new UserAccountInformation();

        //Retrieve all columns for the model
        $agentColumns = Schema::getColumnListing($agent->getTable());

        //Prepare array to be sent for model save
        foreach($agentColumns as $agentColumn) {
            $agent->$agentColumn = $request->input($agentColumn);
        }

        $agentSaved = $agent->save();
        return $agentSaved ? response(['agent' => $agent], 201) : response(['agent' => $agent], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $agent = UserAccountInformation::with('appointments.customers', 'workingLocation', 'appointments.workingLocation')->find($id);
        foreach($agent->appointments as $appointment)
            $appointment->status = $this->AppointmentEventsRepository->determineAppointmentStatus($appointment->id);
        return response($agent, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Array $agentInformation
     * @return \Illuminate\Http\Response
     */
    public function edit($agentInformation) {
        $agentID = $agentInformation['userAccountInformationID'];
        $providedPhoneNumber = $agentInformation['phoneNumber'];
        $providedEmailAddress = $agentInformation['emailAddress'];
        $providedWorkingLocations = $agentInformation['workingLocations'];

        //Find agent that is being edited
        $agent = UserAccountInformation::findOrFail($agentID);

        if(!empty($providedPhoneNumber)) {
            $agent->work_phone = $providedPhoneNumber;
            $agent->save();
        }

        if(!empty($providedEmailAddress)) {
            $agent->email_address = $providedEmailAddress;
            $agent->save();
        }
        //Save new location if provided
        if(!empty($providedWorkingLocations)) {
            $agent->workingLocation()->sync($providedWorkingLocations);
        }

        //Retrieve updated agent information
        $updatedAgent = UserAccountInformation::findOrFail($agentID);
        return response($updatedAgent, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        return response("No update method currently available", 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        return response("No destroy method available", 500);
    }

    /**
     * Return all agents
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator $agents
     */
    public function getAgentList() {
        $agents = $this->UserAccountInformationRepository->getAgentsList();
        return $agents;
    }

    /**
     * Return all agents
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator $agents
     */
    public function getDisplayableAgentList() {
        $agents = $this->UserAccountInformationRepository->getDisplayableAgentList();
        return $agents;
    }
}
