<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · {{ config('cancionero.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="login-page">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4 py-md-5">
        <div class="card login-card login-card--enhanced shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-center mb-3">
                    <a href="{{ url('/') }}" class="text-decoration-none text-secondary small fw-semibold">
                        <i class="bi bi-arrow-left"></i> Volver al inicio
                    </a>
                </div>
                <div class="text-center mb-4">
                    <div class="brand-mark"><i class="bi bi-music-note-beamed"></i></div>
                    <h1 class="h4 mt-3 mb-2">{{ config('cancionero.name') }}</h1>
                    <p class="text-secondary mb-0">@yield('subtitle','Tu biblioteca de canciones y repertorios')</p>
                </div>
                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Revisa los datos ingresados.</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>