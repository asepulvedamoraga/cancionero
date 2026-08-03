@extends('layouts.app')
@section('title','Editar '.$repertoire->name)
@section('content')
<div class="app-page-header">
    <div><h1 class="h2 mb-1">Editar repertorio</h1><p class="text-secondary mb-0">{{ $repertoire->name }}</p></div>
    <a class="btn btn-outline-primary" href="{{ route('repertoires.show',$repertoire) }}"><i class="bi bi-eye"></i>Ver repertorio</a>
</div>

<form class="card card-body mb-4" method="POST" action="{{ route('repertoires.update',$repertoire) }}">
    @csrf @method('PUT') @include('repertoires._form')
</form>

<section class="card mb-4" id="song-selector" data-song-selector>
    <div class="card-header bg-white py-3">
        <h2 class="h5 mb-1">Agregar canciones</h2>
        <p class="text-secondary small mb-0">Busca, filtra y selecciona varias canciones para agregarlas de una sola vez.</p>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('repertoires.edit',$repertoire) }}#song-selector" role="search">
            <div class="row g-2 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label" for="song_q">Título, autor o intérprete</label>
                    <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" id="song_q" name="song_q" value="{{ request('song_q') }}" placeholder="Ej.: Pescador de hombres" autocomplete="off"></div>
                </div>
                <div class="col-sm-4 col-lg-2"><label class="form-label" for="category_id">Categoría</label><select class="form-select" id="category_id" name="category_id"><option value="">Todas</option>@foreach($categories as $item)<option value="{{ $item->id }}" @selected(request('category_id')==$item->id)>{{ $item->name }}</option>@endforeach</select></div>
                <div class="col-sm-4 col-lg-2"><label class="form-label" for="liturgical_moment_id">Momento</label><select class="form-select" id="liturgical_moment_id" name="liturgical_moment_id"><option value="">Todos</option>@foreach($moments as $item)<option value="{{ $item->id }}" @selected(request('liturgical_moment_id')==$item->id)>{{ $item->name }}</option>@endforeach</select></div>
                <div class="col-sm-4 col-lg-2"><label class="form-label" for="liturgical_season_id">Tiempo</label><select class="form-select" id="liturgical_season_id" name="liturgical_season_id"><option value="">Todos</option>@foreach($seasons as $item)<option value="{{ $item->id }}" @selected(request('liturgical_season_id')==$item->id)>{{ $item->name }}</option>@endforeach</select></div>
                <div class="col-lg-1 d-grid"><button class="btn btn-primary" type="submit">Buscar</button></div>
            </div>
            @if(request()->hasAny(['song_q','category_id','liturgical_moment_id','liturgical_season_id']))<div class="mt-2"><a class="small" href="{{ route('repertoires.edit',$repertoire) }}#song-selector"><i class="bi bi-x-circle"></i> Limpiar búsqueda y filtros</a></div>@endif
        </form>
    </div>

    <form method="POST" action="{{ route('repertoires.songs.store',$repertoire) }}">
        @csrf
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <span class="text-secondary small">{{ $songs->total() }} {{ $songs->total() === 1 ? 'canción disponible' : 'canciones disponibles' }}</span>
                <span class="small fw-semibold" data-selected-count>0 canciones seleccionadas</span>
            </div>
            <div class="song-selector-grid">
                @forelse($songs as $song)
                    <label class="song-selector-item" for="song-{{ $song->id }}">
                        <input class="form-check-input" type="checkbox" id="song-{{ $song->id }}" name="song_ids[]" value="{{ $song->id }}">
                        <span class="flex-grow-1">
                            <span class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ $song->title }}</strong><span class="badge text-bg-light">{{ $song->page_count }} {{ $song->page_count === 1 ? 'página' : 'páginas' }}</span></span>
                            <span class="d-block small text-secondary">{{ $song->author ?: 'Autor no indicado' }}@if($song->performer) · {{ $song->performer }}@endif</span><span class="d-block small text-secondary"><i class="bi bi-person"></i> Subida por {{ $song->owner->name }} {{ $song->user_id === auth()->id() ? '· Mía' : '' }}</span>
                            <span class="d-block small text-secondary mt-1">{{ $song->category?->name ?: 'Sin categoría' }} · {{ $song->liturgicalMoment?->name ?: 'Sin momento' }}</span>
                            @if($song->page_count===0)<span class="d-block small text-warning mt-1"><i class="bi bi-exclamation-triangle"></i> Sin páginas disponibles</span>@endif
                        </span>
                    </label>
                @empty
                    <div class="text-center py-5 text-secondary song-selector-empty"><i class="bi bi-search display-6 d-block mb-2"></i>No hay canciones disponibles con esta búsqueda. Prueba otro texto o limpia los filtros.</div>
                @endforelse
            </div>
            @if($songs->hasPages())<div class="mt-4">{{ $songs->links() }}</div>@endif
        </div>
        @if($songs->isNotEmpty())<div class="card-footer bg-white song-selector-actions"><span class="small text-secondary">Las canciones se agregarán al final del repertorio.</span><button class="btn btn-primary" type="submit" data-add-selected disabled><i class="bi bi-plus-lg"></i> Agregar seleccionadas</button></div>@endif
    </form>
