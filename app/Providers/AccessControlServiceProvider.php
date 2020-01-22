<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \REM\AccessControl\AccessControl;

class AccessControlServiceProvider extends ServiceProvider
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
        $this->app->singleton('AccessControl', function($app){
            return new AccessControl($app);
        });
    }
}
