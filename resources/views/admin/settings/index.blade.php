@extends('layouts.app')
@section('title', 'Configuración')
@section('content')
<div class="app-page-header">
    <div>
        <h1>Configuración</h1>
        <p>Administra usuarios y opciones generales del cancionero.</p>
    </div>
</div>

<div class="row g-4">
    @foreach([
        ['route' => route('admin.users.index'), 'icon' => 'bi-people', 'title' => 'Usuarios', 'text' => 'Activa o desactiva las cuentas registradas.'],
        ['route' => route('admin.catalogs.index', 'categories'), 'icon' => 'bi-tags', 'title' => 'Categorías', 'text' => 'Organiza las canciones por uso o temática.'],
        ['route' => route('admin.catalogs.index', 'moments'), 'icon' => 'bi-list-check', 'title' => 'Momentos litúrgicos', 'text' => 'Configura los momentos de una celebración.'],
        ['route' => route('admin.catalogs.index', 'seasons'), 'icon' => 'bi-calendar3', 'title' => 'Tiempos litúrgicos', 'text' => 'Configura los tiempos del calendario litúrgico.'],
    ] as $option)
        <div class="col-md-6">
            <a class="card card-body h-100 app-settings-card app-form-shell" href="{{ $option['route'] }}">
                <i class="bi {{ $option['icon'] }}"></i>
                <div>
                    <h2>{{ $option['title'] }}</h2>
                    <p>{{ $option['text'] }}</p>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
            </a>
        </div>
    @endforeach
</div>
@endsection