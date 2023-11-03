<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopDetail;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AppController extends Controller {
    
    use FunctionTrait, RequestTrait;
    public function __construct() {
        
    }

    public function showProductRacks(Request $request) {
        return view('product_racks');
    }

    public function showNotificationSettings(Request $request) {
        return view('notifications');
    }

    public function showDashboard(Request $request) {
        try{
            $request = $request->only('shop');
            $shop = $request['shop'] ?? Auth::user()->shopifyStore->shop_url;
            $baseShop = Shop::where('shop_url', $shop)->first();
            $shopDetails = $baseShop !== null ? ShopDetail::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first() : null;
            return view('new_dashboard', compact('baseShop', 'shopDetails'));
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        } catch(Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
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

    public function themePopups(Request $request) {
        $html = view('theme_popups')->render();
        if($request->has('shop')) {
            $shop = Shop::with(['getLatestPriceRule', 'getLatestDiscountCode'])->where('shop_url', $request->shop)->first();
            $code = $shop !== null ? $shop->getLatestDiscountCode->code : null;
            return response()->json(['code' => $code, 'status' => true, 'html' => $html]);
        }
        return response()->json(['code' => null, 'status' => true, 'html' => $html]);
    }
}
