@extends('layouts.new_app')
@section('css')
<link rel="stylesheet" href="{{asset('css/product_rack.css')}}">
@endsection
@section('content')
<div class="col-md-9 nopadding">
    <section class="page-title bg-white p-4">
        <div class="title-content">      
            <h1>Product Collection</h1>
            <i class="fas fa-shopping-bag"></i>
        </div>
    </section>
    <section class="main-content">
        <div class="row col-md-12 save-button-div" style="display:none;">
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
                <a href="#" class="btn btn-success mt-2 mb-2 p-2 save-button" style="float:right;width:40%;background-color:#1B4332">Save Changes</a>
            </div>
        </div>
        <div class="options-box productpage-collections p-4 mt-4" id="productpage-collections">
            <h3 style="font-family: 'montserrat'"><strong>Product Page Collections</h3>
            <h5 style="font-family: 'montserrat'">Product collections displayed after product details on product page</h5>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="usersAlsoLiked" class="form-check-input" id="usersAlsoLiked" @if($productRackInfo['usersAlsoLiked'] && $productRackInfo['usersAlsoLiked'] == true) checked @endif>
                        <label class="form-check-label" for="usersAlsoLiked">
                            <span class="productrack-title">Users also liked</span><br>
                            <span class="productrack-description">Show products most viewed together with this product</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="crowdFav" class="form-check-input" @if($productRackInfo['crowd_fav'] && $productRackInfo['crowd_fav'] == true) checked @endif>
                        <label class="form-check-label" for="crowdFav">
                            <span class="productrack-title">Crowd Favorites</span><br>
                            <span class="productrack-description">Show products which have highest conversion</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" data-field="popPicks" id="popPicks" @if($productRackInfo['pop_picks'] && $productRackInfo['pop_picks'] == true) checked @endif>
                        <label class="form-check-label" for="popPicks">
                            <span class="productrack-title">Popular Picks</span><br>
                            <span class="productrack-description"> Show products added to cart the most</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="featuredCollection" id="featuredCollection" class="form-check-input" @if($productRackInfo['featuredCollection'] && $productRackInfo['featuredCollection'] == true) checked @endif>
                        <label class="form-check-label" for="featuredCollection">
                            <span class="productrack-title">Featured collection</span><br>
                            <span class="productrack-description"> Help sell slow-moving inventory with high conversion</span>
                        </label>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="options-box homepage-collections p-4 mt-4" id="homepage-collections">
            <h3 style="font-family: 'montserrat'"><strong>Home Page Collections</h3>
                <h5 style="font-family: 'montserrat'">Tailormade suggestions for your users on the home page</h5>
                
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="pickUpWhereYouLeftOff" id="pickUpWhereYouLeftOff" class="form-check-input" @if($productRackInfo['pickUpWhereYouLeftOff'] && $productRackInfo['pickUpWhereYouLeftOff'] == true) checked @endif>
                        <label class="form-check-label" for="pickUpWhereYouLeftOff">
                            <span class="productrack-title">Pick up where you left off</span><br>
                            <span class="productrack-description">Nudge users to resume previous browsing activity </span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="crowdFavorites" id="crowdFavorites" class="form-check-input" @if($productRackInfo['crowdFavorites'] && $productRackInfo['crowdFavorites'] == true) checked @endif>
                        <label class="form-check-label" for="crowdFavorites">
                            <span class="productrack-title">Crowd Favorites</span><br>
                            <span class="productrack-description">Show products which have highest conversion </span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="most_added_prods" class="form-check-input" @if($productRackInfo['most_added_prods'] && $productRackInfo['most_added_prods'] == true) checked @endif>
                        <label class="form-check-label" for="">
                            <span class="productrack-title">Popular Picks</span><br>
                            <span class="productrack-description"> Show products added to cart the most</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="slow_inv" class="form-check-input" @if($productRackInfo['slow_inv'] && $productRackInfo['slow_inv'] == true) checked @endif>
                        <label class="form-check-label" for="">
                            <span class="productrack-title">Featured collection</span><br>
                            <span class="productrack-description"> Help sell slow-moving inventory with high conversion</span>
                        </label>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/@simondmc/popup-js@1.4.2/popup.min.js"></script>
    <script>
        var madeChanges = false;

        $(document).ready(function () {
            var homeflag = ifUncheckedCheckboxesShouldBeDisabled('homepage-collections')
            var productflag = ifUncheckedCheckboxesShouldBeDisabled('productpage-collections');
            toggleCheckboxesThatAreUnchecked('homepage-collections', homeflag ? true : false);
            toggleCheckboxesThatAreUnchecked('productpage-collections', productflag ? true : false);

            window.addEventListener("beforeunload", function (e) {
                var confirmationMessage = 'It looks like you have been unsaved changes. Sure to go proceed? '
                if(madeChanges) {
                    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
                }    
            });

            $('.form-check-input').change(function (e) {
                madeChanges = true;
                $('.save-button-div').css({'display': 'flex'});
            });

            $('.save-button').click(function (e) {
                e.preventDefault();
                saveChanges();
            });
            /*
            $('.form-check-input').change(function (e) {   
                e.preventDefault();
                var el = $(this);
                const field = el.data('field');
                const value = el.is(':checked') ? 'on':'off';
                var parentId = el.parent().parent().parent().parent().parent().attr('id');
                var disableCheckboxes = ifUncheckedCheckboxesShouldBeDisabled(parentId);
                toggleCheckboxesThatAreUnchecked(parentId, disableCheckboxes ? true : false);
                makeTheAjaxCall(el, field, value)
            });
            */
        });

        function toggleCheckboxesThatAreUnchecked(parentId, flag) {
            var element = '#'+parentId;
            var checkBoxEl = $(element+' input[type=checkbox]:not(:checked)')
            if(flag) 
                checkBoxEl.attr('disabled', 'disabled');
            else 
                checkBoxEl.removeAttr('disabled');
            return true;
        }

        function saveChanges() {
            var payload = {
                "usersAlsoLiked": $('#usersAlsoLiked').is(':checked') ? 'on':'off',
                "featuredCollection": $('#featuredCollection').is(':checked') ? 'on':'off',
                "pickUpWhereYouLeftOff": $('#pickUpWhereYouLeftOff').is(':checked') ? 'on':'off',
                "crowdFavorites": $('#crowdFavorites').is(':checked') ? 'on':'off'
            };
            $.ajax({
                url: "{{route('update.product.racks')}}", 
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
                        }, 3000);
                    }   
                }
            });
            
            /*
            $.ajax({
                url: "{{route('update.product.rack.settings')}}", 
                method: 'POST',
                data: payload,
                async: false,
                success: function (response) {
                    console.log(response);
                }
            });
            */
        }

        function ifUncheckedCheckboxesShouldBeDisabled(parentId) {
            var element = '#'+parentId;
            var count = $(element+' input[type=checkbox]:not(:checked)').length
            var result = count <= 2;
            return result;
        }

        function makeTheAjaxCall(el, field, value) {
            $.ajax({
                url: "{{route('update.product.rack.settings')}}", 
                method: 'POST',
                data: {
                    field: field,
                    value: value
                },
                async: false,
                success: function (response) {
                    console.log(response);
                    /*
                    if(response.status) {
                        var myPopup = new Popup({
                            id: "my-popup",
                            title: response.message ,
                            content: ""   
                        });
                        myPopup.show();
                    } else {
                        el.prop('checked', false);
                        var myPopup = new Popup({
                            id: "my-popup",
                            title: response.message,
                            content: response.htmlContent    
                        });
                        myPopup.show();
                    }
                    */
                }
            });
        }
    </script>
@endsection