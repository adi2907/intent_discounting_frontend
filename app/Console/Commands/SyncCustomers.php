<?php

namespace App\Console\Commands;

use App\Jobs\SyncShopifyCustomers;
use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;

class SyncCustomers extends Command
{
    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $shops = Shop::get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
                $this->info('Checking for shop '.$shop->shop_url);
                SyncShopifyCustomers::dispatch($shop)->onConnection('sync');
            }
        }
        $this->info('================= CUSTOMERS SYNC FINISHED =================');
    }
}
