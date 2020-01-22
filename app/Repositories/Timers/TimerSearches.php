<?php
namespace App\Repositories\Timers;
use App\Models\Inquiries\Inquiry;
use \DateTime;
use \DateInterval;
use App\Models\Customers\Customer;
use App\Models\Timers\Timer;
use App\Models\Timers\TimerConfig;
use App\Models\Timers\TimerOverride;
use App\Models\Timers\TimerConfigGroup;
use App\Models\Timers\TimerContactType;
use App\Models\Auth\Team\UserAccount;
use App\Models\Inventory\Location;
use Carbon\Carbon;

trait TimerSearches
{
    //Retrieves timers associated with the provided user account
    function retrieveTimersByAgent(UserAccount $userAccount){
        $timers = [];
        $inquiries = $userAccount->inquiries()->get();
        foreach($inquiries as $inquiry){
            $inqTimers = $inquiry->timers()->orderBy('timer_expiration_datetime', 'asc')->get();
            foreach($inqTimers as $t){
                $timers[] = $t;
            }
        }
        return $timers;
    }

    //Retrieves timers connected to the provided location
    function retrieveTimersByLocation(Location $location){}

    //Retrieves timers that will or have expired between the provided range
    function retrieveTimersByTimePeriod($startTime = null, $endTime=null){
        $carbonStartDate = new Carbon($startTime);
        $carbonEndDate = new Carbon($endTime);

        $timers = new Timer();
        if(!empty($startTime)) {
            $timers = $timers->where('timer_expiration_datetime', '>=', $carbonStartDate);
        }
        if(!empty($endTime)){
            $timers = $timers->where('timer_expiration_datetime', '<=', $carbonEndDate);
        }
        return $timers->get();
    }

    //Retrieves completed timers
    //todo Necessary?
    function retrieveCompletedTimers(){
        $timers = new Timer();
        return $timers->where('completed', 1)->get();
    }

    //Retrieves any unclaimed timers
    function retrieveUnclaimedTimers(){
        $inquiry = new Inquiry();
        $fk = $inquiry->userAccount()->getForeignKey();
        $inquiries = $inquiry->where($fk, null)->get();
        $timers = [];
        foreach($inquiries as $inquiry){
            $inqTimers = $this->retrieveTimersForInquiry($inquiry);
            foreach($inqTimers as $inqTimer){
                $timers[] = $inqTimer;
            }
        }
        return $timers;
    }

    //Retrieves timers that not completed and past their deadline
    function retrieveTimersPastDeadline(){
        $timers = new Timer();
        return $timers->where('timer_expiration_datetime', '<', nowTimestamp())->get();
    }

    //Retrieves timers associated with the provided
    function retrieveApproachingTimers(DateInterval $dateInterval = null){
        // get timers that expire within 24 hours
        $now = new DateTime("now");
        $dateInterval = (!empty($dateInterval))?($dateInterval):(new DateInterval('P1D'));
        $approaching_expiration = new DateTime("now");
        $approaching_expiration->add($dateInterval);
        return $this->retrieveTimersByTimePeriod($now->format('Y-m-d H:i:s'), $approaching_expiration->format('Y-m-d H:i:s'));
    }

    //Retrieves all timers for the provided inquiry
    function retrieveTimersForInquiry(Inquiry $inquiry){
        $this->getNextTimer($inquiry);
        return $inquiry->timers()->get();
    }

    //Retrieves all timers for the provided customer
    function retrieveTimersForCustomer(Customer $customer){
        $inquiries = $customer->inquiries()->get();
        $timers = [];
        foreach($inquiries as $inquiry){
            $inqTimers = $this->retrieveTimersForInquiry($inquiry);
            foreach($inqTimers as $t){
                $timers[] = $t;
            }
        }
        return $timers;
    }



}