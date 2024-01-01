<?php

namespace App\Console\Commands;

use App\Jobs\SyncShopifyProducts;
use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs shopify products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $shops = Shop::get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
                SyncShopifyProducts::dispatch($shop)->onConnection('database');
            }
        }
        $this->info('================= PRODUCT SYNC FINISHED =================');
    }
}
