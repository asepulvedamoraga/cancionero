@extends('layouts.public')
@section('title', 'Biblioteca Pública')

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl bg-slate-900 text-white shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.26),_transparent_44%),radial-gradient(circle_at_bottom_right,_rgba(20,184,166,0.2),_transparent_46%),linear-gradient(120deg,_#0f172a_0%,_#155e75_48%,_#0f766e_100%)]"></div>
        <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -bottom-16 left-6 h-44 w-44 rounded-full bg-cyan-300/20 blur-2xl"></div>

        <div class="relative grid gap-6 px-6 py-7 sm:px-8 sm:py-8 lg:grid-cols-[1.3fr_0.9fr] lg:px-10 lg:py-9">
            <div>
                <p class="mb-3 inline-flex items-center rounded-full border border-white/30 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.15em] text-cyan-100">
                    Biblioteca pública
                </p>
                <h1 class="max-w-xl text-2xl font-black leading-tight text-white sm:text-3xl lg:text-4xl">
                    Cancionero comunitario para cantar, compartir y preparar celebraciones.
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-cyan-50/90 sm:text-base">
                    Busca canciones por título o autor, abre cada una en modo lectura y descubre repertorios
                    públicos creados por la comunidad.
                </p>

                <div class="mt-5 flex flex-col gap-2.5 sm:flex-row sm:flex-wrap sm:items-center">
                    <a
                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-cyan-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        href="{{ route('register') }}"
                    >
                        Crear cuenta
                    </a>
                    <a
                        class="inline-flex items-center justify-center rounded-xl border border-white/40 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        href="{{ route('login') }}"
                    >
                        Ingresar
                    </a>
                </div>

                <div class="mt-5 grid gap-2 sm:grid-cols-3">
                    <x-public.stat-tile label="Canciones" :value="$stats['songs']" />
                    <x-public.stat-tile label="Repertorios" :value="$stats['repertoires']" />
                    <x-public.stat-tile label="Colaboradores" :value="$stats['contributors']" />
                </div>
            </div>

            <aside class="grid content-start gap-3">
                <div class="rounded-2xl border border-white/25 bg-white/10 p-4 backdrop-blur-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-100">Qué puedes hacer</p>
                    <ul class="mt-2.5 space-y-2 text-sm leading-relaxed text-cyan-50">
                        <li class="rounded-lg bg-white/5 px-3 py-2">Encontrar canciones para celebraciones y ensayos.</li>
                        <li class="rounded-lg bg-white/5 px-3 py-2">Ver letras e información sin iniciar sesión.</li>
                        <li class="rounded-lg bg-white/5 px-3 py-2">Explorar repertorios públicos en un solo lugar.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-cyan-200/35 bg-cyan-300/10 p-4">
                    <p class="text-sm font-semibold text-cyan-50">¿Quieres guardar y gestionar tus propios repertorios?</p>
                    <a
                        class="mt-2.5 inline-flex items-center rounded-lg border border-cyan-100/45 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15"
                        href="{{ route('register') }}"
                    >
                        Empezar gratis
                    </a>
                </div>
            </aside>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Canciones públicas</h2>
                <p class="mt-1 text-sm text-slate-600">Explora canciones disponibles para toda la comunidad.</p>
            </div>
            <a class="text-sm font-semibold text-cyan-700 hover:text-cyan-800" href="{{ route('login') }}">Ingresar</a>
        </div>

        <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <form method="GET" action="{{ route('public.home') }}" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-2xl">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Buscar por título, autor o tema"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 shadow-sm outline-none ring-0 transition placeholder:text-slate-400 focus:border-cyan-600 focus:ring-2 focus:ring-cyan-200"
                >
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-cyan-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-800"
                >
                    Buscar
                </button>
                @if($search !== '')
                    <a
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        href="{{ route('public.home') }}"
                    >
                        Limpiar
                    </a>
                @endif
            </form>
            <p class="text-sm text-slate-600 lg:text-right">
                Mostrando {{ $publicSongs->total() }} resultado(s) disponibles para la comunidad.
            </p>
        </div>

        @if($latestSongs->isNotEmpty())
            <div class="mt-8">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">Últimas agregadas</h3>
                    <span class="text-xs font-medium text-slate-500">Novedades recientes</span>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestSongs as $latestSong)
                        @php($song = $latestSong['song'])
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <h3 class="text-base font-semibold text-slate-900">{{ $song->title }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $song->author ?: 'Autor no indicado' }}</p>
                            <a
                                class="mt-4 inline-flex items-center rounded-lg border border-cyan-700 px-3 py-2 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-50"
                                href="{{ route('public.songs.show', ['song' => $song->slug]) }}"
                            >
                                Ver canción
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($publicSongs as $publicSong)
                @php($song = $publicSong['song'])
                @php($repertoire = $publicSong['repertoire'])
                @php($imageFile = $publicSong['imageFile'])
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    @if($imageFile && $repertoire)
                        <img
                            class="h-44 w-full object-cover"
                            src="{{ route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $song, 'file' => $imageFile]) }}"
                            alt="Portada de {{ $song->title }}"
                        >
                    @else
                        <div class="grid h-44 place-items-center bg-slate-100 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-12 w-12">
                                <path d="M12 3a1 1 0 0 1 1 1v9.126a4 4 0 1 1-2 0V4a1 1 0 0 1 1-1Zm6 4a1 1 0 0 1 1 1v6.126a4 4 0 1 1-2 0V8a1 1 0 0 1 1-1Z" />
                            </svg>
                        </div>
                    @endif
                    <div class="space-y-3 p-4">
                        <h3 class="text-base font-semibold text-slate-900">{{ $song->title }}</h3>
                        <p class="text-sm text-slate-600">{{ $song->author ?: 'Autor no indicado' }}</p>
                        <p class="text-sm text-slate-600">{{ $publicSong['totalFiles'] }} archivo(s) de apoyo</p>

                        @if($repertoire)
                            <p class="text-sm text-slate-600">
                                En repertorio:
                                <a class="font-semibold text-cyan-700 hover:text-cyan-800" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">{{ $repertoire->name }}</a>
                            </p>
                        @endif

                        <div class="flex flex-col gap-2 pt-1 sm:flex-row sm:flex-wrap">
                            <a
                                class="inline-flex items-center justify-center rounded-lg bg-cyan-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-cyan-800"
                                href="{{ route('public.songs.show', ['song' => $song->slug]) }}"
                            >
                                Ver información
                            </a>
                            <a
                                class="inline-flex items-center justify-center rounded-lg border border-cyan-700 px-3 py-2 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-50"
                                href="{{ route('public.songs.read', ['song' => $song->slug]) }}"
                            >
                                Modo lectura
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <x-public.empty-state
                    title="No hay canciones para esta búsqueda"
                    message="Prueba con otro término o revisa los repertorios públicos disponibles."
                    class="sm:col-span-2 xl:col-span-3"
                />
            @endforelse
        </div>

        @if($publicSongs->hasPages())
            <div class="mt-6">
                {{ $publicSongs->withQueryString()->links() }}
            </div>
        @endif
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Repertorios públicos</h2>
                <p class="mt-1 text-sm text-slate-600">Colecciones compartidas por la comunidad.</p>
            </div>
            <a class="text-sm font-semibold text-cyan-700 hover:text-cyan-800" href="{{ route('register') }}">Crear una cuenta</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($publicRepertoires as $repertoire)
                <article class="rounded-2xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-base font-semibold text-slate-900">{{ $repertoire->name }}</h3>
                        <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">
                            {{ $repertoire->songs_count }} canciones
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Por {{ $repertoire->owner->name }}</p>
                    @if($repertoire->description)
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::limit($repertoire->description, 120) }}</p>
                    @endif
                    <a
                        class="mt-4 inline-flex items-center rounded-lg border border-cyan-700 px-3 py-2 text-sm font-semibold text-cyan-700 transition hover:bg-cyan-50"
                        href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}"
                    >
                        Ver repertorio
                    </a>
                </article>
            @empty
                <x-public.empty-state
                    title="Aún no hay repertorios públicos"
                    message="Cuando la comunidad publique repertorios, aparecerán aquí."
                    class="sm:col-span-2 xl:col-span-3"
                />
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-cyan-100 bg-gradient-to-r from-cyan-50 via-teal-50 to-emerald-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700">Próximamente</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Un plan para apoyar el proyecto</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-700 sm:text-base">
                    Estamos preparando el espacio para aportes y donaciones con beneficios para quienes sostienen la plataforma.
                    Si quieres participar desde el inicio, crea tu cuenta y acompáñanos en esta etapa.
                </p>
            </div>
            <a
                class="inline-flex items-center justify-center rounded-xl bg-cyan-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-800"
                href="{{ route('register') }}"
            >
                Quiero aportar
            </a>
        </div>
    </section>
</div>
@endsection
