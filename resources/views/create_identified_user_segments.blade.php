@extends('layouts.new_app')

@section('css')
<link href="{{asset('css/createList.css')}}" type="text/css" rel="stylesheet" />
@endsection

@section('content')
    <div class="col-md-9 nopadding">
        <section class="page-title bg-white p-4">
            <div class="title-content">      
                <h1>Create List</h1>
                <i class="fas fa-chart-pie"></i>
            </div>
        </section>
        <section class="main-content mt-3 mr-3">
        <form method="POST" action="{{route('store.identified.user.segments')}}"> 
            @csrf
            <div class="container"> 
                <div class="form-group list-name-group" style="background:white">
                    <label for="listName" class="list-name-label">List Name:</label>
                    <input type="text" required id="listName" name="listName" class="form-control list-name-input">
                </div>
                
                <div class="container user-profile-container">
                    <h2 class="settings-heading">User Profile</h2>
                    <div class="date-filter-section">
                        <div class="userdate-option">
                            <label for="lastSeen-filter" class="userdate-card-title">Last Seen</label>
                            <select id="lastSeen-filter" name="lastSeen-filter" class="date-filter-select">
                                <option value="">Select an option</option>
                                <option value="on">On</option>
                                <option value="after">After</option>
                                <option value="before">Before</option>
                                <option value="between">Between</option>
                            </select>
                            <label for="lastSeen-input" class="sr-only">Last Seen Date</label>
                            <input type="date" id="lastSeen-input" name="lastSeen-input" class="date-filter-input">
                        </div>
                        <div class="userdate-option">
                            <label for="createdOn-filter" class="userdate-card-title">Created On</label>
                            <select id="createdOn-filter" name="createdOn-filter" class="date-filter-select">
                                <option value="">Select an option</option>
                                <option value="on">On</option>
                                <option value="after">After</option>
                                <option value="before">Before</option>
                                <option value="between">Between</option>
                            </select>
                            <label for="createdOn-input" class="sr-only">Created On Date</label>
                            <input type="date" id="createdOn-input" name="createdOn-input" class="date-filter-input">
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
                            <option value="">Select an option</option>
                            <option value="equal">Equal to</option>
                            <option value="greater">Greater than</option>
                            <option value="lesser">Lesser than</option>
                        </select>
                        <input type="number" id="session-input" name="session-input" class="date-filter-input" placeholder="Enter number">
                    </div>
                </div>
            
                <div class="container behavioral-container" id="did_do_events_card">
                    <h2 class="settings-heading" >Behavioral</h2>
                    <div id="did_do_events_card_container">
                        @include('partials.segments.did_do_events', ['counter' => 1])
                    </div>
                    <!-- Placeholder for additional event-criteria-cards -->
                    <div class="additional-events"></div>
                    <button type="button" class="btn reset-button" id="resetForm">Reset</button>
                </div>
                <div class="container behavioral-container" id="did_not_do_events_card">
                    <h2 class="settings-heading">Behavioral</h2>
                    <div class="did_not_do_events_card_container">
                        @include('partials.segments.did_not_do_events', ['counter' => 1])
                    </div>
                    <div class="additional-events"></div>
                    <button type="button" class="btn reset-button" id="resetDidNotDoForm">Reset</button>
                </div>
            </div>
            <div class="text-center mt-2 mb-2">
                <button type="submit" class="btn btn-primary" style="width:30%">Submit</button>
            </div>
        </form> 
        </section> 
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

        $('#resetNotForm').click(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_not_do_events.defaultHTML')}}",
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        $('#did_not_do_events_card').find('.event-criteria-card').html(response.html);
                    }
                }
            })
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

        $(document).on('change', '.time-select', function (e) {
            e.preventDefault();
            var el = $(this);
            var val = el.val();
            var parentEl = el.parent().parent();
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
                        el.parent().parent().parent().append(response.html);
                    }
                }
            })
        });

        $(document).on('click', '.addNotRule', function (e) {
            e.preventDefault();
            var el = $(this);
            var noOfElements = $('#did_not_do_events_card').find('.event-criteria-card').length;
            $.ajax({
                type: 'GET', 
                url: "{{route('segments.did_not_do_events.defaultHTML')}}",
                data: {counter: noOfElements},
                async: false,
                success: function (response) {
                    if(response.status && response.html) {
                        el.parent().parent().parent().append(response.html);
                    }
                }
            })
        });

        $(document).on('click', '.deleteNotRule', function (e) {
            e.preventDefault();
            var noOfElements = $('#did_not_do_events_card').find('.event-criteria-card').length;
            var el = $(this);
            if(noOfElements > 1) {
                el.parent().parent().remove();
            }
        });

        $(document).on('click', '.deleteRule', function (e) {
            e.preventDefault();
            var noOfElements = $('#did_do_events_card').find('.event-criteria-card').length;
            var el = $(this);
            if(noOfElements > 1) {
                el.parent().parent().remove();
            }
        });
    });
</script>
@endsection