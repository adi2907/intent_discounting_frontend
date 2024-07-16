<?php 

namespace App\Traits;

use App\Models\AlmeClickAnalytics;
use App\Models\DiscountCode;
use App\Models\RetryPurchaseEvent;
use App\Models\Shop;
use App\Models\ShopifyOrder;
use App\Models\ShopifyProducts;
use Exception;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    public function processRetryResponse($order, $payload, $response) {
        try {
            if(isset($response['statusCode']) && isset($response['body']['success'])) {
                if($response['body']['success'] == false) {
                    //Now we need to save this info in the database
                    RetryPurchaseEvent::insert([
                        'order_id' => $order->table_id,
                        'payload' => json_encode($payload),
                        'api_response' => json_encode($response)
                    ]);
                    $currentVal = $order->retry_count == null ? 0 : $order->retry_count;
                    $order->update(['retry_count' => $currentVal + 1]);
                }
            }
        } catch (Throwable $th) {
            Log::info("error in processRetryResponse ".$th->getMessage().' '.$th->getLine());
        } catch (Exception $th) {
            Log::info("error in processRetryResponse ".$th->getMessage().' '.$th->getLine());
        }
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
        try {
            $endpoint = getShopifyAPIURLForStore('shop.json', $shopDetails);
            $headers = getShopifyAPIHeadersForStore($shopDetails);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
            return array_key_exists('statusCode', $response) && $response['statusCode'] == 200; 
        } catch(Exception $e) {
            return false;
        }
    }

    public function getPriceRuleResponse($priceRule, $shop) {
        $endpoint = getShopifyAPIURLForStore('price_rules/'.$priceRule->price_id.'.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        return $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
    }

    public function isPriceRuleValid($priceRule, $shop) {
        $endpoint = getShopifyAPIURLForStore('price_rules/'.$priceRule->price_id.'.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);

        try {
            $now = date('c');
            $body = $response['body']['price_rule'];
            
            $strtotimeNow = strtotime($now);
            $strtotimeExpiry = strtotime($body['ends_at']);

            $log_message = 'Price Rule ID '.$priceRule->price_id.' Now '.$strtotimeNow.' Expiry '.$strtotimeExpiry.' Result '.($strtotimeExpiry >= $strtotimeNow ? 'True' : 'False');
            Log::info('Expiry for shop '.$shop->shop_url);
            Log::info($log_message);

            return $strtotimeExpiry >= $strtotimeNow;
        } catch (Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
        }        

        return array_key_exists('statusCode', $response) && $response['statusCode'] == 200;
    }

    public function createDiscountCode($priceRule, $shop) {
        $code = strtoupper($shop['prefix'].Str::random(5));
        $endpoint = getShopifyAPIURLForStore('price_rules/'.$priceRule->price_id.'/discount_codes.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        $payload = [
            "discount_code" => [
                "code" => $code
            ]
        ];
        $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);
        if(array_key_exists('statusCode', $response) && $response['statusCode'] == 201) {
            return $response['body']['discount_code'];
        }
        return $response;
    }

    public function addScriptTagToStore($store) {
        $version = strtotime(date('Y-m-d h:i:s'));
        $payload = [
            'script_tag' => [
                'event' => 'onload',
                'src' => secure_asset('js/custom_script.js?v='.$version),
                'display_scope' => 'online_store'
            ]
        ];
        $endpoint = getShopifyAPIURLForStore('script_tags.json', $store);
        $headers = getShopifyAPIHeadersForStore($store);
        $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);
        return $response;
    }

    public function updateOrCreateThisProductInDB($product, $shop) {
        try {
            $arr = [];
            $shopId = $shop->id;
            foreach($product['variants'] as $variant) {
                $arr[] = [
                    'updateArr' => [
                        'shop_id' => $shopId,
                        'product_id' => $product['id'],
                        'variant_id' => $variant['id']
                    ],
                    'createArr' => [
                        'shop_id' => $shopId,
                        'product_id' => $product['id'],
                        'variant_id' => $variant['id'],
                        'title' => $product['title'],
                        'handle' => $product['handle'],
                        'price' => $variant['price'],
                        'imageSrc' => $product['image']['src'] ?? null
                    ]
                ];
            }
    
            foreach($arr as $data) {
                ShopifyProducts::updateOrCreate($data['updateArr'], $data['createArr']);
            }
    
            return true;
        } catch(Exception $e) {
            Log::info('Error inserting products');
            Log::info($e->getMessage().' '.$e->getLine());
        } 
    }

    public function updateOrCreateThisCustomerInDB($customer, $shop) {
        try {
            $updateArr = [
                'regd_user_id' => $customer['id'],
                'shop_id' => $shop['id']
            ];

            $createArr = array_merge($updateArr, [
                'name' => $customer['first_name'].' '.$customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'] ?? 'N/A',
                'serial_number' => 1,
                'visited' => 0,
                'added_to_cart' => 0,
                'purchased' => 0
            ]);
    
            $shop->getIdentifiedUsers()->updateOrCreate($updateArr, $createArr);
    
            return true;
        } catch(Exception $e) {
            Log::info('Error inserting products');
            Log::info($e->getMessage().' '.$e->getLine());
        } 
    }

    public function checkAlmeScriptRunningOrNot($shop) {
        try {
            $liveTheme = $this->getLiveThemeForShop($shop);
            if($liveTheme !== null && array_key_exists('id', $liveTheme)) {
                $assetKey = 'asset[key]=config/settings_data.json';
                $asset = $this->getAssetsForTheme($shop, $liveTheme, $assetKey);
                $assetContents = json_decode($asset['value'], true);
                if(is_array($assetContents['current'])) {
                    if(array_key_exists('blocks', $assetContents['current']) && isset($assetContents['current']['blocks'])) {
                        foreach($assetContents['current']['blocks'] as $blockId => $data) {
                            $themeBlockId = config('shopify.APP_BLOCK_ID');
                            if(array_key_exists('type', $data) && $data['type'] == 'shopify://apps/alme/blocks/app-embed/'.$themeBlockId) {
                                return !$data['disabled'];
                            }
                        }
                    } else {
                        Log::info('Invalid response for '.$shop['shop_url']);
                        Log::info($assetContents);
                    }
                }
            }
        } catch(Exception $e) {
            Log::info('Shop '.$shop['shop_url'].' '.$e->getMessage().' '.$e->getLine());
        }
        return false;        
    }

    public function checkIfThemeHasAppBlocksAdded($field, $shop) {
        $liveTheme = $this->getLiveThemeForShop($shop);
        if($liveTheme !== null && array_key_exists('id', $liveTheme)) {
            $pageTarget = $this->checkWhetherItsProductOrMainPage($field);
            $mainPage = 'asset[key]=templates/index.json';
            $productPage = 'asset[key]=templates/product.json';
            $asset = $this->getAssetsForTheme($shop, $liveTheme, $pageTarget == 'main' ? $mainPage : $productPage);
            $assetContents = json_decode($asset['value'], true);
            try {
                foreach($assetContents['sections'] as $key => $obj) {
                    if($obj['type'] == 'apps') {
                        return true;
                    }
                }
            } catch(Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function getStoreBlockName($field) {
        switch($field) {
            case 'pickUpWhereYouLeftOff': return 'pickupWhereYouLeftOff';
            case 'crowdFavorites': return 'crowdFavorites';
            case 'usersAlsoLiked': return 'usersAlsoLiked';
            case 'featuredCollection': return 'featuredCollection'; 
        }
    }

    public function checkWhetherItsProductOrMainPage($field) {
        if($field == 'usersAlsoLiked' || $field == 'featuredCollection') return 'product';
        return 'main';
    }

    public function manageBlocksForThemeEditor($field, $value, $shop) {
        try {
            $liveTheme = $this->getLiveThemeForShop($shop);
            if($liveTheme !== null && array_key_exists('id', $liveTheme)) {
                $pageTarget = $this->checkWhetherItsProductOrMainPage($field);
                if($value) {
                    //Block has to be added
                    if($pageTarget == 'main') {
                        $blockExists = $this->checkIfBlockExistsOnStore($pageTarget, $field, $shop, $liveTheme);
                        if(!$blockExists) {
                            //Now we can add here
                            return $this->addBlockOnPage($pageTarget, $field, $shop, $liveTheme);
                        } 
                    } else {
                        $blockExists = $this->checkIfBlockExistsOnStore($pageTarget, $field, $shop, $liveTheme);
                        if(!$blockExists) {
                            //Now we can add here
                            return $this->addBlockOnPage($pageTarget, $field, $shop, $liveTheme);
                        } 
                    }
                } else {
                    //Block has to be deleted
                    if($pageTarget == 'main') {
                        $blockExists = $this->checkIfBlockExistsOnStore($pageTarget, $field, $shop, $liveTheme);
                        if($blockExists) {
                            //Now we can delete here
                            return $this->removeBlockOnPage($pageTarget, $field, $shop, $liveTheme);
                        } 
                    } else {
                        $blockExists = $this->checkIfBlockExistsOnStore($pageTarget, $field, $shop, $liveTheme);
                        if($blockExists) {
                            //Now we can delete here
                            return $this->removeBlockOnPage($pageTarget, $field, $shop, $liveTheme);
                        } 
                    }
                }
                return true;
            } 
            return 'Live theme not found';
        } catch (Throwable $th) {
            return $th->getMessage().' '.$th->getLine();
        }
    }

    public function removeBlockOnPage($pageTarget, $field, $shop, $liveTheme) {
        //TODO: Add the remove logic
    }

    public function addBlockOnPage($pageTarget, $field, $shop, $liveTheme) {
        try {
            $mainPage = 'asset[key]=templates/index.json';
            $productPage = 'asset[key]=templates/product.json';
            $blockId = config('shopify.APP_BLOCK_ID');
            $storeBlockName = $this->getStoreBlockName($field);
            $asset = $this->getAssetsForTheme($shop, $liveTheme, $pageTarget == 'main' ? $mainPage : $productPage);
            $assetContents = json_decode($asset['value'], true);
            $randomId = $this->getRandomIdForField($field);
            $copyVar = $assetContents; //I need to keep a copy of this
            foreach($assetContents['sections'] as $key => $obj) {
                if($obj['type'] == 'apps') {
                    
                    if(!array_key_exists('blocks', $obj)) {
                        $obj['blocks'] = [];
                    }

                    if(!array_key_exists('block_order', $obj)) {
                        $obj['block_order'] = [];
                    }

                    $newArr = array_merge($obj['blocks'], [
                        $randomId => [
                            'type'=> "shopify://apps/alme/blocks/$storeBlockName/$blockId",
                            'settings' => []
                        ]
                    ]);

                    $copyVar['sections'][$key]['blocks'] = $newArr;
                    $copyVar['sections'][$key]['block_order'][] = $randomId;
                }
            }
            
            $payload = [
                'asset' => [
                    'key' => $pageTarget == 'main' ? 'templates/index.json':'templates/product.json',
                    'value' => str_replace('"settings":[]', '"settings":{}', json_encode($copyVar)) 
                ]
            ];
            $endpoint = getShopifyAPIURLForStore('themes/'.$liveTheme['id'].'/assets.json', $shop, '2023-01');
            $headers = getShopifyAPIHeadersForStore($shop);
            $response = $this->makeAnAPICallToShopify('PUT', $endpoint, $headers, $payload);
            return $response;
        } catch (\Throwable $th) {
            dd($th->getMessage().' '.$th->getLine());
        }
    }

    public function getRandomIdForField($field) {
        switch($field) {
            case 'pickUpWhereYouLeftOff': return '68483392-ba47-4275-b382-d4590c5a7d98';
            case 'crowdFavorites': return 'fb943017-1c5c-4435-bec2-6d738d279810';
            case 'usersAlsoLiked': return 'b431eb3a-cf17-40aa-a3d9-e2a63417c086';
            case 'featuredCollection': return '5c90c8e7-81b3-4745-bf64-7e8d6757df13'; 
        }
    }

    public function checkIfBlockExistsOnStore($pageTarget, $field, $shop, $liveTheme) {
        $mainPage = 'asset[key]=templates/index.json';
        $productPage = 'asset[key]=templates/product.json';
        $blockId = config('shopify.APP_BLOCK_ID');
        $storeBlockName = $this->getStoreBlockName($field);
        $asset = $this->getAssetsForTheme($shop, $liveTheme, $pageTarget == 'main' ? $mainPage : $productPage);
        $assetContents = json_decode($asset['value'], true);
        try {
            foreach($assetContents['sections'] as $key => $obj) {
                if($obj['type'] == 'apps') {
                    foreach($obj['blocks'] as $randomId => $innerObj) {
                        $valueToCompare = "shopify://apps/alme/blocks/$storeBlockName/$blockId";
                        if($innerObj['type'] === $valueToCompare) {
                            return true;
                        } 
                    }
                }
            }
        } catch(Exception $e) {
            return false;
        }
        return false;
    }

    public function getAssetsForTheme($shop, $liveTheme, $type) {
        try{
            $endpoint = getShopifyAPIURLForStore('themes/'.$liveTheme['id'].'/assets.json?'.$type, $shop);
            $headers = getShopifyAPIHeadersForStore($shop);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
            return $response['body']['asset'];
        } catch(Exception $e) {
            return ['status' => false, 'response' => $e->getMessage().' '.$e->getLine()];
        }
    }

    public function getShopDetails($shop) {
        try{
            $endpoint = getShopifyAPIURLForStore('shop.json', $shop);
            $headers = getShopifyAPIHeadersForStore($shop);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
            return $response['body']['shop'];
        } catch(Exception $e) {
            return ['status' => false, 'response' => $e->getMessage().' '.$e->getLine()];
        }
    }

    public function getLiveThemeForShop($shop) {
        $endpoint = getShopifyAPIURLForStore('themes.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        if(array_key_exists('statusCode', $response) && $response['statusCode'] == 200) {
            foreach($response['body']['themes'] as $theme) {
                if(array_key_exists('role', $theme) && $theme['role'] == 'main') {
                    return $theme;
                }
            }
        }
        return null; 
    }

    private function deletePriceRule($priceRule, $shop) {
        if(isset($priceRule->price_id) && $priceRule->price_id !== null) {
            $endpoint = getShopifyAPIURLForStore('price_rules/'.$priceRule->price_id.'.json', $shop);
            $headers = getShopifyAPIHeadersForStore($shop);
            $this->makeAnAPICallToShopify('DELETE', $endpoint, $headers);
        }
    }

    /**
     * Function to create discount code on Shopify and create a
     * database record (validity added now)
     * 
     * @param - PriceRule object from price_rules table
     * @param - Shop object from shop table
     * @param - Frequency i.e the time interval (hours) the merchant set on the dashboard
     *          to generate discount codes
     * 
    */
    private function createAndSaveDiscountCode($priceRule, $shop, $frequency) {
        $data = $this->createDiscountCode($priceRule, $shop);
        if(array_key_exists('code', $data) && $data['code'] !== null && strlen($data['code']) > 0) {
            $shop->getDiscountCode()->create([
                'code' => $data['code'],
                'full_response' => json_encode($data),
                'validity' => time() + ($frequency * 60 * 60) //Add the frequency 
            ]);
            Log::info('Created and saved discount code for '.$shop->shop_url);
        } else {
            // Log::info('here Problem with validity for price rule '.$shop->id.' '.$shop->shop_url);
            // Log::info($data);
        }
    }

    private function createPriceRuleForShop($shop) {
        //Create the price rule and save it
        $endpoint = getShopifyAPIURLForStore('price_rules.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);

        $saleDiscountValue = 10;
        try {
            $saleDiscountValue = isset($shop->notificationSettings) && $shop->notificationSettings->count() > 0 ? $shop->notificationSettings->sale_discount_value : null;
            $minValueCoupon = isset($shop->notificationSettings) && $shop->notificationSettings->count() > 0 ? $shop->notificationSettings->min_value_coupon : null;
            $discountExpiry = isset($shop->notificationSettings) && $shop->notificationSettings->count() > 0 ? $shop->notificationSettings->discount_expiry : null;
        } catch(Exception $e) {
            $saleDiscountValue = 10;
            $minValueCoupon = 0;
            $discountExpiry = null;
        } 
        $saleDiscountValue = '-'.$saleDiscountValue;
        
        Log::info('Creating discount code at percentage off for shop '.$shop->shop_url.' '.$saleDiscountValue);

        if($minValueCoupon > 0) {
            $payload = [
                'price_rule' => [
                    "title" => "ALMEPRICERULE",
                    "target_type" => "line_item",
                    "target_selection" => "all",
                    "allocation_method" => "across",
                    "value_type" => "percentage",
                    "value" => $saleDiscountValue,
                    "customer_selection" => "all",
                    "prerequisite_subtotal_range" => ["greater_than_or_equal_to" => $minValueCoupon],
                    "starts_at" => date('c')
                ]
            ];
        } else {
            $payload = [
                'price_rule' => [
                    "title" => "ALMEPRICERULE",
                    "target_type" => "line_item",
                    "target_selection" => "all",
                    "allocation_method" => "across",
                    "value_type" => "percentage",
                    "value" => $saleDiscountValue,
                    "customer_selection" => "all",
                    "starts_at" => date('c')
                ]
            ];
        }
        
        if($discountExpiry !== null) {
            $discountExpiry = (int) $discountExpiry;
            # log all the attributes
            Log::info('Logging discount expiry timeline');
            Log::info('Discount expiry '.$discountExpiry);
            Log::info('Current time '.strtotime('now'));
            Log::info('Current time '.date('c'));
            Log::info('Shop '.$shop->shop_url);
            $strtotime = strtotime('+'.($discountExpiry * 2).' hours');
            $endsAt = date('c', $strtotime);
            $payload['price_rule'] = array_merge($payload['price_rule'], ['ends_at' => $endsAt]);
        }

        Log::info('Min value discount '.$minValueCoupon);
        Log::info('Payload for store '.$shop->shop_url);
        Log::info($payload);

        $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);
        if(array_key_exists('statusCode', $response) && $response['statusCode'] == 201) {
            $priceRuleId = $response['body']['price_rule']['id'];
            $fullResponse = json_encode($response['body']['price_rule']);
            $shop->getPriceRule()->create([
                'price_id' => $priceRuleId,
                'full_response' => $fullResponse
            ]);
        } else {
            Log::info('Problem while creating price rule');
            Log::info($response);
        }
        
        return true;
    }

    public function checkDiscountCodeRedemption($order, $shops, $almeToken, $sessionId) {
        //Process Discount Code for order
        try {
            if($almeToken == null) {
                return;
            }
            
            if(isset($order->discount_allocations) && $order->discount_allocations != null) {
                $discountAllocations = $order->discount_allocations != null && is_array($order->discount_allocations) ? $order->discount_allocations : json_decode($order->discount_allocations, true);
                if($discountAllocations != null && is_array($discountAllocations) && count($discountAllocations) > 0) {
                    foreach($discountAllocations as $discountInfo) {
                        if(is_array($discountInfo) && array_key_exists('code', $discountInfo)) {
                            $shop_url = $shops[$order->shop_id]['shop_url'];
                            $shop = Shop::where('shop_url', $shop_url)->first();
                            $dbRow = DiscountCode::where('store_id', $shop->id)->where('code', $discountInfo['code'])->first();
                            if($dbRow != null && $shop != null) {
                                $createArr = [
                                    'shop_id' =>  $shop->id,
                                    'alme_token' => $almeToken,
                                    'session_id' => $sessionId,
                                    'discount_id' => $dbRow->id,
                                    'order_id' => $order->table_id,
                                    'created_at' => $order->created_at
                                ];
                                $updateArr = [
                                    'shop_id' =>  $shop->id,
                                    'alme_token' => $almeToken,
                                    'session_id' => $sessionId,
                                    'discount_id' => $dbRow->id,
                                ];
                                Log::info('Creating click analytics row here '.$order->name);
                                AlmeClickAnalytics::updateOrCreate($updateArr, $createArr);
                            }
                        }
                    }
                }
            }
        } catch (Throwable $th) {
            Log::info('Discount allocations problem '.$th->getMessage().' '.$th->getLine());;
        }
    }

    public function saveOrUpdateOrder($request, $shopDetails) {
        try {
            try {
                $cartToken = $request['cart_token'];
                if($cartToken != null && is_string($cartToken) && strlen($cartToken) > 0) {
                    //Nothing to do here, cart token is alright
                } else {
                    $key = 'note_attributes';
                    if(isset($request[$key]) && is_array($request[$key]) && count($request[$key]) > 0) {
                        foreach($request[$key] as $attr) {
                            if($attr != null && is_array($attr) && array_key_exists('name', $attr)) {
                                if($attr['name'] == 'cart_token') {
                                    $cartToken = $attr['value']; //Now we took it from the note_attributes value
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $th) {
                Log::info('Cart token error '.$th->getMessage().' '.$th->getLine());
                $cartToken = $request['cart_token'] ?? null;
            }

            $updateArr = [
                'shop_id' => $shopDetails->id,
                'name' => $request['name'],
                'id' => $request['id']
            ];

            $createArr = array_merge($updateArr, [
                'checkout_id' => $request['checkout_id'],
                'browser_ip' => $request['browser_ip'],
                'cart_token' => $cartToken,
                'source_name' => $request['source_name'] ?? null,
                'total_price' => $request['total_price'],
                'line_items' => json_encode($request['line_items']),
                'discount_allocations' => isset($request['discount_applications']) && is_array($request['discount_applications']) ? json_encode($request['discount_applications']) : null,
                'customer' => isset($request['customer']) && is_array($request['customer']) ? json_encode($request['customer']) : null,
                'shipping_address' => isset($request['shipping_address']) && is_array($request['shipping_address']) ? json_encode($request['shipping_address']) : null
            ]);
            
            return ShopifyOrder::updateOrCreate($updateArr, $createArr);
        } catch (Throwable $th) {
            Log::info('Error in webhook create order job');
            Log::info($th->getMessage().' ',$th->getLine());
        } catch (Exception $th) {
            Log::info('Error in webhook create order job');
            Log::info($th->getMessage().' ',$th->getLine());
        }
    }

    public function getAlmeAnalytics($shopURL, $request = null, $setCache = true) {
        try {
            $order = $request != null && isset($request['order']) && strlen($request['order']) > 0 && in_array($request['order'], ['asc', 'desc']) ? $request['order'] : 'desc';
            //$days = $request != null && isset($request['days']) && strlen($request['days']) > 0 && in_array($request['days'], ['asc', 'desc']) ? $request['days'] : 7;
            $days = 7;

            $hasStartAndEndDate = $request !== null ? array_key_exists('start', $request) && array_key_exists('end', $request) : false;
            $startDate = null;
            $endDate = null;
            if($hasStartAndEndDate) {
                $startDate = date('Y-m-d', $request['start']);
                $endDate = date('Y-m-d', $request['end']);
            }
            
            $cacheKey = 'dashboard_analytics.'.$shopURL;
            if(Cache::has($cacheKey)) return Cache::get($cacheKey);
            $endpointArr = [];
            $arr = [
                'visits_count' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate)) : 'days='.$days,
                'session_count' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate)) : 'days='.$days,
                'cart_count' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate)) : 'days='.$days,
                'user_count' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate)) : 'days='.$days,
                'visit_conversion' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&order='.$order) : 'days='.$days.'&order='.$order,
                'cart_conversion' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&order='.$order) : 'days='.$days.'&order='.$order,
                'product_visits' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&order='.$order) : 'days='.$days.'&order='.$order,
                'product_cart_conversion' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&order='.$order) : 'days='.$days.'&order='.$order
            ];

            $responses = [];
            $prefix = 'analytics/';
            $headers = getAlmeHeaders();
            foreach($arr as $urlPath => $additionalParams) {
                $endpoint = getAlmeAppURLForStore($prefix.$urlPath.'?app_name='.$shopURL);
                if($additionalParams !== null) {
                    $endpoint .= '&'.$additionalParams;
                }
                $endpointArr[] = $endpoint;
                $responses[$urlPath] = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
            }

            if(isset($responses['product_visits']['statusCode']) && $responses['product_visits']['statusCode'] == 200) {
                $responses['product_visits']['body'] = $this->getProductsVisits($shopURL, $responses['product_visits']['body']);
            }

            if(isset($responses['product_cart_conversion']['statusCode']) && $responses['product_cart_conversion']['statusCode'] == 200) {
                $responses['product_cart_conversion']['body'] = $this->getProductsConvertedToCarts($shopURL, $responses['product_cart_conversion']['body']);
            }

            //Graph Data function for Visit conversion graph
            if(isset($responses['visit_conversion']['statusCode']) && $responses['visit_conversion']['statusCode'] == 200) {
                $responses['visit_conversion']['graphData'] = $this->getGraphDataForConversion($responses['visit_conversion']['body']);
            }

            //Graph Data function for Cart conversion graph
            if(isset($responses['cart_conversion']['statusCode']) && $responses['cart_conversion']['statusCode'] == 200) {
                $responses['cart_conversion']['graphData'] = $this->getGraphDataForConversion($responses['cart_conversion']['body']);
            }

            if($setCache)
                Cache::set($cacheKey, $responses, now()->addMinutes(30)); //30 minutes expiry limit to save some API calls

            $responses['endpoints'] = $endpointArr;
            
            return $responses;
        } catch(Exception $e) {
            Log::info('Dashboard route error '.$e->getMessage().' '.$e->getLine());
            return [
                'visits_count' => 'N/A',
                'session_count' => 'N/A',
                'cart_count' => 'N/A',
                'visit_conversion' => 'N/A'
            ];
        }
    }

    public function callAlmeAppIdentifiedUsers($shop, $request) {
        $endpoint = 'analytics/identified_user_activity?app_name='.$shop->shop_url;
        if(isset($request['start_date']) && isset($request['end_date'])) {
            $endpoint .= '&start_date='.date('Y-m-d', $request['start_date']);
            $endpoint .= '&end_date='.date('Y-m-d', $request['end_date']);
        }
        //Log::info('Endpoint '.$endpoint);
        $endpoint = getAlmeAppURLForStore($endpoint);
        $headers = getAlmeHeaders();
        return $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
    }

    public function getShopCustomers($shop) {
        $returnArr = [];
        $since_id = 0;
        $headers = getShopifyAPIHeadersForStore($shop);
        do {

            $countEndpoint = getShopifyAPIURLForStore('customers/count.json', $shop);
            $countResponse = $this->makeAnAPICallToShopify('GET', $countEndpoint, $headers);
            $endpoint = getShopifyAPIURLForStore('customers.json?limit=250&since_id='.$since_id, $shop);
            Log::info($endpoint);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
            if($response['statusCode'] == 200) {
                $customers = $response['body']['customers'];
                if($customers !== null && count($customers) > 0) {
                    Log::info('Count got '.count($customers));
                    foreach($customers as $customer) {
                        $returnArr[$customer['id']] = [
                            'name' => $customer['first_name'].' '.$customer['last_name'],
                            'email' => $customer['email']
                        ];

                        $since_id = $customer['id'];
                    }
                } else {
                    $customers = null;
                }
            } else {
                $customers = null;
            }
        } while($customers !== null && count($customers) > 0);
        return $returnArr;
    }

    public function getTopCarted($shopURL, $request = null) {
        try {
            $order = $request != null && isset($request['order']) && strlen($request['order']) > 0 && in_array($request['order'], ['asc', 'desc']) ? $request['order'] : 'desc';
            $days = 7;

            $hasStartAndEndDate = $request !== null ? array_key_exists('start', $request) && array_key_exists('end', $request) : false;
            $startDate = null;
            $endDate = null;
            if($hasStartAndEndDate) {
                $startDate = date('Y-m-d', $request['start']);
                $endDate = date('Y-m-d', $request['end']);
            }
            
            $endpointArr = [];
            $arr = [
                'product_cart_conversion' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&order='.$order) : 'days='.$days.'&order='.$order
            ];

            $responses = [];
            $prefix = 'analytics/';
            $headers = getAlmeHeaders();
            foreach($arr as $urlPath => $additionalParams) {
                $endpoint = getAlmeAppURLForStore($prefix.$urlPath.'?app_name='.$shopURL);
                if($additionalParams !== null) {
                    $endpoint .= '&'.$additionalParams;
                }
                $endpointArr[] = $endpoint;
                $responses[$urlPath] = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
            }

            //Graph Data function for Cart conversion graph
            if(isset($responses['product_cart_conversion']['statusCode']) && $responses['product_cart_conversion']['statusCode'] == 200) {
                $responses['product_cart_conversion']['body'] = $this->getProductsConvertedToCarts($shopURL, $responses['product_cart_conversion']['body']);
            }
            
            $responses['endpoints'] = $endpointArr;
            
            return $responses;
        } catch(Exception $e) {
            Log::info('Dashboard route error '.$e->getMessage().' '.$e->getLine());
            return [
                'visits_count' => 'N/A',
                'session_count' => 'N/A',
                'cart_count' => 'N/A',
                'visit_conversion' => 'N/A'
            ];
        }
    }

    public function getTopVisits($shopURL, $request = null) {
        try {
            $order = $request != null && isset($request['order']) && strlen($request['order']) > 0 && in_array($request['order'], ['asc', 'desc']) ? $request['order'] : 'desc';
            $days = 7;

            $hasStartAndEndDate = $request !== null ? array_key_exists('start', $request) && array_key_exists('end', $request) : false;
            $startDate = null;
            $endDate = null;
            if($hasStartAndEndDate) {
                $startDate = date('Y-m-d', $request['start']);
                $endDate = date('Y-m-d', $request['end']);
            }
            
            $endpointArr = [];
            $arr = [
                'product_visits' => $hasStartAndEndDate ? trim('start_date='.urlencode($startDate).'&end_date='.urlencode($endDate).'&order='.$order) : 'days='.$days.'&order='.$order,
            ];

            $responses = [];
            $prefix = 'analytics/';
            $headers = getAlmeHeaders();
            foreach($arr as $urlPath => $additionalParams) {
                $endpoint = getAlmeAppURLForStore($prefix.$urlPath.'?app_name='.$shopURL);
                if($additionalParams !== null) {
                    $endpoint .= '&'.$additionalParams;
                }
                $endpointArr[] = $endpoint;
                $responses[$urlPath] = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
            }

            if(isset($responses['product_visits']['statusCode']) && $responses['product_visits']['statusCode'] == 200) {
                $responses['product_visits']['body'] = $this->getProductsVisits($shopURL, $responses['product_visits']['body']);
            }

            $responses['endpoints'] = $endpointArr;
            
            return $responses;
        } catch(Exception $e) {
            Log::info('Dashboard route error '.$e->getMessage().' '.$e->getLine());
            return [
                'visits_count' => 'N/A',
                'session_count' => 'N/A',
                'cart_count' => 'N/A',
                'visit_conversion' => 'N/A'
            ];
        }
    }

    public function getGraphDataForConversion($body) {
        try {
            $returnVal = [];
            if($body !== null && count($body) > 0) {
                foreach($body as $date => $data) {
                    $returnVal[date('M d, Y', strtotime($date))] = round($data['conversion_rate'], 2);
                }
            }

            $yAxis = '[';
            $xAxis = '[';
            $yAxisArr = [];
            $xAxisArr = [];
            foreach($returnVal as $key => $val) {
                $yAxisArr[] = '"'.$key.'"';
                $xAxisArr[] = '"'.$val.'"';
            }

            $yAxis .= implode(', ',$yAxisArr).']';
            $xAxis .= implode(', ', $xAxisArr).']';
            return ['xAxis' => $xAxis, 'yAxis' => $yAxis];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getProductsVisits($shopURL, $body) {
        try {
            if(is_array($body) && count($body) > 0) {
                $productIds = [];
                $conversionArr = [];
                foreach($body as $payload) {
                    $productIds[] = $payload['item__product_id']; //That's the product ID.
                    $conversionArr[$payload['item__product_id']] = $payload['visit_count']; //That's the data associated to the product data
                } 
                $shop = Auth::check() ? Auth::user()->shopifyStore : Shop::where('shop_url', $shopURL)->first();
                $products = $shop->getProducts()->whereIn('product_id', $productIds)->get();
                if($products !== null && $products->count() > 0) {
                    $products = $products->keyBy('product_id')->toArray();
                    return [
                        'products' => $products,
                        'assoc_data' => $conversionArr 
                    ];
                }
            }
        } catch(Exception $e) {
            Log::info('Error in getProductsConvertedToCarts function '.$e->getMessage().' '.$e->getLine());
        }
        return null;
    } 

    public function getProductsConvertedToCarts($shopURL, $body) {
        try {
            if(is_array($body) && count($body) > 0) {
                $productIds = [];
                $conversionArr = [];
                foreach($body as $payload) {
                    $productIds[] = $payload[0]; //That's the product ID.
                    $conversionArr[$payload[0]] = $payload[1]; //That's the data associated to the product data
                } 
                $shop = Auth::check() ? Auth::user()->shopifyStore : Shop::where('shop_url', $shopURL)->first();
                $products = $shop->getProducts()->whereIn('product_id', $productIds)->get();
                if($products !== null && $products->count() > 0) {
                    $products = $products->keyBy('product_id')->toArray();
                    return [
                        'products' => $products,
                        'assoc_data' => $conversionArr 
                    ];
                }
            }
        } catch(Exception $e) {
            Log::info('Error in getProductsConvertedToCarts function '.$e->getMessage().' '.$e->getLine());
        }
        return null;
    } 

    public function getDiscountCodes($obj) {
        try {
            $arrKey = 'discount_codes';
            $returnVal = null;
            if(!is_array($obj)) $obj = $obj->toArray();
            if(array_key_exists($arrKey, $obj) && is_array($obj[$arrKey]) && count($obj[$arrKey]) > 0) {
                $returnVal = [];
                foreach($obj[$arrKey] as $data) {
                    $returnVal[] = [
                        'code' => $data['code'] ?? '',
                        'amount' => $data['amount']
                    ];
                } 
            }   
            return $returnVal;         
        } catch(Exception $e) {
            Log::info('Error in getPayload function '.$e->getMessage().' '.$e->getLine());
            return null;
        }
    }

    public function getLineItemsPayload($obj) {
        $arrKey = 'line_items';
        $returnVal = null;
        if(!is_array($obj)) $obj = $obj->toArray();
        if(array_key_exists($arrKey, $obj) && is_array($obj[$arrKey]) && count($obj[$arrKey]) > 0) {
            $returnVal = [];
            foreach($obj[$arrKey] as $lineItem) {
                $returnVal[] = [
                    "product_id" => $lineItem['product_id'],
                    "title" => $lineItem['title'],
                    "price" => $lineItem['price'],
                    "quantity" => $lineItem['quantity']
                ];
            }
        }
        return $returnVal;
    }

    public function verifyRequestDuplication($cacheKey) {
        return !Cache::has($cacheKey);
    }

    public function getOrderRequestPayloadForAlmeEvent($obj, $shopDetails) {
        try {
            try {
                $cartToken = $obj['cart_token'];
                if($cartToken != null && is_string($cartToken) && strlen($cartToken) > 0) {
                    //Nothing to do here, cart token is alright
                } else {
                    $key = 'note_attributes';
                    if(isset($obj[$key]) && is_array($obj[$key]) && count($obj[$key]) > 0) {
                        foreach($obj[$key] as $attr) {
                            if($attr != null && is_array($attr) && array_key_exists('name', $attr)) {
                                if($attr['name'] == 'cart_token') {
                                    $cartToken = $attr['value']; //Now we took it from the note_attributes value
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $th) {
                Log::info('Cart token error '.$th->getMessage().' '.$th->getLine());
                $cartToken = $obj['cart_token'] ?? null;
            }
            return [
                "cart_token" => $cartToken,
                "app_name" => $shopDetails->shop_url,
                "email" => $obj['email'] ?? null,
                "user_id" => $obj['customer']['id'] ?? null,
                "created_at" => $obj['created_at'],
                "line_items" => $this->getLineItemsPayload($obj),
                "total_discounts" => $obj['total_discounts'],
                "discount_codes" => $this->getDiscountCodes($obj)
            ];
        } catch (Exception $e) {
            Log::info('Error in getPayload function '.$e->getMessage().' '.$e->getLine());
            return null;
        }
    }

    //TODO: Do this logic later
    public function validateWebhookRequest($request, $headers) {
        return true;
        // $hmac_header = $headers['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? null;
        // foreach($request as $key => $value){
        //     $key=str_replace("%","%25",$key);
        //     $key=str_replace("&","%26",$key);
        //     $key=str_replace("=","%3D",$key);
        //     $value=str_replace("%","%25",$value);
        //     $value=str_replace("&","%26",$value);
        //     $arr[] = $key."=".$value;
        // }
        // $str = implode('&', $arr);
        // $calculated_hmac = base64_encode(hash_hmac('sha256', $str, config('shopify.SECRET_KEY'), true));
        // return $hmac_header == $calculated_hmac;
    }
}