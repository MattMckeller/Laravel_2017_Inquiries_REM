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

class StaticTimer extends TimerType{
    var $dbTimerString = 'static';

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

    /**
     * @param Inquiry $inquiry
     * @param TimerConfigGroup $timerGroup
     * @return array[Timer(s)] | false
     */
    function handleSetup(Inquiry $inquiry, TimerConfigGroup $timerGroup){
        //TODO: Implement handleSetup() method.
        //Grab timer configs associated with timer config group
        //Create timers and associate with the provided inquiry; if they need to be created
        $timerConfigurations = $timerGroup->timerConfigs()->where('type',$this->getDatabaseReference())->get();
        $timers = [];
        foreach($timerConfigurations as $timerConfig) {
            $timers[] = $this->createTimerFromConfig($timerConfig, $inquiry);
        }
        return $timers;
    }

    /**
     * @param Inquiry $inquiry
     * @return Timer(s) | false
     */
    function getNext(Inquiry $inquiry){
        $timer = $inquiry->timers()->where('completed',0)->orderBy('timer_expiration_datetime', 'asc')->first();
        return $timer;
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

}