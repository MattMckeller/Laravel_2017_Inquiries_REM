<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 3/8/17
 * Time: 4:23 PM
 */

namespace app\Observers;
use App\Events\InquiryHeatRatingEvent;
use App\Models\Inquiries\Inquiry;
use App\Events\InquiryUpdateModelEvent;


class InquiryObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Inquiry  $inquiry
     * @return void
     */
    public function saved(Inquiry $inquiry)
    {
        if($this->heatIndexChanging($inquiry)){
            event(new InquiryHeatRatingEvent($inquiry));
        }
        event(new InquiryUpdateModelEvent($inquiry));
    }

    public function saving(Inquiry $inquiry){

    }

    private function heatIndexChanging($inquiry){
        $originalHeatIndex = $inquiry->getOriginal('heat_index');
        $newHeatIndex = $inquiry->heat_index;
        return ($originalHeatIndex != $newHeatIndex)?(true):(false);
    }
}