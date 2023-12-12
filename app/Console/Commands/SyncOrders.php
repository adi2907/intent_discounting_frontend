<?php

namespace App\Console\Commands;

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
            $orders = null;
            $sinceId = null;
            $headers = getShopifyAPIHeadersForStore($shop);
            do {
                $this->info('Since ID '.$sinceId);
                $endpoint = getShopifyAPIURLForStore('orders.json?since_id='.$sinceId, $shop);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
                $this->info('Status code '.$response['statusCode']);
                $this->info($response['body']);
                Log::info('Response');
                Log::info($response);
                if($response['statusCode'] == 200) {
                    foreach($response['body']['orders'] as $order) {
                        $updateArr = [
                            'shop_id' => $shop->table_id,
                            'id' => $order['id']
                        ];

                        $createArr = array_merge($updateArr, [
                            'name' => $order['name'],
                            'checkout_id' => $order['checkout_id'],
                            'cart_token' => $order['cart_token'],
                            'total_price' => $order['total_price'],
                            'line_items' => json_encode($order['line_items'])
                        ]);

                        ShopifyOrder::updateOrCreate($updateArr, $createArr);
                        $sinceId = $order['id'];
                    }
                } else {
                    $orders = null;
                }

            } while($orders !== null);
        }
    }
}
