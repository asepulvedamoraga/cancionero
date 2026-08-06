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
    $openToneModal = $errors->has('tone_catalog_id') || $errors->has('files') || $errors->has('files.*');
    $songUsedInRepertoires = (bool) ($songUsedInRepertoires ?? false);
@endphp

<div class="app-page-header">
    <div>
        <h1 class="h2">Editar cancion</h1>
        <p class="text-secondary mb-0">{{ $song->title }}</p>
        @if($activeTone)
            <div class="small text-secondary mt-1">Tonalidad activa: <strong>{{ $activeTone->name }}</strong></div>
        @endif
    </div>
    <a class="btn btn-outline-primary" href="{{ route('songs.show', ['song' => $song, 'tone' => $activeTone?->id]) }}">
        <i class="bi bi-eye"></i>Ver cancion
    </a>
</div>

<form class="card card-body mb-4"
      method="POST"
    action="{{ route('songs.update', ['song' => $song, 'tone' => $activeTone?->id]) }}">
    @csrf
    @method('PUT')
    @include('songs._form', ['selectedTone' => $activeTone])
</form>

<section class="card mb-4 tone-workspace">
    <div class="card-header app-section-header tone-workspace__header">
        <div>
            <strong>Documentos y páginas por tonalidad</strong>
            <div class="small text-secondary">Selecciona una tonalidad para ver, ordenar y subir archivos.</div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-end justify-content-end">
            <form method="GET" action="{{ route('songs.edit', $song) }}" class="tone-workspace__picker mb-0">
                <label class="form-label mb-1" for="tone">Tonalidad activa</label>
                <select class="form-select form-select-sm" id="tone" name="tone">
                    @foreach($song->tones as $tone)
                        <option value="{{ $tone->id }}" @selected($activeTone && $tone->id === $activeTone->id)>
                            {{ $tone->name }}{{ $tone->is_default ? ' · Predeterminada' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#toneCreateModal">
                <i class="bi bi-plus-lg"></i>Agregar tonalidad
            </button>
        </div>
    </div>

    <div class="card-body">
        @if($songUsedInRepertoires)
            <div class="alert alert-warning py-2 mb-3">
                Esta canción está en uno o más repertorios: puedes subir y reemplazar archivos, pero no eliminar archivos ni tonalidades.
            </div>
        @endif

        @if($activeTone)
            <div class="tone-workspace__active mb-3">
                <span class="badge text-bg-light border">Activa</span>
                <span class="tone-workspace__tone-name" title="{{ $activeTone->name }}">{{ $activeTone->name }}</span>
                @if($activeTone->is_default)
                    <span class="badge text-bg-success">Predeterminada</span>
                @endif
            </div>

            <form method="POST"
                  action="{{ route('songs.tones.files.store', ['song' => $song, 'tone' => $activeTone]) }}"
                  enctype="multipart/form-data"
                  class="song-upload-form tone-workspace__upload mb-4"
                  data-song-upload-form
                  data-song-upload-message="Estamos procesando los archivos para la tonalidad activa. Espera a que termine.">
                @csrf
                <div class="row g-2 align-items-start">
                    <div class="col-lg-9">
                        <label class="form-label" for="tone-files">Agregar archivos a {{ $activeTone->name }}</label>
                        <input class="form-control" id="tone-files" type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf" required>
                        <div class="form-text">Estos archivos quedarán asociados solo a esta tonalidad.</div>
                    </div>
                    <div class="col-lg-3 tone-workspace__upload-action">
                        <label class="form-label d-block opacity-0" aria-hidden="true">Subir</label>
                        <button class="btn btn-primary w-100" type="submit">Subir archivos</button>
                    </div>
                </div>
            </form>
        @endif

        @if($toneFiles->isNotEmpty())
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

                            @if($songUsedInRepertoires)
                                <button class="btn btn-sm btn-outline-danger w-100 mt-2" disabled title="No puedes eliminar archivos porque esta canción está en repertorios.">Eliminar</button>
                            @else
                                <form class="mt-2" method="POST" action="{{ route('songs.files.destroy', [$song, $file]) }}" data-confirm="Eliminar este archivo?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="small mt-3" data-sort-status></div>
        @else
            <div class="card card-body text-secondary mb-0">Esta tonalidad no tiene archivos todavía.</div>
        @endif

        <div class="mt-4 pt-3 border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <strong>Tonalidades de esta canción</strong>
                    <div class="small text-secondary">Todas las tonalidades viven aquí. Agrega, cambia la predeterminada o elimina sin salir de la vista.</div>
                </div>
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#toneCreateModal">
                    <i class="bi bi-plus-lg"></i> Nueva tonalidad
                </button>
            </div>

            <div class="d-flex flex-wrap gap-2 tone-workspace__active">
                @foreach($song->tones as $tone)
                    @php($toneFileCount = $song->files->where('song_tone_id', $tone->id)->count())
                    <div class="d-flex align-items-center gap-1 border rounded-pill px-2 py-1 bg-white">
                        <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button" data-tone-jump="{{ route('songs.edit', ['song' => $song, 'tone' => $tone->id]) }}">
                            {{ $tone->name }}
                        </button>
                        @if($tone->is_default)
                            <span class="badge text-bg-success">Predeterminada</span>
                        @else
                            <form method="POST" action="{{ route('songs.tones.default', ['song' => $song, 'tone' => $tone]) }}" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-link btn-sm p-0 text-decoration-none text-success">Hacer predeterminada</button>
                            </form>
                            @if($songUsedInRepertoires)
                                <span class="small text-muted">No eliminable en repertorio</span>
                            @else
                                <form method="POST"
                                      action="{{ route('songs.tones.destroy', ['song' => $song, 'tone' => $tone]) }}"
                                      class="d-inline"
                                      data-confirm="Eliminar la tonalidad {{ $tone->name }}? Se eliminarán {{ $toneFileCount }} archivo(s) asociados.">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-link btn-sm p-0 text-decoration-none text-danger">Eliminar</button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="toneCreateModal" tabindex="-1" aria-labelledby="toneCreateModalLabel" aria-hidden="true" data-tone-create-modal @if($openToneModal) data-tone-create-modal-open="1" @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST"
                  action="{{ route('songs.tones.store', $song) }}"
                  enctype="multipart/form-data"
                  data-song-upload-form
                  data-song-upload-message="Estamos creando la tonalidad y procesando sus archivos. No cierres esta ventana hasta que termine.">
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 mb-0" id="toneCreateModalLabel">Agregar tonalidad</h2>
                        <div class="small text-secondary">La tonalidad nueva quedará disponible de inmediato en esta misma vista.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="new-tone-catalog">Tonalidad</label>
                            <select class="form-select" id="new-tone-catalog" name="tone_catalog_id" required>
                                <option value="">Selecciona una tonalidad</option>
                                @foreach($toneCatalogs as $toneOption)
                                    @php($existsInSong = $song->tones->contains(fn ($songTone) => (int) $songTone->tone_catalog_id === (int) $toneOption->id))
                                    <option value="{{ $toneOption->id }}" @selected((string) old('tone_catalog_id') === (string) $toneOption->id) @disabled($existsInSong)>
                                        {{ $toneOption->name }}{{ $existsInSong ? ' · Ya agregada' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="new-tone-files">Archivos iniciales</label>
                            <input class="form-control" id="new-tone-files" type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf">
                            <div class="form-text">Puedes dejarla vacía y subir archivos después, o cargarlos ahora para que la tonalidad nazca completa.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary">Agregar tonalidad</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const select = document.getElementById('tone');
        const modal = document.querySelector('[data-tone-create-modal]');
        const modalElement = modal ? new bootstrap.Modal(modal) : null;

        const scrollKey = 'songs-edit-scroll-position';
        const focusKey = 'songs-edit-restore-tone-focus';
        const saved = sessionStorage.getItem(scrollKey);
        if (saved !== null) {
            const y = Number(saved);
            if (!Number.isNaN(y)) {
                window.scrollTo({ top: y, behavior: 'auto' });
            }
            sessionStorage.removeItem(scrollKey);
        }

        if (sessionStorage.getItem(focusKey) === '1') {
            select.focus({ preventScroll: true });
            sessionStorage.removeItem(focusKey);
        }

        if (select) {
            select.addEventListener('change', () => {
                sessionStorage.setItem(scrollKey, String(window.scrollY));
                sessionStorage.setItem(focusKey, '1');
                select.form?.submit();
            });
        }

        document.querySelectorAll('[data-tone-jump]').forEach((button) => {
            button.addEventListener('click', () => {
                const url = button.getAttribute('data-tone-jump');
                if (url) {
                    window.location.href = url;
                }
            });
        });

        if (modalElement && modal?.dataset.toneCreateModalOpen === '1') {
            modalElement.show();
        }
    })();
</script>
@endpush
