<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPurchaseEvent;
use App\Models\AlmeShopifyOrders;
use App\Models\Shop;
use App\Models\ShopDetail;
use App\Models\ShopifyOrder;
use App\Models\User;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
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

    public function index() {
        return redirect()->route('login');
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
        $endpoint = getShopifyAPIURLForStore('customers.json', $store);
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
}
