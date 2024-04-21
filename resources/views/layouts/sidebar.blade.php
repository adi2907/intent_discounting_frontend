<!-- Sidebar for larger screens -->
@php 
$route = Route::currentRouteName()
@endphp
<link href="{{asset('css/sidebar.css')}}" type="text/css" rel="stylesheet" />
<div class="col-md-3 sidebar d-none d-md-block">
    <div class="brand mb-4">
        <img src="{{asset('images/TextALME.png')}}" alt="M&H Clothing" class="img-fluid">
        {{Auth::check() ? Auth::user()->shopifyStore->shop_url : 'Some Store'}}
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{route('dashboard')}}" class="dash nav-link" @if($route == 'dashboard') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('productRacks')}}" class="nav-link" @if($route == 'productRacks') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-shopping-bag ml-2"></i> Product Collection
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('notifications')}}" class="nav-link" @if($route == 'notifications') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-message ml-1"></i> Notifications
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('show.identifiedUsers')}}" class="nav-link" @if($route == 'identifiedUsers') style="background-color: white; color:#1B4332;" @endif>
                <i class="fas fa-user ml-2"></i> Identified Users
            </a>
        </li>
        <li class="nav-item">
    <div class="dropdown">
        <a class="nav-link dropdown-toggle" href="{{ route('show.identifiedUsers') }}"  role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-th-list ml-1"></i> Segments
        </a>

        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <a class="dropdown-item" href="{{ route('list.identified.user.segments') }}" @if($route == 'identifiedUsers') style="background-color: white; color:#1B4332;" @endif>
                Identified Users Segment
            </a>
            
            <!-- Add other dropdown items here -->
        </div>
    </div>
</li>

        
        
    </ul>
</div>