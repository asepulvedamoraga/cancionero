<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <span class="text-secondary small" data-selector-result-count>
        {{ $songs->total() }} {{ $songs->total() === 1 ? 'cancion disponible' : 'canciones disponibles' }}
    </span>
    <span class="small text-secondary" data-selector-loading hidden>
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Buscando...
    </span>
</div>

<div class="song-selector-grid" data-selector-results-grid>
    @forelse($songs as $song)
        <label class="song-selector-item" for="song-{{ $song->id }}" data-song-option data-song-id="{{ $song->id }}" data-song-title="{{ $song->title }}">
            <input class="form-check-input" type="checkbox" id="song-{{ $song->id }}" value="{{ $song->id }}" data-song-check>
            <span class="flex-grow-1">
                <span class="d-flex flex-wrap justify-content-between gap-2">
                    <strong>{{ $song->title }}</strong>
                    <span class="badge text-bg-light">{{ $song->page_count }} {{ $song->page_count === 1 ? 'pagina' : 'paginas' }}</span>
                </span>
                <span class="d-block small text-secondary">{{ $song->author ?: 'Autor no indicado' }}@if($song->performer) · {{ $song->performer }}@endif</span>
                <span class="d-block small text-secondary"><i class="bi bi-person"></i> Subida por {{ $song->owner->name }} {{ $song->user_id === auth()->id() ? '· Mia' : '' }}</span>
                <span class="d-block small text-secondary mt-1">{{ $song->category?->name ?: 'Sin categoria' }} · {{ $song->liturgicalMoment?->name ?: 'Sin momento' }}</span>
                @php($defaultTone = $song->tones->firstWhere('is_default', true) ?? $song->tones->first())
                <span class="d-block mt-2">
                    <span class="small text-secondary d-block mb-1">Tonalidad</span>
                    <select class="form-select form-select-sm" aria-label="Seleccionar tonalidad para {{ $song->title }}" data-song-tone>
                        @foreach($song->tones as $tone)
                            <option value="{{ $tone->id }}" @selected(optional($defaultTone)->id === $tone->id)>{{ $tone->name }}</option>
                        @endforeach
                    </select>
                </span>
                @if($song->page_count===0)
                    <span class="d-block small text-warning mt-1"><i class="bi bi-exclamation-triangle"></i> Sin paginas disponibles</span>
                @endif
            </span>
        </label>
    @empty
        <div class="text-center py-5 text-secondary song-selector-empty">
            <i class="bi bi-search display-6 d-block mb-2"></i>
            No hay canciones disponibles con esta busqueda. Prueba otro texto o limpia los filtros.
        </div>
    @endforelse
</div>

@if($songs->hasPages())
    <div class="mt-4" data-selector-pagination>{{ $songs->links() }}</div>
@endif
