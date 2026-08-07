@extends('layouts.app')
@section('title', $repertoire->name)
@section('content')
<div class="app-page-header align-items-start">
    <div>
        <h1 class="h2 mb-1">{{ $repertoire->name }}</h1>
        <p class="text-secondary mb-0">{{ $repertoire->event_type ?: 'Evento no indicado' }} · {{ $repertoire->event_date?->format('d-m-Y') ?: 'Sin fecha' }}</p>
    </div>

    <div class="app-page-actions">
        @if($presentationPageCount)
            <a class="btn btn-primary" href="{{ route('repertoires.presentation', $repertoire) }}"><i class="bi bi-play-fill"></i> Presentar</a>
        @endif

        @can('export', $repertoire)
            <form method="POST" action="{{ route('repertoires.export', $repertoire) }}">
                @csrf
                <button class="btn btn-outline-primary" @disabled(!$presentationPageCount)><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</button>
            </form>
        @endcan

        @can('update', $repertoire)
            <a class="btn btn-outline-primary" href="{{ route('repertoires.edit', $repertoire) }}"><i class="bi bi-pencil"></i>Editar</a>

            <form method="POST" action="{{ route('repertoires.duplicate', $repertoire) }}">
                @csrf
                <button class="btn btn-outline-secondary"><i class="bi bi-copy"></i>Duplicar</button>
            </form>
        @endcan
    </div>
</div>

<div class="card card-body app-form-shell app-detail-card mb-4">
    <div class="row g-3 app-meta-grid">
        <div class="col-md-3">
            <strong>Estado</strong>
            <div>{{ ['draft' => 'Borrador', 'ready' => 'Listo', 'archived' => 'Archivado'][$repertoire->status] }}</div>
        </div>

        <div class="col-md-3">
            <strong>Visibilidad</strong>
            <div><i class="bi {{ $repertoire->visibility === 'public' ? 'bi-globe-americas' : 'bi-lock' }}"></i> {{ $repertoire->visibility === 'public' ? 'Público' : 'Privado' }}</div>
        </div>

        <div class="col-md-3">
            <strong>Propietario</strong>
            <div>{{ $repertoire->owner->name }}</div>
        </div>

        <div class="col-md-3">
            <strong>Canciones</strong>
            <div>{{ $repertoire->songs_count }}</div>
        </div>

        <div class="col-md-3">
            <strong>Hora</strong>
            <div>{{ $repertoire->event_time?->format('H:i') ?: 'No indicada' }}</div>
        </div>

        <div class="col-md-3">
            <strong>Lugar</strong>
            <div>{{ $repertoire->location ?: 'No indicado' }}</div>
        </div>

        @if($repertoire->description)
            <div class="col-12">
                <strong>Descripción</strong>
                <p class="mb-0 app-preline">{{ $repertoire->description }}</p>
            </div>
        @endif
    </div>
</div>

@can('update', $repertoire)
    <div class="card card-body app-form-shell app-detail-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <strong>Compartir repertorio</strong>
                @if($repertoire->visibility === 'public')
                    <p class="text-secondary small mb-0">Cualquier persona con el enlace puede verlo y presentarlo.</p>
                @else
                    <p class="text-secondary small mb-0">Cambia la visibilidad a Público para generar un enlace compartible.</p>
                @endif
            </div>

            @if($repertoire->visibility === 'public')
                <span class="badge text-bg-success"><i class="bi bi-globe-americas"></i> Público</span>
            @else
                <span class="badge text-bg-secondary"><i class="bi bi-lock"></i> Privado</span>
            @endif
        </div>

        @if($repertoire->visibility === 'public')
            <div class="input-group mt-3">
                <input class="form-control" value="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}" readonly aria-label="Enlace público">
                <button class="btn btn-outline-secondary" type="button" data-copy-text="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}"><i class="bi bi-copy"></i><span data-copy-label>Copiar enlace</span></button>
                <a class="btn btn-outline-primary" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i>Abrir</a>
            </div>
        @endif
    </div>
@endcan

<div class="card app-list-card">
    <div class="card-header app-section-header">
        <strong>Canciones en orden</strong>
    </div>

    <div class="list-group list-group-flush">
        @forelse($repertoire->songs as $song)
            <div class="list-group-item d-flex gap-3 align-items-start">
                <span class="repertoire-position">{{ $loop->iteration }}</span>
                <div class="flex-grow-1">
                    <a class="fw-semibold text-decoration-none" href="{{ route('songs.show', $song) }}">{{ $song->title }}</a>
                    <div class="small text-secondary">{{ $song->author ?: 'Autor no indicado' }} · {{ $song->page_count }} páginas</div>

                    @if($song->pivot->notes)
                        <div class="small mt-1 app-preline">{{ $song->pivot->notes }}</div>
                    @endif

                    @if($song->page_count === 0)
                        <div class="small text-warning"><i class="bi bi-exclamation-triangle"></i> Sin páginas disponibles para presentación.</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="app-empty-state">
                <i class="bi bi-music-note-list"></i>
                Este repertorio todavía no tiene canciones.
            </div>
        @endforelse
    </div>
</div>
@endsection
