<?php 

namespace App\Traits;

use App\Models\Shop;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

trait FunctionTrait {
    public function getStoreByDomain($shop) {
        return Shop::where('shop_url', $shop)->first();
    }

    public function isShopifyStoreVersionNew($shop_url, $access_token = null) {
        if($access_token == null || strlen($access_token) < 1) {
            $shopData = $this->getStoreByDomain($shop_url);
            $access_token = $shopData['access_token'];
        }

        $storeObj = ['myshopify_domain' => $shop_url];
    
        
        $endpoint = getShopifyAPIURLForStore('themes.json', $storeObj);
        $headers = getShopifyAPIHeadersForStore(['accessToken' => $access_token]);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        $body = $response['statusCode'] && $response['statusCode'] == 200 ? $response['body'] : null;
        
        $themeId = "";
        foreach($body['themes'] as $val) {
            if($val['role'] == "main"){
                $themeId = $val['id'];
                break;
            }
        }
    
        $endpoint = getShopifyAPIURLForStore("themes/$themeId/assets.json", $storeObj);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        $body = $response['statusCode'] && $response['statusCode'] == 200 ? $response['body'] : null;
        
        $count = 0;
        foreach($body['assets'] as $val) {
            if($val['key'] == "templates/cart.json") {
                $count++;
            }
            if($val['key'] == "templates/collection.json") {
                $count++;
            }
            if($val['key'] == "templates/product.json") {
                $count++;
            }
        }
        return $count > 1;
    }

    public function validateRequestFromShopify($request) {
        try {
            $arr = [];
            if(array_key_exists('hmac', $request)) {
                $hmac = $request['hmac'];
                unset($request['hmac']);
                foreach($request as $key => $value){
                    $key=str_replace("%","%25",$key);
                    $key=str_replace("&","%26",$key);
                    $key=str_replace("=","%3D",$key);
                    $value=str_replace("%","%25",$value);
                    $value=str_replace("&","%26",$value);
                    $arr[] = $key."=".$value;
                }
                $str = implode('&', $arr);
                $ver_hmac =  hash_hmac('sha256', $str, config('shopify.SECRET_KEY'), false);
                dd($ver_hmac.' '.$hmac);
                return $ver_hmac === $hmac;
            }
            return false;
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
            return false;
        }    
    }

    public function verifyInstallation($shopDetails = null) {
        if($shopDetails == null) return false;

        $endpoint = getShopifyAPIURLForStore('shop.json', $shopDetails);
        $headers = getShopifyAPIHeadersForStore($shopDetails);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        return array_key_exists('statusCode', $response) && $response['statusCode'] == 200; 
    }

}