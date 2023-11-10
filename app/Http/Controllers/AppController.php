<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopDetail;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppController extends Controller {
    
    use FunctionTrait, RequestTrait;
    public function __construct() {
        
    }

    public function checkCronStatus() {
        $cacheKey = config('custom.cacheKeys.cronStatus');
        return response()->json(['value' => Cache::has($cacheKey) ? Cache::get($cacheKey) : null]);
    }

    public function showProductRacks(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $productRackInfo = $shop->productRackInfo;
        if($productRackInfo == null || $productRackInfo->count() < 1) {
            $productRackInfo = $shop->productRackInfo()->create([
                'user_liked' => false,
                'crowd_fav' => false,
                'pop_picks' => false,
                'feat_collect' => false,
                'prev_browsing' => false,
                'high_convert_prods' => false,
                'most_added_prods' => false,
                'slow_inv' => false
            ]);
        }
        return view('product_racks', ['productRackInfo' => $productRackInfo]);
    }

    public function showNotificationSettings(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $notifSettings = $shop->notificationSettings;
        if($notifSettings == null || $notifSettings->count() < 1) {
            $notifSettings = $shop->notificationSettings()->create([
                'status' => false,
                'title' => null,
                'description' => null,
                'discount_value' => 10,
                'sale_status' => false,
                'sale_discount_value' => 10,
                'discount_expiry' => 24
            ]);
        }
        return view('notifications', ['notifSettings' => $notifSettings]);
    }

    public function showDashboard(Request $request) {
        try{
            $request = $request->only('shop');
            $shop = $request['shop'] ?? Auth::user()->shopifyStore->shop_url;
            $baseShop = Shop::where('shop_url', $shop)->first();
            $shopDetails = $baseShop !== null ? ShopDetail::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first() : null;
            return view('new_dashboard', compact('baseShop', 'shopDetails'));
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        } catch(Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function getDiscountCodeForStore(Request $request) {
        try{
            if($request->has('shop') && $request->filled('shop')) {
                $shop = Shop::with(['getLatestDiscountCode'])->where('shop_url', $request->shop)->first();
                $code = $shop !== null ? $shop->getLatestDiscountCode->code : null;
                return ['status' => true, 'code' => $code]; 
            } 
            return response()->json(['status' => false, 'message' => 'Invalid Request/No Shop param present in request']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        } catch(Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function themePopups(Request $request) {
        $html = view('theme_popups')->render();
        if($request->has('shop')) {
            $shop = Shop::with(['getLatestPriceRule', 'getLatestDiscountCode'])->where('shop_url', $request->shop)->first();
            $code = $shop !== null ? $shop->getLatestDiscountCode->code : null;
            return response()->json(['code' => $code, 'status' => true, 'html' => $html]);
        }
        return response()->json(['code' => null, 'status' => true, 'html' => $html]);
    }

    public function removeCustomScript(Request $request) {
        try {
            if($request->has('shop_id') && $request->filled('shop_id')) {
                $shop = Shop::where('id', $request->shop_id)->first();
                $endpoint = getShopifyAPIURLForStore('script_tags.json', $shop);
                $headers = getShopifyAPIHeadersForStore($shop);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
                if(array_key_exists('statusCode', $response) && $response['statusCode'] == 200) {
                    $body = $response['body']['script_tags'];
                    if(count($body) > 0) {
                        foreach($body as $scriptTag) {
                            $endpoint = getShopifyAPIURLForStore('script_tags/'.$scriptTag['id'].'.json', $shop);
                            $this->makeAnAPICallToShopify('DELETE', $endpoint, $headers);
                        }
                    }
                    //Install the script tag
                    $installResponse = $this->addScriptTagToStore($shop);
                    return response()->json(['status' => true, 'response' => $installResponse]);
                }
            }
            return response()->json(['status' => false, 'message' => 'Shop ID not in params']);
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function updateNotificationSettings(Request $request) {
        try {
            $user = Auth::user();
            $shop = $user->shopifyStore;
            $value = $request->fieldtype == 'checkbox' ? ($request->value == 'on' ? true : false) : $request->value;
            $shop->notificationSettings()->update([$request->field => $value]);
            return response()->json(['status' => true, 'message' => 'Updated!']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }
}
