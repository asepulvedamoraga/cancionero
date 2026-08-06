<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') · {{ config('cancionero.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800">
    <header class="sticky top-0 z-50 border-b border-white/15 bg-gradient-to-r from-slate-900 via-cyan-900 to-teal-800 shadow-lg">
        <nav class="container flex min-h-14 items-center justify-between gap-3 py-1.5">
            <a class="flex items-center gap-3 text-white no-underline" href="{{ url('/') }}">
                <img
                    src="{{ asset('images/logo-cancionero.png') }}"
                    alt="Logo {{ config('cancionero.name') }}"
                    width="50"
                    height="50"
                    class="h-12 w-12 rounded-md object-contain sm:h-[3.25rem] sm:w-[3.25rem]"
                >
                <span class="max-w-[10rem] truncate text-sm font-semibold sm:max-w-none sm:text-base">{{ config('cancionero.name') }}</span>
            </a>

            <a
                class="inline-flex items-center justify-center rounded-lg border border-white/45 px-3 py-2 text-xs font-semibold text-white no-underline transition hover:bg-white/15 sm:px-4 sm:text-sm"
                href="{{ route('login') }}"
            >
                Ingresar
            </a>
        </nav>
    </header>

    <main class="container app-main app-main--compact py-4 sm:py-5">
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>