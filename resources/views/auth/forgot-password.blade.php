@extends('layouts.guest')
@section('title','Recuperar contraseña')
@section('subtitle','Te enviaremos un enlace de recuperación')
@section('content')
<form method="POST" action="{{ route('password.email') }}">@csrf
    <div class="mb-4"><label class="form-label" for="email">Correo electrónico</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"></div>
    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-envelope"></i>Enviar enlace</button>
</form>
<p class="text-center small mt-4 mb-0"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Volver al ingreso</a></p>
@endsection