<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 4/20/17
 * Time: 2:44 PM
 */
namespace App\Repositories\Timers\TimerTypes;
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

use \App\Repositories\Timers\TimerType;
use App\Repositories\Timers\TimerGroup;

class DynamicTimer extends TimerType{
    var $minimumScore = 5;
    var $dbTimerString = 'dynamic';
    var $isActive = true;

    function getDatabaseReference()
    {
        return $this->dbTimerString;
    }

    /**
     * @param Inquiry $inquiry
     * @param TimerConfigGroup $timerGroup
     * @return array[Timer(s)] | false
     */
    function handleSetup(Inquiry $inquiry, TimerConfigGroup $timerGroup){
        $timerConfigOptions = $timerGroup->timerConfigs()->where('type', $this->getDatabaseReference())->get();
        $selectedConfig = $this->determineCurrentTimerConfig($inquiry, $timerConfigOptions);
        $response = [];
        if($selectedConfig !== false) {
            $response[] = $this->createTimerFromConfig($selectedConfig, $inquiry);
        }
        return $response;
    }

    /**
     * @param Inquiry $inquiry
     * @return Timer(s) | false
     */
    function getNext(Inquiry $inquiry){
        if(!$this->isSetup($inquiry) || $this->hasIncompleteTimer($inquiry)){//There is already an existing timer that matches the requirements.
            $nextTypeSpecificTimer = $inquiry->timers()->where('completed',0)->orderBy('timer_expiration_datetime', 'asc')->whereHas('timerConfig', function ($query) {
                $query->where('type', $this->getDatabaseReference());
            })->first();
            return (!empty($nextTypeSpecificTimer))?($nextTypeSpecificTimer):(false);
        }else{
            //We need to setup the next timer
            $timerConfigGroup = (new TimerGroup)->determineTimerGroup($inquiry);
            $timers = $this->handleSetup($inquiry, $timerConfigGroup);
            //todo this may not be the next timer
            return (!empty($timers))?($timers[0]):(false);
        }

    }

    function calculateConfigExpirationDatetime(TimerConfig $timerConfig){
        $now = new DateTime(nowTimestamp());
        $expiration = $now->add(new DateInterval('PT'.$timerConfig->timer_expiration.'H'));
        return $expiration->format('Y-m-d H:i:s');
    }

    function calculateEarliestDateTime(TimerConfig $timerConfig){
        $now = new DateTime(nowTimestamp());
        $expiration = $now->add(new DateInterval('PT'.$timerConfig->earliest_time.'H'));
        return $expiration->format('Y-m-d H:i:s');
    }

    function determineStatus(Timer $timer){
        if($timer->completed == 1){
            return 'completed';
        }
        if($timer->is_active == 0){
            return 'disabled';
        }
        $override = $timer->timerOverrides()->first();
        if(!empty($override) && $override->exists){
            return $override->timerOverrideType()->first()->name;
        }

        return 'pending';
    }

    function checkPastDueDate(Timer $timer){
        $now = new DateTime("now");
        $expirationTime = new DateTime($timer->timer_expiration_datetime);
        return ($now<$expirationTime)?(true):(false);
    }

    function checkAvailability(Timer $timer){
        $now = new DateTime("now");
        $availableTime = new DateTime($timer->valid_start_date);
        return ($now>=$availableTime)?(true):(false);
    }

    private function determineCurrentTimerConfig(Inquiry $inquiry, $currentConfigOptions){
        $isVoicemail = $this->checkIfVoicemail($inquiry);

        $customerStatus = $this->getInquiryCustomerStatus($inquiry);
        if($customerStatus != 'inquiry'){
            return false;
        }
        //Handle timer config retrieval if the last event was a voicemail
        if($isVoicemail == true){
            $timerConfig = new TImerConfig();
            if($this->checkAgentContacted($inquiry) == true){ //Agent contacted customer
                $timerConfig = $timerConfig->getLeftCustomerVoicemailConfig();
            }else{ // Customer Contacted Us
                $timerConfig = $timerConfig->getCustomerLeftVoicemailConfig();
            }
            return $timerConfig;
        }
        //Determine current inquiry score
        $inqRepo = App::make('InquiryRepository');
        $heatIndex = $inqRepo->calculateIndex($inquiry);
        if ($heatIndex <= $this->minimumScore) {
            return false;
        }
        $currentSelection = null;
        $currentDistance = null;
        //Determine closest timer config
        foreach ($currentConfigOptions as $configOption) {
            //Determine the difference between the config option expected score and the heat index
            // if score is closer than the current overwrite the current selection
            $distance = abs($heatIndex - $configOption->applicable_rating);
            if ($currentDistance === null || $currentDistance > $distance) {
                $currentSelection = $configOption;
                $currentDistance = $distance;
            }
        }
        return $currentSelection;
    }

    private function checkIfVoicemail(Inquiry $inquiry){
        $inquiryEvents = $inquiry->inquiryEvents()->orderBy('provided_timestamp', 'desc')->first();
        if(!$inquiryEvents){return false;}
        return $inquiryEvents->isVoicemail();
    }

    private function checkAgentContacted(Inquiry $inquiry){
        $inquiryEvent = $inquiry->inquiryEvents()->orderBy('provided_timestamp', 'desc')->first();
        if(!$inquiryEvent){return false;}
        return ($inquiryEvent->agent_contacted == 1)?(true):false;
    }

}