<?php

namespace App\Listeners;

use App\Events\VisitCustomerProfileEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Customers\CustomerViewingEvent;
use Illuminate\Support\Facades\App;

class AddCustomerProfileViewedLog
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
    public function handle(VisitCustomerProfileEvent $event)
    {
        $this->createNewLog($event->customer);
    }

    public function createNewLog($customer){
        $log = new CustomerViewingEvent([
                'timestamp'=>nowTimestamp()
            ]
        );
        $log->customer()->associate($customer);
        $log->userAccount()->associate($this->TeamAuth->get_user());
        $log->save();
        return true;
    }
}
