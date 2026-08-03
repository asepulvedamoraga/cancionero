@extends('layouts.guest')
@section('title','Nueva contraseña')
@section('subtitle','Define una nueva contraseña para tu cuenta')
@section('content')
<form method="POST" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3"><label class="form-label" for="email">Correo electrónico</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email',$email) }}" required autocomplete="username"></div>
    <div class="mb-3"><label class="form-label" for="password">Nueva contraseña</label><input class="form-control" id="password" name="password" type="password" required autocomplete="new-password"></div>
    <div class="mb-4"><label class="form-label" for="password_confirmation">Confirmar contraseña</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-key"></i>Guardar contraseña</button>
</form>
@endsection