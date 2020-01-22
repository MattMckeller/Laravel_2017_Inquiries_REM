<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        require app_path() . '/Validators/CustomValidations.php';
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            //$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
//        if ($this->app->environment() == 'local') {
//            $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
//        }
    }
}
