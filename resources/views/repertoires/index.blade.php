@extends('layouts.app')
@section('title','Repertorios')
@section('content')
<div class="app-page-header">
	<div>
		<h1 class="h2 mb-1">Repertorios</h1>
		<p class="text-secondary mb-0">Organiza las canciones de cada celebración o evento.</p>
	</div>
	<div class="app-page-actions">
		<a class="btn btn-primary" href="{{ route('repertoires.create') }}"><i class="bi bi-plus-lg"></i> Nuevo repertorio</a>
	</div>
</div>

<nav class="app-tabs mb-4" aria-label="Secciones de repertorios">
	<a class="app-tab active" href="{{ route('repertoires.index') }}" aria-current="page"><i class="bi bi-list-ol"></i>Repertorios</a>
	<a class="app-tab" href="{{ route('repertoires.trashed') }}"><i class="bi bi-trash3"></i>Papelera @if($trashedCount)<span class="badge text-bg-light">{{ $trashedCount }}</span>@endif</a>
</nav>

<form class="card card-body app-filter-card app-form-shell" method="GET">
	<div class="row g-2">
		<div class="col-lg-4">
			<label class="visually-hidden" for="repertoire-q">Buscar por nombre o lugar</label>
			<input id="repertoire-q" class="form-control" name="q" value="{{ request('q') }}" placeholder="Nombre o lugar">
		</div>

		<div class="col-sm-6 col-lg-2">
			<label class="visually-hidden" for="event-type">Tipo de evento</label>
			<input id="event-type" class="form-control" name="event_type" value="{{ request('event_type') }}" placeholder="Tipo de evento">
		</div>

		<div class="col-sm-6 col-lg-2">
			<label class="visually-hidden" for="repertoire-status">Estado</label>
			<select id="repertoire-status" class="form-select" name="status">
				<option value="">Estado</option>
				<option value="draft" @selected(request('status')==='draft')>Borrador</option>
				<option value="ready" @selected(request('status')==='ready')>Listo</option>
				<option value="archived" @selected(request('status')==='archived')>Archivado</option>
			</select>
		</div>

		<div class="col-sm-6 col-lg-2">
			<label class="visually-hidden" for="repertoire-per-page">Resultados por página</label>
			<select id="repertoire-per-page" class="form-select" name="per_page" onchange="this.form.submit()">
				<option value="12" @selected($repertoires->perPage() === 12)>12 por página</option>
				<option value="24" @selected($repertoires->perPage() === 24)>24 por página</option>
				<option value="48" @selected($repertoires->perPage() === 48)>48 por página</option>
			</select>
		</div>

		<div class="col-sm-6 col-lg-2">
			<label class="visually-hidden" for="repertoire-sort">Ordenar por</label>
			<select id="repertoire-sort" class="form-select" name="sort">
				<option value="event">Próximos eventos</option>
				<option value="name" @selected(request('sort')==='name')>Nombre</option>
				<option value="newest" @selected(request('sort')==='newest')>Más recientes</option>
				<option value="oldest" @selected(request('sort')==='oldest')>Más antiguos</option>
			</select>
		</div>

		<div class="col-sm-6 col-lg-2 d-flex gap-2">
			<button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i>Filtrar</button>
			<a class="btn btn-outline-secondary" href="{{ route('repertoires.index') }}"><i class="bi bi-x-circle"></i>Limpiar</a>
		</div>
	</div>
</form>

<div class="app-list-toolbar">
	<p class="app-result-count">
		@if($repertoires->total())
			Mostrando <strong>{{ $repertoires->firstItem() }}–{{ $repertoires->lastItem() }}</strong> de <strong>{{ $repertoires->total() }}</strong> repertorios
		@else
			Sin resultados
		@endif
	</p>
	@if($repertoires->hasPages())
		<div class="app-list-pagination">{{ $repertoires->onEachSide(1)->links() }}</div>
	@endif
</div>

<div class="row g-3">
	@forelse($repertoires as $repertoire)
		<div class="col-md-6 col-xl-4">
			<article class="card repertoire-card h-100">
				<div class="card-body">
					<div class="d-flex justify-content-between gap-2">
						<h2 class="h5">{{ $repertoire->name }}</h2>
						<span class="badge {{ $repertoire->status === 'ready' ? 'text-bg-success' : ($repertoire->status === 'archived' ? 'text-bg-secondary' : 'text-bg-warning') }}">{{ ['draft'=>'Borrador','ready'=>'Listo','archived'=>'Archivado'][$repertoire->status] }}</span>
					</div>
					<p class="text-secondary mb-2">{{ $repertoire->event_type ?: 'Evento no indicado' }}</p>
					<div class="small text-secondary mb-2"><i class="bi bi-person"></i> {{ $repertoire->owner->name }} · <i class="bi {{ $repertoire->visibility === 'public' ? 'bi-globe-americas' : 'bi-lock' }}"></i> {{ $repertoire->visibility === 'public' ? 'Público' : 'Privado' }}</div>
					<div class="small text-secondary"><i class="bi bi-calendar3"></i> {{ $repertoire->event_date?->format('d-m-Y') ?: 'Sin fecha' }} @if($repertoire->event_time) · {{ $repertoire->event_time->format('H:i') }}@endif</div>
					<div class="small text-secondary mt-1"><i class="bi bi-music-note-list"></i> {{ $repertoire->songs_count }} canciones</div>
				</div>
				<div class="card-footer border-0 app-page-actions">
					<a class="btn btn-sm btn-primary" href="{{ route('repertoires.show',$repertoire) }}"><i class="bi bi-eye"></i>Ver detalle</a>
					@can('update', $repertoire)
						<a class="btn btn-sm btn-outline-secondary" href="{{ route('repertoires.edit',$repertoire) }}"><i class="bi bi-pencil"></i>Editar</a>
					@endcan
				</div>
			</article>
		</div>
	@empty
		<div class="col-12">
			<div class="card app-empty-state">
				<i class="bi bi-list-ol display-4 text-secondary"></i>
				<h2 class="h4 mt-3">No se encontraron repertorios</h2>
				<p class="text-secondary">Crea el primero o cambia los filtros.</p>
			</div>
		</div>
	@endforelse
</div>

<div class="app-list-footer">{{ $repertoires->onEachSide(1)->links() }}</div>
@endsection
