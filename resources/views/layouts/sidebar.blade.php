<!-- Sidebar for larger screens -->
@php 
$route = Route::currentRouteName()
@endphp
<div class="col-md-3 sidebar d-none d-md-block">
    <div class="brand mb-4">
        <img src="{{asset('images/TextALME.png')}}" alt="M&H Clothing" class="img-fluid">
        {{Auth::check() ? Auth::user()->shopifyStore->shop_url : 'Some Store'}}
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{route('dashboard')}}" class="nav-link" @if($route == 'dashboard') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('productRacks')}}" class="nav-link" @if($route == 'productRacks') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-shopping-bag"></i> Product Collection
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('notifications.smart')}}" class="nav-link" @if($route == 'notifications.smart') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-message"></i> Smart Recognize
            </a>
        </li>
        <li class="nav-item">
            <a href="{{route('notifications.smart.convert.ai')}}" class="nav-link" @if($route == 'notifications.smart.convert.ai') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-message"></i> SmartConvertAI 
            </a>
        </li>
        <!-- <li class="nav-item">
            <a href="{{route('notifications')}}" class="nav-link" @if($route == 'notifications') style="background-color: white; color:#1B4332" @endif>
                <i class="fas fa-message"></i> Notifications
            </a>
        </li> -->
        <li class="nav-item">
            <a href="{{route('show.identifiedUsers')}}" class="nav-link" @if($route == 'identifiedUsers') style="background-color: white; color:#1B4332;" @endif>
                <i class="fas fa-user"></i> Identified Users
            </a>
        </li>
    </ul>
</div>