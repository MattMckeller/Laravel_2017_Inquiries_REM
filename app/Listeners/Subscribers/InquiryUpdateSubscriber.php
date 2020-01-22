<?php

namespace App\Listeners\Subscribers;

use App\Events\InquiryContactEvent;
use App\Events\InquiryUpdateEvent;
use App\Events\InquiryUpdateModelEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\InquiryClaimEvent;
use App\Events\InquiryReleaseEvent;
use App\Models\Inquiries\Inquiry;
use App\Events\InquiryHeatRatingEvent;
class InquiryUpdateSubscriber
{
    static $beingBroadcast = [];
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function specificUpdate($event){
        $inquiryID = $event->inquiry->id;
        if($this->shouldBroadcast($inquiryID)){
            $inquiry = new Inquiry();
            $inquiry = $inquiry::find($inquiryID);
            event(new InquiryUpdateEvent($inquiry));
        }else{
            //Do nothing
        }
    }

    public function defaultUpdate($event){
        $this->addToSentList($event->inquiry->id);
    }

    public function shouldBroadcast($inquiryID){
        if(in_array($inquiryID, self::$beingBroadcast)) {
            return false;
        }else{
            return true;
        }
    }

    function addToSentList($inquiryID){
        self::$beingBroadcast[] = $inquiryID;
    }

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(InquiryClaimEvent::class, 'App\Listeners\Subscribers\InquiryUpdateSubscriber@specificUpdate');
        $events->listen(InquiryReleaseEvent::class, 'App\Listeners\Subscribers\InquiryUpdateSubscriber@specificUpdate');
        $events->listen(InquiryUpdateModelEvent::class, 'App\Listeners\Subscribers\InquiryUpdateSubscriber@specificUpdate');
        $events->listen(InquiryContactEvent::class, 'App\Listeners\Subscribers\InquiryUpdateSubscriber@specificUpdate');
        $events->listen(InquiryUpdateEvent::class, 'App\Listeners\Subscribers\InquiryUpdateSubscriber@defaultUpdate');
        $events->listen(InquiryHeatRatingEvent::class, 'App\Listeners\Subscribers\InquiryUpdateSubscriber@specificUpdate');
    }
}
