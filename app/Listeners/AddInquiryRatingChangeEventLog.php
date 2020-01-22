<?php

namespace App\Listeners;

use App\Events\InquiryHeatRatingEvent;
use App\Events\VisitCustomerProfileEvent;
use App\Models\Inquiries\HeatRatingEvent;
use App\Models\Inquiries\InquiryClaimingEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Customers\CustomerViewingEvent;
use Illuminate\Support\Facades\App;

class AddInquiryRatingChangeEventLog
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->TeamAuth = App::make('TeamAuth');

    }

    /**
     * Handle the event.
     *
     * @param  VisitCustomerProfileEvent  $event
     * @return void
     */
    public function handle(InquiryHeatRatingEvent $event)
    {
        $this->createNewLog($event->inquiry);
    }

    public function createNewLog($inquiry){
        $originalHeatIndex = $inquiry->getOriginal('heat_index');
        $newHeatIndex = $inquiry->heat_index;
        if(empty($newHeatIndex)){
            $tmp = $newHeatIndex;
            $newHeatIndex = $originalHeatIndex;
            $originalHeatIndex=$newHeatIndex;
        }
        $log = new HeatRatingEvent([
                'event_timestamp'=>nowTimestamp(),
                'old_heat_index'=>(!empty($originalHeatIndex))?($originalHeatIndex):(0),
                'new_heat_index'=>(!empty($newHeatIndex))?($newHeatIndex):(0)
            ]
        );
        $log->inquiry()->associate($inquiry);
        $log->userAccount()->associate($this->TeamAuth->get_user());
        $log->save();
        return true;
    }
}
