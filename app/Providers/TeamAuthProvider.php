<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \REM\Authentication\TeamAuth;
use \App\Repositories\Auth\UserAccountRepository;

class TeamAuthProvider extends ServiceProvider
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
        $this->app->singleton('TeamAuth', function($app){
            return new TeamAuth($app);
        });
        $this->app->singleton('UserAccountRepository', function(){
            return new UserAccountRepository();
        });
        
    }
}
