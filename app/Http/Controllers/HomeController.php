<?php

namespace App\Http\Controllers;

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
