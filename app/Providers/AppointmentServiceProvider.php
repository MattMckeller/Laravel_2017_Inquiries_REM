<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Appointments\AgentSchedulesRepository;
use App\Repositories\Appointments\AppointmentEventsRepository;
use App\Repositories\Appointments\AppointmentsRepository;

class AppointmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('AgentSchedulesRepository', function(){
            return new AgentSchedulesRepository();
        });
        $this->app->singleton('AppointmentEventsRepository', function(){
            return new AppointmentEventsRepository();
        });
        $this->app->singleton('AppointmentsRepository', function(){
            return new AppointmentsRepository();
        });
    }
}
