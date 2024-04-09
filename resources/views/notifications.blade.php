@extends('layouts.new_app')
@section('css')
<link rel="stylesheet" href="{{asset('css/notifications.css')}}">
@endsection
@section('content')
<div class="col-md-9 nopadding">
    <section class="page-title bg-white p-4">
        <div class="title-content">      
            <h1>Notifications</h1>
            <i class="fas fa-message"></i>
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
            <h2 class="settings-heading">Get contact details</h2>
            <div class="settings-card">
                <div class="settings-option">
                    <div>
                        <span class="settings-card-title">Contact Capture Notification</span>
                        <p class="settings-card-description">In-site notification collects contacts for WhatsApp conversion</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="inputChange" id="contactCaptureCheckbox" data-fieldtype="checkbox" data-fieldName="status" @if(isset($notifSettings['status']) && $notifSettings['status'] == 1) checked @endif>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Brand Logo</span>
                        <p class="settings-card-description">Give the url (CDN link) for your brand logo to display on the notification</p>
                    </div>
                    <input class="contact-input blurInputChange" type="text" id="cdn_logo" data-fieldtype="text" data-fieldName="logo" value="{{$notifSettings['cdn_logo']}}" placeholder="Enter CDN logo here...">
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Notification Title</span>
                        <p class="settings-card-description">Give a catchy title to display on top of the notification</p>
                    </div>
                    <input class="contact-input blurInputChange" type="text" id="notification_title" data-fieldtype="text" data-fieldName="title" value="{{$notifSettings['title']}}" placeholder="Become an Insider to our store. Exclusive updates await">
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Notification Description</span>
                        <p class="settings-card-description">Highlight the value for your users when they submit their contact details</p>
                    </div>
                    <input class="contact-input blurInputChange" id="notification_desc" data-fieldtype="text" data-fieldName="description" type="text" value="{{$notifSettings['description']}}" placeholder="Receive Whatsapp notifications on New Collections">
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Re-engage Users with Contact Notification</span>
                        <p class="settings-card-description">Option to display notification again after user dismissal.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="inputChange" id="reengageCheckbox" data-fieldtype="checkbox" data-fieldName="status" @if(isset($notifSettings['re_engage_flag']) && $notifSettings['re_engage_flag'] == 1) checked @endif>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="settings-option">
                    <div class="settings-text">
                        <span class="settings-card-title">Timed Notification Re-display</span>
                        <p class="settings-card-description">Set interval for notification re-appearance post closure.</p>
                    </div>
                    <input class="discount-input blurInputChange" style="width:10%" id="timed_interval" data-fieldtype="text" data-fieldName="re_engage_timed_interval" type="number" min="1" max="50" value="{{$notifSettings['re_engage_timed_interval']}}">
                </div>
                <div class="settings-option" style="display: none;">
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
            </div>
        </div>  
        <div class="row col-md-12 save-button-div" style="display:none;">
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
        var madeChanges = false;
        $(document).ready(function () {
            /*
            $('.blurInputChange').blur(function (e) {
                e.preventDefault();
                updateSettings($(this));
            });

            $('.inputChange').change(function (e) {
                e.preventDefault();
                updateSettings($(this));
            });
            */

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
                "status": $('#contactCaptureCheckbox').is(':checked') ? 'on':'off',
                "notification_title": $('#notification_title').val(),
                "notification_desc": $('#notification_desc').val(),
                "sale_status": $('#saleStatus').is(':checked') ? 'on':'off',
                "sale_discount_value": $('#sale_discount_value').val(),
                "discount_expiry": $('#discount_expiry').val(),
                "cdn_logo": $('#cdn_logo').val(),
                "re_engage_flag": $('#reengageCheckbox').is(':checked') ? 'on':'off',
                "timed_interval": $('#timed_interval').val()
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
    </script>
@endsection