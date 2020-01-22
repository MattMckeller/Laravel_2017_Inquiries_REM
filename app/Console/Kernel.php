<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AddRole::class,
        Commands\AddResponsibility::class,
        Commands\AddObject::class,
        Commands\AddController::class,
        Commands\Cron\ResetAvailableInquiriesCommand::class,
        Commands\Auth\CreateTeamAccountCommand::class,
        Commands\Migrations\ACLMigrationCommand::class,
        Commands\Migrations\CommandMigrationCommand::class,
        Commands\Migrations\MigrateAllCommand::class,
        Commands\Migrations\MigrateRollbackAllCommand::class,
        Commands\Migrations\MigrateResetAllCommand::class,
        Commands\Migrations\ConfigMigrationCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cron:resetAvailableInquiries')->everyMinute();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
