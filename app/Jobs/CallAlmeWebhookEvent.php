<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\ShopifyOrder;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class CallAlmeWebhookEvent implements ShouldQueue {
    use FunctionTrait, RequestTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $request, $headers;
    /**
     * Create a new job instance.
     */
    public function __construct($requestBody, $requestHeaders) {
        $this->request = $requestBody;
        $this->headers = $requestHeaders;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        try {
            $validRequest = $this->validateWebhookRequest($this->request, $this->headers);
            if($validRequest) {
                //Log::info('In Purchase shopify webhook handler');
                //Webhook call is valid. We can proceed.
                $shopDetails = Shop::where('shop_url', $this->headers['x-shopify-shop-domain'][0])->first();
                //Log::info('Shop details: '.json_encode($shopDetails));
                $cacheKey = "Webhook:Order:{$this->request['id']}";
                //Log::info('Cache key: '.$cacheKey);
                $verify = $this->verifyRequestDuplication($cacheKey);
                //Log::info('Verify: '.$verify);
                if($verify) {
                    try{
                        $order = $this->saveOrUpdateOrder($this->request, $shopDetails);
                        $shops = Shop::whereIn('id', [$shopDetails->id])->get()->keyBy('id')->toArray();
                        ProcessPurchaseEvent::dispatch($order, $shops)->onConnection('database');
                    } catch(Exception $e) {
                        Log::info('Error here in line 52 '.$e->getMessage().' '.$e->getLine());
                    }

                    $payload = $this->getOrderRequestPayloadForAlmeEvent($this->request, $shopDetails);
                    //Log::info('Payload: '.json_encode($payload));
                    if($payload != null) {
                        $endpoint = getAlmeAppURLForStore('events/shopify_webhook_purchase');
                        $headers = getAlmeHeaders();
                        $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                        $shopDetails->getAlmeWebhookEvents()->create([
                            'order_id' => $this->request['id'],
                            'payload' => json_encode($payload),
                            'api_response' => json_encode($response)
                        ]);
                        Cache::put($cacheKey, $response, 30);
                    } else {
                        Log::info('Got payload null for order '.$cacheKey);
                    }
                } else {
                    Log::info('Request duplicated! Cache key '.$cacheKey);
                }
            } else {
                Log::info('Request not valid or not from Shopify!');
            }
        } catch (Exception $th) {
            Log::info('Error in call alme webhook event');
            Log::info($th->getMessage().' '.$th->getLine());
        }
    }
}
