<?php
/**
 * Created by PhpStorm.
 * User: KeanMattingly
 * Date: 7/14/17
 * Time: 10:06 AM
 */

namespace App\Listeners\Subscribers;


use App\Events\Appointments\AppointmentCustomerAdded;
use App\Events\Appointments\AppointmentCanceledEvent;
use App\Events\Appointments\AppointmentClaimedEvent;
use App\Events\Appointments\AppointmentCompletedEvent;
use App\Events\Appointments\AppointmentScheduledEvent;
use App\Events\Appointments\AppointmentUpdatedEvent;
use App\Events\Appointments\AppointmentCustomerWithdrawal;
use App\Jobs\SendAppointmentCanceledEmail;
use App\Jobs\SendAppointmentEmail;

class AppointmentUpdateSubscriber
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

    public function mailAppointmentInformation($event) {
//        $job = (new SendAppointmentEmail($event))->onQueue('emails');
        $job = (new SendAppointmentEmail($event));//->onQueue('emails');
//        dispatch((new SendAppointmentEmail(4))->onQueue('emails'));
        dispatch($job);
    }

    public function emailAppointmentUpdates($event){
        $job = (new SendAppointmentCanceledEmail($event));//->onQueue('emails');
        dispatch($job);
    }

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(AppointmentUpdatedEvent::class, 'App\Listeners\Subscribers\AppointmentUpdateSubscriber@mailAppointmentInformation');
        $events->listen(AppointmentClaimedEvent::class, 'App\Listeners\Subscribers\AppointmentUpdateSubscriber@mailAppointmentInformation');
        $events->listen(AppointmentCanceledEvent::class, 'App\Listeners\Subscribers\AppointmentUpdateSubscriber@emailAppointmentUpdates');
        $events->listen(AppointmentScheduledEvent::class, 'App\Listeners\Subscribers\AppointmentUpdateSubscriber@mailAppointmentInformation');
        $events->listen(AppointmentCompletedEvent::class, 'App\Listeners\Subscribers\AppointmentUpdateSubscriber@mailAppointmentInformation');
        $events->listen(AppointmentCustomerAdded::class, 'App\Listeners\Subscribers\AppointmentUpdateSubscriber@mailAppointmentInformation');
        $events->listen(AppointmentCustomerWithdrawal::class, 'App\Listeners\Subscribers\AppointmentUpdateSubscriber@mailAppointmentInformation');
    }
}