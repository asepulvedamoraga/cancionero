@php($editing = isset($song))
@php($liturgicalCategorySlugs = collect(config('cancionero.liturgical_categories.slugs', ['musica-liturgica', 'musica-religiosa']))->filter()->values())
@php($liturgicalCategoryIds = collect(config('cancionero.liturgical_categories.ids', [1, 2]))->map(fn ($id) => (int) $id)->filter()->values())

<section class="app-form-section">
	<header class="app-form-section__head">
		<h2 class="app-form-section__title">Identificación</h2>
		<p class="app-form-section__text">Define título, slug y autoría principal.</p>
	</header>

	<div class="app-form-grid app-form-grid--3">
		<div class="app-field app-field--full"
			 @unless($editing)
				 data-song-duplicate-check
				 data-suggestions-url="{{ route('songs.suggestions') }}"
			 @endunless>
			<label class="app-label" for="title">Título <span class="required">*</span></label>
			<input class="form-control" id="title" name="title" required maxlength="255" value="{{ old('title', $song->title ?? '') }}" autocomplete="off">
			@unless($editing)
				<div class="song-duplicate-results" data-song-duplicate-results hidden aria-live="polite"></div>
			@endunless
		</div>

		<div class="app-field">
			<label class="app-label" for="slug">Slug</label>
			<input class="form-control" id="slug" name="slug" maxlength="255" value="{{ old('slug', $song->slug ?? '') }}">
			<div class="app-control-hint">Déjalo vacío para generarlo desde el título.</div>
		</div>

		<div class="app-field">
			<label class="app-label" for="author">Autor</label>
			<input class="form-control" id="author" name="author" value="{{ old('author', $song->author ?? '') }}">
		</div>

		<div class="app-field">
			<label class="app-label" for="performer">Intérprete</label>
			<input class="form-control" id="performer" name="performer" value="{{ old('performer', $song->performer ?? '') }}">
		</div>

		<div class="app-field">
			<label class="app-label" for="musical_key">Tonalidad base</label>
			<select class="form-select" id="musical_key" name="musical_key">
				@php($selectedToneName = old('musical_key', $song->musical_key ?? 'Original'))
				@foreach($toneCatalogs as $toneOption)
					<option value="{{ $toneOption->name }}" @selected($selectedToneName === $toneOption->name)>{{ $toneOption->name }}</option>
				@endforeach
			</select>
			<div class="app-control-hint">Se usa como tonalidad inicial al crear la canción.</div>
		</div>

		<div class="app-field">
			<label class="app-label" for="category_id">Categoría</label>
			<select class="form-select" id="category_id" name="category_id" data-song-category-select>
				<option value="">Sin categoría</option>
				@foreach($categories as $item)
					<option value="{{ $item->id }}" data-category-slug="{{ $item->slug }}" @selected(old('category_id', $song->category_id ?? '') == $item->id)>{{ $item->name }}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="app-form-grid app-form-grid--3 mt-2" data-song-liturgical-fields data-liturgical-category-slugs='@json($liturgicalCategorySlugs)' data-liturgical-category-ids='@json($liturgicalCategoryIds)'>

		<div class="app-field" data-song-liturgical-field>
			<label class="app-label" for="liturgical_moment_id">Momento litúrgico principal</label>
			<select class="form-select" id="liturgical_moment_id" name="liturgical_moment_id">
				<option value="">Sin momento</option>
				@foreach($moments as $item)
					<option value="{{ $item->id }}" @selected(old('liturgical_moment_id', $song->liturgical_moment_id ?? '') == $item->id)>{{ $item->name }}</option>
				@endforeach
			</select>
		</div>

		<div class="app-field app-field--full" data-song-liturgical-field>
			<label class="app-label">Tiempos litúrgicos</label>
			<div class="d-flex flex-wrap gap-3">
				@php($selected = old('liturgical_seasons', $editing ? $song->liturgicalSeasons->pluck('id')->all() : []))
				@foreach($seasons as $item)
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="liturgical_seasons[]" id="season-{{ $item->id }}" value="{{ $item->id }}" @checked(in_array($item->id, $selected))>
						<label class="form-check-label" for="season-{{ $item->id }}">{{ $item->name }}</label>
					</div>
				@endforeach
			</div>
		</div>
	</div>
</section>

<section class="app-form-section">
	<header class="app-form-section__head">
		<h2 class="app-form-section__title">Contenido y publicación</h2>
		<p class="app-form-section__text">Detalles de referencia, visibilidad interna y notas.</p>
	</header>

	<div class="app-form-grid app-form-grid--2">
		<div class="app-field">
			<label class="app-label" for="source">Fuente</label>
			<input class="form-control" id="source" name="source" value="{{ old('source', $song->source ?? '') }}">
		</div>

		<div class="app-field">
			<label class="app-label" for="video_url">Video de apoyo de YouTube</label>
			<input class="form-control" id="video_url" name="video_url" type="url" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('video_url', $song->video_url ?? '') }}">
		</div>

		<div class="app-field">
			<label class="app-label" for="is_active">Estado de canción</label>
			<div class="form-check form-switch mt-2">
				<input type="hidden" name="is_active" value="0">
				<input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $song->is_active ?? true))>
				<label class="form-check-label" for="is_active">Canción activa</label>
			</div>
		</div>

		<div class="app-field app-field--full">
			<label class="app-label" for="notes">Observaciones</label>
			<textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes', $song->notes ?? '') }}</textarea>
		</div>

		@unless($editing)
			<div class="app-field app-field--full">
				<label class="app-label" for="files">Archivos</label>
				<input class="form-control" id="files" name="files[]" type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf" data-file-preview>
				<div class="app-control-hint">
					JPG, PNG, WebP o un PDF. Máximo {{ config('cancionero.upload_max_mb') }} MB por archivo.
					@unless($imagickAvailable)
						La conversión automática de PDF está desactivada: se conservará el archivo original.
					@endunless
				</div>
				<div class="file-preview-grid mt-2" data-file-preview-list></div>
			</div>
		@endunless
	</div>
</section>

<div class="app-form-actions mt-3">
	<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i>{{ $editing ? 'Guardar cambios' : 'Crear canción' }}</button>
	<a class="btn btn-outline-secondary" href="{{ route('songs.index') }}">Cancelar</a>
</div>
