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

    public function pickUpWhereYouLeftOff(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'pickUpWhereYouLeftOff');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }
    
    public function crowdFavorites(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'crowdFavorites');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }
    
    public function usersAlsoLiked(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'usersAlsoLiked');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }
    
    public function featuredCollection(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $response = $this->handleHTMLBasedOnType($request->all(), 'featuredCollection');
        } else {
            $response = ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];
        }
        return response()->json($response);
    }

    /*
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
    */

    private function handleHTMLBasedOnType($request, $prop) {
        try {
            $shop = Shop::with(['notificationSettings', 'productRackInfo'])->where('shop_url', $request['shop'])->first();
            if($shop !== null && $shop->count() > 0 && $shop->isActivated()) {
                $productRackSettings = $shop->productRackInfo;
                $flag = isset($productRackSettings) && $productRackSettings !== null && $productRackSettings->count() > 0;
                if($flag) {
                    if($productRackSettings->$prop === 1 || $productRackSettings->$prop === true) {
                        $response = $this->callAlmeAppBackend($request, $prop);
                        if($response['statusCode'] == 200) {
                            $html = $this->getHTML($response['body'], $shop, $prop);
                            $response = ['status' => true, 'response' => $response, 'html' => $html];   
                        } else {
                            $response = ['status' => true, 'response' => $response, 'html' => null, 'debug' => 'Status code not 200 - '.$response['statusCode']];   
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
            case 'usersAlsoLiked': $pathName .= 'users_also_liked'; break;
            case 'pickUpWhereYouLeftOff': $pathName .= 'pick_up_where_you_left_off'; break;
            case 'crowdFavorites': $pathName .= 'crowd_favorites'; break;
            case 'featuredCollection': $pathName .= 'featured_collection'; break;
            
            /*
            case 'most_added_prods': $pathName .= 'most_visited'; break;
            case 'user_liked': $pathName .= 'most_carted'; break;
            case 'pop_picks': $pathName .= 'carts'; break;
            case 'feat_collect': $pathName .= 'visits';break;
            case 'high_convert_prods': $pathName .= 'most_visited'; break;
            
            default: $pathName .= 'most_visited';
            */ 
        }

        $endpoint = getAlmeAppURLForStore($pathName.$getParams);
        $headers = getAlmeHeaders();
        return array_merge(['almebackend' => $endpoint], $this->makeAnAlmeAPICall('GET', $endpoint, $headers));
    }
    
    private function getHTML($body, $shop, $prop) {
        try {
            if($body !== null && count($body) > 0) {
                $products = $shop->getProducts()->whereIn('product_id', $body)->get();
                $viewFilePrefix = 'appExt.';
                $viewFile = null;
                $title = null;
                switch($prop) {
                    case 'pickUpWhereYouLeftOff': $viewFile = 'productList'; $title = 'Pick up where you left off'; break;
                    case 'crowdFavorites': $viewFile = 'productList'; $title = 'Crowd favorites'; break;
                    case 'usersAlsoLiked': $viewFile = 'productList'; $title = 'Users also liked'; break;
                    case 'featuredCollection': $viewFile = 'productList'; $title = 'Featured collection'; break;
                    
                    /*
                    case 'most_added_prods': $viewFile = 'most_viewed'; break;
                    case 'user_liked': $viewFile = 'most_carted'; break;
                    case 'pop_picks': $viewFile = 'user_liked'; break;
                    case 'feat_collect': $viewFile = 'carts';break;
                    case 'high_convert_prods': $viewFile = 'recommended'; break;
                    
                    default: $viewFile = 'most_viewed';  
                    */
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
