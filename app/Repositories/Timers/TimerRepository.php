<?php
namespace App\Repositories\Timers;
use App\Models\Inquiries\Inquiry;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\InquiryClaimingEvent;
use \DateTime;
use \DateInterval;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use App\Models\Timers\Timer;
use App\Models\Timers\TimerConfig;
use App\Models\Timers\TimerOverride;
use App\Models\Timers\TimerOverrideType;
use App\Models\Timers\TimerConfigGroup;
use App\Models\Timers\TimerContactType;
use App\Models\Timers\TimerDelay;
use App\Models\Inquiries\InquiryEvent;

class TimerRepository
{
    use TimerSearches;
    var $defaultDelay = 24; //Hours

    function __construct(){}

    //Creates the next appropriate timer for the provided inquiry
    function getNextTimer(Inquiry $inquiry){
        $timerGroup = new TimerGroup();
        $response = $timerGroup->getNext($inquiry);
        if(empty($response)){return false;}
        return $response;
    }

    //Returns all timers for the provided inquiry
    function getAllTimers(Inquiry $inquiry){
        $this->getNextTimer($inquiry); // Make sure its set up.
        $timers = $inquiry->timers()->get();
        return $timers;
    }


    //Adds a delay to the provided timer
    function addDelay(Timer $timer, DateInterval $delayInterval=null){
        //Get Interval in hours
        $intervalInHours = $this->getDelayIntervalInHours($delayInterval);
        $user = $this->getCurrentUser();

        //Create the delay relationship
        $delay = new TimerDelay();
        $delay->creation_datetime = nowTimestamp();
        $delay->delay_duration = $intervalInHours;
        $delay->timer()->associate($timer);
        $delay->userAccount()->associate($user);
        $delay->save();
        //Add the delay's time to the timer
        return $this->addHoursToTimer($intervalInHours, $timer);
    }

    //Disables a timer
    function disableTimer(Timer $timer, $reason=''){
        $overrideType = new TimerOverrideType();
        $overrideType = $overrideType->firstOrCreate(array(
            'name'=>'disable'
        ));
        $overrideSuccess = $this->overrideTimer($timer, $overrideType, $reason);
        if($overrideSuccess != true){
            trigger_error('There was an error disabling the timer.');
        }
        $timerInstance = new Timer();
        $timerInstance->where('id', $timer->id)->update(['is_active'=>0]);
        return $overrideSuccess;
    }

    //Disables all timers for an inquiry
    function disableInquiryTimers(Inquiry $inquiry){
        $timers = $inquiry->timers()->where('completed', 0)->get();
        foreach($timers as $timer){
            $this->disableTimer($timer);
        }
    }

    //Resets a timer's start point to be this moment.
    function resetTimer(Timer $timer){
        $currentDate = date('Y-m-d H:i:s');
        $timer->timer_expiration_datetime = $currentDate;
        $timer->save();
        return response($timer, 201);
    }

    //Associates an override with a timer and takes the appropriate actions. Determines permission level
    function overrideTimer(Timer $timer, TimerOverrideType $timerOverrideType, $reason=""){
        $timerOverride = new TimerOverride();
        $timerOverride->timestamp = nowTimestamp();
        $timerOverride->reason = $reason;
        $timerOverride->timer()->associate($timer);
        $timerOverride->userAccount()->associate($this->getCurrentUser());
        $timerOverride->timerOverrideType()->associate($timerOverrideType);
        return $timerOverride->save();
    }

    //Marks a timer as complete, creates a new timer.
    function completeTimer(Timer $timer, InquiryEvent $inquiryEvent=null){
        //todo add inquiry event relationship, handle this within timer type most likely
        $t = new Timer();
        $t = $t->where('id', $timer->id)->first();
        if($inquiryEvent != null) {
            $t->inquiryEvent()->associate($inquiryEvent);
        }
        $t->completed = 1;
        $t->save();
        return response($timer, 201);
    }

    //Creates a custom timer for the provided inquiry
    //todo -- implement createCustomTimer()
    function createCustomTimer(Inquiry $inquiry, $timerData){}

    //Determines information about a timer such as the name of the owner, remaining time, etc.
    function analyzeTimer(Timer $timer){
        //Determine timer type
        //fetch inquiry events, contact type, inquiry, etc
        //determine countdown/past due
        //determine status / completed / disabled etc
        $timerType = $this->determineTimerType($timer);
        $timer->inquiryEvent;
        $timer->timerContactType;
        $timer->inquiry;
        $timerData = [
            'status'=>$timerType->determineStatus($timer),
            'past_due'=>$timerType->checkPastDueDate($timer),
            'available'=>$timerType->checkAvailability($timer),
            'has_overrides'=>$this->checkForOverrides($timer),
            'remaining_minutes'=>$this->getRemainingMinutes($timer),
        ];
        return [
            'timer'=>$timer,
            'timer_data'=>$timerData,

        ];
    }


    /**
     * Determines the timers current expiration, calculates the date with the added time and saves the results.
     * @param $hours integer the hours to add to the current timer's expiration
     * @param Timer $timer the timer to add time to
     * @return mixed|boolean success
     */
    protected function addHoursToTimer($hours, Timer $timer){
        $addedTime = new DateInterval('PT'.$hours.'H');
        $currentTimer = new Timer();
        $currentTimer = $currentTimer->find($timer->id);

        //Determine whether the current expiration occurred in the past, if so use now as the start point
        $timeNow = new DateTime(nowTimestamp());
        $currentExpiration = new DateTime($currentTimer->timer_expiration_datetime);
        $mostRecentTime = ($timeNow > $currentExpiration)?($timeNow):($currentExpiration);

        $newTime = $mostRecentTime->add($addedTime)->format('Y-m-d H:i:s');
        $currentTimer->timer_expiration_datetime = $newTime;
        return $currentTimer->save();
    }

    /**
     * @param DateInterval|null $dateInterval
     * @return integer number of total hours in the provided, or default dateInterval
     */
    private function getDelayIntervalInHours(DateInterval $dateInterval=null){
        $delayInterval = (!empty($dateInterval))?($dateInterval):(new DateInterval("PT".$this->defaultDelay."H"));

        $intervalInSeconds = getDateIntervalInSeconds($delayInterval);;
        $intervalInHours = ceil($intervalInSeconds/60/60);
        return $intervalInHours;
    }

    /**
     * @return UserAccount|false the current logged in user
     */
    private function getCurrentUser(){
        $auth = App::make('TeamAuth');
        return $auth->get_user();
    }

    /**
     * Returns the timertype object
     * @param Timer $timer
     * @return TimerType
     */
    private function determineTimerType(Timer $timer){
        return (new TimerGroup())->getNeededTimerType($timer);
    }

    /**
     * returns true if the timer has any overrides applied, otherwise returns false
     * @param Timer $timer
     * @return boolean
     */
    private function checkForOverrides(Timer $timer){
        $overrides = $timer->timerOverrides()->first();
        return (!empty($overrides))?($overrides->exists):(false);
    }

    private function getRemainingMinutes(Timer $timer){
        $expiration = new DateTime($timer->timer_expiration_datetime);
        $now = new DateTime('now');
        $diff = $now->diff($expiration);
        if($diff->invert == true){ // Is the timer past expiration
            return 0;
        }

        $intervalInSeconds = getDateIntervalInSeconds($diff);;
        $intervalInMinutes = ceil($intervalInSeconds/60);
        return $intervalInMinutes;
    }
}