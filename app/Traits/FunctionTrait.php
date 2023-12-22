<?php 

namespace App\Traits;

use App\Models\Shop;
use App\Models\ShopifyProducts;
use Exception;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            //if(Cache::has($cacheKey)) return Cache::get($cacheKey);
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
                $responses['product_visits']['body'] = $this->getProductsVisits($responses['product_visits']['body']);
            }

            if(isset($responses['product_cart_conversion']['statusCode']) && $responses['product_cart_conversion']['statusCode'] == 200) {
                $responses['product_cart_conversion']['body'] = $this->getProductsConvertedToCarts($responses['product_cart_conversion']['body']);
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

    public function getProductsVisits($body) {
        try {
            if(is_array($body) && count($body) > 0) {
                $productIds = [];
                $conversionArr = [];
                foreach($body as $payload) {
                    $productIds[] = $payload['item__product_id']; //That's the product ID.
                    $conversionArr[$payload['item__product_id']] = $payload['visit_count']; //That's the data associated to the product data
                } 
                $shop = Auth::check() ? Auth::user()->shopifyStore : Shop::where('id', 8)->first();
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

    public function getProductsConvertedToCarts($body) {
        try {
            if(is_array($body) && count($body) > 0) {
                $productIds = [];
                $conversionArr = [];
                foreach($body as $payload) {
                    $productIds[] = $payload[0]; //That's the product ID.
                    $conversionArr[$payload[0]] = $payload[1]; //That's the data associated to the product data
                } 
                $shop = Auth::check() ? Auth::user()->shopifyStore : Shop::where('id', 8)->first();
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

}