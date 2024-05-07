<?php

namespace App\Http\Controllers;

use App\Jobs\CallAlmeWebhookEvent;
use App\Jobs\RegisterWebhooks;
use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookController extends Controller {
    use FunctionTrait, RequestTrait;

    public function handleCustomerDataRequest(Request $request) {
        $request = $request->all();
        $validRequest = $this->validateRequestFromShopify($request);
        if($validRequest) {
            $response = [
                'status' => true,
                'message' => 'Not Found',
                'code' => 404,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Invalid Request',
                'code' => 401
            ];
        }
        return response()->json($response, $response['code']);
    }

    public function handleCustomerDataErasure(Request $request) {
        $request = $request->all();
        $validRequest = $this->validateRequestFromShopify($request);
        if($validRequest) {
            $response = [
                'status' => true,
                'message' => 'Not Found',
                'code' => 404,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Invalid Request',
                'code' => 401
            ];
        }
        return response()->json($response, $response['code']);
    }

    public function handleShopDataErasure(Request $request) {
        $request = $request->all();
        $validRequest = $this->validateRequestFromShopify($request);
        if($validRequest) {
            $response = [
                'status' => true,
                'message' => 'Not Found',
                'code' => 404,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Invalid Request',
                'code' => 401
            ];
        }
        return response()->json($response, $response['code']);
    }
    
    public function registerWebhooks(Request $request) {
        RegisterWebhooks::dispatch($request->shop_id)->onConnection('sync');
        return response()->json(['status' => true, 'message' => 'Done']);
    }
    
    public function cartUpdateWebhook(Request $request) {
        //Log::info('Request for cart update');
        //Log::info($request->all());
        return response()->json(['status' => true]);
    }
    
    public function checkoutCreateWebhook(Request $request) {
        //Log::info('Request for checkout create');
        //Log::info($request->all());
        return response()->json(['status' => true]);
    }

    public function checkoutUpdateWebhook(Request $request) {
        //Log::info('Request for checkout update');
        //Log::info($request->all());
        return response()->json(['status' => true]);
    }

    public function cartCreateWebhook(Request $request) {
        //Log::info('Request for cart create');
        //Log::info($request->all());
        return response()->json(['status' => true]);
    }
    
    public function orderCreateWebhook(Request $request) {
        //Log::info('Request for order create');
        try {
            try {
                $headers = $request->headers->all();
                $cacheKey = 'webhook_cache_'.$headers['x-shopify-shop-domain'][0];
                $cacheVal = Cache::get($cacheKey);
                if($cacheVal !== null) {
                    $cacheVal[] = $request->all();
                } else {
                    $cacheVal = [];
                    $cacheVal[] = $request->all();
                }
                Cache::put($cacheKey, $cacheVal);
            } catch (\Throwable $th) {
                Log::info('Order create webhook problem '.$th->getMessage().' '.$th->getLine());
            }

            CallAlmeWebhookEvent::dispatch($request->all(), $request->headers->all())->onConnection('database');
        } catch (Throwable $th) {
            Log::info('Error in order create function '.$th->getMessage().' '.$th->getLine());
        }
        return response()->json(['status' => true]);
    }

    public function orderUpdateWebhook(Request $request) {
        //Log::info('Request for order update');
        //Log::info($request->all());
        return response()->json(['status' => true]);
    }

    public function deleteWebhooks(Request $request) {
        $shops = Shop::get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
                $endpoint = getShopifyAPIURLForStore('webhooks.json', $shop);
                $headers = getShopifyAPIHeadersForStore($shop);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        
                $responses = [];
        
                foreach($response['body']['webhooks'] as $webhook) {
                    if($webhook['topic'] !== 'orders/create') {
                        $newEndpoint = getShopifyAPIURLForStore('webhooks/'.$webhook['id'].'.json', $shop);
                        $responses[] = $this->makeAnAPICallToShopify('DELETE', $newEndpoint, $headers);
                    }
                }
            }
        }
        dd('Done');
    }
}
