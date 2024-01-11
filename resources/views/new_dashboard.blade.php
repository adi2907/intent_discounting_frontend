@extends('dashboard.layout')
@section('css')
<link rel="stylesheet" href="{{asset('css/dashboard.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
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

        {{-- @if(!$checkScriptRunning)
        <div class="row">
            <div class="col-9 col-md-9 col-lg-9">
                <h2 class="section-heading" style="color:red">   
                    Please turn on the script 
                </h2>
            </div>
            <div class="col-3 col-md-3 col-lg-3">
                <a href="{{route('alme.turn.script.on')}}" target="_blank" class="btn btn-secondary mt-2 mr-2" style="float:right;">Click here</a>
            </div>
        </div>    
        @endif --}}

        <div class="row">
            <div class="col-9 col-md-9 col-lg-9">
                <h2 class="section-heading">   
                    Overview
                </h2>
            </div>
            <div class="col-3 col-md-3 col-lg-3">
                <a href="{{route('show.setup.page')}}" style="float:right" class="btn btn-secondary mt-2 mr-2">Setup the App</a>
            </div>
            <div class="col-3 col-md-3 col-lg-3" style="display: none;">
                Select Date Range: &nbsp;
                <input id="date-range" class="form-control" style="width:80%;border-radius:15%" type="text" name="daterange" value="01/01/2018 - 01/15/2018"/>
                <input type="hidden" id="date-start">
                <input type="hidden" id="date-end">
            </div>
        </div>
        <div class="container-fluid mt-3">
            <!-- <div class="row">
                <div class="col-12 col-md-12 col-lg-12 mb-3">
                    <div class="card metric-card">
                        <div class="card-body">
                            <h4>Click the button to Install the theme blocks on your store! <a href="#" target="_blank" id="themeInstallBtn" class="btn btn-md btn-primary" style="float:right">Click</a></h4>
                        </div>
                    </div>
                </div>    
            </div> -->
            <div class="row text-center mb-4">
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                    <div class="card metric-card">
                        <div class="card-body">
                        <h4 id="session-count-header">@isset($almeResponses['session_count']['body']) {{$almeResponses['session_count']['body']['session_count']}} @endisset</h4>
                        <p>Sessions</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                <div class="card metric-card">
                    <div class="card-body">
                    <h4 id="user-count-header">@isset($almeResponses['user_count']['body']) {{$almeResponses['user_count']['body']['user_count']}} @endisset</h4>
                    <p>Users</p>
                    </div>
                </div>
                </div>
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                <div class="card metric-card">
                    <div class="card-body">
                    <h4 id="visit-count-header">@isset($almeResponses['visits_count']['body']) {{$almeResponses['visits_count']['body']['visit_count']}} @endisset</h4>
                    <p>Visits</p>
                    </div>
                </div>
                </div>
                <div class="col-6 col-md-6 col-lg-3 mb-3">
                <div class="card metric-card">
                    <div class="card-body">
                    <h4 id="cart-count-header">@isset($almeResponses['cart_count']['body']) {{$almeResponses['cart_count']['body']['cart_count']}} @endisset</h4>
                    <p>Cart Adds</p>
                    </div>
                </div>
                </div>
            </div>
            <!-- charts-->
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <div class="text-center">
                        <h5 class="chart-title">Visit Conversion %</h5>
                        <h6 class = "chart-subtitle">Percentage of sessions having at least one product page visit</h6>
                        <div class="container" style="max-width: 500px;max-height:500px">
                            <div>
                                <canvas id="visitConversionGraph" style="background-color: white;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="text-center">
                        <h5 class="chart-title">Cart Conversion %</h5>
                        <h6 class = "chart-subtitle">Percentage of sessions having at least one 'Add to cart'</h6>
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
                        <span class="sort-asc sort-top-visited" data-order="asc">&#8593;</span> / <span class="sort-desc sort-top-visited" data-order="desc" style="color:green;font-weight:bold">&#8595;</span>
                    </div>
                </div>
                <div class="parent-top-visited">
                    @include('dashboard.top_products', [
                        'assoc_data' => $almeResponses['product_visits']['body']['assoc_data'],
                        'products' => $almeResponses['product_visits']['body']['products'],
                        'baseShop' => $baseShop
                    ])  
                </div>
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
                        <span class="sort-asc sort-top-carted" data-order="asc">&#8593;</span> / <span class="sort-desc sort-top-carted" data-order="desc" style="color:green;font-weight:bold">&#8595;</span>
                    </div>
                </div>
                <div class="parent-top-converted">
                @include('dashboard.top_cart_conversions', [
                    'assoc_data' => $almeResponses['product_cart_conversion']['body']['assoc_data'],
                    'products' => $almeResponses['product_cart_conversion']['body']['products'],
                    'baseShop' => $baseShop
                ])
                </div>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    //$.noConflict();
    
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>

    function drawDataTables(tableIds) {
        for(var i in tableIds) {
            if($(tableIds[i]).length) {
                $(tableIds[i]).DataTable({
                    info: false,
                    dom: 'rtip',
                    pageLength: 20,
                    searching: false,
                    drawCallback: function( settings ) {
                        $(tableIds[i]+" thead").remove(); 
                    }
                });
            }
        }
    } 
    
        
    $(document).ready(function () {
        setDateTimePicker();

        drawDataTables(['#topCartTable', '#topVisitedTable']);

        $('.sort-top-carted').click(function (e) {
            e.preventDefault();
            var el = $(this);
            var dir = el.data('order');
            var sortClass = '.sort-top-carted';
            var productCardClass = '.product-cart-converted';
            var parentsProductClass = '.parent-top-converted';
            $(sortClass).css({"color":"black"});
            el.css({"color":"green"});
            $(productCardClass).css({"opacity": "50%"})
            $.ajax({
                type: 'GET',
                url: "{{route('order.top.carted')}}",
                data: {"order": dir},
                success: function (response) {
                    if(response.status) {
                        $(parentsProductClass).html(response.html);
                        drawDataTables(['#topCartTable', '#topVisitedTable']);
                    }
                }
            })
            $(productCardClass).css({"opacity": "100%"})
        })

        $('.sort-top-visited').click(function (e) {
            e.preventDefault();
            var el = $(this);
            var dir = el.data('order');
            var sortClass = '.sort-top-visited';
            var productCardClass = '.top-visited-product';
            var parentsProductClass = '.parent-top-visited';
            $(sortClass).css({"color":"black"});
            el.css({"color":"green"});
            $(productCardClass).css({"opacity": "50%"})
            $.ajax({
                type: 'GET',
                url: "{{route('order.top.visited')}}",
                data: {"order": dir},
                success: function (response) {
                    if(response.status) {
                        $(parentsProductClass).html(response.html);
                    }
                }
            })
            $(productCardClass).css({"opacity": "100%"})
        })
        /*
        var themeInstalled = checkThemeInstallation();
        if(themeInstalled) {
            $('#themeInstallBtn').html('Installed!').removeClass('btn-primary').addClass('btn-success').removeAttr('href');
        }
        */

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

    function reloadDashboardWithDateRange(start, end) {
        console.log(start);
        console.log(end);

        $.ajax({
            url: "{{route('reload.dashboard')}}",
            type: 'GET',
            async: false,
            data: {start: start, end:end},
            success: function (res) {
                console.log(res);
                if(res.status) {
                    $('#session-count-header').html(res.response.session_count.body.session_count);
                    $('#user-count-header').html(res.response.user_count.body.user_count);
                    $('#visit-count-header').html(res.response.visits_count.body.visit_count);
                    $('#cart-count-header').html(res.response.cart_count.body.cart_count);
                }
            }
        })
    }

    function setDateTimePicker() {
        var startDate = moment().subtract(14, 'days');
        var endDate = moment();
        $("#date-start").val(startDate.unix());
        $("#date-end").val(endDate.unix());
        $('#date-range').daterangepicker({
          opens: 'left',
          showDropdowns: true,
          startDate,
          endDate,
          orientation: 'bottom',
          alwaysShowCalendars: true,
          drops: 'down',
          locale: {
            format: 'L',
            // cancelLabel: 'Clear'
          },
          ranges: {
            'Last 15 Days': [moment().subtract(14, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'Last 60 Days': [moment().subtract(59, 'days'), moment()],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'Last 180 Days': [moment().subtract(179, 'days'), moment()],
          }
        }, function(start, end, label) {
            reloadDashboardWithDateRange(Math.round(start/1000), Math.round(end/1000));
        });
    }

</script>
@endsection
 
    