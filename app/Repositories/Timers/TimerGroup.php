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
use App\Models\Timers\TimerConfigGroup;
use App\Models\Timers\TimerContactType;

class TimerGroup
{
    private $timerTypes = [];
    private $timerConfigGroup = null;

    function __construct()
    {
        $classes = getAllClassesInDirectory(__DIR__.'/TimerTypes');
        foreach($classes as $class){
            $instance = new $class();
            if($instance->isActive == true) {
                $this->timerTypes[$instance->getDatabaseReference()] = $instance;
            }
        }
    }

    /**
     * @param Inquiry $inquiry
     * @return Timer | false
     */
    function getNext(Inquiry $inquiry){
        $response = false;

        if(!$this->isSetup($inquiry)){$this->setupGroup($inquiry);}

        $timerTypes = $this->getNeededInquiryTimerTypes($inquiry);
        foreach($timerTypes as $timerType) {
            $next = $timerType->getNext($inquiry);
            if(!empty($next)) {
                $timerStatus = $this->getTimerCustomerStatus($next);
                if($timerStatus != 'inquiry'){
                    $this->completeTimer($next);
                    return $this->getNext($inquiry);
                }
                if(empty($response) || $response == false) {
                    $response = $next;
                }elseif(!empty($next)){
                    $nextDateTime = new DateTime($next->timer_expiration_datetime);
                    $currentDateTime  = new DateTime($response->timer_expiration_datetime);
                    $response = ($nextDateTime > $currentDateTime)?($response):($next);
                }
            }
        }

        return $response;
    }

    /**
     * @param Inquiry $inquiry
     * @return array[Timer(s)]
     */
    function setupGroup(Inquiry $inquiry){
        $setupTimers = array();
        $timerGroup = $this->determineTimerGroup($inquiry);
        foreach($this->timerTypes as $timerType){
            $newSetupData = $timerType->handleSetup($inquiry, $timerGroup);
            if(!empty($newSetupData)) {
                $setupTimers = array_merge($newSetupData, $setupTimers);
            }
        }
        return $setupTimers;
    }

    /**
     * @param Inquiry $inquiry
     * @return TimerConfigGroup
     */
    function determineTimerGroup(Inquiry $inquiry){
        //todo Currently just defaulting to the first group. Once more groups are created determine the logic that will be needed
        if(!empty($this->timerConfigGroup)){
            return $this->timerConfigGroup;
        }else {
            $timerConfigGroup = new TimerConfigGroup();
            $timerConfigGroup = $timerConfigGroup::find(1);
            $this->timerConfigGroup = $timerConfigGroup;
            return $timerConfigGroup;
        }
    }


    /**
     * Returns the associated timer types that can be associated with the provided inquiry.
     * @param $inquiry
     * @return array[timerTypes]
     */
    function getNeededInquiryTimerTypes($inquiry){
        //Determine the timer types associated with this inquiry
        $timerConfigGroup = $this->determineTimerGroup($inquiry);
        $timerConfigs = $timerConfigGroup->timerConfigs()->get();
        $types = [];
        $typeNames = [];
        foreach($timerConfigs as $config){
            if(!in_array($config->type, $typeNames)){
                if(!empty($this->timerTypes[$config->type])) {
                    $typeNames[] = $config->type;
                    $types[] = $this->timerTypes[$config->type];
                }
            }
        }
        return $types;
    }
    /**
     * Returns the associated timer types that is associated with the provided timer.
     * @param $timer
     * @return TimerType|null
     */
    function getNeededTimerType(Timer $timer){
        //Determine the timer types associated with this inquiry
        $config = $timer->timerConfig()->first();
        $type = null;
        if(!empty($this->timerTypes[$config->type])) {
            $type = $this->timerTypes[$config->type];
        }
        return $type;
    }

    /**
     * Determines whether the inquiry has had its timers set up or not.
     * @param $inquiry
     * @return boolean true|false
     */
    function isSetup(Inquiry $inquiry){
        //If there are any timers associated with the inquiry then it has been set up.
        $timers = $inquiry->timers()->first();
        return (empty($timers))?(false):($timers->exists());
    }

    /**
     * @param Timer $timer the timer which we will use to track down the customer and determine their status
     * @return string of status
     */
    protected function getTimerCustomerStatus(Timer $timer){
        $inquiry = $timer->inquiry()->first();
        $customer = $inquiry->customer()->first();
        $customerRepo = App::make('CustomerRepository');
        $customerStatus = $customerRepo->getCustomerStatus($customer->id);
        return strtolower($customerStatus);
    }

    protected function completeTimer(Timer $timer){
        //@todo will need to do more than just complete the timer, this will be an infinite loop on dynamic timers
        $timerRepo = App::make('TimerRepository');
        return $timerRepo->completeTimer($timer);
    }

}