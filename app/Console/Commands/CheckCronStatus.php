<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckCronStatus extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-cron-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates cache key to make sure that the cron is working on Hostinger instance';

    /**
     * Execute the console command.
     */
    public function handle() {
        $cacheKey = config('custom.cacheKeys.cronStatus');
        $data = [
            'ok' => true,
            'last_update' => date('Y/m/d h:i:s')
        ];
        Cache::set($cacheKey, $data);
        $this->info('Done');
        return Command::SUCCESS;
    }
}
