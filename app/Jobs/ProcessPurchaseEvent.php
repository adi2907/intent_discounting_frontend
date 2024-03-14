<?php

namespace App\Jobs;

use App\Models\AlmeShopifyOrders;
use App\Models\IpMap;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPurchaseEvent implements ShouldQueue {

    public $shops, $order;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FunctionTrait, RequestTrait;
    /**
     * Create a new job instance.
     */
    public function __construct($order, $shops) {
        $this->shops = $shops;
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $order = $this->order;
        $shops = $this->shops;
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
                "timestamp" => $order->created_at,
                "app_name" => $shops[$order->shop_id]['shop_url'],
                "session_id" => $almeInfo !== null && isset($almeInfo->session_id) ? $almeInfo->session_id : null,
                "products" => $productsArr
            ];

            $endpoint = getAlmeAppURLForStore('events/purchase/');
            $headers = getAlmeHeaders();
            $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
            //$this->processRetryResponse($order, $payload, $response);
            $order->update(['purchase_event_status' => 'Alme purchase event api called', 'purchase_event_response' => json_encode($response)]);
        } else {
            if(isset($order['browser_ip']) && filled($order['browser_ip'])) {
                //Log::info('Logging order '.json_encode($order));
                $dbRowForIP = IpMap::where('ip_address', $order['browser_ip'])->where('shop_id', $order->shop_id)->first();
                if($dbRowForIP !== null && $dbRowForIP->count() > 0) {
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
                        "alme_user_token" => $dbRowForIP->alme_token,
                        "timestamp" => $order->created_at,
                        "app_name" => $shops[$order->shop_id]['shop_url'],
                        "session_id" => isset($dbRowForIP) && isset($dbRowForIP->session_id) ? $dbRowForIP->session_id : null,
                        "products" => $productsArr
                    ];

                    $endpoint = getAlmeAppURLForStore('events/purchase/');
                    $headers = getAlmeHeaders();
                    $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                    //$this->processRetryResponse($order, $payload, $response);
                    $order->update(['purchase_event_status' => 'Buy it now event called', 'purchase_event_response' => json_encode($response)]);
                } else {
                    $order->update(['purchase_event_status' => 'Database IP map not found']);
                }
            } else {
                //Here browser IP is found null so maybe try fetching the order from Shopify
                //and check the note_attributes attribute to see if a cart_token is present in it.
                $flag = false;
                try {
                    $shop = $shops[$order->shop_id];
                    $endpoint = getShopifyAPIURLForStore('orders/'.$order['id'].'.json', $shop);
                    $headers = getShopifyAPIHeadersForStore($shop);
                    $orderResponse = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
                    if($orderResponse['statusCode'] && $orderResponse['statusCode'] == 200) {
                        $orderResponse = $orderResponse['body']['order'];
                        if(isset($orderResponse['note_attributes']) && is_array($orderResponse['note_attributes'])) {
                            foreach($orderResponse['note_attributes'] as $noteAttribute) {
                                if(isset($noteAttribute['name']) && strlen($noteAttribute['name']) && $noteAttribute['name'] == 'cart_token') {
                                    $flag = true;
                                    $order->update(['cart_token' => $noteAttribute['value'], 'purchase_event_status' => null]);
                                }
                            }
                        }
                    }
                } catch (Throwable $th) {
                    Log::info('Error in ProcessPurchaseEvent '.$th->getMessage().' '.$th->getLine());
                }
    
                if(!$flag)
                    $order->update(['purchase_event_status' => 'Browser IP found null even after retrying']);
            }
        }
    }
}
