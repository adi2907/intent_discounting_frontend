<?php

namespace App\Jobs;

use App\Models\ShopifyOrder;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncShopifyOrders implements ShouldQueue
{
    use FunctionTrait, RequestTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $shop;
    /**
     * Create a new job instance.
     */
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $orders = null;
        $shop = $this->shop;
        $sinceId = 0;
        $headers = getShopifyAPIHeadersForStore($shop);
        do {
            $endpoint = getShopifyAPIURLForStore('orders.json?status=any&since_id='.$sinceId, $shop);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
            if($response['statusCode'] == 200) {
                foreach($response['body']['orders'] as $order) {
                    $updateArr = [
                        'shop_id' => $shop->id,
                        'id' => $order['id']
                    ];

                    $createArr = array_merge($updateArr, [
                        'name' => $order['name'],
                        'checkout_id' => $order['checkout_id'],
                        'browser_ip' => $order['browser_ip'],
                        'cart_token' => $order['cart_token'],
                        'source_name' => $order['source_name'] ?? null,
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
