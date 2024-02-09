<?php

namespace App\Console\Commands;

use App\Jobs\SyncShopifyOrders;
use App\Models\Shop;
use App\Models\ShopifyOrder;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOrders extends Command
{
    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-orders';

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
            SyncShopifyOrders::dispatch($shop)->onConnection('sync');
        }
    }
}
