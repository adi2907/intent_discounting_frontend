@extends('layouts.new_app')
@section('css')
<link rel="stylesheet" href="{{asset('css/notifications.css')}}">
<link rel="stylesheet" href="{{asset('css/dashboard.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection
@section('content')
<div class="col-md-9 nopadding">
    <section class="page-title bg-white p-4">
        <div class="title-content">      
            <h1>SmartConvertAI</h1>
            <i class="fas fa-robot"></i>
        </div>
    </section>
    <section class="main-content">
        <div class="row col-md-12 save-button-div" style="display:none;">
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
                <a href="#" class="btn btn-success mt-4 save-button" style="float:right;width:40%;background-color:#1B4332">Save Changes</a>
            </div>
        </div>
        <div class="container mt-5">
            <h2 class="settings-heading">Enable SmartConvertAI</h2>
            <div class="settings-card">
                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Enable Sale Notifications</span>
                        <p class="settings-card-description">Enable personalised sale alerts based on browsing behaviour</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="inputChange" id="saleStatus" data-fieldtype="checkbox" data-fieldName="sale_status" @if(isset($notifSettings['sale_status']) && $notifSettings['sale_status'] == 1) checked @endif>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Discount (%age) for coupons</span>
                        <p class="settings-card-description">Specify how much discount to give using coupons on your store</p>
                    </div>
                    <input class="discount-input blurInputChange" style="width:10%" id="sale_discount_value" data-fieldtype="text" data-fieldName="sale_discount_value" type="number" min="1" max="50" value="{{$notifSettings['sale_discount_value']}}">
                </div>

                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Coupon validity(hours)</span>
                        <p class="settings-card-description">Specify the validity of the dynamically generated coupon in hours</p>
                        <p class="settings-card-description">Recommended to keep between 6 and 24 hours </p>
                    </div>
                    <input class="validity-input blurInputChange" style="width:10%" data-fieldtype="text" id="discount_expiry" data-fieldName="discount_expiry" type="number" min="1" max="100" value="{{$notifSettings['discount_expiry']}}">
                </div>
                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Minimum value for coupons</span>
                        <p class="settings-card-description">Specify the minimum cart value for the discount to apply.</p>
                        <p class="settings-card-description">Keep it empty for no minimum cart value </p>
                    </div>
                    <input class="discount-input blurInputChange" style="width:10%" id="min_value_coupon" data-fieldtype="text" data-fieldName="min_value_coupon" type="number" min="10" @if($notifSettings['min_value_coupon'] > 0) value="{{$notifSettings['min_value_coupon']}}" @else value="" @endif>
                </div>
	    </div>
	
        <h2 class="settings-heading">SmartConvertAI statistics</h2>
        <div class="row mb-4">
            Select Date Range: &nbsp;
            <input id="date-range" class="form-control" style="width:30%;border-radius:15%" type="text" name="daterange" value="01/01/2018 - 01/15/2018"/>
            <input type="hidden" id="date-start">
            <input type="hidden" id="date-end">
        </div>
    
        <div class="statistics-container">
            <div class="statistics-card">
                <i class="statistics-icon fas fa-eye"></i>
                <span class="statistics-value">@if(isset($stats['impressions'])) {{$stats['impressions']}} @else - @endif</span>
                <span class="statistics-label">Total Views</span>
            </div>
            <div class="statistics-card">
                <i class="statistics-icon fas fa-edit"></i>
                <span class="statistics-value">@if(isset($stats['copy_events'])) {{$stats['copy_events']}} @else - @endif</span>
                <span class="statistics-label">Total clicks</span>
            </div>
            <div class="statistics-card">
                <i class="statistics-icon fas fa-percentage"></i>
                <span class="statistics-value">@if(isset($stats['impressions'])) {{ calcPercentage($stats['impressions'], $stats['copy_events']) }}% @else - @endif</span>
                <span class="statistics-label">Click Rate</span>
            </div>
            <div class="statistics-card">
                <i class="statistics-icon fas fa-gift"></i>
                <span class="statistics-value">@if(isset($stats['redemptions'])) {{ $stats['redemptions'] }} @else - @endif</span>
                <span class="statistics-label">Coupon Redemptions</span>
            </div>
            <div class="statistics-card" style="display: none;">
                <i class="statistics-icon fas fa-chart-pie"></i>
                <span class="statistics-value">6%</span>
                <span class="statistics-label">% of new users shared their contact</span>
            </div>
        </div>  
        <div class="row col-md-12" style="display:none;">
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
                <a href="#" class="btn btn-success mt-4 mb-2 p-2 save-button" style="float:right;width:40%;background-color:#1B4332">Save Changes</a>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var madeChanges = false;
        $(document).ready(function () {
            setDateTimePicker();
            window.addEventListener("beforeunload", function (e) {
                var confirmationMessage = 'It looks like you have been unsaved changes. Sure to go proceed? '
                if(madeChanges) {
                    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
                }    
            });

            $('.main-content input').on('keyup keypress change', function (e) {
                madeChanges = true;
                $('.save-button-div').css({'display': 'flex'});
            });

            $('.save-button').click(function (e) {
                e.preventDefault();
                saveChanges();
            });
        });

        function saveChanges() {
            var payload = {
                "sale_status": $('#saleStatus').is(':checked') ? 'on':'off',
                "sale_discount_value": $('#sale_discount_value').val(),
                "discount_expiry": $('#discount_expiry').val(),
                "min_value_coupon": $('#min_value_coupon').val()
            };
            $.ajax({
                url: "{{route('update.store.notifications')}}", 
                method: 'POST',
                data: payload,
                async: false,
                success: function (response) {
                    console.log(response);
                    madeChanges = false;
                    proceedToSave = true;
                    if(response.hasOwnProperty('status') && response.status) {
                        $('.save-button').each(function(i, el) {
                            var x = $(el);
                            x.html(response.message);
                        });

                        setTimeout(function() {
                            window.location.reload();  // You used `el`, not `element`?
                        }, 2000);
                    }   
                }
            });
        }

        function updateSettings(thisVar) {
            const field = thisVar.data('fieldname');
            const type = thisVar.data('fieldtype');
            const value = type == 'checkbox' ? (thisVar.is(':checked') ? 'on':'off') : thisVar.val();

            console.log('Field '+field+' Value '+value);
            $.ajax({
                url: "{{route('update.notification.settings')}}", 
                method: 'POST',
                data: {
                    field: field,
                    fieldtype: type,
                    value: value
                },
                async: false,
                success: function (response) {
                    //alert(response.status + ' ' + response.message);
                    if(response.status) {
                        thisVar.parent().css({'border-color': 'green'});
                    }
                }
            })
        }

        function setDateTimePicker() {
            var startDate = @if(isset($start_imp_date) && !is_null($start_imp_date)) moment('{{$start_imp_date}}') @else moment().subtract(14, 'days') @endif;
            var endDate = @if(isset($end_imp_date) && !is_null($end_imp_date)) moment('{{$end_imp_date}}') @else moment() @endif;
            $("#date-start").val(startDate.unix());
            $("#date-end").val(endDate.unix());
            $('#date-range').daterangepicker({
                showDropdowns: true,
                startDate,
                endDate,
                alwaysShowCalendars: true,
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Last 7 Days': [moment().subtract(7, 'days'), moment()],
                    'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'Last 60 Days': [moment().subtract(59, 'days'), moment()],
                    'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                    'Last 180 Days': [moment().subtract(179, 'days'), moment()],
                }
            }, function(start, end, label) {
                start = moment(start).format('YYYY-MM-DD');
                end = moment(end).format('YYYY-MM-DD');
                window.top.location.href= `{{route('notifications.smart.convert.ai')}}?imp_start_date=${start}&imp_end_date=${end}`;
            });
        }
    </script>
@endsection
