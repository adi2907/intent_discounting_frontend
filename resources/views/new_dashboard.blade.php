@extends('layouts.new_app')
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
        <div class="overview row">
            <!-- Using Bootstrap classes for responsiveness -->
            <div class="metric-box col-4 col-md-2">
                <span class="metric-value">9623</span>
                <span class="metric-title">Sessions</span>
            </div>
            <div class="metric-box col-4 col-md-2">
                <span class="metric-value">5623</span>
                <span class="metric-title">Users</span>
            </div>
            <div class="metric-box col-4 col-md-2">
                <span class="metric-value">850</span>
                <span class="metric-title">Visits</span>
            </div>
            <div class="metric-box col-4 col-md-2">
                <span class="metric-value">516</span>
                <span class="metric-title">Cart Adds</span>
            </div>
            <div class="metric-box col-4 col-md-2">
                <span class="metric-value">323</span>
                <span class="metric-title">Purchases</span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <span class="metric-title ">Visits Conversion%</span>
                <div class="metric-card"> 
                    <img src="images/visits.png" alt="Visit Conversion Chart" class="img-fluid metric-chart">
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <h5>Cart Conversion%</h5>
                    <img src="images/carts.png" alt="Cart Conversion Chart" class="img-fluid metric-chart">
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <h5>Checkout%</h5>
                    <img src="images/purchases.png" alt="purchase Chart" class="img-fluid metric-chart">
                </div>
            </div>
        </div>
    </section>
</div>
        
@endsection
    