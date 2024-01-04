<?php

namespace App\Http\Controllers;

use App\Models\AlmeShopifyOrders;
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

        /**
         * // Use `client.get` to request a list of themes on the shop
        const {body:{themes}} = await client.get({
        path: 'themes',
        });
        // Find the published theme
        const publishedTheme = themes.find((theme) => theme.role === 'main');
        // Retrieve a list of assets in the published theme
        const {body:{assets}} = await client.get({
        path: `themes/${publishedTheme.id}/assets`
        });
        // Check if JSON template files exist for the template specified in APP_BLOCK_TEMPLATES
        const templateJSONFiles = assets.filter((file) => {
        return APP_BLOCK_TEMPLATES.some(template => file.key === `templates/${template}.json`);
        })
        if (templateJSONFiles.length > 0 && (templateJSONFiles.length === APP_BLOCK_TEMPLATES.length)) {
            console.log('All desired templates support sections everywhere!')
        } else if (templateJSONFiles.length) {
            console.log('Only some of the desired templates support sections everywhere.')
        }
        // Retrieve the body of JSON templates and find what section is set as `main`
        const templateMainSections = (await Promise.all(templateJSONFiles.map(async (file, index) => {
        const {body:{asset}} = await client.get({
            path: `themes/${publishedTheme.id}/assets`,
            query: { "asset[key]": file.key }
        })

        const json = JSON.parse(asset.value)
        const main = Object.entries(json.sections).find(([id, section]) => id === 'main' || section.type.startsWith("main-"))
        if (main) {
            return assets.find((file) => file.key === `sections/${main[1].type}.liquid`);
        }
        }))).filter((value) => value)

        // Request the content of each section and check if it has a schema that contains a
        // block of type '@app'
        const sectionsWithAppBlock = (await Promise.all(templateMainSections.map(async (file, index) => {
        let acceptsAppBlock = false;
        const {body:{asset}} = await client.get({
            path: `themes/${publishedTheme.id}/assets`,
            query: { "asset[key]": file.key }
        })

        const match = asset.value.match(/\{\%\s+schema\s+\%\}([\s\S]*?)\{\%\s+endschema\s+\%\}/m)
        const schema = JSON.parse(match[1]);

        if (schema && schema.blocks) {
            acceptsAppBlock = schema.blocks.some((b => b.type === '@app'));
        }

        return acceptsAppBlock ? file : null
        }))).filter((value) => value)
        if (templateJSONFiles.length > 0 && (templateJSONFiles.length === sectionsWithAppBlock.length)) {
            console.log('All desired templates have main sections that support app blocks!');
        } else if (sectionsWithAppBlock.length) {
            console.log('Only some of the desired templates support app blocks.');
        } else {
            console.log("None of the desired templates support app blocks");
        }
         */
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

    public function downloadIdentifiedUsersAsExcel() {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $data = $this->callAlmeAppIdentifiedUsers($shop);
        if($data['statusCode'] == 200 && is_array($data['body']) && count($data['body']) > 0) {
            $headers = [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=IdentifiedUsers-'.time().'.csv',
                'Expires' => '0',
                'Pragma' => 'public'
            ];
        
            $list = $data['body'];
        
            # add headers for each column in the CSV download
            //array_unshift($list, array_keys($list[0]));
        
            $callback = function() use ($list) 
            {
                $FH = fopen('php://output', 'w');
                fputcsv($FH, [
                    'Serial No',
                    'Name',
                    'Phone',
                    'Visit Count',
                    'Cart Adds',
                    'Purchases Count'
                ]);
                foreach ($list as $info) { 
                    fputcsv($FH, [
                        $info['serial_number'] ?? 'N/A',
                        $info['name'] ?? 'N/A',
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
        // Log::info('Cart contents request received');
        // Log::info(json_encode($request->all()));
        $shop = Shop::where('shop_url', $request->shop)->first();

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

    public function showDashboard(Request $request) {
        try{
            $request = $request->only('shop');
            $shop = $request['shop'] ?? Auth::user()->shopifyStore->shop_url;
            $baseShop = Shop::where('shop_url', $shop)->first();
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
        $user = Auth::user();
        $shopifyStore = $user->shopifyStore;
        $response = $this->callAlmeAppIdentifiedUsers($shopifyStore);
        return view('identified_users', ['data' => $response]);
    }

    public function callAlmeAppIdentifiedUsers($shop) {
        $endpoint = getAlmeAppURLForStore('analytics/identified_user_activity?app_name='.$shop->shop_url);
        $headers = getAlmeHeaders();
        return $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
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
            $contactStatus = isset($notificationSettings) && $notificationSettings !== null && isset($notificationSettings->status) && ($notificationSettings->status === true || $notificationSettings->status === 1);
            $html = $contactStatus ? view('contact_capture_popup')->render() : null;
            return response()->json(['code' => $code, 'status' => true, 'html' => $html]);
        }
        return response()->json(['code' => null, 'status' => true, 'html' => null]);
    }

    public function saleNotificationPopup(Request $request) {
        try {
            if ($request->has('shop')) {
                $shop = Shop::with(['getLatestPriceRule', 'getLatestDiscountCode', 'notificationSettings'])
                            ->where('shop_url', $request->shop)
                            ->first();
        
                $code = $shop !== null && $shop->getLatestDiscountCode !== null ? $shop->getLatestDiscountCode->code : null;
                $notificationSettings = $shop->notificationSettings;
                $saleStatus = isset($notificationSettings) && $notificationSettings !== null && 
                              isset($notificationSettings->sale_status) && 
                              ($notificationSettings->sale_status === true || $notificationSettings->sale_status === 1);
                
                $discountValue = $notificationSettings->sale_discount_value ?? 'N/A';
                $discountExpiry = $notificationSettings->discount_expiry ?? 'N/A';
    
                $html=null;
                if ($saleStatus) {
                    $discountValue = $notificationSettings->sale_discount_value ?? 'N/A';
                    $discountExpiry = $notificationSettings->discount_expiry ?? 'N/A';
                    $html = view('sale_notification_popup', [
                        'discountCode' => $code, 
                        'discountValue' => $discountValue, 
                        'discountExpiry' => $discountExpiry
                    ])->render();
                } else {
                    $html = null;
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

  
}
