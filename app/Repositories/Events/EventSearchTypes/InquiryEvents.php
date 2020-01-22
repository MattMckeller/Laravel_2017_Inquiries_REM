<?php
namespace App\Repositories\Events\EventSearchTypes;
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
class InquiryEvents extends EventSearchType
{
    var $limit = 50;
    var $eventType = 'InquiryEvent';
    var $eventName = 'InquiryEvent';

    function getEvents(){
        $retVals = [];
        $events = new InquiryEvent();
        $events = $events->limit($this->getLimit())->get();
        foreach($events as $event){
            $userAccount = $event->userAccount()->first();
            $customer = $event->inquiry()->first();
            $customer = (!empty($customer))?($customer->customer()->first()):(null);

            $instance = new EventInstance();
            $instance->setInquirySource($event->inquirySourceSelection);
            $instance->setAgentContacted($event->agent_contacted);
            $instance->setName($this->getEventName());
            $instance->setType($this->eventType);
            $instance->setTimestamp($event->provided_timestamp);
            if(!empty($userAccount)) {$instance->setUser($userAccount);}
            if(!empty($customer)) {$instance->setCustomer($customer);};
            $retVals[] = $instance;
        }

        return $retVals;
    }



}