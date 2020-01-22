<?php

namespace App\Repositories\Auth;

use App\Models\Auth\Team\UserAccountInformation;
use Illuminate\Support\Facades\App;

class UserAccountInformationRepository
{
    private $AgentSchedulesRepository;

    function __construct()
    {
        $this->AgentSchedulesRepository = App::make('AgentSchedulesRepository');
    }

    public function getAgents($agentInformation)
    {
        $with_appointments = array_key_exists('with_appointments', $agentInformation) ? $agentInformation['with_appointments'] : false;
        $with_working_location = array_key_exists('with_working_location', $agentInformation) ? $agentInformation['with_working_location'] : false;
        $agent_id = array_key_exists('user_account_information_id', $agentInformation) ? $agentInformation['user_account_information_id'] : false;
        $working_location_id = array_key_exists('working_location_id', $agentInformation) ? $agentInformation['working_location_id'] : false;
        $email_address = array_key_exists('email_address', $agentInformation) ? $agentInformation['email_address'] : false;
        $work_phone = array_key_exists('work_phone', $agentInformation) ? $agentInformation['work_phone'] : false;
        $phone_ext = array_key_exists('phone_ext', $agentInformation) ? $agentInformation['phone_ext'] : false;
        $position = array_key_exists('position', $agentInformation) ? $agentInformation['position'] : false;
        $order = array_key_exists('order', $agentInformation) ? $agentInformation['order'] : false;
        $offset = array_key_exists('offset', $agentInformation) ? $agentInformation['offset'] : false;
        $limit = array_key_exists('limit', $agentInformation) ? $agentInformation['limit'] : false;

        if (array_key_exists('agentInfo', $agentInformation) && $agentInformation['agentInfo']) {
            $agentInfo = $agentInformation['agentInfo'];
            $start = $agentInfo['start'];
            $end = $agentInfo['end'];
            $working_location_id = $agentInfo['working_location_id'];
            //Check for any agents available for provided time frame
            $availableAgents = $this->AgentSchedulesRepository->getAvailableAgentsForTimeFrame($start, $end, $working_location_id);
            if(!empty($availableAgents)) {
                return $availableAgents;
            }
        }

        return UserAccountInformation::
              when($with_appointments, function ($query) {
                  return $query->with('appointments');
            })
            ->when($with_working_location, function ($query) {
                return $query->with('workingLocation');
            })
            ->when($agent_id, function ($query) use ($agent_id) {
                return $query->where('id', $agent_id);
            })
            ->when($working_location_id, function ($q) use ($working_location_id){
                return $q->whereHas('workingLocation', function($q) use ($working_location_id){
                    $q->where('working_location_id', $working_location_id);
                });
            })
            ->when($email_address, function($query) use ($email_address) {
                return $query->where('email_address', $email_address);
            })
            ->when($work_phone, function($query) use ($work_phone) {
                return $query->where('work_phone', $work_phone);
            })
            ->when($phone_ext, function($query) use ($phone_ext) {
                return $query->where('phone_ext', $phone_ext);
            })
            ->when($position, function($query) use ($position) {
                return $query->where('position', $position);
            })
            ->when($order, function ($query) use ($order) {
                return $query->orderby($order->sort, $order->direction);
            })
            ->when($offset, function ($query) use ($offset) {
                return $query->offset($offset);
            })
            ->when($limit, function ($query) use ($limit) {
                return $query->limit($limit);
            })
            ->get();

    }

    //Returns all info an a specific agent
    public function getByID($agentID) {
        return UserAccountInformation::with(array('workingLocation'=>function($q) {$q->select('id', 'name');}))->find($agentID);
    }

    //Returns all agents from all properties
    public function getAgentsList() {
        return UserAccountInformation::with('workingLocation')->OrderBy('first_name')->paginate(15);
    }

    //Returns all agents from all properties
    public function getDisplayableAgentList() {
        return UserAccountInformation::where('hidden', false)->with('workingLocation')->OrderBy('first_name')->paginate(15);
    }
}