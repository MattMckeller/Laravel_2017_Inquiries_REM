<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Api\AppointmentResource;
use App\Http\Controllers\Controller;
use App\Models\Auth\Team\UserAccountInformation;
use App\Repositories\Auth\UserAccountInformationRepository;
use App\Http\Controllers\Api\UserAccountInformationResource;
use App\Repositories\Inquiries\InquiryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AgentsController extends Controller
{
    private $AppointmentResource;
    private $UserAccountInformationRepository;
    private $UserAccountInformationResource;
    private $InquiryRepository;
    private $AccessControl;

    function __construct()
    {
        $this->AppointmentResource = App::make(AppointmentResource::class);
        $this->UserAccountInformationRepository = App::make(UserAccountInformationRepository::class);
        $this->UserAccountInformationResource = App::make(UserAccountInformationResource::class);
        $this->InquiryRepository = App::make(InquiryRepository::class);
    }

    public function showAgentUpdateProfile($AID = false) {
        $agent = $this->UserAccountInformationRepository->getByID($AID);
        return view('agentProfile/updateAgent', ['agent' => $agent]);
    }

    public function agentList() {
        $allAgents = $this->UserAccountInformationResource->getDisplayableAgentList();
        return view('agentProfile/agentsList', ['agents' => $allAgents]);
    }

    public function agentProfile($AID) {
        $agent = UserAccountInformation::findOrFail($AID);

        //Get all unclaimed appointments
        //get all inquiries claimed by agent
        return view('agentProfile/agentProfile')->with([
            'agent' => $agent,
            'unclaimedAppointments' => $this->AppointmentResource->getUnclaimedAppointments(),
//            'claimedLeads' => $this->InquiryRepository->getInquiryList($userRole, $AID)
            'claimedLeads' => $this->InquiryRepository->getAgentLeads($AID)
        ]);
    }

    public function submitAgentUpdate(Request $request) {
        $agentInformation = array(
            'userAccountInformationID' => $request->input('agentID'),
            'phoneNumber' => $request->input('phoneNumber'),
            'emailAddress' => $request->input('emailAddress'),
            'workingLocations' => $request->input('workingLocations')
        );

        $updatedAgent = $this->UserAccountInformationResource->edit($agentInformation);
        $agent = json_decode($updatedAgent->content(), TRUE);
        if(!empty($updatedAgent)) {
            return redirect('/agents/' . $agent['id']);
        }
        return false;
    }
}