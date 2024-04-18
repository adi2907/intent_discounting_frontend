<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SegmentController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function createSegment(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        return view('identified_user_segments');
    }

    public function submitSegments(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
    }

    public function listSegments(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
    }

    public function getDidDoEventsDefaultHTML(Request $request) {
        $html = view('partials.segments.did_do_events', [
            'counter' => $request->filled('counter') && $request->counter != null ? $request->counter + 1 : null
        ])->render();
        return response()->json(['status' => true, 'html' => $html]);
    }
}
