@extends('layouts.guest')
@section('title','Crear cuenta')
@section('subtitle','Crea tu cuenta personal')
@section('content')
<form class="guest-form" method="POST" action="{{ route('register.store') }}">
    @csrf

    <div class="app-field">
        <label class="app-label" for="name">Nombre</label>
        <input class="form-control" id="name" name="name" value="{{ old('name') }}" maxlength="255" required autofocus autocomplete="name">
    </div>

    <div class="app-field">
        <label class="app-label" for="email">Correo electrónico</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
    </div>

    <div class="app-field">
        <label class="app-label" for="password">Contraseña</label>
        <input class="form-control" id="password" name="password" type="password" required autocomplete="new-password">
        <div class="app-control-hint">Mínimo 8 caracteres, con mayúscula, minúscula y número.</div>
    </div>

    <div class="app-field">
        <label class="app-label" for="password_confirmation">Confirmar contraseña</label>
        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
    </div>

    <div class="guest-form__actions">
        <button class="btn btn-primary" type="submit"><i class="bi bi-person-plus"></i>Crear cuenta</button>
    </div>
</form>
<p class="guest-form__footer">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Ingresar</a></p>
@endsection