<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopifyProducts;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExtensionController extends Controller {

    public $appName, $maxItems;
    use FunctionTrait, RequestTrait;

    public function __construct()
    {
        $this->appName = 'test_shopify';
        $this->maxItems = 5;
    }

    public function homePageSectionOne(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'hps_one');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }
    
    public function homePageSectionTwo(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'hps_two');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }
    
    public function productPageSectionOne(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'pps_one');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }
    
    public function productPageSectionTwo(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'pps_two');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }

    public function getMostViewedData(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'most_added_prods');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }

    public function getMostCartedData(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'prev_browsing');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }

    public function carts(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'user_liked');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }

    public function recommendedForYou(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'feat_collect');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }

    public function userLiked(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'user_liked');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }

    private function handleHTMLBasedOnType($request, $prop) {
        try {
            $shop = Shop::with(['notificationSettings', 'productRackInfo'])->where('shop_url', $request['shop'])->first();
            if($shop !== null && $shop->count() > 0) {
                $productRackSettings = $shop->productRackInfo;
                $flag = isset($productRackSettings) && $productRackSettings !== null && $productRackSettings->count() > 0;
                if($flag) {
                    if($productRackSettings->$prop === 1 || $productRackSettings->$prop === true) {
                        $response = $this->callAlmeAppBackend($request, $prop);
                        Log::info('Response from Alme');
                        Log::info($response);
                        if($response['statusCode'] == 200) {
                            $html = $this->getMostViewedHTML($response['body'], $shop, $prop);
                            $response = ['status' => true, 'response' => $response, 'html' => $html];   
                        } else {
                            $response = ['status' => true, 'response' => $response, 'html' => null];   
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
        } catch (Throwable $th) {
            Log::info($th->getMessage().' '.$th->getLine());
            $response = ['status' => false, 'message' => $th->getMessage().' '.$th->getLine(), 'html' => null];
        }
        
        return $response;
    }

    private function callAlmeAppBackend($request, $prop) {
        $getParams = '?app_name='.$request['shop'].'&max_items='.$this->maxItems.'&token='.$request['token'];
        
        $pathName = 'api/';
        switch($prop) {
            case 'hps_one': $pathName .= 'most_visited'; break;
            case 'hps_two': $pathName .= 'most_carted'; break;
            case 'pps_one': $pathName .= 'most_visited'; break;
            case 'pps_two': $pathName .= 'most_carted'; break;
            
            case 'most_added_prods': $pathName .= 'most_visited'; break;
            case 'user_liked': $pathName .= 'most_carted'; break;
            case 'pop_picks': $pathName .= 'carts'; break;
            case 'feat_collect': $pathName .= 'visits';break;
            case 'high_convert_prods': $pathName .= 'most_visited'; break;
            
            default: $pathName .= 'most_visited'; 
        }

        $endpoint = getAlmeAppURLForStore($pathName.$getParams);
        Log::info('Alme backend endpoint '.$endpoint);
        $headers = getAlmeHeaders();
        return $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
    }
    
    private function getMostViewedHTML($body, $shop, $prop) {
        try {
            if($body !== null && count($body) > 0) {
                $products = $shop->getProducts()->whereIn('product_id', $body)->get();
                $viewFilePrefix = 'appExt.';
                $viewFile = null;
                $title = null;
                switch($prop) {
                    case 'hps_one': $viewFile = 'productList'; $title = 'Pick up where you left off'; break;
                    case 'hps_two': $viewFile = 'productList'; $title = 'Crowd favorites'; break;
                    case 'pps_one': $viewFile = 'productList'; $title = 'Users also liked'; break;
                    case 'pps_two': $viewFile = 'productList'; $title = 'Featured collection'; break;
                    
                    case 'most_added_prods': $viewFile = 'most_viewed'; break;
                    case 'user_liked': $viewFile = 'most_carted'; break;
                    case 'pop_picks': $viewFile = 'user_liked'; break;
                    case 'feat_collect': $viewFile = 'carts';break;
                    case 'high_convert_prods': $viewFile = 'recommended'; break;
                    
                    default: $viewFile = 'most_viewed';  
                }

                return view($viewFilePrefix.$viewFile, ['products' => $products, 'title' => $title])->render();
            }
            Log::info('Body null found');
            Log::info($body);
            return null;
        } catch (\Throwable $th) {
            Log::info($th->getMessage().' '.$th->getLine());
            return null;
        }
    }
}
