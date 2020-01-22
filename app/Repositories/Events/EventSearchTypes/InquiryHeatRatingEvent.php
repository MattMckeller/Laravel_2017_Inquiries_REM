<?php
namespace App\Repositories\Events\EventSearchTypes;
use App\Models\Inquiries\HeatRatingEvent;
use App\Models\Inquiries\Inquiry;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\InquiryClaimingEvent;
use App\Models\Inquiries\InquiryEvent;
use \DateTime;
use \DateInterval;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use App\Repositories\Events\EventSearchType;
use App\Repositories\Events\EventInstance;
class InquiryHeatRatingEvent extends EventSearchType
{
    var $limit = 50;
    var $eventType = 'InquiryEvent';
    var $eventName = 'InquiryHeatRatingEvent';

    function getEvents(){
        $retVals = [];
        $events = new HeatRatingEvent();
        $events = $events->limit($this->getLimit())->get();
        foreach($events as $event){
            $userAccount = $event->userAccount()->first();
            $customer = $event->inquiry()->first();
            $customer = (!empty($customer))?($customer->customer()->first()):(null);
            $data = array();

            $instance = new EventInstance();
            $instance->setName($this->getEventName());
            $instance->setType($this->getEventType());
            $instance->setTimestamp($event->event_timestamp);
            if(!empty($userAccount)) {$instance->setUser($userAccount);}
            if(!empty($customer)) {$instance->setCustomer($customer);}
            $data['old_heat_index']=$event->old_heat_index;
            $data['new_heat_index']=$event->new_heat_index;
            $instance->setData($data);
            $retVals[] = $instance;
        }

        return $retVals;
    }

}