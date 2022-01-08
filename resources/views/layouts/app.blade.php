<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <link rel="icon"
    type="image/png"
    href="{{ asset('favicon-32x32.png') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.label', 'Laravel') }}</title>

    <!-- Scripts -->
    @routes
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    @if ('production' === config('app.env'))
        <!-- Start AllRoads Snippet -->
        <script>
        ! function(e, t, n, a, p, r, s) {
        e[a] || ((p = e[a] = function() {
        p.process ? p.process.apply(p, arguments) : p.queue.push(arguments)
        }).queue = [], p.t = +new Date, (r = t.createElement(n)).async = 1, r.src = "https://events.allroadsanalytics.com/allroads.min.js?t=" + 864e5 * Math.ceil(new Date / 864e5), (s = t.getElementsByTagName(n)[0]).parentNode.insertBefore(r, s))
        }(window, document, "script", "allroads"),
        allroads("init", "ID-timcom", {follow: true}),
        allroads("event", "pageload");
        </script>
        <!-- End AllRoads Snippet -->

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-R2B4E76E6P"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-R2B4E76E6P');
        </script>
        <!-- end Global site tag (gtag.js) - Google Analytics -->
    @endif


    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @livewireStyles
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('favicon-32x32.png') }}" alt="{{ config('app.label', 'Laravel') }}"
                    class="">
                    {{ config('app.label', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Log in') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Sign Up') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
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

        <main class="py-4 mb-5">
            @if ($alert = session('alert'))
                <div class="alert alert-{{ $alert['type'] ?? 'primary' }}" role="alert">
                    <div class="container">
                        {!! $alert['message'] ?? $alert !!}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
