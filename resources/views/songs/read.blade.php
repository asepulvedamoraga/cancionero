<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $song->title }} - Lectura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="read-mode">
<header class="read-mode__header">
    <div class="read-mode__title-wrap">
        <p class="read-mode__eyebrow">Modo lectura</p>
        <strong>{{ $song->title }}</strong>
        <span>{{ $selectedTone->name }}</span>
    </div>
    <a class="read-mode__exit" href="{{ route('songs.show', ['song' => $song, 'tone' => $selectedTone->id]) }}">
        <i class="bi bi-arrow-left"></i> Volver al detalle
    </a>
</header>

<nav class="read-mode__tones" aria-label="Cambiar tonalidad">
    @foreach($song->tones as $tone)
        <a class="{{ $tone->id === $selectedTone->id ? 'active' : '' }}" href="{{ route('songs.read', ['song' => $song, 'tone' => $tone->id]) }}">{{ $tone->name }}</a>
    @endforeach
</nav>

<main class="read-mode__pages">
    @forelse($displayFiles as $file)
        @php($fileUrl = route('songs.files.show', [$song, $file, 'v' => $file->updated_at?->timestamp ?? $file->id]))
        <article class="read-mode__page">
            <img src="{{ $fileUrl }}" alt="Pagina {{ $loop->iteration }}">
        </article>
    @empty
        <section class="read-mode__empty">
            <i class="bi bi-file-earmark-x"></i>
            <h2>Sin paginas para esta tonalidad</h2>
            <p>Prueba con otra tonalidad para continuar la lectura.</p>
        </section>
    @endforelse
</main>

@if($song->youtubeEmbedUrl())
    <section class="read-mode__video">
        <h2><i class="bi bi-play-circle"></i> Video de apoyo</h2>
        <div class="read-mode__frame">
            <iframe
                src="{{ $song->youtubeEmbedUrl() }}"
                title="Video de apoyo de {{ $song->title }}"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
            ></iframe>
        </div>
    </section>
@endif
</body>
</html>
