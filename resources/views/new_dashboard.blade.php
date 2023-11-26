@extends('layouts.new_app')
@section('css')
<link rel="stylesheet" href="{{asset('css/dashboard.css')}}">
@endsection
@section('content')

<div class="col-md-9 nopadding">
    <section class="page-title bg-white p-4">
        <div class="title-content">
            <h1>Dashboard</h1>
            <i class="fas fa-tachometer"></i>
        </div>
    </section>
    <section class="main-content">
        <h2 class="section-heading">Overview</h2>
        <div class="container-fluid mt-3">
            <!-- Metric cards-->
            <div class="row text-center mb-4">
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                <div class="card metric-card">
                    <div class="card-body">
                    <h4>@isset($almeResponses['session_count']['body']) {{$almeResponses['session_count']['body']['session_count']}} @endisset</h4>
                    <p>Sessions</p>
                    </div>
                </div>
                </div>
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                <div class="card metric-card">
                    <div class="card-body">
                    <h4>@isset($almeResponses['user_count']['body']) {{$almeResponses['user_count']['body']['user_count']}} @endisset</h4>
                    <p>Users</p>
                    </div>
                </div>
                </div>
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                <div class="card metric-card">
                    <div class="card-body">
                    <h4>@isset($almeResponses['visits_count']['body']) {{$almeResponses['visits_count']['body']['visit_count']}} @endisset</h4>
                    <p>Visits</p>
                    </div>
                </div>
                </div>
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                <div class="card metric-card">
                    <div class="card-body">
                    <h4>@isset($almeResponses['cart_count']['body']) {{$almeResponses['cart_count']['body']['cart_count']}} @endisset</h4>
                    <p>Cart Adds</p>
                    </div>
                </div>
                </div>
            </div>
            <!-- charts-->
            <div class="row">
                <div class="col-12 col-md-4 mb-3">
                    <div class="text-center">
                    <h5 class="chart-title">Visit Conversion%</h5>
                    <img src="images/visits.png" alt="Visit Conversion Chart" class="img-fluid mt-2">
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <div class="text-center">
                    <h5 class="chart-title">Cart Conversion%</h5>
                    <img src="images/conversion.png" alt="Cart Conversion Chart" class="img-fluid mt-2">
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <div class="text-center">
                    <h5 class="chart-title">Checkout Conversion%</h5>
                    <img src="images/checkout.png" alt="Checkout Chart" class="img-fluid mt-2">
                    </div>
                </div>
                </div>
        </div>
        <h2 class="section-heading">Products</h2>
        <div class="container-fluid mt-3">
            <!-- Products-->
            <div class="row mb-3">
            <!-- Products visited stats -->
            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="section-title">Visited</h2>
                    <div class="sort-toggle">
                        <span class="sort-asc">&#8593;</span> / <span class="sort-desc">&#8595;</span>
                    </div>
                </div>
                
                <div class="card visited-card">
                    <div class="card-body d-flex align-items-center">
                        <img src="images/prod1.png" alt="Relaxed Fit T-Shirt" class="product-image">
                        <div class="product-details">
                            <h3 class="product-title">Relaxed Fit T-Shirt</h3>
                        </div>
                        <div class="visit-count-border d-flex flex-column align-items-center justify-content-center">
                            <span class="visit-number">13476</span>
                            <span class="visits-label">Visits</span>
                        </div>
                    </div>
                </div>
                <div class="card visited-card">
                    <div class="card-body d-flex align-items-center">
                        <img src="images/prod1.png" alt="Relaxed Fit T-Shirt" class="product-image">
                        <div class="product-details">
                            <h3 class="product-title">Relaxed Fit T-Shirt</h3>
                        </div>
                        <div class="visit-count-border d-flex flex-column align-items-center justify-content-center">
                            <span class="visit-number">13476</span>
                            <span class="visits-label">Visits</span>
                        </div>
                    </div>
                </div>
                    
                <div class="text-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>
                
            </div>


            @isset($almeResponses['product_cart_conversion']['body']) 
            @if(is_array($almeResponses['product_cart_conversion']['body']['products']) && count($almeResponses['product_cart_conversion']['body']['products']) > 0)
            <!-- Products conversion stats -->
            <div class="col-12 col-md-6">
                
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="section-title">Cart conversions</h2>
                    <div class="sort-toggle">
                        <span class="sort-asc">&#8593;</span> / <span class="sort-desc">&#8595;</span>
                    </div>
                </div>
                    
                @foreach($almeResponses['product_cart_conversion']['body']['products'] as $productId => $product)    
                <div class="card conversion-card">
                    <div class="card-body d-flex align-items-center">
                        <img src="{{$product->imageSrc}}" alt="Floral T-Shirt" class="product-image">    
                        <h3 class="product-title"><a href="https://admin.shopify.com/{{str_replace('.shopify.com', null, $baseShop->shop_url)}}/products/{{$productId}}">{{$product->title}}</a></h3>
                        <div class="ml-auto conversion-rates">
                            <div class="conversion-rate cart-conversion mr-4">
                                @if(isset($almeResponses['product_cart_conversion']['body']['assoc_data']) && array_key_exists($productId, $almeResponses['product_cart_conversion']['body']['assoc_data']))
                                    <span class="percentage">{{$almeResponses['product_cart_conversion']['body']['assoc_data'][$productId]['conversion_rate']}}%</span>
                                @else 
                                    <span class="percentage">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @enforeach
                <div class="text-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            @endif
            @endisset

            
            </div>
        </div>
    </section>
</div>
@endsection
    