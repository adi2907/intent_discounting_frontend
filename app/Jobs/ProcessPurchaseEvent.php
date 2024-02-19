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
        $customerDetails = $order->getCustomerDetails();
        $endpoint = getAlmeAppURLForStore('events/purchase/');
        $headers = getAlmeHeaders();
            
        $almeInfo = AlmeShopifyOrders::where('shopify_cart_token', $order->cart_token)
                                             ->where('session_id', '<>', null)
                                             ->where('alme_token', '<>', null)
                                             ->orderBy('created_at', 'desc')
                                             ->first();
        if($almeInfo !== null && $almeInfo->count() > 0) {
            $line_items = is_array($order->line_items) ? $order->line_items : json_decode($order->line_items, true);
            $productsArr = $this->getProductsArr($line_items);
            $sessionId = $almeInfo !== null && isset($almeInfo->session_id) ? $almeInfo->session_id : null;
            $payload = $this->getPurchaseEventPayload($order, $almeInfo->alme_token, $shops[$order->shop_id]['shop_url'], $sessionId, $productsArr, $customerDetails);
            $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
            //$this->processRetryResponse($order, $payload, $response);
            $order->update(['purchase_event_status' => 'Alme purchase event api called', 'purchase_event_response' => json_encode($response)]);
        } else {
            if(isset($order['browser_ip']) && filled($order['browser_ip'])) {
                //Log::info('Logging order '.json_encode($order));
                $dbRowForIP = IpMap::where('ip_address', $order['browser_ip'])->where('shop_id', $order->shop_id)->first();
                if($dbRowForIP !== null && $dbRowForIP->count() > 0) {
                    $line_items = is_array($order->line_items) ? $order->line_items : json_decode($order->line_items, true);
                    $productsArr = $this->getProductsArr($line_items);
                    $sessionId = isset($dbRowForIP) && isset($dbRowForIP->session_id) ? $dbRowForIP->session_id : null;
                    $payload = $this->getPurchaseEventPayload($order, $dbRowForIP->alme_token, $shops[$order->shop_id]['shop_url'], $sessionId, $productsArr, $customerDetails);
                    $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                    $order->update(['purchase_event_status' => 'Buy it now event called', 'purchase_event_response' => json_encode($response)]);
                } else {
                    $order->update(['purchase_event_status' => 'Database IP map not found']);
                }
            } else {
                $order->update(['purchase_event_status' => 'Browser IP found null']);
            }
        }
    }

    private function getProductsArr($line_items) {
        $arr = [];
        foreach($line_items as $item) {
            $arr[] = [
                "product_id" => $item['product_id'],
                "product_name" => $item['title'],
                "product_price" => $item['price'],
                "product_qty" => $item['quantity']
            ];
        }
        return $arr;
    }

    private function getPurchaseEventPayload($order, $alme_token, $shopURL, $sessionId, $productsArr, $customerDetails) {
        return [
            "cart_token" => $order->cart_token,
            "alme_user_token" => $alme_token,
            "timestamp" => $order->created_at,
            "app_name" => $shopURL,
            "session_id" => $sessionId,
            "products" => $productsArr,
            "customer" => $customerDetails
        ];
    }
}
