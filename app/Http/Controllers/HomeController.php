<?php

namespace App\Http\Controllers;

use App\Jobs\CreateShopDiscountCode;
use App\Jobs\ProcessPurchaseEvent;
use App\Models\AlmeShopifyOrders;
use App\Models\IpMap;
use App\Models\Shop;
use App\Models\ShopDetail;
use App\Models\ShopifyOrder;
use App\Models\User;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class HomeController extends Controller {

    use FunctionTrait, RequestTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //$this->middleware('auth');
    }

    public function testWebhookCache() {
        $shops = Shop::get();
        $returnVal = [];
        foreach($shops as $shop) {
            $cacheKey = 'webhook_cache_'.$shop->shop_url;
            $returnVal[$shop->shop_url] = Cache::get($cacheKey);
        }
        return response()->json(['status' => true, 'response' => $returnVal]);
    }

    public function index() {
        return redirect()->route('login');
    }

    public function checkInstallAndScript() {
        $shops = Shop::get();
        $returnVal = [];
        foreach($shops as $shop) {
            $install = $this->verifyInstallation($shop);
            $script = $this->checkAlmeScriptRunningOrNot($shop);

            $returnVal[] = [
                'shop' => $shop->shop_url,
                'install' => $install,
                'scriptEnabled' => $script
            ];
        }

        return response()->json(['status' => true, 'data'=> $returnVal]);
    }

    public function testPriceRules() {
        $shops = Shop::whereHas('getLatestPriceRule')->with('getLatestPriceRule')->get();
        $returnVal = [];
        foreach($shops as $shop) {
            $response = $this->getPriceRuleResponse($shop->getLatestPriceRule, $shop);
            $returnVal[] = [
                'shop' => $shop->shop_url,
                'response' => $response
            ];
        }

        return $returnVal;
    }

    public function testOrder($id, Request $request) {
        try {
            $order = ShopifyOrder::where('table_id', $id)->first();
            $shop = Shop::where('id', $order->shop_id)->first();
            $endpoint = getShopifyAPIURLForStore('orders/'.$order->id.'.json', $shop);
            $headers = getShopifyAPIHeadersForStore($shop);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
            return response()->json(['status' => true, 'data' => $response]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'error' => $th->getMessage().' '.$th->getLine(),
                'id' => $id,
                'id_type' => gettype($id),
                'request' => $request->all()
            ]);
        }

        
    }

    public function testAlmePayload($id) {
        $order = ShopifyOrder::where('table_id', $id)->first();
        $shop = Shop::where('id', $order->shop_id)->first();
        
        $returnVal = [];
        
        $almeInfo = AlmeShopifyOrders::where('shopify_cart_token', $order->cart_token)
                                             ->where('session_id', '<>', null)
                                             ->where('alme_token', '<>', null)
                                             ->orderBy('created_at', 'desc')
                                             ->first();
        if($almeInfo !== null && $almeInfo->count() > 0) {
            $line_items = is_array($order->line_items) ? $order->line_items : json_decode($order->line_items, true);
            $productsArr = [];
            foreach($line_items as $item) {
                $productsArr[] = [
                    "product_id" => $item['product_id'],
                    "product_name" => $item['title'],
                    "product_price" => $item['price'],
                    "product_qty" => $item['quantity']
                ];
            }

            $payload = [
                "cart_token" => $order->cart_token,
                "alme_user_token" => $almeInfo->alme_token,
                "timestamp" => $order->created_at,
                "app_name" => $shop['shop_url'],
                "session_id" => $almeInfo !== null && isset($almeInfo->session_id) ? $almeInfo->session_id : null,
                "products" => $productsArr
            ];
            $returnVal['status'] = 200; 
            $returnVal['Result'] = 'Found from AlmeShopifyOrders';
            $returnVal['API Payload'] = $payload;
        } else {
            if(isset($order->browser_ip) && filled($order->browser_ip)) {
                $dbRowForIP = IpMap::where('ip_address', $order->browser_ip)->where('shop_id', $order->shop_id)->first();
                if($dbRowForIP !== null && $dbRowForIP->count() > 0) {
                    $line_items = is_array($order->line_items) ? $order->line_items : json_decode($order->line_items, true);
                    $productsArr = [];
                    foreach($line_items as $item) {
                        $productsArr[] = [
                            "product_id" => $item['product_id'],
                            "product_name" => $item['title'],
                            "product_price" => $item['price'],
                            "product_qty" => $item['quantity']
                        ];
                    }

                    $payload = [
                        "cart_token" => $order->cart_token,
                        "alme_user_token" => $dbRowForIP->alme_token,
                        "timestamp" => $order->created_at,
                        "app_name" => $shop['shop_url'],
                        "session_id" => isset($dbRowForIP) && isset($dbRowForIP->session_id) ? $dbRowForIP->session_id : null,
                        "products" => $productsArr
                    ];
                    $returnVal['status'] = 200;
                    $returnVal['Result'] = 'Found from IPMap';
                    $returnVal['API Payload'] = $payload;  
                } else {
                    $returnVal['status'] = 404;
                    $returnVal['Result'] = 'Database IP map not found';
                }
            } else {
                $returnVal['status'] = 404;
                $returnVal['Result'] = 'No Data found';
            }
        }

        return response()->json($returnVal);
    }

    public function testOrders() {
        $store = Shop::where('id', 31)->first();
        $endpoint = getShopifyAPIURLForStore('orders/5549434962162.json', $store);
        $headers = getShopifyAPIHeadersForStore($store);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        dd($response['body']);
    }

    public function testCustomers() {
        $store = Shop::where('id', 31)->first();
        $endpoint = getShopifyAPIURLForStore('customers/7545405767922.json', $store);
        $headers = getShopifyAPIHeadersForStore($store);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        dd($response['body']);
    }

    public function testPurchaseEvent() {
        $orders = ShopifyOrder::where('shop_id', 31)->where('id', 5543423672562)->get();
        if($orders !== null && $orders->count() > 0) {
            $shopIds = $orders->pluck('shop_id')->toArray();
            $shops = Shop::whereIn('id', array_unique($shopIds))->get()->keyBy('id')->toArray();
            foreach($orders as $order) {
                ProcessPurchaseEvent::dispatch($order, $shops)->onConnection('sync');
            }
        }
        dd('Done');
    }

    public function deleteCoupons() {
        $shop = Shop::where('shop_url', 'millet-amma-store.myshopify.com')->first();
        $headers = getShopifyAPIHeadersForStore($shop);
        $endpoint = getShopifyAPIURLForStore('price_rules.json', $shop);
        $priceRules = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
        $responses = [];
        foreach($priceRules['body']['price_rules'] as $priceRule) {
            $dbRecord = $shop->getPriceRules()->where('price_id', (string) $priceRule['id'])->first();
            if($dbRecord !== null && $dbRecord->count() > 0) {
                $obj = json_decode($dbRecord->full_response, true);
                $newEndpoint = getShopifyAPIURLForStore('price_rules/'.$obj['id'].'/discount_codes.json', $shop);
                $responses[] = $this->makeAnAPICallToShopify('GET', $newEndpoint, $headers);
            } 
        }

        $deleteResponses = [];

        foreach($responses as $response) {
            foreach($response['body']['discount_codes'] as $code) {
                $deleteEndpoint = getShopifyAPIURLForStore('price_rules/'.$code['price_rule_id'].'/discount_codes/'.$code['id'].'.json', $shop);
                $deleteResponses[] = $this->makeAnAPICallToShopify('DELETE', $deleteEndpoint, $headers);
            }
        }

        dd($deleteResponses);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function sampleDashboard() {
        try{
            $request = 'almestore1.myshopify.com';
            $baseShop = Shop::where('shop_url', $request)->first();
            $shop = $baseShop->shop_url;
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

    public function sampleMinOrderCoupon($id) {
        $shop = Shop::where('id', $id)->first();
        $endpoint = getShopifyAPIURLForStore('price_rules.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        $payload = [
            'price_rule' => [
                "title" => "ALMEPRICEOFF".Str::random(3),
                "target_type" => "line_item",
                "target_selection" => "all",
                "allocation_method" => "across",
                "value_type" => "fixed_amount",
                "value" => "-50.0",
                "customer_selection" => "all",
                "prerequisite_subtotal_range" => ["greater_than_or_equal_to" => "150.0"],
                "starts_at" => "2024-04-25T18:59:59Z"
            ]
        ];

        $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);

        if($response['statusCode'] == 201) {
            $priceRuleId = $response['body']['price_rule']['id'];
            $discountCodeEndpoint = getShopifyAPIURLForStore('price_rules/'.$priceRuleId.'/discount_codes.json', $shop);
            $discountCodePayload = [
                "discount_code" => [
                    "code" => strtoupper(Str::random(4))
                ]
            ];
            $discountCodeResponse = $this->makeAnAPICallToShopify('POST', $discountCodeEndpoint, $headers, $discountCodePayload);
            dd($discountCodeResponse);
        }
        dd($response);
    }

    public function testCreateDiscount() {
        $shop = Shop::where('id', 17)->first();
        CreateShopDiscountCode::dispatch($shop)->onConnection('sync');
        dd('Done');
    }

    public function sampleProductRack(Request $request) {
        $user = User::first();
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

    public function checkPurchaseEvent($id, Request $request) {
        try {
            $order = ShopifyOrder::with(['getShop'])->where('table_id', $id)->first();
            $shop = $order->getShop;
            $shops = [$shop->id => $shop];

            //First properly take everything from Shopify and save it in db
            $endpoint = getShopifyAPIURLForStore('orders/'.$order->id.'.json', $shop);
            $headers = getShopifyAPIHeadersForStore($shop);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);

            if($response['statusCode'] == 200) {
                $orderBody = $response['body']['order'];
                $payload = $this->getOrderRequestPayloadForAlmeEvent($orderBody, $shop);
                if(isset($payload['cart_token'])) {
                    $almeShopifyOrderRow = AlmeShopifyOrders::where('shop_id', $shop->id)->where('shopify_cart_token', $payload['cart_token'])->first();
                    $returnResp = ['status' => true, 'data' => $almeShopifyOrderRow];
                }
            } else {
                $returnResp = ['status' => false, 'message' => 'Alme endpoint not called because order not found'];
            }

            return response()->json($returnResp);

        } catch (Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getLine().' '.$e->getMessage()]);
        }
    }
}
