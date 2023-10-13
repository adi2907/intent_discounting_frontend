<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopDetail;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class AppController extends Controller {
    
    use FunctionTrait, RequestTrait;
    public function __construct() {
        
    }

    public function showDashboard(Request $request) {
        try{
            $request = $request->only('shop');
            $shop = $request['shop'];
            $baseShop = Shop::where('shop_url', $shop)->first();
            $shopDetails = ShopDetail::where('shop_id', $baseShop->id)->orderBy('id', 'desc')->first();
            return view('dashboard', compact('baseShop', 'shopDetails'));
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        } catch(Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function themePopups(Request $request) {
        $html = view('theme_popups')->render();
        return response()->json(['status' => true, 'html' => $html]);
    }
}
