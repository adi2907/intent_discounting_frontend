<?php

namespace App\Console\Commands;

use App\Models\AlmeShopifyOrders;
use App\Models\IpMap;
use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CheckDiscountCodeRedemptions extends Command
{
    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-discount-code-redemptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks discount code redemptions for old orders for all shops';

    /**
     * Execute the console command.
     */
    public function handle() {
        $shops = Shop::whereIn('id', [17, 31, 32])->get()->keyBy('id');
        foreach($shops as $shop) {

            $installValid = $this->verifyInstallation($shop);
            if(!$installValid) continue;

            $cacheKey = 'check:again:order:discount:redemption:'.$shop->id;
            $hasKey = Cache::has($cacheKey);
            $limit = 25;
            $orderId = null;
            if($hasKey) {
                $orderId = Cache::get($cacheKey);
            } else {
                $orderId = 99999999;
            }
            $lastOrderId = null;

            $this->info('Checking for '.$shop->shop_url.' id > '.$orderId);

            $orders = $shop->getOrders()->where('table_id', '<', $orderId)->limit($limit)->orderBy('table_id', 'desc')->get();
            if($orders != null) {
                foreach($orders as $order) {
                    try {
                        $shopifyOrder = $this->getShopifyOrder($shop, $order->id);
                        if(isset($shopifyOrder['body']['order'])) {
                            $shopifyOrder = $shopifyOrder['body']['order'];
                            $tokenAndSessionId = $this->getAlmeTokenOrSessionIdForOrder($shopifyOrder, $shop);
                            if($tokenAndSessionId !== null) {
                                if(array_key_exists('discount_applications', $shopifyOrder) && is_array($shopifyOrder['discount_applications']) && count($shopifyOrder['discount_applications']) > 0) {
                                    $order->update(['discount_allocations' => $shopifyOrder['discount_applications']]);
                                    $this->checkDiscountCodeRedemption($order, $shops, $tokenAndSessionId['almeToken'], $tokenAndSessionId['sessionId']);
                                }
                            }    
                        } 
                    } catch (Throwable $th) {
                        $this->info('Problem check discount code redemptions '.$th->getMessage().' '.$th->getLine());
                    }
                    
                    $lastOrderId = $order->table_id;
                }

                Cache::put($cacheKey, $lastOrderId);
            }
        }
    }

    private function getShopifyOrder($shop, $orderId) {
        $endpoint = getShopifyAPIURLForStore('orders/'.$orderId.'.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        return $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
    }

    private function getAlmeTokenOrSessionIdForOrder($shopifyOrder, $shop) {
        $almeInfo = AlmeShopifyOrders::where('shopify_cart_token', $shopifyOrder['cart_token'])
                                            ->where('shop_id', $shop->id) 
                                            ->where('session_id', '<>', null)
                                            ->where('alme_token', '<>', null)
                                            ->orderBy('created_at', 'desc')
                                            ->first();
        if($almeInfo !== null && $almeInfo->count() > 0) {
            return ['almeToken' => $almeInfo->alme_token, 'sessionId' => $almeInfo->session_id];    
        }

        if(isset($shopifyOrder['browser_ip']) && filled($shopifyOrder['browser_ip'])) {
            //Log::info('Logging order '.json_encode($order));
            $dbRowForIP = IpMap::where('ip_address', $shopifyOrder['browser_ip'])->where('shop_id', $shopifyOrder->shop_id)->first();
            if($dbRowForIP !== null && $dbRowForIP->count() > 0) {
                return ['almeToken' => $dbRowForIP->alme_token, 'sessionId' => $dbRowForIP->session_id];
            }
        } else {
            if(isset($shopifyOrder['note_attributes']) && is_array($shopifyOrder['note_attributes'])) {
                $cartToken = null;
                foreach($shopifyOrder['note_attributes'] as $noteAttribute) {
                    if(isset($noteAttribute['name']) && strlen($noteAttribute['name']) && $noteAttribute['name'] == 'cart_token') {
                        $cartToken = $noteAttribute['name'];
                    }
                }

                $almeInfo = AlmeShopifyOrders::where('shopify_cart_token', $cartToken)
                                             ->where('shop_id', $shop->id) 
                                             ->where('session_id', '<>', null)
                                             ->where('alme_token', '<>', null)
                                             ->orderBy('created_at', 'desc')
                                             ->first();
                if($almeInfo !== null && $almeInfo->count() > 0) {
                    return ['almeToken' => $almeInfo->alme_token, 'sessionId' => $almeInfo->session_id];   
                }
            }
        }

        return null;
    }
}
