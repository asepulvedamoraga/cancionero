@extends('layouts.app')
@section('title','Nueva canción')
@section('content')<div class="app-page-header"><div><h1 class="h2">Nueva canción</h1><p class="text-secondary">Registra los datos y sus páginas.</p></div></div><form class="card card-body" method="POST" action="{{ route('songs.store') }}" enctype="multipart/form-data">@csrf @include('songs._form')</form>@endsection
