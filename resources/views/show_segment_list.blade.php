@extends('layouts.new_app')

@section('css')
<link href="{{asset('css/createList.css')}}" type="text/css" rel="stylesheet" />
@endsection

@section('content')
    <div class="col-md-9 nopadding">
        <section class="page-title bg-white p-4">
            <div class="title-content">      
                <h1>Show List</h1>
                <i class="fas fa-chart-pie"></i>
            </div>
        </section>
        <section class="main-content mt-3 mr-2 ml-2">
        <form method="POST" action="{{route('store.identified.user.segments')}}"> 
            @csrf
            <div class="container"> 
                <div  class="form-group bg-white list-name-group">
                    <label for="listName" class="list-name-label">List Name:</label>
                    <input type="text" id="listName" value="{{$segment->listName ?? ''}}" name="listName" class="form-control list-name-input">
                </div>
                
                <div class="container user-profile-container">
                    <h2 class="settings-heading">User Profile</h2>
                    <div class="date-filter-section">
                        <div class="userdate-option">
                            <label for="lastSeen-filter" class="userdate-card-title">Last Seen</label>
                            <select id="lastSeen-filter" name="lastSeen-filter" class="date-filter-select">
                                <option @if($segment->{'lastSeen-filter'} == 'on') selected @endif value="on">On</option>
                                <option @if($segment->{'lastSeen-filter'} == 'after') selected @endif value="after">After</option>
                                <option @if($segment->{'lastSeen-filter'} == 'before') selected @endif value="before">Before</option>
                                <option @if($segment->{'lastSeen-filter'} == 'between') selected @endif value="between">Between</option>
                            </select>
                            <label for="lastSeen-input" class="sr-only">Last Seen Date</label>
                            <input type="date" id="lastSeen-input" value="{{ $segment->{'lastSeen-input'} }}" name="lastSeen-input" class="date-filter-input">
                        </div>
                        <div class="userdate-option">
                            <label for="createdOn-filter" class="userdate-card-title">Created On</label>
                            <select id="createdOn-filter" name="createdOn-filter" class="date-filter-select">
                                <option @if($segment->{'createdOn-filter'} == 'on') selected @endif value="on">On</option>
                                <option @if($segment->{'createdOn-filter'} == 'after') selected @endif value="after">After</option>
                                <option @if($segment->{'createdOn-filter'} == 'before') selected @endif value="before">Before</option>
                                <option @if($segment->{'createdOn-filter'} == 'between') selected @endif value="between">Between</option>
                            </select>
                            <label for="createdOn-input" class="sr-only">Created On Date</label>
                            <input type="date" id="createdOn-input" value="{{ $segment->{'createdOn-input'} }}" name="createdOn-input" class="date-filter-input">
                        </div>
                    </div>
                    <div class="form-group" style="display: none;">
                        <label>Acquisition Source:</label>
                        <div class="form-check">
                            <input type="checkbox" id="organic" name="acquisition_source" value="organic" class="form-check-input" disabled="">
                            <label class="form-check-label" for="organic">Organic</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="paid" name="acquisition_source" value="paid" class="form-check-input" disabled="">
                            <label class="form-check-label" for="paid">Paid</label>
                        </div>
                    </div>
                    <div class="form-group" style="display: none;">
                        <label>Primary Usage:</label>
                        <div class="form-check">
                            <input type="checkbox" id="mobile" name="primary_usage" value="mobile" class="form-check-input" disabled="">
                            <label class="form-check-label" for="mobile">Mobile</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="desktop" name="primary_usage" value="desktop" class="form-check-input" disabled="">
                            <label class="form-check-label" for="desktop">Desktop</label>
                        </div>
                    </div>
                    <div class="sessions-option">
                        <label for="session-filter" class="sessions-card-title">Number of Sessions</label>
                        <select id="session-filter" name="session-filter" class="date-filter-select">
                            <option @if($segment->{'session-filter'} == 'equal') selected @endif value="equal">Equal to</option>
                            <option @if($segment->{'session-filter'} == 'greater') selected @endif value="greater">Greater than</option>
                            <option @if($segment->{'session-filter'} == 'lesser') selected @endif value="lesser">Lesser than</option>
                        </select>
                        <input type="number" value="{{ $segment->session_input }}" id="session-input" name="session-input" class="date-filter-input" placeholder="Enter number">
                    </div>
                </div>
            
                <div class="container behavioral-container" id="did_do_events_card">
                    <h2 class="settings-heading" >Behavioral</h2>
                    <div id="did_do_events_card_container">
                        @php $rules = $segment->getRules(); @endphp
                        @foreach($rules as $key => $rule) 
                            @include('partials.segments.did_do_events', ['rule' => $rule, 'counter' => ($key + 1)])
                        @endforeach
                    </div>
                    <!-- Placeholder for additional event-criteria-cards -->
                    <div class="additional-events"></div>
                    <button type="button" class="btn reset-button" id="resetForm">Reset</button>
                </div>
                {{--<div class="container behavioral-container">
                    <h2 class="settings-heading">Behavioral</h2>
                    @include('partials.segments.did_not_do_events')
                    <!-- Placeholder for additional event-criteria-cards -->
                    <div class="additional-events"></div>
                    <button type="button" class="btn reset-button">Reset</button>
                </div>--}}
            </div>
            {{-- <div class="text-center mt-2 mb-2">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div> --}}
        </form> 
        </section> 
        @if(isset($segmentData) && is_array($segmentData) && isset($segmentData['body']) && count($segmentData) > 0)
            <section class="main-content mt-3 mr-4 ml-4">
                <div class="container">
                    <h4>Users in segment</h4><br>
                    <table class="table">
                        <thead>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </thead>
                        <tbody>
                            @foreach($segmentData['body'] as $userRow)
                            <tr>
                                <td>{{$userRow['name'] ?? ''}}</td>
                                <td>{{$userRow['phone'] ?? ''}}</td>
                                <td>{{$userRow['email'] ?? ''}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>           
        @endif
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $(document).on('click', '.btn-logic', function (e) {
            e.preventDefault();
            var el = $(this);
            var parent = el.parent();
            parent.find('.btn-logic').each(function () {
                $(this).removeClass('active-and-or-button');
            });
            el.addClass('active-and-or-button');
            var value = el.data('value');
            parent.find('.and_or_val').val(value);
        });

        $('#resetForm').click(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_do_events.defaultHTML')}}",
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        $('#did_do_events_card').find('.event-criteria-card').html(response.html);
                    }
                }
            })
        });

        $(document).on('change', '#time-select', function (e) {
            e.preventDefault();
            var el = $(this);
            var val = el.val();
            var parentEl = el.parent().parent().parent();
            parentEl.find('.within-last-days-container').css({'display': val == 'within_last_days' ? 'block' : 'none'});
            parentEl.find('.before-days-container').css({'display': val == 'before_days' ? 'block' : 'none'});
        });

        $(document).on('click', '.addRule', function (e) {
            e.preventDefault();
            var el = $(this);
            var noOfElements = $('.event-criteria-card').length;
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_do_events.defaultHTML')}}",
                data: {counter: noOfElements},
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        el.parent().parent().append(response.html);
                    }
                }
            })
        });

        $(document).on('click', '.deleteRule', function (e) {
            e.preventDefault();
            var noOfElements = $('.event-criteria-card').length;
            var el = $(this);
            if(noOfElements > 1) {
                el.parent().remove();
            }
        });
    });
</script>
@endsection