<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void {
        $schedule->command('app:discount')->everyThirtyMinutes();
        $schedule->command('app:check-cron-status')->everyFiveSeconds();
        $schedule->command('app:sync-orders')->everyOddHour();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {

        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
        
    }
}
