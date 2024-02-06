<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPurchaseEvent;
use App\Models\Shop;
use App\Models\ShopifyOrder;
use Illuminate\Console\Command;

class RetryPurchaseEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:retry-purchase-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retries failed purchase events a maximum of 3 times';

    /**
     * Execute the console command.
     */
    public function handle() {
        $limit = 1;
        $query = ShopifyOrder::where('purchase_event_response', 'like', '%success":false%')->where(function ($query) {
            return $query->where('retry_count', null)->orWhere('retry_count', '<', 3);
        });
        $orders = $query->limit($limit)->get();
        if($orders !== null && $orders->count() > 0) {
            $shopIds = $orders->pluck('shop_id')->toArray();
            $shops = Shop::whereIn('id', array_unique($shopIds))->get()->keyBy('id')->toArray();
            $this->info('Processing '.$orders->count().' orders');
            foreach($orders as $order) {
                $this->info('Processing order name '.$order->name);
                ProcessPurchaseEvent::dispatch($order, $shops)->onConnection('sync');
            }
        }
    }
}
