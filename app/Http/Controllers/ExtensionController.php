<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopifyProducts;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExtensionController extends Controller {

    public $appName, $maxItems;
    use FunctionTrait, RequestTrait;

    public function __construct()
    {
        $this->appName = 'test_shopify';
        $this->maxItems = 5;
    }

    public function getMostViewedData(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $shop = Shop::with(['notificationSettings', 'productRackInfo'])->where('shop_url', $request->shop)->first();
            if($shop !== null && $shop->count() > 0) {
                $productRackSettings = $shop->productRackInfo;
                $flag = isset($productRackSettings) && $productRackSettings !== null && $productRackSettings->count() > 0;
                if($flag) {
                    if($productRackSettings->most_added_prods === 1 || $productRackSettings->most_added_prods === true) {
                        $almeToken = $request->token;
                        $getParams = '?app_name='.$this->appName.'&max_items='.$this->maxItems.'&token='.$almeToken;
                        $endpoint = getAlmeAppURLForStore('most_visited'.$getParams);
                        $headers = getAlmeHeaders();
                        $response = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
                        Log::info('Response from Alme API '.$endpoint);
                        Log::info($response['body']);
                        if($response['statusCode'] == 200) {
                            $html = $this->getMostViewedHTML($response['body'], $shop);
                            $response = ['status' => true, 'response' => $response, 'endpoint' => $endpoint, 'headers' => $headers, 'html' => $html];   
                        } else {
                            $response = ['status' => true, 'response' => $response, 'endpoint' => $endpoint, 'headers' => $headers, 'html' => null];   
                        }
                    } else {
                        $response = ['status' => true, 'message' => 'Flag set false', 'debug' => $productRackSettings, 'html' => null];
                    }
                } else {
                    $response = ['status' => true, 'message' => 'Flag not set true', 'debug' => $productRackSettings, 'html' => null];
                } 
            } else {
                $response = ['status' => true, 'message' => 'Store not found', 'debug' => $request->all(), 'html' => null];
            }
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }
    
    private function getMostViewedHTML($body, $shop) {
        try {
            if($body !== null && count($body) > 0) {
                $products = $shop->getProducts()->whereIn('product_id', $body)->groupBy('product_id')->get();
                return view('appExt.most_viewed', ['products' => $products])->render();
            }
            return null;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
