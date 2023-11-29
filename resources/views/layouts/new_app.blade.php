<!DOCTYPE html>
<html lang="en">
    <head>
        @include('layouts.head')
        @yield('css')
    </head>
    <body>
    <img id="site-logo" src="{{asset('images/TextALME.png')}}" alt="Alme Logo">
    <div class="container-fluid">
        <div class="row">
            @include('layouts.sidebar')
            @include('layouts.mobile_sidebar')
            @yield('content')
        </div>
    </div>
    @include('layouts.scripts')
    @yield('scripts')
    <!-- Including Bootstrap JS and FontAwesome for Navbar functionality on mobile -->    
    </body>
</html>
