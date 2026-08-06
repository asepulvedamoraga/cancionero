@extends('layouts.public')
@section('title', $song->title)

@section('content')
<div class="space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-cyan-700 no-underline hover:text-cyan-800">
                    <span aria-hidden="true">←</span> Volver al inicio
                </a>
                <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $song->title }}</h1>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">{{ $song->author ?: 'Autor no indicado' }} · Compartida por {{ $song->owner->name }}</p>
            </div>
            <a class="inline-flex items-center justify-center rounded-xl bg-cyan-700 px-4 py-2.5 text-sm font-semibold text-white no-underline transition hover:bg-cyan-800" href="{{ route('public.songs.read', ['song' => $song->slug, 'tone' => $selectedTone->id]) }}">Modo lectura</a>
        </div>
    </section>

    @if($song->notes)
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Notas</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-700 sm:text-base">{{ $song->notes }}</p>
        </section>
    @endif

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tonalidad</p><p class="mt-1 text-base font-semibold text-slate-900">{{ $selectedTone->name }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Archivos</p><p class="mt-1 text-base font-semibold text-slate-900">{{ $displayFiles->count() }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categoría</p><p class="mt-1 text-base font-semibold text-slate-900">{{ $song->category?->name ?: 'No definida' }}</p></article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Momento litúrgico</p><p class="mt-1 text-base font-semibold text-slate-900">{{ $song->liturgicalMoment?->name ?: 'No definido' }}</p></article>
    </section>

    @if($song->tones->count() > 1)
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Tonalidades</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($song->tones as $tone)
                    <a
                        class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold no-underline transition {{ $tone->id === $selectedTone->id ? 'bg-cyan-700 text-white hover:bg-cyan-800' : 'border border-cyan-700 text-cyan-700 hover:bg-cyan-50' }}"
                        href="{{ route('public.songs.show', ['song' => $song->slug, 'tone' => $tone->id]) }}"
                    >
                        {{ $tone->name }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">Archivos disponibles</h2>
        </div>
        <div class="p-5 sm:p-6">
            @if($displayFiles->isEmpty())
                <p class="text-sm text-slate-600">Esta tonalidad no tiene archivos disponibles por ahora.</p>
            @else
                <div class="song-file-grid">
                    @foreach($displayFiles as $file)
                        <article class="song-file-card">
                            @if(in_array($file->file_type, ['image', 'generated_image'], true))
                                <img src="{{ route('public.songs.files.show', ['song' => $song->slug, 'file' => $file]) }}" alt="Archivo {{ $loop->iteration }} de {{ $song->title }}">
                            @else
                                <div class="pdf-placeholder"><i class="bi bi-file-earmark-pdf"></i><small>{{ $file->original_name }}</small></div>
                            @endif
                            <div class="p-2 d-flex gap-2 flex-wrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('public.songs.files.show', ['song' => $song->slug, 'file' => $file]) }}" target="_blank" rel="noopener">Abrir</a>
                                @if($file->file_type === 'pdf')
                                    <a class="btn btn-sm btn-primary" href="{{ route('public.songs.files.show', ['song' => $song->slug, 'file' => $file]) }}" target="_blank" rel="noopener">PDF</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if($publicRepertoires->isNotEmpty())
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">También está en repertorios públicos</h2>
            </div>
            <div class="flex flex-wrap gap-2 px-5 py-4 sm:px-6">
                @foreach($publicRepertoires as $repertoire)
                    <a class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 no-underline transition hover:bg-slate-100" href="{{ route('public.repertoires.show', ['repertoire' => $repertoire->slug]) }}">{{ $repertoire->name }}</a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
