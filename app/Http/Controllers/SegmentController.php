<?php

namespace App\Http\Controllers;

use App\Jobs\RunSegment;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class SegmentController extends Controller {

    use FunctionTrait, RequestTrait;

    public function __construct() {
        $this->middleware('auth');
    }

    public function create(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        return view('create_identified_user_segments');
    }

    public function store(Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $segmentArr = [];
        $notSegmentArr = [];

        try {
            $topRules = [
                'lastSeen_filter'   => $request->lastSeen_filter,
                'lastSeen_input'    => $request->lastSeen_input,
                'lastSeen_inputEnd' => $request->filled('lastSeen_inputEnd') ? $request->lastSeen_inputEnd : null,

                'createdOn_filter'      => $request->createdOn_filter,
                'createdOn_input'       => $request->createdOn_input,
                'createdOn_inputEnd'    => $request->filled('createdOn_inputEnd') ? $request->createdOn_input : null,
                
                'session_filter'    => $request->session_filter,
                'session_input'     => $request->session_input
            ];
        } catch (Exception $th) {
            $topRules = [];
        }

        try {
            if($request->filled('did_event_select')) {
                foreach($request->did_event_select as $key => $value) {
                    if($value !== null) {
                        $segmentArr[] = [
                            'did_event_select' => $value,
                            'occurrence_select' => $request->{'occurrence-select'}[$key],
                            'time_select' => $request->{'time-select'}[$key],
                            'within_last_days' => $request->{'within-last-days'}[$key],
                            'before_days' => $request->{'before-days'}[$key],
                            'and_or_val' => $request->and_or_val[$key]
                        ];
                    }
                }
            }

            if($request->filled('did_not_event_select')) {
                foreach($request->did_not_event_select as $key => $value) {
                    if($value !== null) {
                        $notSegmentArr[] = [
                            'did_event_select' => $value, 
                            'occurrence_select' => $request->{'not-occurrence-select'}[$key],
                            'time_select' => $request->{'not-time-select'}[$key],
                            'within_last_days' => $request->{'not-within-last-days'}[$key],
                            'before_days' => $request->{'not-before-days'}[$key],
                            'and_or_val' => $request->not_and_or_val[$key]
                        ];
                    }
                }
            }
            
        } catch (Throwable $th) {
            Log::info('Error in segment arr '.$th->getMessage().' '.$th->getLine());
            $segmentArr = [];
        }

        $dbArr = [
            'listName' => $request->listName,
            'lastSeen-filter' => $request->{'lastSeen-filter'},
            'lastSeen-input' => $request->{'lastSeen-input'},
            'createdOn-filter' => $request->{'createdOn-filter'},
            'session-filter' => $request->{'session-filter'},
            'session_input' => $request->{'session-input'},
            'createdOn-input' => $request->{'createdOn-input'},
            'no_of_users' => 0,
            'users_measurement' => '',
            'top_rules' => json_encode($topRules),
            'rules' => json_encode($segmentArr),
            'not_rules' => json_encode($notSegmentArr)
        ];
        
        $row = $shop->getAudienceSegments()->create($dbArr);
        // //This is run just to store the 
        RunSegment::dispatch($row)->onConnection('sync');

        return redirect()->route('show.identified.user.segments', ['id' => $row->id])->with('success', 'Segment created. Processing will begin shortly.');
    }

    public function show($id, Request $request) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $segment = $shop->getAudienceSegments()->where('id', $id)->first();
        $segmentData = $this->runSegment($shop, $segment);
        return view('show_segment_list', compact('user', 'shop', 'segment', 'segmentData'));
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

    public function getDidNotDoEventsDefaultHTML(Request $request) {
        $html = view('partials.segments.did_not_do_events', [
            'counter' => $request->filled('counter') && $request->counter != null ? $request->counter + 1 : null
        ])->render();
        return response()->json(['status' => true, 'html' => $html]);
    }

    public function delete($id) {
        $user = Auth::user();
        $shop = $user->shopifyStore;
        $shop->getAudienceSegments()->where('id', $id)->delete();
        return back()->with('success', 'Segment deleted');
    }
}
