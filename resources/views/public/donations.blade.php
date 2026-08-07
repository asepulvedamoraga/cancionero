@extends('layouts.public')
@section('title', 'Apoya este proyecto')

@section('content')
<div class="public-detail-stack">
    <section class="public-hero public-hero--compact" aria-labelledby="public-donations-title">
        <div class="public-hero__mesh" aria-hidden="true"></div>
        <div class="public-hero__content">
            <p class="public-hero__eyebrow">Comunidad</p>
            <h1 id="public-donations-title" class="public-hero__title">Apoya este proyecto</h1>
            <p class="public-hero__subtitle">
                Esta plataforma nació para ayudar a músicos, bandas, coros y comunidades a organizar canciones y repertorios
                de forma simple, rápida y colaborativa.
            </p>
            <p class="public-hero__identity">Creado por músicos, para músicos.</p>
        </div>
    </section>

    <section class="public-section" aria-labelledby="donations-why-title">
        <div class="public-section__head">
            <div>
                <p class="public-section__kicker">¿Por qué existe este proyecto?</p>
                <h2 id="donations-why-title" class="public-section__title">Una herramienta hecha para servir a la música</h2>
                <p class="public-section__subtitle">
                    El proyecto nació para facilitar la preparación musical: ordenar canciones, armar repertorios y compartir
                    material útil para ensayos, celebraciones y presentaciones.
                </p>
            </div>
        </div>
        <p class="public-section__subtitle mt-3 mb-0">
            Sigue creciendo gracias al tiempo dedicado a su desarrollo y al aporte de una comunidad que lo usa cada semana.
        </p>
    </section>

    <section class="public-section" aria-labelledby="donations-free-title">
        <div class="public-section__head">
            <div>
                <p class="public-section__kicker">Compromiso</p>
                <h2 id="donations-free-title" class="public-section__title">Siempre será gratuito</h2>
                <p class="public-section__subtitle">
                    El acceso seguirá siendo gratuito para todos. No habrá funciones bloqueadas por pago ni una versión Premium.
                    Las donaciones serán voluntarias, siguiendo un modelo simple: usar libremente y aportar solo si quieres.
                </p>
            </div>
        </div>
    </section>

    <section class="public-section" aria-labelledby="donations-impact-title">
        <div class="public-section__head">
            <div>
                <p class="public-section__kicker">Impacto</p>
                <h2 id="donations-impact-title" class="public-section__title">¿En qué ayudan las donaciones?</h2>
            </div>
        </div>

        <div class="public-card-grid public-card-grid--repertoires public-donations-grid mt-3">
            <article class="public-card public-card--donation-item">
                <div class="public-card__body">
                    <h3 class="public-card__title">Servidor y dominio</h3>
                    <p class="public-card__meta">Mantener la plataforma activa, rápida y estable.</p>
                </div>
            </article>
            <article class="public-card public-card--donation-item">
                <div class="public-card__body">
                    <h3 class="public-card__title">Copias de seguridad</h3>
                    <p class="public-card__meta">Proteger los documentos y repertorios compartidos.</p>
                </div>
            </article>
            <article class="public-card public-card--donation-item">
                <div class="public-card__body">
                    <h3 class="public-card__title">Nuevas funcionalidades</h3>
                    <p class="public-card__meta">Implementar mejoras que faciliten el trabajo musical.</p>
                </div>
            </article>
            <article class="public-card public-card--donation-item">
                <div class="public-card__body">
                    <h3 class="public-card__title">Mantenimiento general</h3>
                    <p class="public-card__meta">Corregir, optimizar y mantener una experiencia confiable.</p>
                </div>
            </article>
        </div>

        <div class="public-donations-cta mt-3">
            <button type="button" class="public-btn public-btn--accent" aria-disabled="true" disabled>
                Donaciones próximamente
            </button>
            <p class="public-section__meta mb-0">Pronto podrás apoyar este proyecto de forma voluntaria.</p>
        </div>
    </section>
</div>
@endsection
