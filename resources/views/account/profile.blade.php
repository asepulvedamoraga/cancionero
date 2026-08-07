@extends('layouts.app')
@section('title','Mi perfil')
@section('content')
<div class="app-page-header">
	<div>
		<h1>Mi perfil</h1>
		<p>Actualiza tus datos de acceso y seguridad.</p>
	</div>
</div>

<div class="row g-4">
	<div class="col-lg-7">
		<section class="card app-form-shell">
			<div class="card-body">
				<form method="POST" action="{{ route('profile.update') }}">
					@csrf
					@method('PUT')

					<section class="app-form-section">
						<header class="app-form-section__head">
							<h2 class="app-form-section__title">Datos personales</h2>
							<p class="app-form-section__text">Información visible de tu cuenta.</p>
						</header>

						<div class="app-form-grid app-form-grid--2">
							<div class="app-field">
								<label class="app-label" for="name">Nombre <span class="required">*</span></label>
								<input class="form-control" id="name" name="name" value="{{ old('name',$user->name) }}" required maxlength="255" autocomplete="name">
							</div>

							<div class="app-field">
								<label class="app-label" for="email">Correo electrónico <span class="required">*</span></label>
								<input class="form-control" id="email" name="email" type="email" value="{{ old('email',$user->email) }}" required autocomplete="email">
								@if(!$user->hasVerifiedEmail())
									<div class="app-inline-help text-warning"><i class="bi bi-exclamation-triangle"></i> Este correo aún no está verificado.</div>
								@endif
							</div>
						</div>

						<div class="app-form-actions">
							<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i>Guardar perfil</button>
						</div>
					</section>
				</form>
			</div>
		</section>
	</div>

	<div class="col-lg-5">
		<section class="card app-form-shell">
			<div class="card-body">
				<form method="POST" action="{{ route('profile.password.update') }}">
					@csrf
					@method('PUT')

					<section class="app-form-section">
						<header class="app-form-section__head">
							<h2 class="app-form-section__title">Cambiar contraseña</h2>
							<p class="app-form-section__text">Usa una clave robusta para proteger tu cuenta.</p>
						</header>

						<div class="app-form-grid">
							<div class="app-field">
								<label class="app-label" for="current_password">Contraseña actual <span class="required">*</span></label>
								<input class="form-control" id="current_password" name="current_password" type="password" required autocomplete="current-password">
							</div>

							<div class="app-field">
								<label class="app-label" for="password">Nueva contraseña <span class="required">*</span></label>
								<input class="form-control" id="password" name="password" type="password" required autocomplete="new-password">
								<div class="app-control-hint">Mínimo 8 caracteres, con mayúscula, minúscula y número.</div>
							</div>

							<div class="app-field">
								<label class="app-label" for="password_confirmation">Confirmar contraseña <span class="required">*</span></label>
								<input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
							</div>
						</div>

						<div class="app-form-actions">
							<button class="btn btn-primary" type="submit"><i class="bi bi-key"></i>Cambiar contraseña</button>
						</div>
					</section>
				</form>
			</div>
		</section>
	</div>
</div>
@endsection