<?php

namespace App\Http\Controllers;

use App\Jobs\RegisterWebhooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller {

    public function cartUpdateWebhook(Request $request) {
        Log::info('Request for cart update');
        Log::info($request->all());
        return response()->json(['status' => true]);
    }

    public function registerWebhooks(Request $request) {
        RegisterWebhooks::dispatch($request->shop_id)->onConnection('sync');
        return response()->json(['status' => true, 'message' => 'Done']);
    }
    
}
