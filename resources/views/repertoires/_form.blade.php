<section class="app-form-section">
    <header class="app-form-section__head">
        <h2 class="app-form-section__title">Datos del repertorio</h2>
        <p class="app-form-section__text">Define nombre, estado y contexto del evento.</p>
    </header>

    <div class="app-form-grid app-form-grid--3">
        <div class="app-field app-field--full">
            <label class="app-label" for="name">Nombre <span class="required">*</span></label>
            <input class="form-control" id="name" name="name" value="{{ old('name', $repertoire->name ?? '') }}" required maxlength="255">
        </div>

        <div class="app-field">
            <label class="app-label" for="status">Estado <span class="required">*</span></label>
            <select class="form-select" id="status" name="status" required>
                @foreach(['draft'=>'Borrador','ready'=>'Listo','archived'=>'Archivado'] as $value=>$label)
                    <option value="{{ $value }}" @selected(old('status', $repertoire->status ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="app-field">
            <label class="app-label" for="event_type">Tipo de evento</label>
            <input class="form-control" id="event_type" name="event_type" value="{{ old('event_type', $repertoire->event_type ?? '') }}" maxlength="255" placeholder="Misa, matrimonio, ensayo...">
        </div>

        <div class="app-field">
            <label class="app-label" for="location">Lugar</label>
            <input class="form-control" id="location" name="location" value="{{ old('location', $repertoire->location ?? '') }}" maxlength="255">
        </div>

        <div class="app-field">
            <label class="app-label" for="event_date">Fecha</label>
            <input class="form-control" type="date" id="event_date" name="event_date" value="{{ old('event_date', isset($repertoire) && $repertoire->event_date ? $repertoire->event_date->format('Y-m-d') : '') }}">
        </div>

        <div class="app-field">
            <label class="app-label" for="event_time">Hora</label>
            <input class="form-control" type="time" id="event_time" name="event_time" value="{{ old('event_time', isset($repertoire) && $repertoire->event_time ? $repertoire->event_time->format('H:i') : '') }}">
        </div>

        <div class="app-field">
            <label class="app-label" for="slug">Slug opcional</label>
            <input class="form-control" id="slug" name="slug" value="{{ old('slug', $repertoire->slug ?? '') }}" maxlength="255">
        </div>
    </div>
</section>

<section class="app-form-section">
    <header class="app-form-section__head">
        <h2 class="app-form-section__title">Visibilidad y acceso</h2>
        <p class="app-form-section__text">Configura si el repertorio será público y si permite descarga.</p>
    </header>

    <div class="app-form-grid app-form-grid--2">
        <div class="app-field">
            <label class="app-label" for="visibility">Visibilidad <span class="required">*</span></label>
            <select class="form-select" id="visibility" name="visibility" required>
                <option value="private" @selected(old('visibility', $repertoire->visibility ?? 'private') === 'private')>Privado</option>
                <option value="public" @selected(old('visibility', $repertoire->visibility ?? 'private') === 'public')>Público</option>
            </select>
            <div class="app-control-hint">Los repertorios privados solo son visibles para ti y los administradores.</div>
        </div>

        <div class="app-field">
            <label class="app-label" for="allow_public_download">Descarga pública</label>
            <div class="form-check form-switch mt-2">
                <input type="hidden" name="allow_public_download" value="0">
                <input class="form-check-input" type="checkbox" id="allow_public_download" name="allow_public_download" value="1" @checked(old('allow_public_download', $repertoire->allow_public_download ?? false))>
                <label class="form-check-label" for="allow_public_download">Permitir descarga pública del PDF</label>
            </div>
            <div class="app-control-hint">Cuando esté activo, cualquier persona con el enlace público podrá descargar el PDF consolidado.</div>
        </div>

        <div class="app-field app-field--full">
            <label class="app-label" for="description">Descripción</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $repertoire->description ?? '') }}</textarea>
        </div>
    </div>
</section>

<div class="app-form-actions mt-3">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i>Guardar repertorio</button>
    <a class="btn btn-outline-secondary" href="{{ route('repertoires.index') }}">Cancelar</a>
</div>
