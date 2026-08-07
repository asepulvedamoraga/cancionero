@extends('layouts.app')
@section('title', $definition['title'])
@section('content')
<div class="app-page-header">
    <div>
        <a class="app-back-link" href="{{ route('admin.settings') }}"><i class="bi bi-arrow-left"></i> Configuración</a>
        <h1 class="mt-2">{{ $definition['title'] }}</h1>
        <p>{{ $definition['description'] }}</p>
    </div>
</div>

<div class="row g-4 align-items-start">
    <div class="col-lg-4">
        <div class="card app-sticky-card app-form-shell">
            <div class="card-body p-lg-4">
                <div class="app-card-heading">
                    <span class="app-card-icon"><i class="bi bi-plus-lg"></i></span>
                    <div><h2>Agregar {{ $definition['singular'] }}</h2><p>Crea una nueva opción para los formularios.</p></div>
                </div>
                <form method="POST" action="{{ route('admin.catalogs.store', $catalog) }}">@csrf
                    <section class="app-form-section">
                        <div class="app-form-grid">
                            <div class="app-field"><label class="app-label" for="new-name">Nombre <span class="required">*</span></label><input class="form-control" id="new-name" name="name" value="{{ old('name') }}" required maxlength="255"></div>
                            <div class="app-field"><label class="app-label" for="new-slug">Slug <span class="text-secondary fw-normal">(opcional)</span></label><input class="form-control" id="new-slug" name="slug" value="{{ old('slug') }}" maxlength="255"><div class="app-control-hint">Si se deja vacío, se genera desde el nombre.</div></div>
                            <div class="app-field"><label class="app-label" for="new-description">Descripción</label><textarea class="form-control" id="new-description" name="description" rows="3" maxlength="2000">{{ old('description') }}</textarea></div>
                            <div class="app-field"><label class="app-label" for="new-order">Orden</label><input class="form-control" id="new-order" type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" required></div>
                        </div>
                        <input type="hidden" name="is_active" value="1">
                        <div class="app-form-actions"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i>Agregar</button></div>
                    </section>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <form class="card card-body app-filter-card app-form-shell" method="GET" role="search">
            <div class="row g-2">
                <div class="col-md-5"><label class="visually-hidden" for="catalog-q">Buscar</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" id="catalog-q" name="q" value="{{ request('q') }}" placeholder="Nombre o descripción"></div></div>
                <div class="col-sm-4 col-md-3"><label class="visually-hidden" for="catalog-status">Estado</label><select class="form-select" id="catalog-status" name="status"><option value="">Cualquier estado</option><option value="active" @selected(request('status') === 'active')>Activas</option><option value="inactive" @selected(request('status') === 'inactive')>Inactivas</option></select></div>
                <div class="col-sm-4 col-md-2"><label class="visually-hidden" for="catalog-per-page">Resultados por página</label><select class="form-select" id="catalog-per-page" name="per_page" onchange="this.form.submit()"><option value="12" @selected($items->perPage() === 12)>12 por página</option><option value="24" @selected($items->perPage() === 24)>24 por página</option><option value="48" @selected($items->perPage() === 48)>48 por página</option></select></div>
                <div class="col-sm-4 col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel"></i>Filtrar</button><a class="btn btn-outline-secondary" href="{{ route('admin.catalogs.index', $catalog) }}"><i class="bi bi-x-lg"></i>Limpiar</a></div>
            </div>
        </form>

        <div class="app-list-toolbar">
            <p class="app-result-count">@if($items->total()) Mostrando <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong> de <strong>{{ $items->total() }}</strong> opciones @else Sin resultados @endif</p>
            @if($items->hasPages())<div class="app-list-pagination">{{ $items->onEachSide(1)->links() }}</div>@endif
        </div>

        <div class="catalog-list">
            @forelse($items as $item)
                <details class="card catalog-item app-form-shell">
                    <summary class="catalog-item-summary">
                        <span class="catalog-chevron"><i class="bi bi-chevron-right"></i></span>
                        <span class="flex-grow-1"><strong>{{ $item->name }}</strong><small>{{ $item->songs_count }} {{ $item->songs_count === 1 ? 'canción asociada' : 'canciones asociadas' }}</small></span>
                        <span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $item->is_active ? 'Activa' : 'Inactiva' }}</span>
                    </summary>
                    <form method="POST" action="{{ route('admin.catalogs.update', [$catalog, $item]) }}">@csrf @method('PUT')
                        <div class="card-body catalog-item-body">
                            <section class="app-form-section">
                                <div class="app-form-grid app-form-grid--2">
                                    <div class="app-field"><label class="app-label" for="name-{{ $item->id }}">Nombre</label><input class="form-control" id="name-{{ $item->id }}" name="name" value="{{ $item->name }}" required maxlength="255"></div>
                                    <div class="app-field"><label class="app-label" for="slug-{{ $item->id }}">Slug</label><input class="form-control" id="slug-{{ $item->id }}" name="slug" value="{{ $item->slug }}" maxlength="255"></div>
                                    <div class="app-field app-field--full"><label class="app-label" for="description-{{ $item->id }}">Descripción</label><textarea class="form-control" id="description-{{ $item->id }}" name="description" rows="2" maxlength="2000">{{ $item->description }}</textarea></div>
                                    <div class="app-field"><label class="app-label" for="order-{{ $item->id }}">Orden</label><input class="form-control" id="order-{{ $item->id }}" type="number" min="0" name="sort_order" value="{{ $item->sort_order }}" required></div>
                                    <div class="app-field d-flex flex-wrap align-items-end justify-content-sm-end gap-3">
                                        <input type="hidden" name="is_active" value="0">
                                        <div class="form-check form-switch mb-2"><input class="form-check-input" id="active-{{ $item->id }}" type="checkbox" name="is_active" value="1" @checked($item->is_active)><label class="form-check-label" for="active-{{ $item->id }}">Disponible en formularios</label></div>
                                        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i>Guardar cambios</button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </form>
                </details>
            @empty
                <div class="card app-form-shell"><div class="app-empty-state app-empty-state--compact"><i class="bi bi-inboxes"></i><h2 class="h5">No se encontraron opciones</h2><p class="mb-0">Cambia los filtros o agrega una nueva opción.</p></div></div>
            @endforelse
        </div>
        <div class="app-list-footer">{{ $items->onEachSide(1)->links() }}</div>
    </div>
</div>
@endsection