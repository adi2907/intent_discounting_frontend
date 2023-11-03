<!-- Navbar for mobile screens -->
@php 
$route = Route::currentRouteName()
@endphp
<nav class="navbar navbar-expand-md navbar-light bg-light d-md-none w-100">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item"><a href="#" class="nav-link" @if($route == 'dashboard') style="background-color: white; color:#1B4332" @endif>Dashboard</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Product Collection</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Notifications</a></li>
        </ul>
    </div>
</nav>