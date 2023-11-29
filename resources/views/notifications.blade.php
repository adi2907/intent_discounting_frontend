@extends('layouts.new_app')
@section('css')
<link rel="stylesheet" href="{{asset('css/notifications.css')}}">
@endsection
@section('content')
<div class="col-md-10 nopadding">
    <section class="page-title bg-white p-4">
        <div class="title-content">      
            <h1>Notifications</h1>
            <i class="fas fa-message"></i>
        </div>
    </section>
    <section class="main-content">
        <div class="container mt-5">
            <h2 class="settings-heading">Get contact details</h2>
            <div class="settings-card">
                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Contact Capture Notification</span>
                        <p class="settings-card-description">In-site notification collects contacts for WhatsApp conversion</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="inputChange" data-fieldtype="checkbox" data-fieldName="status" @if(isset($notifSettings['status']) && $notifSettings['status'] == 1) checked @endif>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Notification Title</span>
                        <p class="settings-card-description">Give a catchy title to display on top of the notification</p>
                    </div>
                    <input class="contact-input blurInputChange" type="text" data-fieldtype="text" data-fieldName="title" value="{{$notifSettings['title']}}" placeholder="Become an Insider to our store. Exclusive updates await">
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Notification Description</span>
                        <p class="settings-card-description">Highlight the value for your users when they submit their contact details</p>
                    </div>
                    <input class="contact-input blurInputChange" data-fieldtype="text" data-fieldName="description" type="text" value="{{$notifSettings['description']}}" placeholder="Receive Whatsapp notifications on New Collections">
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Discount for submitting contact</span>
                        <p class="settings-card-description">Incentivise your users to submit their contact details by giving an exclusive discount</p>
                    </div>
                    <input class="discount-input blurInputChange" data-fieldtype="text" data-fieldName="discount_value" type="number" min="1" max="50" value="{{$notifSettings['discount_value']}}">
                </div>
            </div>
            <h2 class="settings-heading">Enable Sale Notifications</h2>
            <h2></h2>
            <div class="settings-card">
                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Enable Sale Notifications</span>
                        <p class="settings-card-description">Enable personalised sale alerts based on browsing behaviour</p>
                    </div>
                    
                    <label class="switch">
                        <input type="checkbox" class="inputChange" data-fieldtype="checkbox" data-fieldName="sale_status" @if(isset($notifSettings['sale_status']) && $notifSettings['sale_status'] == 1) checked @endif>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Discount (%age) for coupons</span>
                        <p class="settings-card-description">Specify how much discount to give using coupons on your store</p>
                    </div>
                    <input class="discount-input blurInputChange" data-fieldtype="text" data-fieldName="sale_discount_value" type="number" min="1" max="50" value="{{$notifSettings['sale_discount_value']}}">
                </div>

                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Coupon validity(hours)</span>
                        <p class="settings-card-description">Specify the validity of the dynamically generated coupon in hours</p>
                        <p class="settings-card-description">Recommended to keep between 6 and 24 hours </p>
                    </div>
                    <input class="validity-input blurInputChange" data-fieldtype="text" data-fieldName="discount_expiry" type="number" min="1" max="100" value="{{$notifSettings['discount_expiry']}}">
                </div>
            </div>
        </div>  
    </section>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.blurInputChange').blur(function (e) {
                e.preventDefault();
                updateSettings($(this));
            });

            $('.inputChange').change(function (e) {
                e.preventDefault();
                updateSettings($(this));
            })
        });

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
    </script>
@endsection