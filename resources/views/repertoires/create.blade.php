@extends('layouts.app')
@section('title','Nuevo repertorio')
@section('content')
<div class="app-page-header">
	<div>
		<h1 class="h2">Nuevo repertorio</h1>
		<p class="text-secondary">Crea la base del repertorio y luego agrega canciones en orden.</p>
	</div>
</div>

<form class="card app-form-shell" method="POST" action="{{ route('repertoires.store') }}">
	@csrf
	<div class="card-body">
		@include('repertoires._form')
	</div>
</form>
@endsection
