<?php

namespace App\Jobs;

use App\Models\AlmeClickAnalytics;
use App\Models\AlmeShopifyOrders;
use App\Models\DiscountCode;
use App\Models\IpMap;
use App\Models\Shop;
use App\Models\ShopifyOrder;
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

    //Global variables added so we can access them after completing all operations
    public $almeToken, $cartToken, $sessionId;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FunctionTrait, RequestTrait;
    /**
     * Create a new job instance.
     */
    public function __construct($order, $shops) {
        $this->shops = $shops;
        $this->order = $order;

        $this->almeToken = null;
        $this->sessionId = null;
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

            $this->almeToken = $almeInfo->alme_token;
            $this->sessionId = $almeInfo !== null && isset($almeInfo->session_id) ? $almeInfo->session_id : null;

            $payload = [
                "cart_token" => $order->cart_token,
                "alme_user_token" => $this->almeToken,
                "timestamp" => $order->created_at,
                "app_name" => $shops[$order->shop_id]['shop_url'],
                "session_id" => $this->sessionId,
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

                    $this->almeToken = $dbRowForIP->alme_token;
                    $this->sessionId = isset($dbRowForIP) && isset($dbRowForIP->session_id) ? $dbRowForIP->session_id : null;

                    $payload = [
                        "cart_token" => $order->cart_token,
                        "alme_user_token" => $this->almeToken,
                        "timestamp" => $order->created_at,
                        "app_name" => $shops[$order->shop_id]['shop_url'],
                        "session_id" => $this->sessionId,
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
                                    $this->tryAgain($noteAttribute['value'], $shop, $orderResponse);
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

        //Process Discount Code for order
        try {
            Log::info('Starting discount check for order '.$order->name);
            if($this->almeToken == null) {
                Log::info('Early return');
                return;
            }
            
            if(isset($order->discount_allocations) && $order->discount_allocations != null) {
                $discountAllocations = json_decode($order->discount_allocations, true);
                if($discountAllocations != null && is_array($discountAllocations) && count($discountAllocations) > 0) {
                    foreach($discountAllocations as $discountInfo) {
                        if(is_array($discountInfo) && array_key_exists('code', $discountInfo)) {
                            $shop_url = $shops[$order->shop_id]['shop_url'];
                            $shop = Shop::where('shop_url', $shop_url)->first();
                            $dbRow = DiscountCode::where('store_id', $shop->id)->where('code', $discountInfo['code'])->first();
                            if($dbRow != null && $shop != null) {
                                $createArr = [
                                    'shop_id' =>  $shop->id,
                                    'discount_id' => $dbRow->id,
                                    'order_id' => $order->table_id,
                                    'created_at' => $order->created_at
                                ];
                                Log::info('About to create new alme click analytics');
                                AlmeClickAnalytics::create($createArr);
                            }
                        }
                    }
                } else {
                    Log::info('discount allocations is empty');
                }
            }
        } catch (Throwable $th) {
            Log::info('Discount allocations problem '.$th->getMessage().' '.$th->getLine());;
        }

    }

    public function tryAgain($cartToken, $shop, $order) {
        $tableRow = ShopifyOrder::where('shop_id', $shop['id'])->where('id', $order['id'])->first();
        $almeInfo = AlmeShopifyOrders::where('shopify_cart_token', $cartToken)
                                             ->where('session_id', '<>', null)
                                             ->where('alme_token', '<>', null)
                                             ->orderBy('created_at', 'desc')
                                             ->first();
        if($almeInfo !== null && $almeInfo->count() > 0) {
            $line_items = $order['line_items'];
            $productsArr = [];
            foreach($line_items as $item) {
                $productsArr[] = [
                    "product_id" => $item['product_id'],
                    "product_name" => $item['title'],
                    "product_price" => $item['price'],
                    "product_qty" => $item['quantity']
                ];
            }

            $this->almeToken = $almeInfo->alme_token;
            $this->sessionId = $almeInfo !== null && isset($almeInfo->session_id) ? $almeInfo->session_id : null;

            $payload = [
                "cart_token" => $cartToken,
                "alme_user_token" => $this->almeToken,
                "timestamp" => $order['created_at'],
                "app_name" => $shop['shop_url'],
                "session_id" => $this->sessionId,
                "products" => $productsArr
            ];

            $endpoint = getAlmeAppURLForStore('events/purchase/');
            $headers = getAlmeHeaders();
            $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
            //$this->processRetryResponse($order, $payload, $response);
            $tableRow->update(['purchase_event_status' => 'Alme purchase event api called on second try', 'purchase_event_response' => json_encode($response)]);
        } else {
            $tableRow->update(['purchase_event_status' => 'Purchase event failed even on second try']);
        }
    }
}
