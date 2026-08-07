@extends('layouts.guest')
@section('title','Ingresar')
@section('subtitle','Ingresa para administrar tus canciones y repertorios')
@section('content')
<form class="guest-form" method="POST" action="{{ route('login.store') }}">
    @csrf

    <div class="app-field">
        <label for="email" class="app-label">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
    </div>

    <div class="app-field">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <label for="password" class="app-label">Contraseña</label>
            <a class="app-inline-help" href="{{ route('password.request') }}">¿La olvidaste?</a>
        </div>
        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
    </div>

    <div class="form-check">
        <input id="remember" name="remember" value="1" type="checkbox" class="form-check-input">
        <label for="remember" class="form-check-label">Mantener sesión iniciada</label>
    </div>

    <div class="guest-form__actions">
        <button class="btn btn-primary" type="submit"><i class="bi bi-box-arrow-in-right"></i>Ingresar</button>
    </div>
</form>
<p class="guest-form__footer">¿No tienes una cuenta? <a href="{{ route('register') }}">Crear cuenta</a></p>
@endsection