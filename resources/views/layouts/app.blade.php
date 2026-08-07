<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('cancionero.name')) · {{ config('cancionero.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-cancionero.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-cancionero.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <a class="app-skip-link" href="#main-content">Saltar al contenido</a>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm app-navbar py-1">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ auth()->user()->hasVerifiedEmail() ? route('dashboard') : route('verification.notice') }}">
                <img class="app-navbar-logo" src="{{ asset('images/logo-cancionero.png') }}" alt="Logo {{ config('cancionero.name') }}" width="58" height="58">
                <span>{{ config('cancionero.name') }}</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto gap-lg-1">
                    @if(auth()->user()->hasVerifiedEmail())
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-house me-1"></i>Inicio</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('songs.*') ? 'active' : '' }}" href="{{ route('songs.index') }}"><i class="bi bi-music-note-list me-1"></i>Canciones</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('repertoires.*') ? 'active' : '' }}" href="{{ route('repertoires.index') }}"><i class="bi bi-list-ol me-1"></i>Repertorios</a></li>
                        @if(auth()->user()->is_admin)
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><i class="bi bi-gear me-1"></i>Configuración</a></li>
                        @endif
                    @endif
                </ul>

                <div class="navbar-nav align-items-lg-center gap-lg-2 app-navbar-actions">
                    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-light btn-sm" type="submit"><i class="bi bi-box-arrow-right"></i>Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main id="main-content" class="container app-main" tabindex="-1">
        <div class="app-flash-stack">
            @if(session('status'))
                <div class="alert alert-success d-flex gap-2" role="status"><i class="bi bi-check-circle-fill"></i><div>{{ session('status') }}</div></div>
            @endif

            @foreach((array) session('warnings', []) as $warning)
                <div class="alert alert-warning d-flex gap-2" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><div>{{ $warning }}</div></div>
            @endforeach

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex gap-2">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <div>
                            <strong>Revisa los datos ingresados.</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="app-site-footer" role="contentinfo">
        <div class="container">
            Desarrollado por Adrián Sepúlveda, contacto:
            <a href="tel:+5696132744">+5696132744</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>