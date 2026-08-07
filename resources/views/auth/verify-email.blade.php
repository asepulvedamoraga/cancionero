@extends('layouts.app')
@section('title','Verifica tu correo')
@section('content')
<div class="row justify-content-center">
	<div class="col-lg-7">
		<div class="card app-form-shell">
			<div class="card-body p-4 p-md-5 text-center">
				<div class="brand-mark"><i class="bi bi-envelope-check"></i></div>
				<h1 class="h3 mt-3">Verifica tu correo electrónico</h1>
				<p class="text-secondary">Enviamos un enlace a <strong>{{ auth()->user()->email }}</strong>. Debes abrirlo antes de usar la biblioteca.</p>

				<div class="app-form-actions justify-content-center mt-3">
					<form method="POST" action="{{ route('verification.send') }}">
						@csrf
						<button class="btn btn-primary"><i class="bi bi-send"></i>Reenviar enlace</button>
					</form>

					<a class="btn btn-outline-secondary" href="{{ route('profile.edit') }}"><i class="bi bi-pencil"></i>Cambiar correo</a>

					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button class="btn btn-outline-secondary"><i class="bi bi-box-arrow-right"></i>Cerrar sesión</button>
					</form>
				</div>

				<p class="small text-secondary mt-4 mb-0">En desarrollo, el correo se escribe en <code>storage/logs/laravel.log</code>.</p>
			</div>
		</div>
	</div>
</div>
@endsection