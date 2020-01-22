<?php

namespace App\Listeners;

use App\Events\AppointmentCreatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAgentAppointmentNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AppointmentCreatedEvent  $event
     * @return void
     */
    public function handle(AppointmentCreatedEvent $event)
    {
        //logic to send email
    }
}
