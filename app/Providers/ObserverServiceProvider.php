<?php

namespace App\Providers;

use App\Models\Inquiries\InquiryEvent;
use Illuminate\Support\ServiceProvider;
use App\Models\Inquiries\Inquiry;
use App\Observers\InquiryObserver;
use App\Observers\InquiryEventObserver;
use App\Models\Appointments\Appointment;
use app\Observers\AppointmentObserver;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Inquiry::observe(InquiryObserver::class);
        InquiryEvent::observe(InquiryEventObserver::class);
        Appointment::observe(AppointmentObserver::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
