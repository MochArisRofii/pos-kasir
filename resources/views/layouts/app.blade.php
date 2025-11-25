<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - POS Kasir</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }} - POS Kasir
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    {{-- Di bagian Left Side Of Navbar --}}
                    @auth
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">Dashboard</a>
                            </li>

                            @if (Auth::user()->role === 'admin')
                                {{-- Menu Admin --}}
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('prodsus.index') }}">Produk Khusus</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('transactions.index') }}">Transactions</a>
                                </li>
                            @endif

                            @if (Auth::user()->role === 'cashier')
                                {{-- Menu Kasir --}}
                                @if (Session::get('cashier_initialized'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('transactions.create') }}">New Transaction</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('prodsus.process') }}">Proses Barang</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('transactions.index') }}">Transactions</a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a class="nav-link text-warning" href="{{ route('cashier.initialize') }}">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Initialize Cashier
                                        </a>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    @endauth

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} ({{ Auth::user()->role }})
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

        <main class="py-4">
            @if (session('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
