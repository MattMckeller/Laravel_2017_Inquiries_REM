<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use REM\Encryption\RemEncryption;

class RemEncryptionServiceProvider extends ServiceProvider
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
        $this->app->singleton('RemEncryption', function($app){
            return new RemEncryption($app);
        });
    }
}
