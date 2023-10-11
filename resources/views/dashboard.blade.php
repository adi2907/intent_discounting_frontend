@extends('layouts.app')
@section('content')
<div class="Polaris-Page">
    <div class="Polaris-Page__Content">
        <div class="Polaris-Page__Content">
            <!--App Embed Block display-->
            <div class="Polaris-Layout">
                <!-- App enable/disable section start -->
                <div class="Polaris-Layout__AnnotatedSection">
                    <div class="Polaris-Layout__AnnotationWrapper">
                        <div class="Polaris-Layout__AnnotationContent">
                            <div class="Polaris-Card">
                                <div class="Polaris-Card__Section">
                                    <div class="Polaris-SettingAction">
                                        <div class="Polaris-SettingAction__Setting sub-main-heading" style="display: inline-block">
                                            Activate the app in your theme to get started. 
                                            </br>
                                            Click 'Activate App' below or navigate to Online Store > Customize Theme > Theme Settings > App embeds > Shipping Restrictions > Save 
                                        </div>
                                        <div class="Polaris-SettingAction__Action hider-btn-content" style="display: inline-block">
                                            <a href="#">
                                                <button type="button" class="Polaris-Button Polaris-Button--primary deactive-hider-btn loading-btn"  style="display: inline-block">
                                                    <span class="Polaris-Button__Content"><span>Activate App</span></span>
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function(){
        $("#restrict_shipping").submit(function(){
            event.preventDefault();
            $.ajax({
                url: "/shopifyapp/apis/save_zipcode.php",
                data: $("#restrict_shipping").serialize(),
                type: "POST",
                dataType: 'json',
                success: function (e) {
                    location.reload();
                    console.log(JSON.stringify(e));
                },
                error:function(e){
                    console.log(JSON.stringify(e));
                }
            });
            return false;
        });
    });
</script>
@endsection