<?php

namespace App\Http\Controllers;

use App\Jobs\FinishInstallation;
use App\Jobs\SyncShopifyProducts;
use App\Models\Shop;
use App\Models\ShopDetail;
use App\Models\User;
use App\Models\UserShops;
use App\Models\UserStores;
use App\Traits\RequestTrait;
use App\Traits\FunctionTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class InstallationController extends Controller {
    
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
            $userShop = UserShops::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first();
            $user = User::where('id', $userShop->user_id)->first();
            $shopDetails = ShopDetail::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first();
            Auth::loginUsingId($user->id);
            UserShops::where('user_id', $user->id)->update(['active' => 0]);
            UserShops::where('user_id', $user->id)->where('shop_id', $baseShop->id)->update(['active' => 1]);
            return redirect()->route('dashboard');
        } else {
            $redirectUrl = urlencode(route('shopify.auth.redirect'));
            $installUrl = "https://$shop/admin/oauth/authorize?client_id=$this->apiKey&scope=$this->scopes&redirect_uri=$redirectUrl";
            return redirect()->to($installUrl);
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
                
                if($response['statusCode'] && $response['statusCode'] == 200) {
                    $accessToken = $response['body']['access_token'];
                    $storeObj = array_merge($storeObj, ['accessToken' => $accessToken]);

                    $shopifyShopData = $this->getShopifyStoreData($storeObj);

                    $updateArr = ['shop_url' => $shop];
                    $createArr = array_merge($updateArr, [
                        'access_token' => $accessToken,
                        'hmac' => $hmac,
                        'install_date' => date('Y-m-d h:i:s')
                    ]);
                    
                    $dbShop = Shop::updateOrCreate($updateArr, $createArr);
                    
                    $updateArr = [
                        'email' => $shopifyShopData['email']
                    ];
                    $createArr = array_merge($updateArr, [
                        'password' => Hash::make('Alme@2024'),
                        'name' => $shopifyShopData['name']
                    ]);
                    $user = User::updateOrCreate($updateArr, $createArr);
                    $user->markEmailAsVerified();
                    UserShops::where('user_id', $user->id)->update(['active' => 0]);
                    UserShops::updateOrCreate([
                        'user_id' => $user->id,
                        'shop_id' => $dbShop->id
                    ],[
                        'user_id' => $user->id,
                        'shop_id' => $dbShop->id,
                        'active' => true
                    ]);
                    FinishInstallation::dispatch($dbShop, $user)->onConnection('database');
                }
                return redirect()->to("https://$shop/admin/apps/".$this->apiKey);
            }
            return response()->json(['status' => false, 'message' => 'Request not from Shopify!']);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    private function getShopifyStoreData($storeObj) {
        $endpoint = getShopifyAPIURLForStore('shop.json', $storeObj);
        $headers = getShopifyAPIHeadersForStore($storeObj);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        return $response['body']['shop'];
    }
}
