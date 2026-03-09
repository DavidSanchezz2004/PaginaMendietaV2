@extends('layouts.app')

@section('title', 'Portal Mendieta - Mi Perfil')

@push('styles')
	<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
	<link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
@endpush

@section('content')
	<div class="app-layout">
		<aside class="sidebar-premium">
			<div class="sidebar-header">
				<img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
				<div class="header-text">
					<h2>Portal Mendieta</h2>
					<p>Panel interno</p>
				</div>
			</div>

			<hr class="sidebar-divider">

			<div class="sidebar-menu-wrapper">
				<span class="menu-label">MENÚ PRINCIPAL</span>
				@include('partials.sidebar-menu')
			</div>
		</aside>

		<div class="main-wrapper">
			@include('partials.header', [
				'welcomeName' => $displayName,
				'userName' => $displayName,
				'userEmail' => $user?->email,
				'avatarUrl' => $avatarUrl,
			])

			<main class="main-content">
				@if (session('status'))
					<div class="placeholder-content" id="profile-success-alert" style="margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
						<p>{{ session('status') }}</p>
						<button type="button" id="close-success-alert" class="btn-secondary" style="width: auto; padding: 0.4rem 0.7rem;">✕</button>
					</div>
				@endif

				@if ($errors->any())
					<div class="placeholder-content" style="margin-bottom: 1rem;">
						@foreach ($errors->all() as $error)
							<p>{{ $error }}</p>
						@endforeach
					</div>
				@endif

				<div class="profile-wrapper">
					<div class="profile-header-card">
						<div class="profile-avatar-wrapper">
							<img src="{{ $avatarUrl }}" alt="Avatar" class="profile-avatar-large">
							<button type="button" class="edit-avatar-btn" id="btn-avatar-trigger" title="Cambiar Foto">
								<i class='bx bx-camera'></i>
							</button>
						</div>
						<div class="profile-title-info">
							<h2>{{ $displayName }}</h2>
							<p>{{ $profileSubtitle }}</p>
							<div class="profile-badge">{{ $roleLabel }}</div>
						</div>
					</div>

					<div class="profile-details-card">
						<h3 class="card-title">Información Personal</h3>
						<form id="profile-form" class="form-grid" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
							@csrf
							@method('PATCH')
							<input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>

							<div class="form-group">
								<label>Nombres</label>
								<input type="text" name="first_names" class="form-input" data-editable="true" value="{{ old('first_names', $firstNames) }}" readonly>
							</div>
							<div class="form-group">
								<label>Apellidos</label>
								<input type="text" name="last_names" class="form-input" data-editable="true" value="{{ old('last_names', $lastName === 'No registrado' ? '' : $lastName) }}" readonly>
							</div>
							<div class="form-group">
								<label>Documento (DNI / RUC)</label>
								<input type="text" name="document_number" class="form-input" data-editable="true" value="{{ old('document_number', $documentNumber ?? 'No registrado') }}" readonly>
							</div>
							<div class="form-group">
								<label>Correo Electrónico</label>
								<input type="email" class="form-input" value="{{ $user?->email ?? '' }}" disabled>
							</div>
							<div class="form-group">
								<label>Teléfono Celular</label>
								<input type="tel" name="phone" class="form-input" data-editable="true" value="{{ old('phone', $phone ?? 'No registrado') }}" readonly>
							</div>
							<div class="form-group full-width">
								<label>Dirección</label>
								<input type="text" name="address" class="form-input" data-editable="true" value="{{ old('address', $address ?? 'No registrada') }}" readonly>
							</div>

							<div class="form-group full-width profile-actions" id="actions-view">
								<button type="button" class="btn-primary" id="btn-edit-profile" style="grid-column: span 2;">
									<i class='bx bx-edit-alt'></i> Editar Perfil
								</button>
							</div>

							<div class="form-group full-width profile-actions" id="actions-edit" style="display: none;">
								<button type="button" class="btn-secondary" id="btn-cancel-edit">Cancelar Cambios</button>
								<button type="submit" class="btn-primary" id="btn-save-profile">
									<i class='bx bx-save'></i> Guardar Información
								</button>
							</div>
						</form>
					</div>
				</div>
			</main>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		document.body.classList.add('mendieta-admin');

		const toggles = document.querySelectorAll('.toggle-submenu');
		toggles.forEach(toggle => {
			toggle.addEventListener('click', function(e) {
				e.preventDefault();
				const parentLi = this.parentElement;
				const chevron = this.querySelector('.chevron');

				parentLi.classList.toggle('open');

				if (parentLi.classList.contains('open')) {
					chevron.style.transform = 'rotate(180deg)';
				} else {
					chevron.style.transform = 'rotate(0deg)';
				}
			});
		});

		// themeToggleBtn, themeIcon, savedTheme, profileBtn y profileDropdown
		// ya están gestionados en layouts/app.blade.php — no redeclarar aquí.
		const successAlert      = document.getElementById('profile-success-alert');
		const closeSuccessAlert = document.getElementById('close-success-alert');

		if (successAlert) {
			setTimeout(() => {
				successAlert.style.display = 'none';
			}, 3500);

			if (closeSuccessAlert) {
				closeSuccessAlert.addEventListener('click', () => {
					successAlert.style.display = 'none';
				});
			}
		}

		const btnEditProfile = document.getElementById('btn-edit-profile');
		const btnCancelEdit = document.getElementById('btn-cancel-edit');
		const profileForm = document.getElementById('profile-form');
		const avatarInput = document.getElementById('avatar');
		const avatarTrigger = document.getElementById('btn-avatar-trigger');
		const avatarPreview = document.querySelector('.profile-avatar-large');
		const actionsView = document.getElementById('actions-view');
		const actionsEdit = document.getElementById('actions-edit');
		const formInputs = document.querySelectorAll('.form-grid .form-input[data-editable="true"]');
		let originalValues = [];

		if (avatarTrigger && avatarInput) {
			avatarTrigger.addEventListener('click', () => avatarInput.click());
		}

		if (avatarInput && avatarPreview) {
			avatarInput.addEventListener('change', () => {
				const file = avatarInput.files?.[0];
				if (!file) return;

				avatarPreview.src = URL.createObjectURL(file);

				if (profileForm) {
					profileForm.requestSubmit();
				}
			});
		}

		if (btnEditProfile && btnCancelEdit && actionsView && actionsEdit) {
			btnEditProfile.addEventListener('click', () => {
				originalValues = Array.from(formInputs).map(input => input.value);

				formInputs.forEach(input => {
					if (input.value === 'No registrado' || input.value === 'No registrada') {
						input.value = '';
					}

					input.removeAttribute('readonly');
				});

				if(formInputs.length > 0) {
					formInputs[0].focus();
				}

				actionsView.style.display = 'none';
				actionsEdit.style.display = 'grid';
				document.querySelector('.form-grid').classList.add('is-editing');
			});

			btnCancelEdit.addEventListener('click', () => {
				formInputs.forEach((input, index) => {
					input.value = originalValues[index];
					input.setAttribute('readonly', 'true');
				});

				if (avatarInput) {
					avatarInput.value = '';
				}

				actionsEdit.style.display = 'none';
				actionsView.style.display = 'grid';
				document.querySelector('.form-grid').classList.remove('is-editing');
			});
		}
	</script>
@endpush
