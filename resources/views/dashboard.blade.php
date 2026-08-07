@extends('layouts.app')
@section('title','Inicio')
@section('content')
<div class="app-page-header app-hero-header">
    <div>
        <span class="app-eyebrow">Panel principal</span>
        <h1>Hola, {{ auth()->user()->name }}</h1>
        <p>Resumen de tu biblioteca musical, en un solo lugar.</p>
    </div>
    <div class="app-page-actions">
        <a class="btn btn-primary" href="{{ route('songs.create') }}"><i class="bi bi-plus-lg"></i>Nueva canción</a>
        <a class="btn btn-outline-primary" href="{{ route('repertoires.create') }}"><i class="bi bi-list-ol"></i>Crear repertorio</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6"><a class="card metric-card h-100" href="{{ route('songs.index') }}"><span class="metric-icon"><i class="bi bi-music-note-list"></i></span><span><small>Canciones disponibles</small><strong>{{ $songCount }}</strong></span><i class="bi bi-arrow-right metric-arrow"></i></a></div>
    <div class="col-sm-6"><a class="card metric-card h-100" href="{{ route('repertoires.index') }}"><span class="metric-icon"><i class="bi bi-list-ol"></i></span><span><small>Repertorios visibles</small><strong>{{ $repertoireCount }}</strong></span><i class="bi bi-arrow-right metric-arrow"></i></a></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <section class="card h-100 app-list-card">
            <div class="card-header app-section-header">
                <div>
                    <span class="app-section-kicker">Biblioteca</span>
                    <h2>Últimas canciones</h2>
                </div>
                <a href="{{ route('songs.index', ['sort' => 'newest']) }}">Ver todas <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($latestSongs as $song)
                    <a class="list-group-item list-group-item-action app-dashboard-row" href="{{ route('songs.show',$song) }}">
                        <span class="app-row-icon"><i class="bi bi-music-note"></i></span>
                        <span class="flex-grow-1"><strong>{{ $song->title }}</strong><small>{{ $song->author ?: 'Autor no indicado' }}</small></span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @empty
                    <div class="app-empty-state app-empty-state--compact">
                        <i class="bi bi-music-note"></i>
                        <p class="mb-2">Aún no hay canciones.</p>
                        <div class="app-empty-state__actions">
                            <a class="btn btn-primary btn-sm" href="{{ route('songs.create') }}"><i class="bi bi-plus-lg"></i>Crear canción</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="card h-100 app-list-card">
            <div class="card-header app-section-header">
                <div>
                    <span class="app-section-kicker">Organización</span>
                    <h2>Repertorios recientes</h2>
                </div>
                <a href="{{ route('repertoires.index', ['sort' => 'newest']) }}">Ver todos <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentRepertoires as $repertoire)
                    <a class="list-group-item list-group-item-action app-dashboard-row" href="{{ route('repertoires.show',$repertoire) }}">
                        <span class="app-row-icon"><i class="bi bi-list-ol"></i></span>
                        <span class="flex-grow-1"><strong>{{ $repertoire->name }}</strong><small>{{ $repertoire->event_date?->format('d-m-Y') ?: 'Sin fecha' }}</small></span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @empty
                    <div class="app-empty-state app-empty-state--compact">
                        <i class="bi bi-list-ol"></i>
                        <p class="mb-2">Aún no hay repertorios.</p>
                        <div class="app-empty-state__actions">
                            <a class="btn btn-primary btn-sm" href="{{ route('repertoires.create') }}"><i class="bi bi-plus-lg"></i>Crear repertorio</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @if($lastRepertoire)
        <div class="col-12">
            <section class="card app-feature-card">
                <div class="card-body">
                    <span class="app-card-icon"><i class="bi bi-stars"></i></span>
                    <span class="app-section-kicker">Último repertorio actualizado</span>
                    <h2><a class="stretched-link" href="{{ route('repertoires.show',$lastRepertoire) }}">{{ $lastRepertoire->name }}</a></h2>
                    <p class="mb-2">Accede rápidamente para revisar sus canciones y configuración.</p>
                    <span class="badge {{ $lastRepertoire->status === 'ready' ? 'text-bg-success' : ($lastRepertoire->status === 'archived' ? 'text-bg-secondary' : 'text-bg-warning') }}">{{ ['draft'=>'Borrador','ready'=>'Listo','archived'=>'Archivado'][$lastRepertoire->status] }}</span>
                </div>
            </section>
        </div>
    @endif
</div>
@endsection