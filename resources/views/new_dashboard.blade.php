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
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12 mb-3">
                    <div class="card metric-card">
                        <div class="card-body">
                            <h4>Click the button to Install the theme blocks on your store! <a href="#" target="_blank" id="themeInstallBtn" class="btn btn-md btn-primary" style="float:right">Click</a></h4>
                        </div>
                    </div>
                </div>    
            </div>
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
                <div class="col-12 col-md-6 mb-3">
                    <div class="text-center">
                        <h5 class="chart-title">Visit Conversion%</h5>
                        <div class="container" style="max-width: 500px;max-height:500px">
                            <div>
                                <canvas id="visitConversionGraph" style="background-color: white;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="text-center">
                        <h5 class="chart-title">Cart Conversion%</h5>
                        <div class="container" style="max-width: 500px;max-height:500px">
                            <div>
                                <canvas id="cartConversionGraph" style="background-color: white;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-3" style="display: none;">
                    <div class="text-center">
                        <h5 class="chart-title">Checkout Conversion%</h5>
                        <div class="container" style="max-width: 500px;max-height:500px">
                            <div>
                                <canvas id="myChart3"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
        </div>
        <h2 class="section-heading">Products</h2>
        <div class="container-fluid mt-3">
            <!-- Products-->
            <div class="row mb-3">
            @isset($almeResponses['product_visits']['body']) 
            @if(is_array($almeResponses['product_visits']['body']['products']) && count($almeResponses['product_visits']['body']['products']) > 0)
        
            <!-- Products visited stats -->
            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="section-title">Top Visited</h2>
                    <div class="sort-toggle">
                        <span class="sort-asc">&#8593;</span> / <span class="sort-desc">&#8595;</span>
                    </div>
                </div>
                @foreach($almeResponses['product_visits']['body']['products'] as $productId => $product)  
                <div class="card visited-card">
                    <div class="card-body d-flex align-items-center">
                        <img src="{{$product['imageSrc']}}" alt="Floral T-Shirt" class="product-image">   
                        <div class="product-details">
                            <h3 class="product-title" style="color:black"><a  style="color:black;font-family:'Montserrat'" target="_blank" href="https://admin.shopify.com/store/{{explode('.', $baseShop['shop_url'])[0]}}/products/{{$productId}}">{{$product['title']}}</a></h3>
                        </div>
                        <div class="visit-count-border d-flex flex-column align-items-center justify-content-center">
                            <!-- <span class="visit-number">13476</span> -->
                            @if(isset($almeResponses['product_visits']['body']['assoc_data']) && array_key_exists($productId, $almeResponses['product_visits']['body']['assoc_data']))
                                <span class="visit-number" style="font-size: 1rem !important;">{{$almeResponses['product_visits']['body']['assoc_data'][$productId]}}</span>
                            @else 
                                <span class="visit-number">N/A</span>
                            @endif
                            <span class="visits-label">Visits</span>
                        </div>
                    </div>
                </div>
                @endforeach  
                {{--<div class="text-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>--}}
                
            </div>
            @endif
            @endisset

            @isset($almeResponses['product_cart_conversion']['body']) 
            @if(is_array($almeResponses['product_cart_conversion']['body']['products']) && count($almeResponses['product_cart_conversion']['body']['products']) > 0)
            <!-- Products conversion stats -->
            <div class="col-12 col-md-6">
                
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h2 class="section-title">Top Cart conversions</h2>
                    <div class="sort-toggle">
                        <span class="sort-asc">&#8593;</span> / <span class="sort-desc">&#8595;</span>
                    </div>
                </div>
                @foreach($almeResponses['product_cart_conversion']['body']['assoc_data'] as $productId => $data)    
                <div class="card conversion-card">
                    <div class="card-body d-flex align-items-center">
                        <img src="{{$almeResponses['product_cart_conversion']['body']['products'][$productId]['imageSrc']}}" alt="Floral T-Shirt" class="product-image">    
                        <h3 class="product-title"><a style="color:black;font-family:'Montserrat'" target="_blank" href="https://admin.shopify.com/store/{{explode('.', $baseShop['shop_url'])[0]}}/products/{{$productId}}">{{$almeResponses['product_cart_conversion']['body']['products'][$productId]['title']}}</a></h3>
                        <div class="ml-auto conversion-rates">
                            <div class="conversion-rate cart-conversion mr-4">
                                @if(isset($almeResponses['product_cart_conversion']['body']['assoc_data']) && array_key_exists($productId, $almeResponses['product_cart_conversion']['body']['assoc_data']))
                                    <span class="percentage" style="font-size: 1rem !important;">{{round($data['conversion_rate'], 2)}}%</span>
                                @else 
                                    <span class="percentage">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                {{--<div class="text-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>--}}
            </div>
            @endif
            @endisset

            
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        var themeInstalled = checkThemeInstallation();
        if(themeInstalled) {
            $('#themeInstallBtn').html('Installed!').removeClass('btn-primary').addClass('btn-success').removeAttr('href');
        }

        const graphOptions = {
            responsive: true,
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 20,
                    bottom: 0
                },
            },
            animation: {
                duration: 1,
                onComplete: function({ chart }) {
                    const ctx = chart.ctx;

                    chart.config.data.datasets.forEach(function(dataset, i) {
                    const meta = chart.getDatasetMeta(i);

                    meta.data.forEach(function(bar, index) {
                        const data = dataset.data[index];

                        ctx.fillText(data, bar.x, bar.y - 5);
                    });
                    });
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                },
            }
        }
        const visitConversionGraph = document.getElementById('visitConversionGraph');
        new Chart(visitConversionGraph, {
            type: 'bar',
            data: {
            labels: @if(isset($almeResponses['visit_conversion']['graphData'])) {!! $almeResponses['visit_conversion']['graphData']['yAxis'] !!} @else [] @endif,
            datasets: [{
                backgroundColor: ['#3A5D59'],
                //label: 'Visit Conversion %',
                data: @if(isset($almeResponses['visit_conversion']['graphData'])) {!! $almeResponses['visit_conversion']['graphData']['xAxis'] !!} @else [] @endif,
                borderWidth: 1
            }]
            },
            options: graphOptions
        });

        const cartConversionGraph = document.getElementById('cartConversionGraph');
        new Chart(cartConversionGraph, {
            type: 'bar',
            data: {
            labels: @if(isset($almeResponses['cart_conversion']['graphData'])) {!! $almeResponses['cart_conversion']['graphData']['yAxis'] !!} @else [] @endif,
            datasets: [{
                backgroundColor: ['#3A5D59'],
                label: 'Cart conversion %',
                data: @if(isset($almeResponses['cart_conversion']['graphData'])) {!! $almeResponses['cart_conversion']['graphData']['xAxis'] !!} @else [] @endif,
                borderWidth: 1
            }]
            },
            options: graphOptions
        });

        const ctx3 = document.getElementById('myChart3');
        new Chart(ctx3, {
            type: 'bar',
            data: {
            labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
            datasets: [{
                backgroundColor: ['#3A5D59'],
                label: '# of Votes',
                data: [4, 15, 2, 1, 11, 3],
                borderWidth: 1
            }]
            },
            options: {
            scales: {
                y: {
                beginAtZero: true
                }
            }
            }
        });
    }) 

    function checkThemeInstallation() {
        var result = false;

        $.ajax({
            type: 'GET',
            url: "{{route('store.check.theme.installation')}}",
            async:false,
            success: function (response) {
                if(response.status) {
                    result = true;
                } else {
                    $('#themeInstallBtn').html('Install here').attr('href', response.themeEditorURL);
                }
            }
        })

        return result;
    }

</script>
@endsection
 
    