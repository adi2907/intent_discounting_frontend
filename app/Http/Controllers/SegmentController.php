<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SegmentController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function create(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        return view('identified_user_segments');
    }

    public function store(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $shop->getAudienceSegments()->create([
            'listName' => $request->listName,
            'lastSeen-filter' => $request->{'lastSeen-filter'},
            'lastSeen-input' => $request->{'lastSeen-input'},
            'createdOn-filter' => $request->{'createdOn-filter'},
            'createdOn-input' => $request->{'createdOn-input'},
            'rules' => null
        ]);

        return back()->with('success', 'Segment created. Processing will begin shortly.');
    }

    public function list(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $segments = $shop->getAudienceSegments;
        return view('segment_list', compact('user', 'shop', 'segments'));
    }

    public function getDidDoEventsDefaultHTML(Request $request) {
        $html = view('partials.segments.did_do_events', [
            'counter' => $request->filled('counter') && $request->counter != null ? $request->counter + 1 : null
        ])->render();
        return response()->json(['status' => true, 'html' => $html]);
    }

    public function deleteSegment($id) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $shop->getAudienceSegments()->where('id', $id)->delete();
        return back()->with('success', 'Segment deleted');
    }
}
