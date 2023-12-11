<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopDetail;
use App\Models\User;
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

    public function checkStoreThemeInstall() {
        if(Auth::check()) {
            $user = Auth::user();
            $store = $user->shopifyStore;
        } else {
            $user = User::whereHas('shopifyStore')->with('shopifyStore')->first();
            $store = $user->shopifyStore;
        }

        $endpoint = getShopifyAPIURLForStore('themes.json', $store);
        $headers = getShopifyAPIHeadersForStore($store);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        
        $activeTheme = null;
        if(isset($response['body']) && $response['statusCode'] == 200) {
            $themes = $response['body']['themes'];
            if($themes !== null && count($themes) > 0) {
                foreach($themes as $theme) {
                    if(array_key_exists('role', $theme) && $theme['role'] === 'main') {
                        $activeTheme = $theme;
                    }
                }
            }
        }

        $templateData = $this->tryToFindTemplateData($store, $activeTheme);

        if($activeTheme !== null) {
            return response()->json([
                'status' => false, 
                'activeTheme' => $activeTheme, 
                'templateData' => $templateData,
                'themeEditorURL' => 'https://admin.shopify.com/store/'.(str_replace('.myshopify.com', '', $store->shop_url)).'/themes/'.$activeTheme['id'].'/editor']
            );
        }

        return response()->json(['status' => false]);
    }

    private function tryToFindTemplateData($store, $activeTheme) {
        $endpoint = getShopifyAPIURLForStore('themes/'.$activeTheme['id'].'/assets.json?asset[key]=templates/product.json', $store);
        $anotherEndpoint = getShopifyAPIURLForStore('themes/'.$activeTheme['id'].'/assets.json?asset[key]=templates/index.json', $store);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, getShopifyAPIHeadersForStore($store));
        $response = json_decode($response['body']['asset']['value'], true);

        $anotherResponse = $this->makeAnAPICallToShopify('GET', $anotherEndpoint, getShopifyAPIHeadersForStore($store));
        $anotherResponse = json_decode($anotherResponse['body']['asset']['value'], true);
        return [
            'productJson' => $response,
            'homeJson' => $anotherResponse
        ];
    }

    public function checkSubmitContact(Request $request) {
        if($request->has('shop')) {
            $shop = Shop::with(['notificationSettings'])->where('shop_url', $request->shop)->first();
            if($shop !== null && $shop->count() > 0) {
                $notificationSettings = $shop->notificationSettings;
                if(isset($notificationSettings->status) && ($notificationSettings->status === 1 || $notificationSettings->status === true)) {
                    return response()->json(['status' => true, 'data' => true]);
                }
            }
        }
        return response()->json(['status' => true, 'data' => false]);
    }

    public function checkCronStatus() {
        $cacheKeys = config('custom.cacheKeys');
        $returnVal = [];
        foreach($cacheKeys as $key) {
            $returnVal[$key] = Cache::has($key) ? Cache::get($key) : null;
        }
        return response()->json(['value' => $returnVal]);
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

    public function mapCartContents(Request $request) {
        Log::info('Cart contents request received');
        Log::info(json_encode($request->all()));
        return response()->json(['status' => true, 'message' => '']);
    }

    public function showDashboard(Request $request) {
        try{
            $request = $request->only('shop');
            $shop = $request['shop'] ?? Auth::user()->shopifyStore->shop_url;
            $baseShop = Shop::where('shop_url', $shop)->first();
            $shopDetails = $baseShop !== null ? ShopDetail::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first() : null;
            $almeResponses = $this->getAlmeAnalytics($shop);
            //dd($almeResponses);
            return view('new_dashboard', compact('baseShop', 'shopDetails', 'almeResponses'));
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

    public function contactCaptureSettings(Request $request) {
        if($request->has('shop')) {
            $shop = Shop::with(['getLatestPriceRule', 'getLatestDiscountCode', 'notificationSettings'])->where('shop_url', $request->shop)->first();
            $code = $shop !== null ? $shop->getLatestDiscountCode->code : null;
            $notificationSettings = $shop->notificationSettings;
            $saleStatus = isset($notificationSettings) && $notificationSettings !== null && isset($notificationSettings->status) && ($notificationSettings->status === true || $notificationSettings->status === 1);
            $html = $saleStatus ? view('theme_popups')->render() : null;
            return response()->json(['code' => $code, 'status' => true, 'html' => $html]);
        }
        return response()->json(['code' => null, 'status' => true, 'html' => null]);
    }

    public function saleNotificationPopup(Request $request) {
        if($request->has('shop')) {
            $shop = Shop::with(['getLatestPriceRule', 'getLatestDiscountCode', 'notificationSettings'])->where('shop_url', $request->shop)->first();
            $code = $shop !== null ? $shop->getLatestDiscountCode->code : null;
            $notificationSettings = $shop->notificationSettings;
            $saleStatus = isset($notificationSettings) && $notificationSettings !== null && isset($notificationSettings->sale_status) && ($notificationSettings->sale_status === true || $notificationSettings->sale_status === 1);
            $html = $saleStatus ? view('sale_notification_popup')->render() : null;
            return response()->json(['code' => $code, 'status' => true, 'html' => $html]);
        }
        return response()->json(['code' => null, 'status' => true, 'html' => null]);
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

    public function updateProductRackSettings(Request $request) {
        try{
            if($request->ajax() && $request->filled('field') && $request->filled('value')) {
                $user = Auth::user();
                $shop = $user->shopifyStore;
                $value = $request->value === 'on';
                $shop->productRackInfo()->update([$request->field => $value]);
                return response()->json(['status' => true, 'message' => 'Updated!']);
            }
            return response()->json(['status' => false, 'message' => 'Invalid Request']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }
}
