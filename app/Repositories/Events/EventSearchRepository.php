<?php
namespace App\Repositories\Events;
use App\Models\Auth\Team\UserAccountInformation;
use App\Models\Inquiries\Inquiry;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\InquiryClaimingEvent;
use \DateTime;
use \DateInterval;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use App\Repositories\Timers\TimerRepository;

class EventSearchRepository
{
    private $eventTypes = [];
    private $TimerRepository;

    function __construct()
    {
        $classes = getAllClassesInDirectory(__DIR__.'/EventSearchTypes');
        foreach($classes as $class){
            $this->eventTypes[] = new $class();
        }
        $this->TimerRepository = App::make(TimerRepository::class);
    }

    function getAllEvents(){
        return $this->_getAllEvents();
    }

    function _getAllEvents(){
        $allEvents = [];
        foreach($this->eventTypes as $eventType){
            $allEvents = array_merge($allEvents, $eventType->getEvents());
        }

        return $allEvents;
    }

    function getCustomerEvents($cid) {
        $customer = Customer::findOrFail($cid);
        $allEvents = array();
        //inquiry events
        $allEvents['inquiryEvents'] = array();
        $allEvents['inquiryClaimingEvents'] = array();
        $allEvents['appointmentEvents'] = array();
        $allEvents['timerEvents'] = array();

        foreach($customer->inquiries as $inquiry) {
            foreach($inquiry->inquiryEvents as $inquiryEvent) {
                array_push($allEvents['inquiryEvents'], $inquiryEvent);
            }
            unset($event);

            //inquiry claiming events
            foreach($inquiry->inquiryClaimingEvents as $inquiryClaimingEvent) {
                array_push($allEvents['inquiryClaimingEvents'], $inquiryClaimingEvent);
            }
            unset($event);
        }

        //appointment events
        foreach($customer->appointmentEvents as $appointmentEvent) {
            array_push($allEvents['appointmentEvents'], $appointmentEvent);
        }
        unset($event);

        //timers
        $timers = $this->TimerRepository->retrieveTimersForCustomer($customer);
        foreach($timers as $timer) {
            array_push($allEvents['timerEvents'], $timer);
        }
        unset($event);

        return $allEvents;
    }

    function _getCustomerEvents($cid) {
        return $this->getCustomerEvents($cid);
    }
}