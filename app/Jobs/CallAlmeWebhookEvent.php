<?php

namespace App\Jobs;

use App\Models\Shop;
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
                //Webhook call is valid. We can proceed.
                $shopDetails = Shop::where('shop_url', $this->headers['x-shopify-shop-domain'][0])->first();
                $cacheKey = "Webhook:Order:{$this->request['id']}";
                $verify = $this->verifyRequestDuplication($cacheKey);
                if($verify) {
                    $payload = $this->getRequestPayload($this->request);
                    if($payload != null) {
                        $endpoint = getAlmeAppURLForStore('events/shopify_webhook_purchase');
                        $headers = getAlmeHeaders();
                        $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                        $shopDetails->getAlmeWebhookEvents()->create([
                            'payload' => json_encode($payload),
                            'api_response' => json_encode($response)
                        ]);
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

    private function getRequestPayload($request) {
        try {
            return [
                "cart_token" => $request['cart_token'],
                "email" => $request['email'] ?? null,
                "user_id" => $request['customer']['id'] ?? null,
                "created_at" => $request['created_at'],
                "line_items" => $this->getLineItemsPayload($request),
                "total_discounts" => $request['total_discounts'],
                "discount_codes" => $this->getDiscountCodes($request)
            ];
        } catch (Exception $e) {
            Log::info('Error in getPayload function '.$e->getMessage().' '.$e->getLine());
            return null;
        }
    }

    private function getDiscountCodes($request) {
        try {
            $arrKey = 'discount_codes';
            $returnVal = null;
            if(array_key_exists($arrKey, $request) && is_array($request[$arrKey]) && count($request[$arrKey]) > 0) {
                $returnVal = [];
                foreach($request[$arrKey] as $data) {
                    $returnVal[] = [
                        'code' => $data['code'] ?? '',
                        'amount' => $data['amount']
                    ];
                } 
            }   
            return $returnVal;         
        } catch(Exception $e) {
            Log::info('Error in getPayload function '.$e->getMessage().' '.$e->getLine());
            return null;
        }
    }

    private function getLineItemsPayload($request) {
        $arrKey = 'line_items';
        $returnVal = null;
        if(array_key_exists($arrKey, $request) && is_array($request[$arrKey]) && count($request[$arrKey]) > 0) {
            $returnVal = [];
            foreach($request[$arrKey] as $lineItem) {
                $returnVal[] = [
                    "product_id" => $lineItem['product_id'],
                    "title" => $lineItem['title'],
                    "price" => $lineItem['price'],
                    "quantity" => $lineItem['quantity']
                ];
            }
        }
        return $returnVal;
    }

    private function verifyRequestDuplication($cacheKey) {
        return !Cache::has($cacheKey);
    }
}
