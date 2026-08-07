@extends('layouts.app')
@section('title','Nueva canción')
@section('content')
<div class="app-page-header">
	<div>
		<h1 class="h2">Nueva canción</h1>
		<p class="text-secondary">Registra datos generales, metadatos litúrgicos cuando apliquen y archivos de apoyo.</p>
	</div>
</div>

<form class="card app-form-shell song-upload-form" method="POST" action="{{ route('songs.store') }}" enctype="multipart/form-data" data-song-upload-form data-song-upload-message="Estamos convirtiendo el PDF y generando sus páginas. No cierres esta ventana hasta que termine.">
	@csrf
	<div class="card-body">
		@include('songs._form')
	</div>
</form>
@endsection
