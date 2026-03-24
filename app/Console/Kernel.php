<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $timezone = 'Asia/Kolkata';

        $schedule->command('email:tracker-alert')
            ->timezone($timezone)
            ->daily();

        $schedule->command('email:daily-tracker-exception-alert')
            ->timezone($timezone)
            ->dailyAt('23:59');

        $schedule->command('app:generate-reports')
            ->timezone($timezone)
            ->dailyAt('23:59');

        $schedule->command('app:generate-backfill-reports')->timezone($timezone)->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
