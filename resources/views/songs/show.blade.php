@extends('layouts.app')
@section('title', $song->title)

@push('styles')
<style>
.song-video-card { max-width: 860px; margin-left: auto; margin-right: auto; }
.song-video-frame { max-height: 420px; }
</style>
@endpush

@section('content')
<div class="app-page-header align-items-start">
    <div>
        <h1 class="h2 mb-1">{{ $song->title }}</h1>
        <p class="text-secondary mb-0">{{ $song->author ?: 'Autor no indicado' }}</p>
    </div>
    <div class="app-page-actions">
        <a class="btn btn-dark" href="{{ route('songs.read', ['song' => $song, 'tone' => $selectedTone->id]) }}">
            <i class="bi bi-book"></i>Modo lectura
        </a>
        @can('update', $song)
            <a class="btn btn-primary" href="{{ route('songs.edit', ['song' => $song, 'tone' => $selectedTone->id]) }}">
                <i class="bi bi-pencil"></i>Editar
            </a>
        @endcan
        <a class="btn btn-outline-secondary" href="{{ route('songs.index') }}">
            <i class="bi bi-arrow-left"></i>Volver
        </a>
    </div>
</div>

@if($songUsedInRepertoires)
    <div class="alert alert-warning py-2 mb-4">
        Esta canción está siendo utilizada en uno o más repertorios y no puede archivarse ni eliminarse.
    </div>
@endif

<div class="card card-body mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <strong>Tonalidad</strong>
        @foreach($song->tones as $tone)
            <a class="btn btn-sm {{ $tone->id === $selectedTone->id ? 'btn-primary' : 'btn-outline-secondary' }}"
               href="{{ route('songs.show', ['song' => $song, 'tone' => $tone->id]) }}">
                {{ $tone->name }}
            </a>
        @endforeach
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">Intérprete</dt>
                <dd class="col-sm-8">{{ $song->performer ?: '—' }}</dd>
                <dt class="col-sm-4">Tonalidad activa</dt>
                <dd class="col-sm-8">{{ $selectedTone->name }}</dd>
                <dt class="col-sm-4">Categoría</dt>
                <dd class="col-sm-8">{{ $song->category?->name ?: '—' }}</dd>
                <dt class="col-sm-4">Momento litúrgico</dt>
                <dd class="col-sm-8">{{ $song->liturgicalMoment?->name ?: '—' }}</dd>
                <dt class="col-sm-4">Tiempos</dt>
                <dd class="col-sm-8">{{ $song->liturgicalSeasons->pluck('name')->join(', ') ?: '—' }}</dd>
                <dt class="col-sm-4">Fuente</dt>
                <dd class="col-sm-8">{{ $song->source ?: '—' }}</dd>
                <dt class="col-sm-4">Video de apoyo</dt>
                <dd class="col-sm-8">
                    @if($song->youtubeEmbedUrl())
                        <a href="{{ $song->video_url }}" target="_blank" rel="noopener">Abrir en YouTube</a>
                    @else
                        —
                    @endif
                </dd>
                <dt class="col-sm-4">Observaciones</dt>
                <dd class="col-sm-8" style="white-space: pre-line;">{{ $song->notes ?: '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-body">
            <strong>Estado</strong>
            <span class="mt-2 badge {{ $song->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                {{ $song->is_active ? 'Activa' : 'Inactiva' }}
            </span>
            <div class="small text-secondary mt-3"><i class="bi bi-person"></i> Subida por {{ $song->owner->name }}</div>
            <hr>
            <p class="small text-secondary">Puedes incorporar esta canción al crear o editar un repertorio.</p>
            <a class="btn btn-outline-primary" href="{{ route('repertoires.index') }}"><i class="bi bi-list-ol"></i>Ir a repertorios</a>
        </div>
    </div>
</div>

<h2 class="h4 mb-3">Páginas y documentos</h2>
<div class="song-pages">
    @forelse($displayFiles as $file)
        @php($fileUrl = route('songs.files.show', [$song, $file, 'v' => $file->updated_at?->timestamp ?? $file->id]))
        <article class="card song-page">
            <div class="card-body">
                @if($file->file_type === 'pdf')
                    <div class="pdf-placeholder large"><i class="bi bi-file-earmark-pdf"></i><span>{{ $file->original_name }}</span></div>
                @else
                    <a href="{{ $fileUrl }}" target="_blank">
                        <img src="{{ $fileUrl }}" alt="{{ $file->original_name }}">
                    </a>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ $fileUrl }}">Abrir</a>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('songs.files.download', [$song, $file, 'v' => $file->updated_at?->timestamp ?? $file->id]) }}">Descargar original</a>
                </div>
            </div>
        </article>
    @empty
        <div class="card card-body text-secondary">Esta tonalidad todavía no tiene páginas.</div>
    @endforelse
</div>

@if($song->youtubeEmbedUrl())
    <div class="card card-body mb-4 song-video-card">
        <strong class="mb-2 d-block">Video de apoyo</strong>
        <div class="ratio ratio-16x9 song-video-frame">
            <iframe src="{{ $song->youtubeEmbedUrl() }}"
                    title="Video de apoyo de {{ $song->title }}"
                    loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen></iframe>
        </div>
    </div>
@endif

@can('delete', $song)
    <form class="app-danger-zone" method="POST" action="{{ route('songs.destroy', $song) }}" data-confirm="¿Archivar esta canción? Los archivos se conservarán.">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger" @disabled($songUsedInRepertoires)>
            <i class="bi bi-archive"></i>Archivar canción
        </button>
    </form>
@endcan
@endsection
