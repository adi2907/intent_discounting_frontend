<?php

namespace App\Http\Controllers;

use App\Jobs\ConfigureMissingProducts;
use App\Jobs\CreateNotificationAsset;
use App\Jobs\CreateShopDiscountCode;
use App\Jobs\SyncShopifyOrders;
use App\Models\AlmeAnalytics;
use App\Models\AlmeClickAnalytics;
use App\Models\AlmeShopifyOrders;
use App\Models\IpMap;
use App\Models\Shop;
use App\Models\ShopDetail;
use App\Models\ShopifyOrder;
use App\Models\User;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppController extends Controller {
    
    use FunctionTrait, RequestTrait;
    public function __construct() {
        
    }

    public function smartConvertAI(Request $request) {
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
        $contactStats = $shop->getNotificationStats();
        return view('notifications_ai', ['notifSettings' => $notifSettings, 'stats' => $contactStats]);
    }

    public function mapCheckout(Request $request) {
        Log::info('Request came for mapping checkout');
        Log::info(json_encode($request->all()));

        return response()->json(['status' => true, 'request' => $request->all()]);
    }

    public function showAltIdentifiedUsers(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $request = $request->all();
        $daysDiffFromStart = $daysDiffFromEnd = null;

        if(!array_key_exists('start_date', $request) && !array_key_exists('end_date', $request)) {
            $request['start_date'] = strtotime('-1 day');
            $request['end_date'] = strtotime('today');
        } else {
            $daysDiffFromStart = floor(abs($request['start_date'] - time())/60/60/24);
            $daysDiffFromEnd = floor(abs($request['end_date'] - time())/60/60/24);
        }

        $data = $this->callAlmeAppIdentifiedUsers($shop, $request);
        //$regdUserIdArr = $this->getRegdUserIds($data);
        // $customers = $shop->getIdentifiedUsers();
        // if($regdUserIdArr !== null && is_array($regdUserIdArr) && count($regdUserIdArr) > 0) {
        //     $customers = $customers->where(function ($query) use ($regdUserIdArr) {
        //         return $query->where('regd_user_id', '>', 0)->whereIn('regd_user_id', $regdUserIdArr);
        //     });    
        // } else {
        //     $customers = $customers->where(function ($query) {
        //         return $query->where('regd_user_id', '>', 0);
        //     });   
        // }

        // $customers = $customers->select(['email', 'name', 'regd_user_id'])->get();
        
        // $customersArr = [];
        // if($customers !== null && $customers->count() > 0) {
        //     $customersArr = $customers->keyBy('regd_user_id')->toArray();
        // }

        return view('new_identified_users', compact(/*'customersArr',*/ 'data', 'request', 'daysDiffFromStart', 'daysDiffFromEnd'));
    }

    public function getRegdUserIds($data) {
        if($data['statusCode'] == 200 && is_array($data['body']) && count($data['body']) > 0) {
            $returnVal = [];
            $collect = new Collection($data['body']);
            $uniqueArr = array_unique($collect->pluck('regd_user_id')->toArray());
            $uniqueArr = array_filter($uniqueArr, function ($val) { return $val !== null && $val > 0; });
            if($uniqueArr != null && count($uniqueArr) > 0) {
                foreach($uniqueArr as $val) {
                    $returnVal[] = (int) $val;
                }
            }
            return $returnVal;
        }
        return null;
    }

    public function checkShopifyAPIs(Request $request) {
        $shop = Auth::check() ? Auth::user()->shopifyStore : Shop::where('shop_url', $request->shop)->first();
        //$shopEndpoint = getShopifyAPIURLForStore('shop.json', $shop);
        $ordersEndpoint = getShopifyAPIURLForStore('orders.json?status=any', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        //$firstResponse = $this->makeAnAPICallToShopify('GET', $shopEndpoint, $headers);
        $secondResponse = $this->makeAnAPICallToShopify('GET', $ordersEndpoint, $headers);
        $arr = [];
        foreach($secondResponse['body']['orders'] as $order) {
            $arr[] = [
                'email' => $order['email'],
                'created_at' => $order['created_at'],
                'phone' => $order['phone'],
                'name' => $order['name'],
                'id' => $order['id']
            ];
        }
        return response()->json($arr);
    }

    public function updateStoreNotifications(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $updateArr = [];

        
        if($request->filled('status')) {
            $shop->notificationSettings()->update([
                'status' => $request->status == 'on',
                'title' => $request->notification_title,
                'description' => $request->notification_desc,
                'cdn_logo' => $request->cdn_logo,
            ]);
        }

        if($request->filled('sale_status')) {
            /*
            * Keep settings that was created last so we can compare 
            * If the discount percentage value changed, then create a discount code right away,
            * pass an override flag into the job file.
            */
            $prevSettings = $shop->notificationSettings;

            $saleNotificationSwitchedOn = $request->sale_status == 'on';

            $shop->notificationSettings()->update([
                'sale_status' => $saleNotificationSwitchedOn,
                'sale_discount_value' => $request->sale_discount_value,
                'discount_expiry' => $request->discount_expiry,
                'min_value_coupon' => $request->filled('min_value_coupon') ? $request->min_value_coupon : 0
            ]);
            
            if($prevSettings != null && $prevSettings->count() > 0) {
                if(
                    //Something has to change in frontend in order to create a new discount code
                    $request->sale_discount_value !== $prevSettings->sale_discount_value ||
                    $request->discount_expiry !== $prevSettings->discount_expiry ||
                    $request->min_value_coupon !== $prevSettings->min_value_coupon
                ) {
                    if($saleNotificationSwitchedOn) {
                        Log::info('Discount percent changed for shop '.$shop->shop_url.' Creating a discount code now.');
                        
                        //First delete the existing price rule from Shopify and DB
                        $shop->refresh('getLatestPriceRule');
                        $priceRule = $shop->getLatestPriceRule;
                        if($priceRule !== null && $priceRule->price_id !== null && strlen($priceRule->price_id) > 0) {
                            $this->deletePriceRule($priceRule, $shop);
                            
                            //Don't delete from database. IMPORTANT! We need the history of coupons creation.
                            //$shop->getLatestPriceRule()->delete();
                        }
                        $shop->refresh('notificationSettings');
                        $this->createPriceRuleForShop($shop);
                        $shop->refresh('getLatestPriceRule');
                        $frequency = (int) $shop->notificationSettings->discount_expiry;
                        $this->createAndSaveDiscountCode($shop->getLatestPriceRule, $shop, $frequency);
                    }
                }
            }
        }
        
        return response()->json(['status' => true, 'message' => 'Updated!']);
    }

    public function updateProductRacks(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $shop->productRackInfo()->update([
            'usersAlsoLiked' => $request->usersAlsoLiked == 'on',
            'featuredCollection' => $request->featuredCollection == 'on',
            'pickUpWhereYouLeftOff' => $request->pickUpWhereYouLeftOff == 'on',
            'crowdFavorites' => $request->crowdFavorites == 'on'
        ]);
        return response()->json(['status' => true, 'message' => 'Updated!']);
    }

    public function syncOrders() {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        SyncShopifyOrders::dispatch($shop)->onConnection('sync');
        return back();

    }

    public function mapIp(Request $request) {
        try{
            //Log::info('Request received for ip map '.json_encode($request->all()));
            if($request->has('shop') && $request->filled('shop')) {
                $shop = Shop::where('shop_url', $request->shop)->first();
                $ip = $request->ipAddr;
                $token = $request->token;
                $sessionId = $request->session_id;
                $checkRecord = IpMap::where('shop_id', $shop->id)->where('ip_address', $ip)->exists();
                if($checkRecord) {
                    IpMap::where('shop_id', $shop->id)->where('ip_address', $ip)->update(['alme_token' => $token]);
                } else {
                    $updateArr = ['alme_token' => $token];
                    $createArr = array_merge($updateArr, [
                        'shop_id' => $shop->id, 
                        'ip_address' => $ip, 
                        'alme_token' => $token, 
                        'session_id' => $sessionId
                    ]);
                    IpMap::updateOrCreate($updateArr, $createArr);
                }
                //Create analytics row and click events row
                $arr = [
                    'shop_id' => $shop->id,
                    'alme_token' => $token,
                    'session_id' => $sessionId
                ];

                AlmeAnalytics::updateOrCreate($arr, $arr);
                AlmeClickAnalytics::updateOrCreate($arr, $arr);
            }
            return response()->json(['status' => true, 'message' => 'OK']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'OK', 'error' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function saleNotification(Request $request) {
        try {
            if($request->has('app_name') && $request->filled('app_name')) {
                $shop = Shop::with('notificationSettings')->where('shop_url', $request->app_name)->first();
                $blockRequestsUntil = strtotime('+5 minutes');
                if($shop !== null && $shop->count() > 0) {
                    if(isset($shop->notificationSettings) && isset($shop->notificationSettings->sale_status)) {
                        if($shop->notificationSettings->sale_status == 1 || $shop->notificationSettings->sale_status === true) {
                            
                            $almeToken = null;
                            if($request->has('token') && filled($request->token)) {
                                $almeToken = $request->token;
                            }

                            if($almeToken === null) {
                                if($request->has('alme_user_token') && filled($request->alme_user_token)) {
                                    $almeToken = $request->alme_user_token;
                                }
                            }

                            if($almeToken !== null) {
                                $endpoint = getAlmeAppURLForStore('notification/sale_notification/?session_id='.$request->session_id.'&token='.$almeToken.'&app_name='.$request->app_name);
                                $headers = getAlmeHeaders();
                                $response = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
                                if(array_key_exists('criteria_met', $response['body']) && $response['body']['criteria_met'] == false) {
                                    return response()->json(['status' => true, 'message' => 'Turned off', 'blockRequests' => true, 'blockRequestsUntil' => $blockRequestsUntil]);
                                }
                                
                                return response()->json($response['body']);
                            }
                            return response()->json(['status' => true, 'message' => 'Alme Token found null']);
                        } else {
                            return response()->json(['status' => true, 'message' => 'Turned off', 'blockRequests' => true, 'blockRequestsUntil' => $blockRequestsUntil]);
                        } 
                    } else {
                        return response()->json(['status' => true, 'message' => 'Data not found', 'blockRequests' => true, 'blockRequestsUntil' => $blockRequestsUntil]);
                    }
                } else {
                    return response()->json(['status' => true, 'message' => 'Shop Not Found', 'blockRequests' => true, 'blockRequestsUntil' => $blockRequestsUntil]);
                }
            }
            return response()->json(['status' => true, 'message' => 'OK']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'OK', 'error' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function submitContact(Request $request) {
        try {
            if($request->has('app_name') && $request->filled('app_name')) {
                $shop = Shop::where('shop_url', $request->app_name)->first();
                if($shop !== null) {
                    $almeToken = $request->alme_user_token;
                    $payload = [
                        "name" => $request->name,
                        "phone" => $request->phone,
                        "alme_user_token" => $almeToken,
                        "app_name" => $request->app_name,
                    ];
                    $endpoint = getAlmeAppURLForStore('notification/submit_contact/');
                    $headers = getAlmeHeaders();
                    $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                    try {
                        $stats = $shop->getContactCaptureStats();
                        if($stats !== null && array_key_exists('submissions', $stats)) {
                            $stats['submissions'] += 1;
                        } else {
                            $stats = [
                                'total_views' => 0,
                                'submissions' => 0,
                                'percentage' => 0
                            ];
                        }
                        
                        $shop->setContactCaptureStats($stats);
                    
                    } catch (Exception $th) {
                        Log::info($th->getMessage().' '.$th->getLine());
                    }

                    try {
                        $updateArr = [
                            'alme_token' => $almeToken,
                            'shop_id' => $shop->id
                        ];

                        $clickArr = array_merge($updateArr, [
                            'contact_notif_click' => time()
                        ]);

                        AlmeAnalytics::updateOrCreate($updateArr, $updateArr);
                        AlmeAnalytics::where($updateArr)->increment('contact_impressions');
                        AlmeClickAnalytics::updateOrCreate($updateArr, $clickArr);
                        AlmeClickAnalytics::where($updateArr)->orderBy('id','desc')->first()->update(['contact_notif_click' => time()]);
                    } catch (Throwable $th) {
                        Log::info('Line 349 '.$th->getMessage().' '.$th->getLine());
                    }
                    // if($response['statusCode'] !== 200) {
                    //     Log::info('Error in submit contact');
                    //     Log::info('Payload '.json_encode($payload));
                    //     Log::info('Response '.json_encode($response));
                    // }
                    return response()->json($response['body']);
                }
            }
            return response()->json(['status' => true, 'message' => 'OK']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'OK', 'error' => $e->getMessage().' '.$e->getLine()]);
        }
    }
    
    public function getPurchaseEvents() {
        $user = Auth::user();
        if($user->email == 'helloworld.adi@gmail.com') {
            $data = ShopifyOrder::orderBy('created_at', 'desc')->limit(20)->get();
            return response()->json(['status' => true, 'data' => $data]);
        }
        return response()->json(['status' => false]);
    }

    public function checkAlmeScripts(Request $request) {
        $returnVal = [];
        $shops = Shop::where('shop_url', $request->shop)->get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
                $liveTheme = $this->getLiveThemeForShop($shop);
                //$returnVal['theme'][$shop->shop_url] = $liveTheme;
                $assets = $this->getAssets($shop, $liveTheme);
                $returnVal['assets'][$shop->shop_url] = $assets;
                $shopDetails = $this->getShopDetails($shop);
                $returnVal['shop'][$shop->shop_url] = $shopDetails;
            } else {
                $returnVal['result'][$shop->shop_url] = 'Invalid installation';
            }
        }
        return response()->json($returnVal);
    }

    private function getAssets($shop, $liveTheme) {
        $appBlockTemplates = ['product', 'collection', 'index'];
        $endpoint = getShopifyAPIURLForStore('themes/'.$liveTheme['id'].'/assets.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint,$headers);
        return $response;
    }

    /*
    public function turnAlmeScriptOn() {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $liveTheme = $this->getLiveThemeForShop($shop);
        $scriptIsRunning = $this->checkAlmeScriptRunningOrNot($shop);
        if(!$scriptIsRunning) {
            $assetKey = 'asset[key]=config/settings_data.json';
            $asset = $this->getAssetsForTheme($shop, $liveTheme, $assetKey);
            $assetContents = json_decode($asset['value'], true);
            $copyAssetContents = $assetContents;
            if(is_array($assetContents['current'])) {
                foreach($assetContents['current']['blocks'] as $blockId => $data) {
                    $themeBlockId = config('shopify.APP_BLOCK_ID');
                    if(array_key_exists('type', $data) && $data['type'] == 'shopify://apps/alme/blocks/app-embed/'.$themeBlockId) {
                        $copyAssetContents['current']['blocks'][$blockId]['disabled'] = false;
                    }
                }
            }

            $payload = [
                'asset' => [
                    'key' => 'config/settings_data.json',
                    'value' => str_replace('"settings":[]', '"settings":{}', json_encode($copyAssetContents)) 
                ]
            ];

            $endpoint = getShopifyAPIURLForStore('themes/'.$liveTheme['id'].'/assets.json', $shop, '2023-01');
            $headers = getShopifyAPIHeadersForStore($shop);
            $this->makeAnAPICallToShopify('PUT', $endpoint, $headers, $payload);

            return back();
        }
    }
    */

    public function turnAlmeScriptOn() {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $liveTheme = $this->getLiveThemeForShop($shop);
        $storeName = str_replace('.myshopify.com', '', $shop->shop_url);
        $url = 'https://admin.shopify.com/store/'.$storeName.'/themes/'.$liveTheme['id'].'/editor?context=apps';
        return redirect($url);
    }

    public function showSetupPage() {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $liveTheme = $this->getLiveThemeForShop($shop);
        $storeName = str_replace('.myshopify.com', '', $shop->shop_url);
        $url = 'https://admin.shopify.com/store/'.$storeName.'/themes/'.$liveTheme['id'].'/editor';
        return view('store_setup', compact('user', 'shop', 'liveTheme', 'storeName', 'url'));
    }

    public function reloadDashboard(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $almeResponses = $this->getAlmeAnalytics($shop->shop_url, $request->all(), false);
        return response()->json(['status' => true, 'response'=> $almeResponses, 'request' => $request->all()]);
    }

    public function orderTopCarted(Request $request) {
        if($request->ajax()) {
            $user = Auth::user();
            $shop = $user->shopifyStore;
            $almeResponses = $this->getTopCarted($shop->shop_url, $request->all());
            //dd($almeResponses);
            if($almeResponses['product_cart_conversion'] && $almeResponses['product_cart_conversion']['statusCode'] == 200) {
                $html = view('dashboard.top_cart_conversions', [
                    'assoc_data' => $almeResponses['product_cart_conversion']['body']['assoc_data'],
                    'products' => $almeResponses['product_cart_conversion']['body']['products'],
                    'baseShop' => $shop
                ])->render();
                
                return response()->json(['status' => true, 'response'=> $almeResponses, 'html' => $html, 'request' => $request->all()]);
            }
            return response()->json(['status' => true, 'response'=> $almeResponses, 'html' => '', 'request' => $request->all()]);
        }
    }

    public function orderTopVisited(Request $request) {
        if($request->ajax()) {
            $user = Auth::user();
            $shop = $user->shopifyStore;
            $almeResponses = $this->getTopVisits($shop->shop_url, $request->all());
            if($almeResponses['product_visits'] && $almeResponses['product_visits']['statusCode'] == 200) {
                $html = view('dashboard.top_products', [
                    'assoc_data' => $almeResponses['product_visits']['body']['assoc_data'],
                    'products' => $almeResponses['product_visits']['body']['products'],
                    'baseShop' => $shop
                ])->render();
                
                return response()->json(['status' => true, 'response'=> $almeResponses, 'html' => $html, 'request' => $request->all()]);
            }
            return response()->json(['status' => true, 'response'=> $almeResponses, 'html' => '', 'request' => $request->all()]);
        }
    }

    public function downloadIdentifiedUsersAsExcel(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $data = $shop->getIdentifiedUsers()->where(function ($q) use ($request){
            if(filled($request->start_date) && filled($request->end_date)) {
                return $q->where('last_visited', '>', date('Y-m-d 00:00:00', $request['start_date']))
                         ->where('last_visited', '<', date('Y-m-d 23:59:59', $request['end_date']));
            }
        })->get();
        if($data !== null && $data->count() > 0) {
            $headers = [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=IdentifiedUsers-'.time().'.csv',
                'Expires' => '0',
                'Pragma' => 'public'
            ];
        
            $callback = function() use ($data) {
                $FH = fopen('php://output', 'w');
                fputcsv($FH, [
                    'Serial No',
                    'Name',
                    'Email',
                    'Phone',
                    'Visit Count',
                    'Cart Adds',
                    'Purchases Count'
                ]);
                foreach ($data as $info) { 
                    fputcsv($FH, [
                        $info['serial_number'] ?? 'N/A',
                        $info['name'] ?? 'N/A',
                        $info['email'] ?? '',
                        $info['phone'] ?? 'N/A',
                        $info['visited'] ?? 'N/A',
                        $info['added_to_cart'] ?? 'N/A',
                        $info['purchased'] ?? 'N/A',
                    ]);
                }
                fclose($FH);
            }; 
            return response()->stream($callback, 200, $headers);
        }
        return back()->with('error', 'No Data Found To Export');
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
                'usersAlsoLiked' => false,
                'crowdFavorites' => false,
                'pickUpWhereYouLeftOff' => false,
                'featuredCollection' => false
            ]);
        }
        return view('product_racks', ['productRackInfo' => $productRackInfo]);
    }

    //This is to render contact capture settings page
    public function showNotificationSettings(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $notifSettings = $shop->notificationSettings;
        $notificationStats = $shop->getContactCaptureStats();
        if($notifSettings == null || $notifSettings->count() < 1) {
            $notifSettings = $shop->notificationSettings()->create([
                'status' => false,
                'title' => null,
                'description' => null,
                'discount_value' => 10,
                'sale_status' => false,
                'sale_discount_value' => 10,
                'discount_expiry' => 24,
            ]);
        }
        return view('notifications', ['notifSettings' => $notifSettings, 'stats' => $notificationStats]);
    }

    public function mapCartContents(Request $request) {
        if($request->has('shop')) {
            $shop = Shop::where('shop_url', $request->shop)->first();

            if($shop !== null && $shop->count() > 0) {
                $updateArr = [
                    'shop_id' => $shop->id,
                    'shopify_cart_token' => $request->cartId
                ];
        
                $createArr = array_merge($updateArr, [
                    'session_id' => $request->session_id,
                    'alme_token' => $request->almeToken
                ]);
        
                AlmeShopifyOrders::updateOrCreate($updateArr, $createArr);
                return response()->json(['status' => true, 'message' => 'Recorded!']);
            }
        }
        return response()->json(['status' => false, 'message' => 'Invalid request']);
    }

    public function checkAlmeAPIs(Request $request) {
        try {
            $shop = Shop::where('shop_url', $request->shop ?? 'almestore1.myshopify.com')->first();
            if($shop !== null) {
                return response()->json(['status' => true, 'data' => $this->getAlmeAnalytics($shop->shop_url)]);
            }
            return response()->json(['status' => false, 'message' => 'Null shop']);
        } catch (Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    public function showDashboard(Request $request) {
        try{
            $request = $request->only('shop');
            $shop = Auth::user()->shopifyStore->shop_url;
            $baseShop = Auth::user()->shopifyStore;
            $shopDetails = $baseShop !== null ? ShopDetail::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first() : null;
            $checkScriptRunning = $this->checkAlmeScriptRunningOrNot($baseShop);
            // $liveTheme = $this->getLiveThemeForShop($baseShop);
            // $appEmbedURL = 'https://admin.shopify.com/store/'.str_replace('.myshopify.com', '', $shop).'/themes/'.$liveTheme['id'].'/editor?context=apps';
            $almeResponses = $this->getAlmeAnalytics($shop);
            return view('new_dashboard', compact('baseShop', 'shopDetails', 'almeResponses', 'checkScriptRunning'));
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        } catch(Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function showIdentifiedUsers(Request $request){
        /*
            $user = Auth::user();
            $shopifyStore = $user->shopifyStore;
            $response = $this->callAlmeAppIdentifiedUsers($shopifyStore);
        */
        return view('identified_users');
    }

    public function listIdentifiedUsers(Request $request) {
        try {
            if($request->ajax()) {
                $request = $request->all();
                $shop = Auth::user()->shopifyStore; //Take the auth user's shopify shop
                
                $almeData = $this->callAlmeAppIdentifiedUsers($shop, $request);
                $data = [];
        
                if($almeData['statusCode'] == 200 && is_array($almeData['body']) && count($almeData['body']) > 0) {
                    foreach($almeData['body'] as $info) {
                        $data[] = [
                            'shop_id' => $shop->id,
                            'regd_user_id' => isset($info['regd_user_id']) && $info['regd_user_id'] > 0 ? $info['regd_user_id'] : 0,
                            'name' => $info['name'] ?? 'N/A',
                            'last_visited' => date('Y-m-d h:i:s', strtotime($info['last_visited'])) ?? 'N/A',
                            'email' => $info['email'] ?? 'N/A',
                            'serial_number' => $info['serial_number'] ?? 'N/A',
                            'phone' => $info['phone'] ?? 'N/A',
                            'visited' => $info['visited'] ?? 'N/A',
                            'added_to_cart' => $info['added_to_cart'] ?? 'N/A',
                            'purchased' => $info['purchased'] ?? 'N/A'
                        ];
                    }
                }

                $count = count($data);
                
                /*
                $customers = $customers->select(['first_name', 'last_name', 'email', 'phone', 'created_at']); //Select columns
                if(isset($request['search']) && isset($request['search']['value'])) 
                    $customers = $this->filterCustomers($customers, $request); //Filter customers based on the search term
                
                $builder = $shop->getIdentifiedUsers(); //Load the relationship (Query builder)
                $limit = $request['length'];
                $offset = $request['start'];
                
                if(isset($request['start_date']) && isset($request['end_date'])) {
                    if(strlen($request['start_date']) && strlen($request['end_date'])) {
                        $builder = $builder->where('last_visited', '>', date('Y-m-d 00:00:00', $request['start_date']))
                                ->where('last_visited', '<', date('Y-m-d 23:59:59', $request['end_date']));
                    }
                }
                
                if(isset($request['order']) && isset($request['order'][0]))
                    $builder = $this->orderIdentifiedUsers($builder, $request); //Order customers based on the column
                
                $count = $builder->count(); //Take the total count returned so far
                $builder = $builder->offset($offset)->limit($limit); //LIMIT and OFFSET logic for MySQL
                
                $data = [];
                $query = $builder->toSql(); //For debugging the SQL query generated so far
                $rows = $builder->get(); //Fetch from DB by using get() function
                if($rows !== null){
                    foreach ($rows as $item) {
                        $item['last_visited'] = date('M d, Y', strtotime($item['last_visited']));
                        $data[] = $item->toArray();
                    }
                }
                   
                //$data[] = $item->toArray();
                */
                return response()->json([
                    "draw" => intval(request()->query('draw')),
                    "recordsTotal"    => $count,
                    "recordsFiltered" => $count,
                    "data" => $data,
                    "debug" => [
                        "request" => $request,
                        "sqlQuery" => ''
                    ]
                ], 200);
            }
            
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()], 500);
        }
    }

    public function getListOfStores() {
        $shops = Shop::select(['id', 'shop_url'])->get()->toArray();
        return response()->json(['status' => true, 'data' => $shops]);
    }

    public function orderIdentifiedUsers($builder, $request) {
        $column = $request['order'][0]['column'];
        $dir = $request['order'][0]['dir'];
        $db_column = null;
        switch($column) {
            case 0: $db_column = 'id'; break;
            case 1: $db_column = 'name'; break;
            case 2: $db_column = 'email'; break;
            case 3: $db_column = 'last_visited'; break;
            case 4: $db_column = 'phone'; break; 
            case 5: $db_column = 'visited'; break;
            case 6: $db_column = 'added_to_cart'; break;
            case 7: $db_column = 'purchased'; break;
            default: $db_column = 'id';
        }
        //Log::info('Order by '.$db_column.' '.$dir);
        return $builder->orderBy($db_column, $dir);   
    }

    public function getDiscountCodeForStore(Request $request) {
        try{
            if($request->has('shop') && $request->filled('shop')) {
                $shop = Shop::with(['getLatestDiscountCode'])->where('shop_url', $request->shop)->first();
                if(isset($shop->getLatestDiscountCode) && $shop->getLatestDiscountCode !== null) {
                    $code = $shop !== null ? $shop->getLatestDiscountCode->code : null;
                    return ['status' => true, 'code' => $code]; 
                }
                return response()->json(['status' => false, 'message' => 'No Data found']);
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
            $shop = Shop::with(['getLatestPriceRule', 'notificationSettings', 'notificationAsset'])->where('shop_url', $request->shop)->first();
            $code = null;
            $notificationSettings = $shop->notificationSettings;
            $contactStatus = isset($notificationSettings) && $notificationSettings !== null && isset($notificationSettings->status) && ($notificationSettings->status === true || $notificationSettings->status === 1);
            $html = null;
            if($contactStatus) {
                $asset = $shop->notificationAsset;
                if(isset($asset) && $asset != null && filled($asset->contact_capture_html) && strlen($asset->contact_capture_html)) {
                    $html = $asset->contact_capture_html;
                    $arrayValidate = [
                        '{{TITLE}}' => $notificationSettings->title,
                        '{{DESCRIPTION}}' => $notificationSettings->description
                    ];

                    try {
                        if(isset($notificationSettings->cdn_logo) && strlen($notificationSettings->cdn_logo) > 0) {
                            $arrayValidate = array_merge($arrayValidate, [
                                asset('images/TextALME.png') => $notificationSettings->cdn_logo
                            ]);
                        }
                    } catch (Exception $th) {
                        Log::info($th->getMessage().' '.$th->getLine());
                    }
                    
                    foreach($arrayValidate as $strToLookFor => $value) {
                        $checkIfHasStr = str_contains($html, $strToLookFor);
                        if($checkIfHasStr) {
                            $html = str_replace($strToLookFor, $value, $html);
                        }
                    }
                } else {
                    $html = view('contact_capture_popup', ['settings' => $notificationSettings])->render();
                }
            }

            try {
                if($html != null && strlen($html) > 0) {
                    $stats = $shop->getContactCaptureStats();
                    if($stats !== null && array_key_exists('total_views', $stats)) {
                        $stats['total_views'] += 1;
                    } else {
                        $stats = [
                            'total_views' => 0,
                            'submissions' => 0,
                            'percentage' => 0
                        ];
                    }
                    
                    $shop->setContactCaptureStats($stats);
                    
                    try {
                        if($request->filled('almeToken')) {
                            $arr = [
                                'shop_id' => $shop->id,
                                'alme_token' => $request->almeToken,
                                'session_id' => $request->session_id
                            ];
    
                            AlmeAnalytics::updateOrCreate($arr, $arr);
                            AlmeClickAnalytics::updateOrCreate($arr, $arr);
                            
                            AlmeAnalytics::where($arr)->increment('contact_impressions');
                            AlmeClickAnalytics::where([
                                'shop_id' => $shop->id,
                                'alme_token' => $request->almeToken])->orderBy('id', 'desc')->first()->update(['contact_notif_impression' => time()]);
                        }
                    } catch (Throwable $th) {
                        Log::info('Line 903 '.$th->getMessage().' '.$th->getLine());
                    }
                }    
            } catch (Exception $th) {
                Log::info($th->getMessage().' '.$th->getLine());
            }
            
            return response()->json(['code' => $code, 'status' => true, 'html' => $html]);    
        }
        return response()->json(['code' => null, 'status' => true, 'html' => null]);
    }

    public function saleNotificationPopup(Request $request) {
        try {
            if ($request->has('shop')) {
                $shop = Shop::with(['getLatestPriceRule', 'getLatestDiscountCode', 'notificationSettings', 'notificationAsset'])
                            ->where('shop_url', $request->shop)
                            ->first();
                $code = null;
                $notificationSettings = $shop->notificationSettings;
                $saleStatus = isset($notificationSettings) && $notificationSettings !== null && 
                              isset($notificationSettings->sale_status) && 
                              ($notificationSettings->sale_status === true || $notificationSettings->sale_status === 1);
                
                $discountValue = $notificationSettings->sale_discount_value ?? 'N/A';
                $discountExpiry = $notificationSettings->discount_expiry ?? 'N/A';
                            
                $html = null;
                if ($saleStatus) {
                    $code = $shop !== null && $shop->getLatestDiscountCode !== null ? $shop->getLatestDiscountCode->code : null;

                    //Early return
                    if($code == null || strlen($code) < 1) 
                        return response()->json(['code' => null, 'status' => true, 'html' => null]);
                    
                    $discountValue = $notificationSettings->sale_discount_value ?? 'N/A';
                    $discountExpiry = $notificationSettings->discount_expiry ?? 'N/A';

                    $notificationAsset = $shop->notificationAsset;
                    if(isset($notificationAsset) && $notificationAsset != null && filled($notificationAsset) && $notificationAsset->sale_notif_html != null && strlen($notificationAsset->sale_notif_html) > 0) {  
                        $html = $notificationAsset->sale_notif_html;
                        $arrayValidate = [
                            '{{DISCOUNT_CODE}}' => $code,
                            '{{DISCOUNT_VALUE}}' => $discountValue,
                            '{{DISCOUNT_EXPIRY}}' => $discountExpiry,
                            '{{MIN_VALUE_COUPON_STYLE}}' => 'style="display:none"',
                            '{{MIN_VALUE_COUPON_VALUE}}' => null
                        ];

                        if($notificationSettings != null && $notificationSettings->min_value_coupon != null && $notificationSettings->min_value_coupon > 0) {
                            $arrayValidate['{{MIN_VALUE_COUPON_STYLE}}'] = null;
                            $arrayValidate['{{MIN_VALUE_COUPON_VALUE}}'] = $notificationSettings->min_value_coupon;
                        }

                        if(strlen($html) > 0) {
                            foreach($arrayValidate as $strToLookFor => $replacementValue) {
                                $hasReplacement = str_contains($html, $strToLookFor);
                                if($hasReplacement) {
                                    $html = str_replace($strToLookFor, $replacementValue, $html);
                                }
                            }
                        } else {
                            $html = null;
                        }
                    } else {
                        $html = view('sale_notification_popup', [
                            'discountCode' => $code, 
                            'discountValue' => $discountValue, 
                            'discountExpiry' => $discountExpiry,
                            'minValueCoupon' => $notificationSettings->min_value_coupon
                        ])->render();
                    }
                } else {  
                    $html = null;
                }

                //Just before sending the notification popup HTML, increment the counter for stats
                try {
                    if($html !== null && strlen($html) > 0) {
                        $stats = $shop->getNotificationStats();
                        if($stats !== null && array_key_exists('total_views', $stats)) {
                            $stats['total_views'] += 1;
                        } else {
                            $stats = [
                                'total_views' => 0,
                                'submissions' => 0,
                                'percentage' => 0
                            ];
                        }
                        
                        $shop->setNotifStats($stats);
                    }
                } catch (Exception $th) {
                    Log::info('Error during incrementing notif stats '.$shop->shop_url.' '.$th->getMessage().' '.$th->getLine());
                }
                
                return response()->json(['code' => $code, 'status' => true, 'html' => $html]);
            }
            return response()->json(['code' => null, 'status' => true, 'html' => null]);
        } catch (Exception $th) {
            return response()->json(['code' => null, 'status' => false, 'debug' => $th->getMessage().' '.$th->getLine(), 'html' => null]);
        }
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
                    //$installResponse = $this->addScriptTagToStore($shop);
                    $installResponse = null;
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
                $field = $request->field;

                //$check = $this->checkIfThemeHasAppBlocksAdded($field, $shop);
                if(true) {
                    //$response = $this->manageBlocksForThemeEditor($field, $value, $shop);
                    $shop->productRackInfo()->update([$field => $value]);
                    return response()->json(['status' => true, 'message' => 'Updated!']);
                } else {
                    $liveTheme = $this->getLiveThemeForShop($shop);
                    $themeURL = 'https://admin.shopify.com/store/'.str_replace('.myshopify.com', '', $shop->shop_url).'/themes/'.$liveTheme['id'].'/editor';
                    $htmlContent = '<a href="'.$themeURL.'" class="btn btn-link" target="_blank">Please add theme app blocks to your store. Click here to add it.</a>';
                    return response()->json(['status' => false, 'message' => 'Your store needs changes', 'themeURL' => $themeURL, 'htmlContent' => $htmlContent]);
                }
            }
            return response()->json(['status' => false, 'message' => 'Invalid Request']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function logMeOut(Request $request) {
        if(Auth::check()) Auth::logout();
        return redirect()->to('login');
    }

    public function sendEventToAlme(Request $request) {
        if($request->has('shop') && $request->filled('shop')) {
            try {
                $shop = Shop::where('shop_url', $request->shop)->first();
                if($shop) {
                    $endpoint = getAlmeAppURLForStore('events/');
                    $headers = getAlmeHeaders();
                    $payload = [
                        "events" => $request->events,
                        "session_id" => $request->session_id,
                        "alme_user_token" => $request->alme_user_token,
                        "lastEventTimestamp" => $request->lastEventTimestamp
                    ];

                    //Push this into the database so we don't have to process it immediately
                    ConfigureMissingProducts::dispatch($shop, $request->events)->onConnection('database');
                    $response = $this->makeAnAlmeAPICall('POST', $endpoint, $headers, $payload);
                    return response()->json($response['body']);
                }
                return response()->json(['status' => 'ok']);
            } catch (Throwable $th) {
                return response()->json(['status' => false, 'message' => $th->getMessage().' '.$th->getLine()]);
            }
        }
    }

    public function createNotificationAsset(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        CreateNotificationAsset::dispatch($user, $shop)->onConnection('sync');
        dd('Done'); 
    }

    public function mapCustomer(Request $request) {
        if($request->filled('shop_name') && $request->filled('customer_id')) {
            $shop = Shop::where('shop_url', $request->shop_name)->first();
            if($shop) {
                $endpoint = getShopifyAPIURLForStore('customers/'.$request->customer_id.'.json', $shop);
                $headers = getShopifyAPIHeadersForStore($shop);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
                return response()->json(['status' => true, 'response' => $response['body']]);
            }
            return response()->json(['status' => false, 'message' => 'Shop not found!']);
        }
        return response()->json(['status' => false, 'message' => 'Please pass shop_name and customer_id in the params.']);
    }
}
