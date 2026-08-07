@extends('layouts.public')
@section('title', config('cancionero.name'))

@section('content')
<section class="public-hero public-hero--compact">
    <div class="public-hero__content">
        <span class="public-hero__eyebrow">Bienvenido</span>
        <h1>Gestiona canciones y repertorios con una experiencia clara y moderna.</h1>
        <p>
            Organiza tu biblioteca liturgica, prepara celebraciones y comparte repertorios publicos en minutos.
        </p>
        <div class="public-hero__actions">
            <a class="public-btn public-btn--primary" href="{{ route('public.home') }}">
                <i class="bi bi-house-door"></i> Ir al inicio
            </a>
            @guest
                <a class="public-btn public-btn--ghost" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesion
                </a>
            @else
                <a class="public-btn public-btn--ghost" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Ir al panel
                </a>
            @endguest
        </div>
    </div>
</section>
@endsection
