<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPurchaseEvent;
use App\Models\AlmeShopifyOrders;
use App\Models\IpMap;
use App\Models\Shop;
use App\Models\ShopifyOrder;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PurchaseEventAlme extends Command
{
    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purchase-event-alme';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fires purchase event API on alme side';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = ShopifyOrder::where('purchase_event_status', null);
        $orders = $query->get();
        if($orders !== null && $orders->count() > 0) {
            $this->info('Processing '.$orders->count().' orders');
            $shopIds = $orders->pluck('shop_id')->toArray();
            $shops = Shop::whereIn('id', array_unique($shopIds))->get()->keyBy('id')->toArray();
            foreach($orders as $order) {
                ProcessPurchaseEvent::dispatch($order, $shops)->onConnection('sync');
            }
        }
    }
}
