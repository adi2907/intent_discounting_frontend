<?php

namespace App\Http\Controllers;

use App\Models\AlmeAnalytics;
use App\Models\AlmeClickAnalytics;
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

    public function recordCopyDiscountCodeEvent(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            $shop = Shop::where('shop_url', $request->shop)->first();
            $stats = $shop->getNotificationStats();
            if($stats != null) {
                $stats['submissions'] += 1;
                $shop->setNotifStats($stats);
            }

            try {
                $arr = [
                    'shop_id' => $shop->id,
                    'alme_token' => $request->token,
                    'session_id' => $request->session_id,
                    'sale_notif_click' => time()
                ];

                AlmeClickAnalytics::create($arr);
            } catch (Throwable $th) {
                Log::info('Line 45 '.$th->getMessage().' '.$th->getLine());
            }

            $response = ['status' => true, 'message' => 'Recorded'];
        } else {
            $response = ['status' => true, 'message' => 'Shop not in request'];
        }
        return response()->json($response); 
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

    public function cartSuggestions(Request $request) {
        $response = $request->has('shop') && $request->filled('shop') ?
            $this->handleCartSuggestionsHTML($request->all()) :
            ['status' => true, 'message' => 'Store not in request', 'debug' => $request->all(), 'html' => null];

        return response()->json($response);
    }

    private function handleCartSuggestionsHTML($request) {
        // Log::info('Request received for cart suggestions');
        // Log::info($request);
        $shop = Shop::where('shop_url', $request['shop'])->first();
        $products = $shop->getProducts()->limit(4)->get();
        $viewFilePrefix = 'appExt.';
        $viewFile = 'productList'; 
        $title = 'Suggested products';
        $html = view($viewFilePrefix.$viewFile, ['products' => $products, 'title' => $title])->render(); 
        return ['status' => true, 'html' => $html];
    }

    private function handleHTMLBasedOnType($request, $prop) {
        try {
            $shop = Shop::with(['notificationSettings', 'productRackInfo'])->where('shop_url', $request['shop'])->first();
            if($shop !== null) {
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
                $response = ['status' => true, 'message' => 'Store not found', 'debug' => $request, 'html' => null];
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
        }

        $endpoint = getAlmeAppURLForStore($pathName.$getParams);
        $headers = getAlmeHeaders();
        return array_merge(['almebackend' => $endpoint], $this->makeAnAlmeAPICall('GET', $endpoint, $headers));
    }
    
    private function getHTML($body, $shop, $prop) {
        try {
            if($body !== null && count($body) > 0) {
                $products = $shop->getProducts()->whereIn('product_id', $body)->get()->keyBy('product_id')->toArray();
                $viewFilePrefix = 'appExt.';
                $viewFile = null;
                $title = null;

                switch($prop) {
                    case 'pickUpWhereYouLeftOff': $viewFile = 'productList'; $title = 'Pick up where you left off'; break;
                    case 'crowdFavorites': $viewFile = 'productList'; $title = 'Crowd favorites'; break;
                    case 'usersAlsoLiked': $viewFile = 'productList'; $title = 'Users also liked'; break;
                    case 'featuredCollection': $viewFile = 'productList'; $title = 'Featured collection'; break;
                }
                
                return view($viewFilePrefix.$viewFile, ['products' => $products, 'title' => $title, 'shop_url' => $shop->shop_url])->render();
                
                // if($shop == 'almestore1.myshopify.com') {
                //     switch($prop) {
                //         case 'pickUpWhereYouLeftOff': $viewFile = 'productList'; $title = 'Pick up where you left off'; break;
                //         case 'crowdFavorites': $viewFile = 'productList'; $title = 'Crowd favorites'; break;
                //         case 'usersAlsoLiked': $viewFile = 'productList'; $title = 'Users also liked'; break;
                //         case 'featuredCollection': $viewFile = 'productList'; $title = 'Featured collection'; break;
                //     }
                // } else if($shop == 'millet-amma.myshopify.com') {
                //     switch($prop) {
                //         case 'pickUpWhereYouLeftOff': $viewFile = 'productList'; $title = 'Pick up where you left off'; break;
                //         case 'crowdFavorites': $viewFile = 'productList'; $title = 'Crowd favorites'; break;
                //         case 'usersAlsoLiked': $viewFile = 'productList'; $title = 'Users also liked'; break;
                //         case 'featuredCollection': $viewFile = 'productList'; $title = 'Featured collection'; break;
                //     }
                // } else {
                // switch($prop) {
                //     case 'pickUpWhereYouLeftOff': $viewFile = 'productList'; $title = 'Pick up where you left off'; break;
                //     case 'crowdFavorites': $viewFile = 'productList'; $title = 'Crowd favorites'; break;
                //     case 'usersAlsoLiked': $viewFile = 'productList'; $title = 'Users also liked'; break;
                //     case 'featuredCollection': $viewFile = 'productList'; $title = 'Featured collection'; break;
                // }
                //}

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
