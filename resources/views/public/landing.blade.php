@extends('layouts.public')
@section('title', 'Biblioteca Pública')

@section('content')
<div class="landing-page">
    <section class="landing-compact-header card card-body mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="app-section-kicker mb-1">Biblioteca pública</span>
                <h1 class="h4 mb-1">Cancionero comunitario</h1>
                <p class="text-secondary mb-0">Busca canciones, ábrelas en modo lectura y revisa repertorios públicos.</p>
            </div>
            <div class="landing-compact-header__actions">
                <a class="btn btn-outline-primary" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>Ingresar</a>
                <a class="btn btn-primary" href="{{ route('register') }}"><i class="bi bi-person-plus"></i>Crear cuenta</a>
            </div>
        </div>
        <div class="landing-compact-stats mt-3">
            <span><strong>{{ $stats['songs'] }}</strong> canciones</span>
            <span><strong>{{ $stats['repertoires'] }}</strong> repertorios públicos</span>
            <span><strong>{{ $stats['contributors'] }}</strong> colaboradores</span>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header app-section-header">
            <h2 class="mb-0">Canciones públicas</h2>
            <a href="{{ route('login') }}">Ingresar</a>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-lg-8">
                    <form method="GET" action="{{ route('public.home') }}" class="landing-search-form">
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Buscar por título, autor o tema">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i>Buscar</button>
                        @if($search !== '')
                            <a class="btn btn-outline-secondary" href="{{ route('public.home') }}">Limpiar</a>
                        @endif
                    </form>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <small class="text-secondary">Mostrando {{ $publicSongs->total() }} resultado(s) disponibles para la comunidad.</small>
                </div>
            </div>

            @if($latestSongs->isNotEmpty())
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="h6 mb-0">Últimas agregadas</h3>
                        <span class="text-secondary small">Novedades recientes</span>
                    </div>
                    <div class="landing-grid landing-grid--songs">
                        @foreach($latestSongs as $latestSong)
                            @php($song = $latestSong['song'])
                            <article class="landing-item landing-item--song card">
                                <div class="card-body">
                                    <h3 class="h6 mb-1">{{ $song->title }}</h3>
                                    <p class="small text-secondary mb-2">{{ $song->author ?: 'Autor no indicado' }}</p>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('public.songs.show', ['song' => $song->slug]) }}">Ver canción</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="landing-grid landing-grid--songs">
                @forelse($publicSongs as $publicSong)
                    @php($song = $publicSong['song'])
                    @php($repertoire = $publicSong['repertoire'])
                    @php($imageFile = $publicSong['imageFile'])
                    <article class="landing-item landing-item--song card">
                        @if($imageFile && $repertoire)
                            <img class="landing-song-cover" src="{{ route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $song, 'file' => $imageFile]) }}" alt="Portada de {{ $song->title }}">
                        @else
                            <div class="landing-song-cover landing-song-cover--empty"><i class="bi bi-music-note-beamed"></i></div>
                        @endif
                        <div class="card-body">
                            <h3 class="h6 mb-1">{{ $song->title }}</h3>
                            <p class="small text-secondary mb-2">{{ $song->author ?: 'Autor no indicado' }}</p>
                            <p class="small text-secondary mb-3"><i class="bi bi-file-earmark-music"></i> {{ $publicSong['totalFiles'] }} archivo(s) de apoyo</p>
                            @if($repertoire)
                                <p class="small text-secondary mb-2">
                                    En repertorio:
                                    <a class="app-list-link" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">{{ $repertoire->name }}</a>
                                </p>
                            @endif
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-primary" href="{{ route('public.songs.show', ['song' => $song->slug]) }}"><i class="bi bi-music-note-list"></i>Ver información</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('public.songs.read', ['song' => $song->slug]) }}"><i class="bi bi-book-half"></i>Modo lectura</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="app-empty-state">
                        <i class="bi bi-music-note-list"></i>
                        <h2 class="h4">No hay canciones para esta búsqueda</h2>
                        <p class="mb-0">Prueba con otro término o revisa los repertorios públicos disponibles.</p>
                    </div>
                @endforelse
            </div>

            @if($publicSongs->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $publicSongs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header app-section-header">
            <h2 class="mb-0">Repertorios públicos</h2>
            <a href="{{ route('register') }}">Crear una cuenta</a>
        </div>
        <div class="card-body">
            <div class="landing-grid landing-grid--repertoires">
                @forelse($publicRepertoires as $repertoire)
                    <article class="landing-item landing-item--repertoire card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h3 class="h6 mb-0">{{ $repertoire->name }}</h3>
                                <span class="badge text-bg-light">{{ $repertoire->songs_count }} canciones</span>
                            </div>
                            <p class="small text-secondary mb-2">
                                <i class="bi bi-person"></i> {{ $repertoire->owner->name }}
                            </p>
                            @if($repertoire->description)
                                <p class="small text-secondary mb-3">{{ \Illuminate\Support\Str::limit($repertoire->description, 120) }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">
                                    <i class="bi bi-eye"></i>Ver repertorio
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="app-empty-state">
                        <i class="bi bi-journal-bookmark"></i>
                        <h2 class="h4">Aún no hay repertorios públicos</h2>
                        <p class="mb-0">Cuando la comunidad publique repertorios, aparecerán aquí.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="landing-support card card-body">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-kicker">Próximamente</span>
                <h2 class="h4 mb-2">Un plan para apoyar el proyecto</h2>
                <p class="text-secondary mb-0">
                    Estamos preparando el espacio para aportes y donaciones con beneficios para quienes sostienen la plataforma.
                    Si quieres participar desde el inicio, crea tu cuenta y acompáñanos en esta etapa.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="btn btn-primary" href="{{ route('register') }}"><i class="bi bi-heart"></i>Quiero aportar</a>
            </div>
        </div>
    </section>
</div>
@endsection
