@extends('layouts.public')
@section('title', $repertoire->name)
@section('content')
<div class="public-detail-stack">
    <section class="public-section public-detail-hero" aria-labelledby="public-repertoire-title">
        <div class="public-detail-hero__content">
            <p class="public-detail-kicker">Repertorio público</p>
            <h1 id="public-repertoire-title" class="public-detail-title">{{ $repertoire->name }}</h1>
            <p class="public-detail-subtitle">
                {{ $repertoire->event_type ?: 'Evento' }}
                @if($repertoire->event_date)
                    · {{ $repertoire->event_date->format('d-m-Y') }}
                @endif
                @if($repertoire->location)
                    · {{ $repertoire->location }}
                @endif
            </p>
        </div>

        <div class="public-detail-actions">
            @if($presentationPageCount)
                <a class="public-btn public-btn--primary" href="{{ route('public.repertoires.presentation',['repertoire' => $repertoire->slug]) }}">Presentar</a>
            @endif
            @if($repertoire->allow_public_download)
                <a class="public-btn public-btn--ghost" href="{{ route('public.repertoires.download',['repertoire' => $repertoire->slug]) }}">Descargar PDF</a>
            @endif
        </div>
    </section>

    <section class="public-section" aria-labelledby="public-repertoire-meta-title">
        <h2 id="public-repertoire-meta-title" class="visually-hidden">Información del repertorio</h2>

        <div class="public-kpi-grid public-kpi-grid--3">
            <article class="public-kpi-card">
                <p class="public-kpi-label">Compartido por</p>
                <p class="public-kpi-value">{{ $repertoire->owner->name }}</p>
            </article>

            <article class="public-kpi-card">
                <p class="public-kpi-label">Fecha y hora</p>
                <p class="public-kpi-value">
                    {{ $repertoire->event_date?->format('d-m-Y') ?: 'No indicada' }}
                    @if($repertoire->event_time)
                        · {{ $repertoire->event_time->format('H:i') }}
                    @endif
                </p>
            </article>

            <article class="public-kpi-card">
                <p class="public-kpi-label">Canciones disponibles</p>
                <p class="public-kpi-value">{{ $songs->count() }}</p>
            </article>
        </div>

        @if($repertoire->description)
            <div class="public-detail-note">
                <p class="public-kpi-label">Descripción</p>
                <p class="public-detail-note__text">{{ $repertoire->description }}</p>
            </div>
        @endif
    </section>

    <section class="public-section public-detail-list" aria-labelledby="public-repertoire-songs-title">
        <div class="public-section__head">
            <div>
                <p class="public-section__kicker">Contenido</p>
                <h2 id="public-repertoire-songs-title" class="public-section__title">Canciones</h2>
            </div>
        </div>

        <div class="public-detail-list__group">
            @forelse($songs as $song)
                <article class="public-detail-row">
                    <span class="public-detail-row__index">{{ $loop->iteration }}</span>
                    <div>
                        <h3 class="public-detail-row__title">{{ $song->title }}</h3>
                        <p class="public-detail-row__meta">{{ $song->author ?: 'Autor no indicado' }} · {{ $song->page_count }} {{ $song->page_count === 1 ? 'página' : 'páginas' }}</p>
                    </div>
                </article>
            @empty
                <x-public.empty-state
                    title="Sin canciones disponibles"
                    message="Este repertorio aún no tiene páginas públicas para presentar."
                    action-label="Volver al inicio"
                    :action-url="route('public.home')"
                />
            @endforelse
        </div>
    </section>

    <p class="public-detail-footnote">Vista pública de solo lectura. Las notas internas y acciones de edición no se muestran.</p>
</div>
@endsection