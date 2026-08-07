<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') · {{ config('cancionero.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-cancionero.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-cancionero.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="public-body bg-slate-50 text-slate-800">
    <a class="public-skip-link" href="#public-main-content">Saltar al contenido</a>

    <header class="public-header" data-public-header>
        <nav class="container public-header__inner" aria-label="Navegacion principal">
            <a class="public-brand no-underline" href="{{ url('/') }}">
                <img
                    src="{{ asset('images/logo-cancionero.png') }}"
                    alt="Logo {{ config('cancionero.name') }}"
                    width="50"
                    height="50"
                    class="public-brand__logo"
                >
                <span class="public-brand__name">{{ config('cancionero.name') }}</span>
            </a>

            <div class="public-header__actions">
                <a
                    class="public-btn public-btn--ghost no-underline"
                    href="{{ route('login') }}"
                >
                    Ingresar
                </a>
                <a
                    class="public-btn public-btn--primary no-underline"
                    href="{{ route('register') }}"
                >
                    Crear cuenta
                </a>
            </div>
        </nav>
    </header>

    <main id="public-main-content" class="container app-main app-main--compact public-main" tabindex="-1">
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>