<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void {
        //Create discount codes for all stores depending on what expiry they provided in their dashboards.
	    $schedule->command('app:discount')->everyThirtyMinutes();

        //This command helps us check if crons are running at all on the server. Nothing is done in this.
        $schedule->command('app:check-cron-status')->everyThreeHours();

        //Syncs orders from all stores. Frequency increased given that we're working with webhooks now.
        //$schedule->command('app:sync-orders')->everyMinute();

        //Calls Alme's purchase event api for the orders that haven't been informed to Alme backend yet.
        //$schedule->command('app:purchase-event-alme')->everyMinute();

        //Syncs products data from all stores. 
        $schedule->command('app:sync-products')->everySixHours();

        //Processes Orders from cache every minute
        $schedule->command('app:process-cache')->everyFiveMinutes();

        //Sets dashboard
        $schedule->command('app:set-dashboard')->everyFifteenMinutes();

        //Sync Customers
        $schedule->command('app:sync-customers')->twiceDaily();

        //Run Segments that updates the count
        $schedule->command('app:run-segment-command')->daily();

        //Check Discount code for old orders
        $schedule->command('app:check-discount-code-redemptions')->everyThirtyMinutes();

        //Syncs Alme's Identified Users data into the database. So we can do custom querying on it on our end.
        $schedule->command('app:sync-identified-users')->everyThirtyMinutes();

        //Retries failed purchase events and saves information in the table
        //Turned off for now
        //$schedule->command('app:retry-purchase-events')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {

        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
        
    }
}
