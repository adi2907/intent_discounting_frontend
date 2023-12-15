<?php

namespace App\Console\Commands;

use App\Models\AlmeShopifyOrders;
use App\Models\Shop;
use App\Models\ShopifyOrder;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;

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
        $orders = ShopifyOrder::where('purchase_event_status', null)->limit(5)->get();
        if($orders !== null && $orders->count() > 0) {
            $shopIds = $orders->pluck('shop_id')->toArray();
            $shops = Shop::whereIn('id', array_unique($shopIds))->get()->keyBy('id')->toArray();
            foreach($orders as $order) {
                $almeInfo = AlmeShopifyOrders::where('shopify_cart_token', $order->cart_token)
                                             ->where('session_id', '<>', null)
                                             ->where('alme_token', '<>', null)
                                             ->orderBy('created_at', 'desc')
                                             ->first();
                if($almeInfo !== null && $almeInfo->count() > 0) {
                    $line_items = is_array($order->line_items) ? $order->line_items : json_decode($order->line_items, true);
                    $productsArr = [];
                    foreach($line_items as $item) {
                        $productsArr[] = [
                            "product_id" => $item['product_id'],
                            "product_name" => $item['title'],
                            "product_price" => $item['price'],
                            "product_qty" => $item['quantity']
                        ];
                    }

                    $payload = [
                        "cart_token" => $order->cart_token,
                        "alme_user_token" => $almeInfo->alme_token,
                        "app_name" => $shops[$order->shop_id]['shop_url'],
                        "session_id" => $almeInfo->session_id,
                        "products" => $productsArr
                    ];

                    $endpoint = getAlmeAppURLForStore('events/purchase/');
                    $headers = getAlmeHeaders();
                    $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                    $order->update(['purchase_event_status' => 'Alme purchase event api called', 'purchase_event_response' => json_encode($response)]);
                } else {
                    $order->update(['purchase_event_status' => 'Alme frontend data not found']);
                }
            }
        }
    }
}
