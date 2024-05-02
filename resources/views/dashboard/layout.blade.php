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
            @if(Auth::check())
                @include('layouts.sidebar')
                @include('layouts.mobile_sidebar')
            @endif
            @yield('content')
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js?v={{time()}}"></script>
    @yield('scripts')
    <!-- Including Bootstrap JS and FontAwesome for Navbar functionality on mobile -->    
    </body>
</html>
