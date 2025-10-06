<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'DELEXES UNIVERSITY COLLEGE')</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/delexes-custom.css') }}" rel="stylesheet">
    
    @yield('head')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark shadow-sm" style="background-color: #1e3a8a;">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="DELEXES UNIVERSITY COLLEGE" height="50" class="me-3">
                    <div>
                        <div class="fw-bold text-white">DELEXES UNIVERSITY COLLEGE</div>
                        
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @auth
                            @if(Auth::user()->isStaff())
                                @if(Auth::user()->isHOD())
                                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('hod.dashboard') }}">HOD Dashboard</a></li>
                                @elseif(Auth::user()->isPresident())
                                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('president.dashboard') }}">President Dashboard</a></li>
                                @elseif(Auth::user()->isRegistrar())
                                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('registrar.dashboard') }}">Registrar Dashboard</a></li>
                                @else
                                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                                @endif
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-white" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Manage
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                        <li><a class="dropdown-item" href="{{ route('admin.departments.index') }}">
                                            <i class="fas fa-building"></i> Departments
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.programs.index') }}">
                                            <i class="fas fa-graduation-cap"></i> Programs
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                            <i class="fas fa-users"></i> Users
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.form-types.index') }}">
                                            <i class="fas fa-file-alt"></i> Form Types
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.departments.create') }}">
                                            <i class="fas fa-plus"></i> Add Department
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.programs.create') }}">
                                            <i class="fas fa-plus"></i> Add Program
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.users.create') }}">
                                            <i class="fas fa-user-plus"></i> Add User
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.form-types.create') }}">
                                            <i class="fas fa-plus"></i> Add Form Type
                                        </a></li>
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item"><a class="nav-link text-white" href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="{{ route('portal.application') }}">My Application</a></li>
                                <li class="nav-item"><a class="nav-link text-white" href="{{ route('portal.results') }}">Application Status</a></li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('registration.create'))
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="{{ route('registration.create') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main  style="background-color: #f8fafc; min-height: calc(100vh - 76px);">
            @yield('content')
        </main>
    </div>
</body>
</html>
