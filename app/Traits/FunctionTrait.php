<?php 

namespace App\Traits;

use App\Models\RetryPurchaseEvent;
use App\Models\Shop;
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

    public function isPriceRuleValid($priceRule, $shop) {
        $endpoint = getShopifyAPIURLForStore('price_rules/'.$priceRule->price_id.'.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        return array_key_exists('statusCode', $response) && $response['statusCode'] == 200;
    }

    public function createDiscountCode($priceRule, $shop) {
        $code = Str::random(6);
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

    public function runSegment($shop, $row) {
        $baseEndpoint = getAlmeAppURLForStore('segments/identified-users-list');
        $headers = getAlmeHeaders();
        $rules = $row->getRules();
        $responseArr = [];
        foreach($rules as $ruleArr) {
            $payload = [
                'app_name' => $shop->shop_url,
                'action' => $ruleArr['did_event_select'],
            ];

            if($ruleArr['time_select'] == 'yesterday') {
                $payload['yesterday'] = 'true';
            }

            if($ruleArr['time_select'] == 'today') {
                $payload['today'] = 'true';
            }

            if($ruleArr['time_select'] == 'within_last_days') {
                $payload['last_x_days'] = $ruleArr['within_last_days'];
            }

            if($ruleArr['time_select'] == 'before_days') {
                $payload['before_x_days'] = $ruleArr['before_days'];
            }

            $getParams = [];
            foreach($payload as $key => $value) {
                $getParams[] = $key.'='.$value;
            }
            $getParams = implode('&', $getParams);
            $endpoint = $baseEndpoint.'?'.$getParams;
            $responseArr[] = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
        }
        
        $ids = $this->processAlmeAudienceSegments($responseArr, $rules);
        $finalAudience = $this->getFinalSegmentAudience($ids, $responseArr);
        return ['status' => true, 'body' => $finalAudience];
    }

    public function getFinalSegmentAudience($ids, $responseArr) {
        $returnVal = [];
        
        foreach($responseArr as $arr) {
            $tempArrKeys = collect($arr['body'])->keyBy('id')->toArray();
            foreach($ids as $id) {
                if(array_key_exists($id, $tempArrKeys)) {
                    $returnVal[$id] = $tempArrKeys[$id];
                }
            }
        }

        return $returnVal;
    }

    public function processAlmeAudienceSegments($responseArr, $rules) {
        $dataToReturn = [];

        //match the rules with alme responses
        foreach($rules as $key => $value) {
            $currentAndOr = $value['and_or_val'];
            $currentSegment = $responseArr[$key]['body'];
            if($key == 0) {
                //Initialize first segment to be returned in case there's only one rule
                $dataToReturn = array_keys(collect($currentSegment)->keyBy('id')->toArray());
            }

            $nextRuleExists = array_key_exists($key + 1, $rules) && $rules[$key + 1] != null;
            if($nextRuleExists) {
                //There are more than 1 rules in the segment so now we need to compare
                $dataToReturn = $this->compareTwoSegmentsWithUnionOrIntersection($currentSegment, $responseArr[$key + 1]['body'], $dataToReturn, $currentAndOr);
            } else {
                //No more to compare
                //I guess do nothing
            }
        }

        return $dataToReturn;
    }

    public function array_union($x, $y) { 
        $aunion = array_merge(
            array_intersect($x, $y),   // Intersection of $x and $y
            array_diff($x, $y),        // Elements in $x but not in $y
            array_diff($y, $x)         // Elements in $y but not in $x
        );
    
        return $aunion;
    }

    public function compareTwoSegmentsWithUnionOrIntersection($currentSegment, $almeBody, $dataToReturn, $currentAndOr) {
        
        $currentSegment = collect($currentSegment)->keyBy('id')->toArray();
        $almeBody = collect($almeBody)->keyBy('id')->toArray();

        $currentSegmentKeys = array_keys($currentSegment);
        $almeBodyKeys = array_keys($almeBody);

        if($almeBodyKeys != null && count($almeBodyKeys) > 0) {
            $tempRes = null;
            if($currentAndOr == 'and') 
                $tempRes = array_intersect($currentSegmentKeys, $almeBodyKeys);

            if($currentAndOr == 'or')
                $tempRes = $this->array_union($currentSegmentKeys, $almeBodyKeys);

            return array_unique(array_merge($tempRes, $dataToReturn));
        }
        return $dataToReturn;
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
        Log::info('Endpoint '.$endpoint);
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
            return [
                "cart_token" => $obj['cart_token'],
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