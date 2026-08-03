@extends('layouts.guest')
@section('title','Crear cuenta')
@section('subtitle','Crea tu cuenta personal')
@section('content')
<form method="POST" action="{{ route('register.store') }}">@csrf
    <div class="mb-3"><label class="form-label" for="name">Nombre</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" maxlength="255" required autofocus autocomplete="name"></div>
    <div class="mb-3"><label class="form-label" for="email">Correo electrónico</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"></div>
    <div class="mb-3"><label class="form-label" for="password">Contraseña</label><input class="form-control" id="password" name="password" type="password" required autocomplete="new-password"><div class="form-text">Mínimo 8 caracteres, con mayúscula, minúscula y número.</div></div>
    <div class="mb-4"><label class="form-label" for="password_confirmation">Confirmar contraseña</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-person-plus"></i>Crear cuenta</button>
</form>
<p class="text-center text-secondary small mt-4 mb-0">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Ingresar</a></p>
@endsection