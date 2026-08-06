<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') · {{ config('cancionero.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body><nav class="navbar navbar-dark bg-primary shadow-sm"><div class="container"><a class="navbar-brand fw-semibold" href="{{ url('/') }}"><img class="app-navbar-logo" src="{{ asset('images/logo-cancionero.png') }}" alt="Logo {{ config('cancionero.name') }}" width="96" height="96" style="width:96px !important;height:96px !important;max-width:none;max-height:none;object-fit:contain;display:block;"><span>{{ config('cancionero.name') }}</span></a><a class="btn btn-outline-light btn-sm" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>Ingresar</a></div></nav><main class="container app-main">@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif @yield('content')</main></body>
</html>