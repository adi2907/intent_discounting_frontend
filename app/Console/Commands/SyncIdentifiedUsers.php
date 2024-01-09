<?php

namespace App\Console\Commands;

use App\Jobs\SyncIdentifiedUsers as JobsSyncIdentifiedUsers;
use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;

class SyncIdentifiedUsers extends Command
{
    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-identified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs identified users for each store';

    /**
     * Execute the console command.
     */
    public function handle() {
        $shops = Shop::get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
                JobsSyncIdentifiedUsers::dispatch($shop)->onConnection('sync');
                $this->info('Processed for store '.$shop->shop_url);
            }
        }
        $this->info('Done');
    }
}
