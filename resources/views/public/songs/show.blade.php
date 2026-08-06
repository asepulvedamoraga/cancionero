@extends('layouts.public')
@section('title', $song->title)

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('public.home') }}" class="app-back-link mb-2"><i class="bi bi-arrow-left"></i>Volver al inicio</a>
        <h1 class="mb-1">{{ $song->title }}</h1>
        <p class="text-secondary mb-0">{{ $song->author ?: 'Autor no indicado' }} · Compartida por {{ $song->owner->name }}</p>
    </div>
    <a class="btn btn-primary" href="{{ route('public.songs.read', ['song' => $song->slug, 'tone' => $selectedTone->id]) }}"><i class="bi bi-book-half"></i>Modo lectura</a>
</div>

@if($song->notes)
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6 mb-2">Notas</h2>
            <p class="mb-0 text-secondary">{{ $song->notes }}</p>
        </div>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100"><div class="card-body"><small class="text-secondary">Tonalidad</small><p class="mb-0 fw-semibold">{{ $selectedTone->name }}</p></div></div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100"><div class="card-body"><small class="text-secondary">Archivos</small><p class="mb-0 fw-semibold">{{ $displayFiles->count() }}</p></div></div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100"><div class="card-body"><small class="text-secondary">Categoría</small><p class="mb-0 fw-semibold">{{ $song->category?->name ?: 'No definida' }}</p></div></div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100"><div class="card-body"><small class="text-secondary">Momento litúrgico</small><p class="mb-0 fw-semibold">{{ $song->liturgicalMoment?->name ?: 'No definido' }}</p></div></div>
    </div>
</div>

@if($song->tones->count() > 1)
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6 mb-2">Tonalidades</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach($song->tones as $tone)
                    <a class="btn btn-sm {{ $tone->id === $selectedTone->id ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('public.songs.show', ['song' => $song->slug, 'tone' => $tone->id]) }}">{{ $tone->name }}</a>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header app-section-header">
        <h2 class="mb-0">Archivos disponibles</h2>
    </div>
    <div class="card-body">
        @if($displayFiles->isEmpty())
            <p class="text-secondary mb-0">Esta tonalidad no tiene archivos disponibles por ahora.</p>
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
    </div>
</div>

@if($publicRepertoires->isNotEmpty())
    <div class="card">
        <div class="card-header app-section-header">
            <h2 class="mb-0">También está en repertorios públicos</h2>
        </div>
        <div class="card-body d-flex flex-wrap gap-2">
            @foreach($publicRepertoires as $repertoire)
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">{{ $repertoire->name }}</a>
            @endforeach
        </div>
    </div>
@endif
@endsection
