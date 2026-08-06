@extends('layouts.public')
@section('title',$repertoire->name)
@section('content')
<div class="space-y-5">
	<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
		<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
			<div>
				<span class="inline-flex items-center rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-800">
					Repertorio público
				</span>
				<h1 class="mt-3 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $repertoire->name }}</h1>
				<p class="mt-2 text-sm text-slate-600 sm:text-base">
					{{ $repertoire->event_type ?: 'Evento' }}
					@if($repertoire->event_date)
						· {{ $repertoire->event_date->format('d-m-Y') }}
					@endif
					@if($repertoire->location)
						· {{ $repertoire->location }}
					@endif
				</p>
			</div>
			<div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
				@if($presentationPageCount)
					<a class="inline-flex items-center justify-center rounded-xl bg-cyan-700 px-4 py-2.5 text-sm font-semibold text-white no-underline transition hover:bg-cyan-800" href="{{ route('public.repertoires.presentation',['repertoire'=>$repertoire->slug]) }}">Presentar</a>
				@endif
				@if($repertoire->allow_public_download)
					<a class="inline-flex items-center justify-center rounded-xl border border-cyan-700 px-4 py-2.5 text-sm font-semibold text-cyan-700 no-underline transition hover:bg-cyan-50" href="{{ route('public.repertoires.download',['repertoire'=>$repertoire->slug]) }}">Descargar PDF</a>
				@endif
			</div>
		</div>
	</section>

	<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			<article class="rounded-xl bg-slate-50 p-4">
				<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Compartido por</p>
				<p class="mt-1 text-base font-semibold text-slate-900">{{ $repertoire->owner->name }}</p>
			</article>
			<article class="rounded-xl bg-slate-50 p-4">
				<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha y hora</p>
				<p class="mt-1 text-base font-semibold text-slate-900">
					{{ $repertoire->event_date?->format('d-m-Y') ?: 'No indicada' }}
					@if($repertoire->event_time)
						· {{ $repertoire->event_time->format('H:i') }}
					@endif
				</p>
			</article>
			<article class="rounded-xl bg-slate-50 p-4">
				<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Canciones disponibles</p>
				<p class="mt-1 text-base font-semibold text-slate-900">{{ $songs->count() }}</p>
			</article>
		</div>
		@if($repertoire->description)
			<div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
				<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Descripción</p>
				<p class="mt-2 text-sm leading-relaxed text-slate-700 sm:text-base">{{ $repertoire->description }}</p>
			</div>
		@endif
	</section>

	<section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
		<div class="border-b border-slate-100 px-5 py-4 sm:px-6">
			<h2 class="text-lg font-bold text-slate-900">Canciones</h2>
		</div>
		<div class="divide-y divide-slate-100">
			@forelse($songs as $song)
				<article class="flex items-start gap-3 px-5 py-4 sm:px-6">
					<span class="mt-0.5 grid h-8 w-8 place-items-center rounded-full bg-cyan-100 text-sm font-bold text-cyan-800">{{ $loop->iteration }}</span>
					<div>
						<h3 class="text-base font-semibold text-slate-900">{{ $song->title }}</h3>
						<p class="text-sm text-slate-600">
							{{ $song->author ?: 'Autor no indicado' }} · {{ $song->page_count }} {{ $song->page_count === 1 ? 'página' : 'páginas' }}
						</p>
					</div>
				</article>
			@empty
				<x-public.empty-state
					title="Sin canciones disponibles"
					message="Este repertorio aún no tiene páginas públicas para presentar."
				/>
			@endforelse
		</div>
	</section>

	<p class="text-center text-xs text-slate-500 sm:text-sm">Vista pública de solo lectura. Las notas internas y acciones de edición no se muestran.</p>
</div>
@endsection