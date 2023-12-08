<?php

namespace App\Http\Controllers;

use App\Jobs\RegisterWebhooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller {

    public function registerWebhooks(Request $request) {
        RegisterWebhooks::dispatch($request->shop_id)->onConnection('sync');
        return response()->json(['status' => true, 'message' => 'Done']);
    }
    
    public function cartUpdateWebhook(Request $request) {
        Log::info('Request for cart update');
        Log::info($request->all());
        return response()->json(['status' => true]);
    }
    
    public function checkoutCreateWebhook(Request $request) {
        Log::info('Request for checkout create');
        Log::info($request->all());
        return response()->json(['status' => true]);
    }

    public function checkoutUpdateWebhook(Request $request) {
        Log::info('Request for checkout update');
        Log::info($request->all());
        return response()->json(['status' => true]);
    }

    public function cartCreateWebhook(Request $request) {
        Log::info('Request for cart create');
        Log::info($request->all());
        return response()->json(['status' => true]);
    }
    
    public function orderCreateWebhook(Request $request) {
        Log::info('Request for order create');
        Log::info($request->all());
        return response()->json(['status' => true]);
    }

    public function orderUpdateWebhook(Request $request) {
        Log::info('Request for order update');
        Log::info($request->all());
        return response()->json(['status' => true]);
    }
}
