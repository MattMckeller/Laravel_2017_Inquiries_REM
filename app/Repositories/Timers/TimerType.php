<?php
namespace App\Repositories\Timers;
use App\Models\Inquiries\Inquiry;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\Customer;
use App\Models\Inquiries\InquiryClaimingEvent;
use App\Models\Timers\TimerDelay;
use \DateTime;
use \DateInterval;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\App;
use App\Models\Timers\Timer;
use App\Models\Timers\TimerConfig;
use App\Models\Timers\TimerOverride;
use App\Models\Timers\TimerConfigGroup;
use App\Models\Timers\TimerContactType;

abstract Class TimerType
{
    var $dbTimerString;
    var $isActive = true;

    abstract function calculateConfigExpirationDatetime(TimerConfig $timerConfig);
    abstract function calculateEarliestDateTime(TimerConfig $timerConfig);
    abstract function determineStatus(Timer $timer);
    abstract function checkPastDueDate(Timer $timer);
    abstract function checkAvailability(Timer $timer);
    abstract function getNext(Inquiry $inquiry);
    abstract function handleSetup(Inquiry $inquiry, TimerConfigGroup $timerGroup);

    /**
     * @return string database config type name
     */
    function getDatabaseReference(){
        return $this->dbTimerString;
    }

    /**
     * Returns the created timer with appropriate associations
     * @param TimerConfig $timerConfig
     * @param Inquiry $inquiry
     * @return Timer
     */
    function createTimerFromConfig(TimerConfig $timerConfig, Inquiry $inquiry){
        $timer = new Timer();
        $timer->display_name = $timerConfig->display_name;
        $timer->timer_expiration_datetime = $this->calculateConfigExpirationDatetime($timerConfig);
        $timer->valid_start_date = $this->calculateEarliestDateTime($timerConfig);
        $timer->timerContactType()->associate($timerConfig->timerContactType()->first());
        $timer->timerConfig()->associate($timerConfig);
        $timer->inquiry()->associate($inquiry);
        $timer->timerContactType();
        $timer->completed = 0;
        $timer->save();
        return $timer;
    }

    /**
     * checks if there is currently a timer associated with this inquiry that has the corresponding database type
     * @param Inquiry $inquiry
     * @return bool
     */
    protected function isSetup(Inquiry $inquiry){
        //If there are any timers associated with the inquiry then it has been set up.
        $timers = $inquiry->timers()->get();
        foreach($timers as $t){
            if($this->timerMatchesTimerType($t)){
                return true;
            }
        }
        return false;
    }

    /**
     * checks if there is currently a timer associated with this inquiry that has the corresponding database type
     * @param Inquiry $inquiry
     * @return bool
     */
    protected function hasCompletedTimer(Inquiry $inquiry){
        //If there are any timers associated with the inquiry then it has been set up.
        $timers = $inquiry->timers()->where('completed',1)->get();
        foreach($timers as $t){
            if($this->timerMatchesTimerType($t)){
                return true;
            }
        }
        return false;
    }

    /**
     * checks if there is currently a timer associated with this inquiry that has the corresponding database type
     * @param Inquiry $inquiry
     * @return bool
     */
    protected function hasIncompleteTimer(Inquiry $inquiry){
        //If there are any timers associated with the inquiry then it has been set up.
        $timers = $inquiry->timers()->where('completed',0)->get();
        foreach($timers as $t){
            if($this->timerMatchesTimerType($t)){
                return true;
            }
        }
        return false;
    }

    protected function timerMatchesTimerType(Timer $timer){
        $config = $timer->timerConfig()->first();
        if($config->type == $this->getDatabaseReference()){
            return true;
        }
        return false;
    }

    protected function getTypeSpecificTimers($inquiry){
        $typeSpecificTimers = $inquiry->timers()->where('completed',0)->orderBy('timer_expiration_datetime', 'asc')->whereHas('timer_config', function ($query) {
            $query->where('type', $this->getDatabaseReference());
        })->get();
        return $typeSpecificTimers;
    }

    /**
     * @param Inquiry $inquiry the inquiry which we will use to track down the customer and determine their status
     * @return string of status
     */
    protected function getInquiryCustomerStatus(Inquiry $inquiry){
        $customer = $inquiry->customer()->first();
        $customerRepo = App::make('CustomerRepository');
        $customerStatus = $customerRepo->getCustomerStatus($customer->id);
        return strtolower($customerStatus);
    }
}