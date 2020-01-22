<?php

namespace App\Providers;

use App\Repositories\Timers\TimerRepository;
use Illuminate\Support\ServiceProvider;

class TimerServiceProvider extends ServiceProvider
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
        $this->app->singleton('TimerRepository', function(){
            return new TimerRepository();
        });
    }
}
