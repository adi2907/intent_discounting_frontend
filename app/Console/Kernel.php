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
        $schedule->command('app:sync-orders')->everyMinute();
        $schedule->command('app:purchase-event-alme')->everyMinute();
        $schedule->command('app:sync-products')->everyOddHour();
        $schedule->command('app:sync-identified-users')->everyOddHour();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {

        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
        
    }
}
