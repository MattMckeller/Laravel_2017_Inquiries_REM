<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 3/8/17
 * Time: 4:23 PM
 */

namespace app\Observers;
use App\Models\Inquiries\Inquiry;
use App\Models\Inquiries\InquiryEvent;
use App\Events\InquiryUpdateModelEvent;


class InquiryEventObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Inquiry  $inquiry
     * @return void
     */
    public function saved(InquiryEvent $inquiryEvent)
    {
        $inquiryEvent = $inquiryEvent->fresh();
        $inquiry = $inquiryEvent->inquiry()->first();
        if(!empty($inquiry)) {
            event(new InquiryUpdateModelEvent($inquiry));
        }
    }
}