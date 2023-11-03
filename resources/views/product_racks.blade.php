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
        <div class="options-box p-4 mt-4">
            <h3 style="font-family: 'montserrat'"><strong>Product Page Collections</h3>
            <h5 style="font-family: 'montserrat'">Product collections displayed after product details on product page</h5>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="customSuggestions">
                        <label class="form-check-label" for="customSuggestions">
                            <span class="productrack-title">Users also liked</span><br>
                            <span class="productrack-description">Show products most viewed together with this product</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label" for="toggleSuggestions">
                            <span class="productrack-title">Crowd Favorites</span><br>
                            <span class="productrack-description">Show products which have highest conversion</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label" for="styleSuggestions">
                            <span class="productrack-title">Popular Picks</span><br>
                            <span class="productrack-description"> Show products added to cart the most</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label" for="crowdItems">
                            <span class="productrack-title">Featured collection</span><br>
                            <span class="productrack-description"> Help sell slow-moving inventory with high conversion</span>
                        </label>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="options-box p-4 mt-4">
            <h3 style="font-family: 'montserrat'"><strong>Home Page Collections</h3>
                <h5 style="font-family: 'montserrat'">Tailormade suggestions for your users on the home page</h5>
                
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label" for="customSuggestions">
                            <span class="productrack-title">Pick up where you left off</span><br>
                            <span class="productrack-description">Nudge users to resume previous browsing activity</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label" for="toggleSuggestions">
                            <span class="productrack-title">Crowd Favorites</span><br>
                            <span class="productrack-description">Show products which have highest conversion</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label" for="styleSuggestions">
                            <span class="productrack-title">Popular Picks</span><br>
                            <span class="productrack-description"> Show products added to cart the most</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label" for="crowdItems">
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