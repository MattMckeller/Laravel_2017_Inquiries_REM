<?php
namespace App\Repositories\Inquiries;
use App\Models\Inquiries\Inquiry;
use \DateTime;
use \DateInterval;
use App\Models\Customers\Customer;

trait InquiryHeatIndex
{
    function calculateIndex($inquiry){
        return $inquiry->heat_index;
        $heatIndex = null;
        $timeValue = $this->getTimeSinceCreationValue($inquiry);
        $lastContactValue = $this->getContactStatusValue($inquiry);
        $heatIndex = $timeValue*10;
        $inquiry = $this->updateInquiryIndexValue($inquiry, $heatIndex);
        $hasAppointment = $inquiry->hasAppointment();
        $hasLease = $inquiry->hasLease();
        $overrides = $this->getInquiryOverrides($inquiry);
        //Overriden
//        if(!empty($overrides)){
//            $heatIndex = 0;
//        }

        return $heatIndex;
    }

    function getTimeSinceCreationValue($inquiry){
        $createdAt = $inquiry->inquiry_timestamp;
        $createdAt = new \DateTime($createdAt);
        $now = new \Datetime("now");
        $diffIntervalFromCreationToNow = $createdAt->diff($now);

        $daysSinceCreationValues = [
            '16' => 4,
            '15' => 6,
            '14' => 8,
            '13' => 4,
            '12' => 5,
            '11' => 6,
            '10' => 7,
            '9' => 8,
            '8' => 8,
            '7' => 9,
            '6' => 5,
            '5' => 6,
            '4' => 7,
            '3' => 8,
            '2' => 9,
            '1' => 10,
            '0' => 10,
        ];

        //Grab days since this inquiry was created, return the stored value for this day or 0 if it is not in the list
        $actualDaysSinceCreation = $diffIntervalFromCreationToNow->days;
        $daysSinceCreationValue = (!empty($daysSinceCreationValues[$actualDaysSinceCreation]))?($daysSinceCreationValues[$actualDaysSinceCreation]):(0);
        return $daysSinceCreationValue;
    }

    function updateInquiryIndexValue($inquiry, $newValue){
        if($inquiry->heat_index != $newValue || $inquiry->heat_index == null){
            $inquiry->heat_index = $newValue;
            $inquiry->save();
        }
        return $inquiry;
    }

    function getContactStatusValue($inquiry){
        $lastContactValue = null;
        $allRelatedEvents = $inquiry->inquiryEvents()->get();
        $mostRecentEvent = null;
        foreach($allRelatedEvents as $event){
            $eventTimestamp = new \DateTime($event->provided_timestamp);
            $now = new \DateTime("now");
            $diffIntervalFromCreationToNow = $eventTimestamp->diff($now);
            //Grab days since this inquiry was created, return the stored value for this day or 0 if it is not in the list
            $daysSinceEvent = $diffIntervalFromCreationToNow->days;
            $mostRecentEvent = ($mostRecentEvent == null)?($daysSinceEvent):(min($mostRecentEvent, $daysSinceEvent));
        }

        $values = [
            '16' => 4,
            '15' => 6,
            '14' => 8,
            '13' => 3,
            '12' => 4,
            '11' => 5,
            '10' => 6,
            '9' => 7,
            '8' => 8,
            '7' => 8,
            '6' => 6,
            '5' => 6,
            '4' => 4,
            '3' => 2,
            '2' => 2,
            '1' => 1,
            '0' => 0,
        ];
        $lastContactValue = (!empty($mostRecentEvent) && !empty($values[$mostRecentEvent]))?($values[$mostRecentEvent]):(0);
        return $lastContactValue;
    }
    
    function getInquiryOverrides($inquiry){
        $overrideReturns = array();
        $overrideInstances = $inquiry->inquiryOverrides()->get();
        if(!$overrideInstances->isEmpty()) {
            foreach($overrideInstances as $override){
                $overrideType = $override->inquiryOverrideType()->first();
                $overrideReturns[] = $overrideType->name;
            };
        }
        return $overrideReturns;
    }

}