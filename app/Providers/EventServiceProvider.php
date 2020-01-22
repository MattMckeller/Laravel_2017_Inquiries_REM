<?php

namespace App\Providers;

use App\Events\Appointments\AppointmentCanceledEvent;
use App\Events\Appointments\AppointmentClaimedEvent;
use App\Events\Appointments\AppointmentCompletedEvent;
use App\Events\Appointments\AppointmentCreatedEvent;
use App\Events\Appointments\AppointmentCustomerAdded;
use App\Events\Appointments\AppointmentCustomerWithdrawal;
use App\Events\Appointments\AppointmentScheduledEvent;
use App\Events\Appointments\AppointmentUpdatedEvent;
use App\Events\InquiryHeatRatingEvent;
use App\Listeners\AddInquiryRatingChangeEventLog;
use App\Listeners\Subscribers\AppointmentUpdateSubscriber;
use App\Listeners\Subscribers\InquiryUpdateSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\VisitCustomerProfileEvent;
use App\Listeners\AddCustomerProfileViewedLog;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
        VisitCustomerProfileEvent::class=>[
            AddCustomerProfileViewedLog::class,
        ],
        InquiryHeatRatingEvent::class=>[
            AddInquiryRatingChangeEventLog::class,
        ],
        AppointmentCanceledEvent::class=>[

        ],
        AppointmentClaimedEvent::class=>[

        ],
        AppointmentCompletedEvent::class=>[

        ],
        AppointmentCustomerAdded::class=>[

        ],
        AppointmentCustomerWithdrawal::class=>[

        ],
        AppointmentScheduledEvent::class=>[

        ],
        AppointmentUpdatedEvent::class=>[

        ]
    ];

    protected $subscribe = [
        InquiryUpdateSubscriber::class,
        AppointmentUpdateSubscriber::class
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
