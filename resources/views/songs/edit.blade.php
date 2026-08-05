@extends('layouts.app')
@section('title', 'Editar '.$song->title)

@section('content')
@php
    $activeTone = $selectedTone ?? $song->tones->first();
    if (! $activeTone) {
        $activeTone = $song->ensureDefaultTone();
        if ($song->tones->isEmpty()) {
            $song->setRelation('tones', collect([$activeTone]));
        }
    }

    $toneFiles = $activeTone ? $song->filesForTone($activeTone->id) : collect();
@endphp

<div class="app-page-header">
    <div>
        <h1 class="h2">Editar cancion</h1>
        <p class="text-secondary mb-0">{{ $song->title }}</p>
    </div>
    <a class="btn btn-outline-primary" href="{{ route('songs.show', ['song' => $song, 'tone' => $activeTone?->id]) }}">
        <i class="bi bi-eye"></i>Ver cancion
    </a>
</div>

<div class="card card-body mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <strong>Tonalidades</strong>
        @foreach($song->tones as $tone)
            <a class="btn btn-sm {{ $activeTone && $tone->id === $activeTone->id ? 'btn-primary' : 'btn-outline-secondary' }}"
               href="{{ route('songs.edit', ['song' => $song, 'tone' => $tone->id]) }}">
                {{ $tone->name }}
            </a>
        @endforeach
    </div>

    <div class="d-flex flex-wrap gap-2">
        @foreach($song->tones as $tone)
            <form method="POST" action="{{ route('songs.tones.default', ['song' => $song, 'tone' => $tone]) }}">
                @csrf
                @method('PUT')
                <button class="btn btn-sm {{ $tone->is_default ? 'btn-success' : 'btn-outline-success' }}" @disabled($tone->is_default)>
                    {{ $tone->is_default ? 'Predeterminada' : 'Marcar predeterminada: '.$tone->name }}
                </button>
            </form>

            @if(! $tone->is_default)
                <form method="POST"
                      action="{{ route('songs.tones.destroy', ['song' => $song, 'tone' => $tone]) }}"
                      data-confirm="Eliminar la tonalidad {{ $tone->name }}? Los archivos se reasignaran automaticamente.">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Eliminar {{ $tone->name }}</button>
                </form>
            @endif
        @endforeach
    </div>

    <form class="row g-2 mt-3" method="POST" action="{{ route('songs.tones.store', $song) }}">
        @csrf
        <div class="col-md-6">
            <input class="form-control form-control-sm" name="name" maxlength="60" placeholder="Nueva tonalidad (ej: Re, Fa#, Sol)">
        </div>
        <div class="col-md-3">
            <button class="btn btn-sm btn-outline-primary">Agregar tonalidad</button>
        </div>
    </form>
</div>

<form class="card card-body mb-4 song-upload-form"
      method="POST"
      action="{{ route('songs.update', $song) }}"
      enctype="multipart/form-data"
      data-song-upload-form
      data-song-upload-message="Estamos convirtiendo el PDF y generando sus paginas. No cierres esta ventana hasta que termine.">
    @csrf
    @method('PUT')
    @include('songs._form', ['selectedTone' => $activeTone])
</form>

@if($toneFiles->isNotEmpty())
    <div class="card">
        <div class="card-header app-section-header">
            <strong>Paginas y documentos · {{ $activeTone?->name ?? 'Sin tonalidad' }}</strong>
            <span class="text-secondary ms-2">Arrastra para ordenar</span>
        </div>
        <div class="card-body">
            <div class="song-file-grid" data-song-files data-reorder-url="{{ route('songs.files.reorder', $song) }}">
                @foreach($toneFiles as $file)
                    <article class="song-file-card" data-file-id="{{ $file->id }}">
                        <div class="drag-handle" title="Arrastrar">::</div>

                        @if($file->file_type === 'pdf')
                            <div class="pdf-placeholder"><i class="bi bi-file-earmark-pdf"></i><span>PDF</span></div>
                        @else
                            <img src="{{ route('songs.files.preview', [$song, $file]) }}" alt="Vista previa de {{ $file->original_name }}">
                        @endif

                        <div class="p-2">
                            <small class="d-block text-truncate">{{ $file->original_name }}</small>

                            <div class="btn-group btn-group-sm mt-2">
                                <a class="btn btn-outline-primary" href="{{ route('songs.files.show', [$song, $file]) }}" target="_blank">Abrir</a>
                                <a class="btn btn-outline-secondary" href="{{ route('songs.files.download', [$song, $file]) }}">Descargar</a>
                            </div>

                            <form class="mt-2 song-upload-form"
                                  method="POST"
                                  action="{{ route('songs.files.replace', [$song, $file]) }}"
                                  enctype="multipart/form-data"
                                  data-song-upload-form
                                  data-song-upload-message="Estamos reemplazando el archivo y convirtiendo el PDF. Espera a que termine el proceso.">
                                @csrf
                                @method('PUT')
                                <label class="btn btn-sm btn-outline-secondary w-100">
                                    Reemplazar
                                    <input class="visually-hidden" type="file" name="file" accept="image/jpeg,image/png,image/webp,application/pdf" required onchange="this.form.submit()">
                                </label>
                            </form>

                            <form class="mt-2" method="POST" action="{{ route('songs.files.destroy', [$song, $file]) }}" data-confirm="Eliminar este archivo?">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="small mt-3" data-sort-status></div>
        </div>
    </div>
@else
    <div class="card card-body text-secondary mb-4">Esta tonalidad no tiene archivos todavia.</div>
@endif
@endsection
