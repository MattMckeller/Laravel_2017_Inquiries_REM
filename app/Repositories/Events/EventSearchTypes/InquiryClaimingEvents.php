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
class InquiryClaimingEvents extends EventSearchType
{
    var $limit = 50;
    var $eventType = 'InquiryEvent';
    var $eventName = 'InquiryClaimingEvent';

    function getEvents(){
        $retVals = [];
        $events = new InquiryClaimingEvent();
        $events = $events->whereIsClaim()->limit($this->getLimit())->get();
        foreach($events as $event){
            $userAccount = $event->userAccount()->first();
            $customer = $event->inquiry()->first();
            $customer = (!empty($customer))?($customer->customer()->first()):(null);
            $actingUser = $event->actingUser()->first();
            $actingUser = (!empty($actingUser))?($actingUser->userAccountInformation()->first()->toArray()):(null);
            $data = array();

            $instance = new EventInstance();
            $instance->setName($this->getEventName());
            $instance->setType($this->getEventType());
            $instance->setTimestamp($event->timestamp);
            if(!empty($userAccount)) {$instance->setUser($userAccount);}
            if(!empty($customer)) {$instance->setCustomer($customer);}
            if(!empty($actingUser)) {$data['acting_user']=$actingUser;}
            $instance->setData($data);
            $retVals[] = $instance;
        }

        return $retVals;
    }

}