</section>

<section class="card mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h2 class="h5 mb-1">Canciones del repertorio</h2><p class="small text-secondary mb-0">{{ $repertoire->songs->count() }} {{ $repertoire->songs->count() === 1 ? 'canción seleccionada' : 'canciones seleccionadas' }}</p></div>@if($repertoire->songs->isNotEmpty())<span class="small text-secondary"><i class="bi bi-grip-vertical"></i> Arrastra para ordenar</span>@endif</div>
    <div class="card-body">
        <div class="repertoire-song-list" data-repertoire-songs data-reorder-url="{{ route('repertoires.songs.reorder',$repertoire) }}">
            @forelse($repertoire->songs as $song)
                <article class="repertoire-song-item" data-song-id="{{ $song->id }}">
                    <button class="repertoire-drag-handle" type="button" aria-label="Arrastrar {{ $song->title }}"><i class="bi bi-grip-vertical"></i></button>
                    <span class="repertoire-position">{{ $loop->iteration }}</span>
                    <div class="flex-grow-1"><div class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ $song->title }}</strong><span class="badge text-bg-light">{{ $song->page_count }} {{ $song->page_count === 1 ? 'página' : 'páginas' }}</span></div><div class="small text-secondary">{{ $song->author ?: 'Autor no indicado' }}</div><div class="small text-secondary"><i class="bi bi-person"></i> Subida por {{ $song->owner->name }}</div>@if($song->page_count===0)<div class="small text-warning"><i class="bi bi-exclamation-triangle"></i> Sin páginas disponibles</div>@endif<form class="mt-2" method="POST" action="{{ route('repertoires.songs.update',[$repertoire,$song]) }}">@csrf @method('PUT')<div class="input-group input-group-sm"><input class="form-control" name="notes" value="{{ $song->pivot->notes }}" maxlength="2000" placeholder="Nota particular para esta canción"><button class="btn btn-outline-secondary">Guardar nota</button></div></form></div>
                    <form method="POST" action="{{ route('repertoires.songs.destroy',[$repertoire,$song]) }}" data-confirm="¿Quitar esta canción del repertorio?">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-outline-danger" title="Quitar canción" aria-label="Quitar {{ $song->title }}"><i class="bi bi-x-lg"></i></button></form>
                </article>
            @empty
                <div class="text-center py-5 text-secondary" data-empty-repertoire><i class="bi bi-music-note-list display-6 d-block mb-2"></i>Aún no hay canciones. Selecciónalas en la sección superior.</div>
            @endforelse
        </div>
        <div class="small mt-3" data-repertoire-sort-status></div>
    </div>
</section>

<form class="app-danger-zone" method="POST" action="{{ route('repertoires.destroy',$repertoire) }}" data-confirm="¿Enviar este repertorio a la papelera? El enlace público quedará desactivado.">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash3"></i>Enviar a la papelera</button></form>
@endsection