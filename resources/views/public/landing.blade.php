@extends('layouts.public')
@section('title', 'Biblioteca Pública')

@section('content')
<div class="public-landing">
    <section class="public-hero" aria-labelledby="public-hero-title">
        <div class="public-hero__mesh" aria-hidden="true"></div>

        <div class="public-hero__content">
            <p class="public-hero__eyebrow">Plataforma colaborativa para músicos</p>
            <h1 id="public-hero-title" class="public-hero__title">
                Organiza tu música
            </h1>
            <p class="public-hero__subtitle">
                Reúne canciones, crea repertorios y ten todo listo para tocar en cualquier momento.
            </p>
            <p class="public-hero__identity">Creado por músicos, para músicos.</p>

            <div class="public-hero__actions">
                <a class="public-btn public-btn--primary" href="{{ route('register') }}">Empezar gratis</a>
                <a class="public-btn public-btn--soft" href="{{ route('login') }}">Ingresar</a>
                <a class="public-btn public-btn--ghost" href="{{ route('public.donations') }}">Apoyar proyecto</a>
            </div>

            <div class="public-hero__stats">
                <x-public.stat-tile label="Canciones" :value="$stats['songs']" />
                <x-public.stat-tile label="Repertorios" :value="$stats['repertoires']" />
                <x-public.stat-tile label="Colaboradores" :value="$stats['contributors']" />
            </div>
        </div>

        <aside class="public-hero__preview" aria-label="Vista rapida">
            <p class="public-preview__kicker">Vista rapida</p>
            <div class="public-preview__search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span>Buscar por titulo, autor o tema</span>
            </div>

            <div class="public-preview__list" role="list">
                @foreach($latestSongs->take(3) as $latestSong)
                    @php($song = $latestSong['song'])
                    <article class="public-preview__item" role="listitem">
                        <div>
                            <p class="public-preview__song">{{ $song->title }}</p>
                            <p class="public-preview__author">{{ $song->author ?: 'Autor no indicado' }}</p>
                        </div>
                        <a class="public-inline-link" href="{{ route('public.songs.read', ['song' => $song->slug]) }}">Leer</a>
                    </article>
                @endforeach
            </div>

            <a class="public-preview__cta" href="{{ route('public.donations') }}">
                <i class="bi bi-lightning-charge" aria-hidden="true"></i>
                Una comunidad que comparte música
            </a>
        </aside>
    </section>

    <section class="public-section public-section--songs" aria-labelledby="public-songs-title">
        <div class="public-section__head">
            <div>
                <p class="public-section__kicker">Catalogo</p>
                <h2 id="public-songs-title" class="public-section__title">Canciones publicas</h2>
                <p class="public-section__subtitle">Explora canciones disponibles para toda la comunidad.</p>
            </div>
            <p class="public-section__meta">
                Mostrando {{ $publicSongs->total() }} resultado(s)
            </p>
        </div>

        <form method="GET" action="{{ route('public.home') }}" class="public-search-form" role="search">
            <label class="visually-hidden" for="public-song-search">Buscar canciones</label>
            <input
                id="public-song-search"
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Buscar por titulo, autor o tema"
                class="public-search-form__input"
            >
            <button type="submit" class="public-btn public-btn--primary">Buscar</button>
            @if($search !== '')
                <a class="public-btn public-btn--ghost" href="{{ route('public.home') }}">Limpiar</a>
            @endif
        </form>

        <div class="public-card-grid public-card-grid--songs">
            @forelse($publicSongs as $publicSong)
                @php($song = $publicSong['song'])
                @php($repertoire = $publicSong['repertoire'])
                @php($imageFile = $publicSong['imageFile'])
                <article class="public-card public-card--song">
                    @if($imageFile && $repertoire)
                        <img
                            class="public-card__cover"
                            src="{{ route('public.repertoires.files.show', ['repertoire' => $repertoire->slug, 'song' => $song, 'file' => $imageFile]) }}"
                            alt="Portada de {{ $song->title }}"
                        >
                    @else
                        <div class="public-card__cover public-card__cover--empty" aria-hidden="true">
                            <i class="bi bi-music-note-beamed"></i>
                        </div>
                    @endif

                    <div class="public-card__body">
                        <h3 class="public-card__title">{{ $song->title }}</h3>
                        <p class="public-card__subtitle">{{ $song->author ?: 'Autor no indicado' }}</p>
                        <p class="public-card__meta">{{ $publicSong['totalFiles'] }} archivo(s) de apoyo</p>

                        @if($repertoire)
                            <p class="public-card__meta">
                                En repertorio:
                                <a class="public-inline-link" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">{{ $repertoire->name }}</a>
                            </p>
                        @endif

                        <div class="public-card__actions">
                            <a class="public-btn public-btn--primary public-btn--small" href="{{ route('public.songs.show', ['song' => $song->slug]) }}">Ver info</a>
                            <a class="public-btn public-btn--ghost public-btn--small" href="{{ route('public.songs.read', ['song' => $song->slug]) }}">Modo lectura</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-public.empty-state
                    title="No hay canciones para esta busqueda"
                    message="Prueba con otro termino o revisa los repertorios publicos disponibles."
                    action-label="Limpiar búsqueda"
                    :action-url="route('public.home')"
                    class="sm:col-span-2 xl:col-span-3"
                />
            @endforelse
        </div>

        @if($publicSongs->hasPages())
            <div class="public-section__pagination">
                {{ $publicSongs->withQueryString()->links() }}
            </div>
        @endif
    </section>

    <section class="public-section" aria-labelledby="public-repertoires-title">
        <div class="public-section__head">
            <div>
                <p class="public-section__kicker">Colecciones</p>
                <h2 id="public-repertoires-title" class="public-section__title">Repertorios publicos</h2>
                <p class="public-section__subtitle">Colecciones compartidas por la comunidad.</p>
            </div>
            <a class="public-inline-link" href="{{ route('register') }}">Crear cuenta</a>
        </div>

        <div class="public-card-grid public-card-grid--repertoires">
            @forelse($publicRepertoires as $repertoire)
                <article class="public-card public-card--repertoire">
                    <div class="public-card__repertoire-head">
                        <h3 class="public-card__title">{{ $repertoire->name }}</h3>
                        <span class="public-chip">{{ $repertoire->songs_count }} canciones</span>
                    </div>
                    <p class="public-card__subtitle">Por {{ $repertoire->owner->name }}</p>

                    @if($repertoire->description)
                        <p class="public-card__meta">{{ \Illuminate\Support\Str::limit($repertoire->description, 120) }}</p>
                    @endif

                    <a class="public-btn public-btn--ghost public-btn--small" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">
                        Ver repertorio
                    </a>
                </article>
            @empty
                <x-public.empty-state
                    title="Aun no hay repertorios publicos"
                    message="Cuando la comunidad publique repertorios, apareceran aqui."
                    action-label="Crear cuenta"
                    :action-url="route('register')"
                    class="sm:col-span-2 xl:col-span-3"
                />
            @endforelse
        </div>
    </section>

    <section class="public-section public-section--cta" aria-labelledby="public-support-title">
        <div>
            <p class="public-section__kicker">Comunidad</p>
            <h2 id="public-support-title" class="public-section__title">Apoya el crecimiento del proyecto</h2>
            <p class="public-section__subtitle">
                Este proyecto seguirá siendo gratuito. Si quieres, pronto podrás apoyarlo con donaciones voluntarias.
            </p>
        </div>
        <a class="public-btn public-btn--accent" href="{{ route('public.donations') }}">Más información</a>
    </section>
</div>
@endsection
