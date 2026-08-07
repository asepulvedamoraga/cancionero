@extends('layouts.public')
@section('title', $song->title)

@section('content')
<div class="public-detail-stack">
    <section class="public-section public-detail-hero" aria-labelledby="public-song-title">
        <div class="public-detail-hero__content">
            <a href="{{ route('public.home') }}" class="public-inline-link"><span aria-hidden="true">←</span> Volver al inicio</a>
            <h1 id="public-song-title" class="public-detail-title mt-2">{{ $song->title }}</h1>
            <p class="public-detail-subtitle">{{ $song->author ?: 'Autor no indicado' }} · Compartida por {{ $song->owner->name }}</p>
        </div>
        <div class="public-detail-actions">
            <a class="public-btn public-btn--primary" href="{{ route('public.songs.read', ['song' => $song->slug, 'tone' => $selectedTone->id]) }}">Modo lectura</a>
        </div>
    </section>

    @if($song->notes)
        <section class="public-section">
            <p class="public-kpi-label">Notas</p>
            <p class="public-detail-note__text">{{ $song->notes }}</p>
        </section>
    @endif

    <section class="public-kpi-grid public-kpi-grid--4" aria-label="Información de la canción">
        <article class="public-kpi-card">
            <p class="public-kpi-label">Tonalidad</p>
            <p class="public-kpi-value">{{ $selectedTone->name }}</p>
        </article>
        <article class="public-kpi-card">
            <p class="public-kpi-label">Archivos</p>
            <p class="public-kpi-value">{{ $displayFiles->count() }}</p>
        </article>
        <article class="public-kpi-card">
            <p class="public-kpi-label">Categoría</p>
            <p class="public-kpi-value">{{ $song->category?->name ?: 'No definida' }}</p>
        </article>
        <article class="public-kpi-card">
            <p class="public-kpi-label">Momento litúrgico</p>
            <p class="public-kpi-value">{{ $song->liturgicalMoment?->name ?: 'No definido' }}</p>
        </article>
    </section>

    @if($song->tones->count() > 1)
        <section class="public-section">
            <p class="public-kpi-label">Tonalidades</p>
            <div class="public-tone-grid">
                @foreach($song->tones as $tone)
                    <a class="public-tone-link {{ $tone->id === $selectedTone->id ? 'is-active' : '' }}" href="{{ route('public.songs.show', ['song' => $song->slug, 'tone' => $tone->id]) }}">{{ $tone->name }}</a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="public-section public-detail-list" aria-labelledby="public-song-files-title">
        <div class="public-section__head">
            <div>
                <p class="public-section__kicker">Biblioteca</p>
                <h2 id="public-song-files-title" class="public-section__title">Archivos disponibles</h2>
            </div>
        </div>

        @if($displayFiles->isEmpty())
            <x-public.empty-state
                title="Sin archivos disponibles"
                message="Esta tonalidad no tiene archivos disponibles por ahora."
                action-label="Volver al inicio"
                :action-url="route('public.home')"
            />
        @else
            <div class="song-file-grid">
                @foreach($displayFiles as $file)
                    <article class="song-file-card">
                        @if(in_array($file->file_type, ['image', 'generated_image'], true))
                            <img src="{{ route('public.songs.files.show', ['song' => $song->slug, 'file' => $file]) }}" alt="Archivo {{ $loop->iteration }} de {{ $song->title }}">
                        @else
                            <div class="pdf-placeholder"><i class="bi bi-file-earmark-pdf"></i><small>{{ $file->original_name }}</small></div>
                        @endif
                        <div class="p-2 d-flex gap-2 flex-wrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('public.songs.files.show', ['song' => $song->slug, 'file' => $file]) }}" target="_blank" rel="noopener">Abrir</a>
                            @if($file->file_type === 'pdf')
                                <a class="btn btn-sm btn-primary" href="{{ route('public.songs.files.show', ['song' => $song->slug, 'file' => $file]) }}" target="_blank" rel="noopener">PDF</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if($publicRepertoires->isNotEmpty())
        <section class="public-section" aria-labelledby="public-song-repertoires-title">
            <div class="public-section__head">
                <div>
                    <p class="public-section__kicker">Colecciones</p>
                    <h2 id="public-song-repertoires-title" class="public-section__title">También está en repertorios públicos</h2>
                </div>
            </div>
            <div class="public-chip-list">
                @foreach($publicRepertoires as $repertoire)
                    <a class="public-chip-link" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">{{ $repertoire->name }}</a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
