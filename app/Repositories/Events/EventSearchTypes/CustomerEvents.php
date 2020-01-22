<?php
namespace App\Repositories\Events\EventSearchTypes;
use App\Models\Inquiries\Inquiry;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\InquiryClaimingEvent;
use \DateTime;
use \DateInterval;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use App\Models\Customers\CustomerViewingEvent;
use App\Repositories\Events\EventSearchType;
use App\Repositories\Events\EventInstance;
class CustomerEvents extends EventSearchType
{
    var $eventType = 'CustomerEvent';
    var $eventName = 'CustomerProfileViewingEvent';
    var $limit = 50;

    function getEvents(){
        $retVals = [];
        $customerEvent = new CustomerViewingEvent();
        $customerEvents = $customerEvent->limit($this->getLimit())->get();
        foreach($customerEvents as $event){
            $instance = new EventInstance();
            $instance->setName($this->getEventName());
            $instance->setType($this->getEventType());
            $instance->setTimestamp($event->timestamp);
            $instance->setUser($event->userAccount()->first());
            $instance->setCustomer($event->customer()->first());
            $retVals[] = $instance;
        }

        return $retVals;
    }
}