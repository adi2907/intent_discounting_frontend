<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPurchaseEvent;
use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessCache extends Command {

    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes cache keys to insert into orders table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $shops = Shop::select(['id', 'shop_url'])->get();
        foreach($shops as $shop) {
            $cacheKey = 'webhook_cache_'.$shop->shop_url;
            $cacheVal = Cache::get($cacheKey);
            $cacheKeyArr = [];
            if($cacheVal != null && is_array($cacheVal) && count($cacheVal) > 0) {
                foreach($cacheVal as $key => $orderReq) {
                    try{
                        $cacheKeyArr[] = $key;
                        $order = $this->saveOrUpdateOrder($orderReq, $shop);
                        $shops = [$shop->id => $shop];
                        ProcessPurchaseEvent::dispatch($order, $shops)->onConnection('database');
                    } catch(Exception $e) {
                        Log::info('Error here in line 52 '.$e->getMessage().' '.$e->getLine());
                    }
        
                    $payload = $this->getOrderRequestPayloadForAlmeEvent($orderReq, $shop);
                    //Log::info('Payload: '.json_encode($payload));
                    if($payload != null) {
                        $endpoint = getAlmeAppURLForStore('events/shopify_webhook_purchase');
                        $headers = getAlmeHeaders();
                        $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                        $shop->getAlmeWebhookEvents()->create([
                            'order_id' => $orderReq['id'],
                            'payload' => json_encode($payload),
                            'api_response' => json_encode($response)
                        ]);
                    } else {
                        Log::info('Got payload null for order '.$cacheKey);
                    }
                }

                //Reset the cache back because we need to remove the keys that we just got
                $newCacheVal = Cache::get($cacheKey);
                foreach(array_keys($newCacheVal) as $key) {
                    if(in_array($key, $cacheKeyArr)) {
                        unset($newCacheVal[$key]);
                    }
                }

                Log::info('Processed cache for shop '.$shop->shop_url);
                Cache::put($cacheKey, $newCacheVal);
            }
        }
    }
}
