@extends('layouts.guest')
@section('title','Recuperar contraseña')
@section('subtitle','Te enviaremos un enlace de recuperación')
@section('content')
<form class="guest-form" method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="app-field">
        <label class="app-label" for="email">Correo electrónico</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
    </div>

    <div class="guest-form__actions">
        <button class="btn btn-primary" type="submit"><i class="bi bi-envelope"></i>Enviar enlace</button>
    </div>
</form>
<p class="guest-form__footer"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Volver al ingreso</a></p>
@endsection