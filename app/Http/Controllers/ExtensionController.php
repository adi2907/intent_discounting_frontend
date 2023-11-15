<?php

namespace App\Http\Controllers;

use App\Models\Shop;
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
                        $response = ['status' => true, 'response' => $response, 'endpoint' => $endpoint, 'headers' => $headers];   
                    }
                } else {
                    $response = ['status' => true, 'message' => 'Flag not set true', 'debug' => $productRackSettings];
                } 
            } else {
                $response = ['status' => true, 'message' => 'Store not found', 'debug' => $request->all()];
            }
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all()];
        }

        return response()->json($response);
    }
}
