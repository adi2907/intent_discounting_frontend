@extends('layouts.new_app')
@section('css')
<link rel="stylesheet" href="{{asset('css/notifications.css')}}">
@endsection
@section('content')
<div class="col-md-9 nopadding">
    <section class="page-title bg-white p-4">
        <div class="title-content">      
            <h1>SmartRecognize</h1>
            <i class="fas fa-brain"></i>
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
            <h2 class="settings-heading">Enable SmartRecognize</h2>
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
                <div class="settings-option" style="display: none;">
                    <div class="settings-text">
                        <span class="settings-card-title">Discount for submitting contact</span>
                        <p class="settings-card-description">Incentivise your users to submit their contact details by giving an exclusive discount</p>
                    </div>
                    <input class="discount-input blurInputChange" data-fieldtype="text" data-fieldName="discount_value" type="number" min="1" max="50" value="{{$notifSettings['discount_value']}}">
                </div>
            </div>
            <p><p>
            <h2 class="settings-heading">SmartRecognize statistics</h2>
            <div class="statistics-container">
                <div class="statistics-card">
                    <i class="statistics-icon fas fa-eye"></i>
                    <span class="statistics-value">@if(isset($stats['total_views'])) {{$stats['total_views']}} @else - @endif</span>
                    <span class="statistics-label">Total Views</span>
                </div>
                <div class="statistics-card">
                    <i class="statistics-icon fas fa-edit"></i>
                    <span class="statistics-value">@if(isset($stats['submissions'])) {{$stats['submissions']}} @else - @endif</span>
                    <span class="statistics-label">Total Submissions</span>
                </div>
                <div class="statistics-card">
                    <i class="statistics-icon fas fa-percentage"></i>
                    <span class="statistics-value">@if(isset($stats['percentage'])) {{ calcPercentage($stats['total_views'], $stats['submissions']) }}% @else - @endif</span>
                    <span class="statistics-label">Submit rate</span>
                </div>
                <div class="statistics-card">
                    <i class="statistics-icon fas fa-user-plus"></i>
                    <span class="statistics-value">-</span>
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
                
                "cdn_logo": $('#cdn_logo').val()
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
