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
                
                @include('partials.segments.top_part')
            
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

        $('.lastSeenFilterSelect').change(function (e) {
            e.preventDefault();
            var el = $(this);
            var val = el.val();
            var display = null;
            if(val == 'between') {
                display = 'block';
            } else {
                display = 'none';
            }

            $('.between_top_last_date').css({'display': display});
        });

        $('.createdFilterSelect').change(function (e) {
            e.preventDefault();
            var el = $(this);
            var val = el.val();
            var display = null;
            if(val == 'between') {
                display = 'block';
            } else {
                display = 'none';
            }

            $('.between_top_created_date').css({'display': display});
        });

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