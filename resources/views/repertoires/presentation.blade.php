<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $repertoire->name }} · Presentación</title>
    @vite(['resources/css/presentation.css','resources/js/presentation.js'])
</head>
<body class="presentation-body" data-presentation>
<header class="presentation-header" data-controls>
    <div class="presentation-heading"><strong data-song-title>{{ $pages[0]['song_title'] ?? $repertoire->name }}</strong><span data-song-counter>@if($pages)Canción {{ $pages[0]['song_position'] }} de {{ $pages[0]['song_count'] }}@endif</span></div>
    <div class="presentation-toolbar">
        <button type="button" data-index-toggle aria-label="Abrir índice">☰</button>
        <button type="button" data-zoom-out aria-label="Disminuir zoom">−</button>
        <button type="button" data-zoom-reset aria-label="Restablecer zoom">100%</button>
        <button type="button" data-zoom-in aria-label="Aumentar zoom">+</button>
        <button type="button" data-fullscreen aria-label="Pantalla completa">⛶</button>
        <a href="{{ $exitUrl }}" aria-label="Salir de la presentación">Salir</a>
    </div>
</header>

<main class="presentation-viewport" data-viewport>
    @forelse($pages as $page)
        <section class="presentation-slide" data-slide data-page-index="{{ $loop->index }}" data-song-id="{{ $page['song_id'] }}" data-song-title="{{ $page['song_title'] }}" data-song-position="{{ $page['song_position'] }}" data-song-count="{{ $page['song_count'] }}" data-page-position="{{ $page['page_position'] }}" data-song-page-count="{{ $page['song_page_count'] }}" data-global-position="{{ $page['global_page_position'] }}" data-total-pages="{{ $page['total_pages'] }}" @if(!$loop->first) hidden @endif>
            <div class="presentation-content" data-slide-content>
                @if($page['file_type']==='pdf')
                    <iframe src="{{ $page['image_url'] }}#toolbar=0&navpanes=0&view=FitH" title="{{ $page['original_name'] }}"></iframe>
                @else
                    <img src="{{ $page['image_url'] }}" alt="{{ $page['song_title'] }}, página {{ $page['page_position'] }}" draggable="false">
                @endif
            </div>
        </section>
    @empty
        <div class="presentation-empty"><h1>{{ $repertoire->name }}</h1><p>No hay páginas disponibles para presentar.</p><a href="{{ $exitUrl }}">Volver al repertorio</a></div>
    @endforelse
    @if($pages)<button class="navigation-zone navigation-zone-left" type="button" data-previous aria-label="Página anterior"></button><button class="navigation-zone navigation-zone-right" type="button" data-next aria-label="Página siguiente"></button>@endif
</main>

@if($pages)
<footer class="presentation-footer" data-controls>
    <button type="button" data-previous aria-label="Página anterior">‹ Anterior</button>
    <div class="presentation-progress"><span data-page-counter>Página 1 de {{ count($pages) }}</span><span data-total-progress><i style="width:{{ 100 / count($pages) }}%"></i></span><span data-song-page-counter>Página {{ $pages[0]['page_position'] }} de {{ $pages[0]['song_page_count'] }}</span></div>
    <button type="button" data-next aria-label="Página siguiente">Siguiente ›</button>
</footer>

<aside class="presentation-index" data-index hidden>
    <div class="presentation-index-header"><strong>Índice</strong><button type="button" data-index-close aria-label="Cerrar índice">×</button></div>
    <nav>@foreach(collect($pages)->unique('song_id') as $page)<button type="button" data-go-to="{{ $loop->index === 0 ? 0 : collect($pages)->search(fn($item) => $item['song_id'] === $page['song_id']) }}"><span>{{ $page['song_position'] }}</span>{{ $page['song_title'] }}<small>{{ $page['song_page_count'] }} {{ $page['song_page_count'] === 1 ? 'página' : 'páginas' }}</small></button>@endforeach</nav>
</aside>
@endif
</body>
</html>
