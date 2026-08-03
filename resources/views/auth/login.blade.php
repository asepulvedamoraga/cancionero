@extends('layouts.guest')
@section('title','Ingresar')
@section('subtitle','Ingresa para administrar tus canciones y repertorios')
@section('content')
<form method="POST" action="{{ route('login.store') }}">@csrf
    <div class="mb-3"><label for="email" class="form-label">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username"></div>
    <div class="mb-2"><div class="d-flex justify-content-between"><label for="password" class="form-label">Contraseña</label><a class="small" href="{{ route('password.request') }}">¿La olvidaste?</a></div><input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password"></div>
    <div class="form-check mb-4"><input id="remember" name="remember" value="1" type="checkbox" class="form-check-input"><label for="remember" class="form-check-label">Mantener sesión iniciada</label></div>
    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i>Ingresar</button>
</form>
<p class="text-center text-secondary small mt-4 mb-0">¿No tienes una cuenta? <a href="{{ route('register') }}">Crear cuenta</a></p>
@endsection