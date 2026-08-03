@extends('layouts.app')
@section('title','Nuevo repertorio')
@section('content')
<div class="mb-4"><h1 class="h2">Nuevo repertorio</h1><p class="text-secondary">Define el evento; después podrás seleccionar y ordenar sus canciones.</p></div>
<form class="card card-body" method="POST" action="{{ route('repertoires.store') }}">@csrf @include('repertoires._form')</form>
@endsection
