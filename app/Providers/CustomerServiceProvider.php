<?php

namespace App\Providers;

use App\Repositories\Customers\CustomerRepository;
use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
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
        $this->app->singleton('CustomerRepository', function($app){
            return new CustomerRepository($app);
        });
    }
}
