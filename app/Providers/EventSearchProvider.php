<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Events\EventSearchRepository;

class EventSearchProvider extends ServiceProvider
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
        $this->app->singleton('EventSearchRepository', function($app){
            return new EventSearchRepository($app);
        });
    }
}
