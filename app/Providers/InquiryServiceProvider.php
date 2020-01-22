<?php

namespace App\Providers;

use App\Repositories\Inquiries\InquiryRepository;
use Illuminate\Support\ServiceProvider;

class InquiryServiceProvider extends ServiceProvider
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
        $this->app->singleton('InquiryRepository', function($app){
            return new InquiryRepository($app);
        });
    }
}
