<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopDetail;
use App\Traits\RequestTrait;
use App\Traits\FunctionTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstallationController extends Controller
{
    use RequestTrait, FunctionTrait;

    public $apiKey, $apiSecret, $scopes, $appURL;
    public function __construct() {
        $this->apiKey = config('shopify.API_KEY');
        $this->apiSecret = config('shopify.SECRET_KEY');
        $this->scopes = config('shopify.APP_SCOPES');
        $this->appURL = config('app.url');
    }

    /**
     * This method gets invoked when someone wants to install, re-install and open the app.
    */
    public function startInstallation(Request $request) {
        $shop = $request->shop ?? null;
        if(!$shop || $shop == null || strlen($shop) < 1) {
            return response()->json(['status' => false, 'message' => 'Shop param not present in the request!']);
        }

        $validateRequest = $this->validateRequestFromShopify($request->all());
        if(!$validateRequest){
            return response()->json(['status' => false, 'message' => 'Request not from Shopify!']);
        }

        $shopDetails = Shop::where('shop_url', $shop)->orderBy('id', 'desc')->first();

        if($shopDetails != null && $shopDetails->count() > 0 && $this->verifyInstallation($shopDetails)) {
            $baseShop = Shop::where('shop_url', $shop)->first();
            $shopDetails = ShopDetail::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first();
            return view('dashboard', compact('baseShop', 'shopDetails'));
        } else {
            $redirect_url = urlencode(route('shopify.auth.redirect'));
            $install_url = "https://$shop/admin/oauth/authorize?client_id=$this->apiKey&scope=$this->scopes&redirect_uri=$redirect_url";
            return redirect()->to($install_url);
        }
    }

    public function handleRedirect(Request $request) {
        $hmac = $request->has('hmac') ? $request->hmac : null;
        $shop = $request->shop;

        if(!$hmac || strlen($hmac) < 1) {
            return response()->json(['status' => false, 'message' => 'Invalid or No HMAC header present.']);
        }

        if(!$shop || strlen($shop) < 1) {
            return response()->json(['status' => false, 'message' => 'Invalid or No Shop header present.']);
        }

        try{
            $validateRequest = $this->validateRequestFromShopify($request->all());
            if($validateRequest) {
                $payload = [
                    "client_id" => $this->apiKey, // Your API key
                    "client_secret" => $this->apiSecret, // Your app credentials (secret key)
                    "code" => $request->code // Grab the access key from the URL
                ];
    
                $storeObj = ['myshopify_domain' => $shop];
            
                $endpoint = "https://$shop/admin/oauth/access_token";
                $headers = [ 'Content-Type' => 'application/json' ];
                $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);
                Log::info('Response for access token');
                Log::info($payload);
                Log::info($response);
                if($response['statusCode'] && $response['statusCode'] == 200) {
                    $accessToken = $response['body']['access_token'];
                    $updateArr = ['shop_url' => $shop];
                    $createArr = array_merge($updateArr, [
                        'access_token' => $accessToken,
                        'hmac' => $hmac,
                        'install_date' => date('Y-m-d h:i:s')
                    ]);
                    Shop::updateOrCreate($updateArr, $createArr);
    
                    //if(!$this->isShopifyStoreVersionNew($shop, $accessToken)) {
                        $payload = [
                            'script_tag' => [
                                'event' => 'onload',
                                'src' => asset('js/custom_script.js?v='.date('c')),
                                'display_scope' => 'online_store'
                            ]
                        ];
                        $endpoint = getShopifyAPIURLForStore('script_tags.json', $storeObj);
                        $headers = getShopifyAPIHeadersForStore(['accessToken' => $accessToken]);
                        $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);
                        Log::info('Response for script tags');
                        Log::info($payload);
                        Log::info($response);
                    //}
                }
                return redirect()->to("https://$shop/admin/apps/".$this->apiKey);
            }
            return response()->json(['status' => false, 'message' => 'Request not from Shopify!']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }
}
