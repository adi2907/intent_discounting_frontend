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
        <div class="options-box productpage-collections p-4 mt-4" id="productpage-collections">
            <h3 style="font-family: 'montserrat'"><strong>Product Page Collections</h3>
            <h5 style="font-family: 'montserrat'">Product collections displayed after product details on product page</h5>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="usersAlsoLiked" class="form-check-input" id="customSuggestions" @if($productRackInfo['usersAlsoLiked'] && $productRackInfo['usersAlsoLiked'] == true) checked @endif>
                        <label class="form-check-label" for="customSuggestions">
                            <span class="productrack-title">Users also liked</span><br>
                            <span class="productrack-description">Show products most viewed together with this product</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="crowd_fav" class="form-check-input" @if($productRackInfo['crowd_fav'] && $productRackInfo['crowd_fav'] == true) checked @endif>
                        <label class="form-check-label" for="toggleSuggestions">
                            <span class="productrack-title">Crowd Favorites</span><br>
                            <span class="productrack-description">Show products which have highest conversion</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="pop_picks" class="form-check-input" @if($productRackInfo['pop_picks'] && $productRackInfo['pop_picks'] == true) checked @endif>
                        <label class="form-check-label" for="styleSuggestions">
                            <span class="productrack-title">Popular Picks</span><br>
                            <span class="productrack-description"> Show products added to cart the most</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="featuredCollection" class="form-check-input" @if($productRackInfo['featuredCollection'] && $productRackInfo['featuredCollection'] == true) checked @endif>
                        <label class="form-check-label" for="crowdItems">
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
                        <input type="checkbox" data-field="pickUpWhereYouLeftOff" class="form-check-input" @if($productRackInfo['pickUpWhereYouLeftOff'] && $productRackInfo['pickUpWhereYouLeftOff'] == true) checked @endif>
                        <label class="form-check-label" for="">
                            <span class="productrack-title">Pick up where you left off</span><br>
                            <span class="productrack-description">Nudge users to resume previous browsing activity </span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" data-field="crowdFavorites" class="form-check-input" @if($productRackInfo['crowdFavorites'] && $productRackInfo['crowdFavorites'] == true) checked @endif>
                        <label class="form-check-label" for="">
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
    </section>
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@simondmc/popup-js@1.4.2/popup.min.js"></script>
    <script>
        $(document).ready(function () {
            var homeflag = ifUncheckedCheckboxesShouldBeDisabled('homepage-collections')
            var productflag = ifUncheckedCheckboxesShouldBeDisabled('productpage-collections');
            toggleCheckboxesThatAreUnchecked('homepage-collections', homeflag ? true : false);
            toggleCheckboxesThatAreUnchecked('productpage-collections', productflag ? true : false);

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
                    if(response.status) {
                        var myPopup = new Popup({
                            id: "my-popup",
                            title: response.message ,
                            content: null   
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
                }
            });
        }
    </script>
@endsection