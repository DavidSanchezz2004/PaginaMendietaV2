@extends('layouts.app')

@section('title', isset($report) ? 'Editar Reporte' : 'Nuevo Reporte')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
	<div class="app-layout">
		<aside class="sidebar-premium">
			<div class="sidebar-header">
				<img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
				<div class="header-text">
					<h2>Portal Mendieta</h2>
					<p>Panel Administrativo</p>
				</div>
			</div>

			<hr class="sidebar-divider">

			<div class="sidebar-menu-wrapper">
				<span class="menu-label">MENÚ PRINCIPAL</span>
				@include('partials.sidebar-menu')
			</div>
		</aside>

		<section class="main-wrapper">
			@include('partials.header', [
				'welcomeName' => auth()->user()?->name,
				'userName' => auth()->user()?->name,
				'userEmail' => auth()->user()?->email,
			])

			<div class="main-content">
			<div class="dashboard-container">
				<div class="page-header simple-header">
					<div>
						<h1 class="page-title">{{ isset($report) ? 'Editar Reporte' : 'Subir Nuevo Reporte' }}</h1>
						<p class="page-description">
							Completa el formulario para {{ isset($report) ? 'actualizar el' : 'registrar un nuevo' }} reporte en la empresa activa.
						</p>
					</div>
					<a href="{{ route('reports.index') }}" class="btn-secondary">
						<i class='bx bx-arrow-back'></i> Volver
					</a>
				</div>

				<div class="module-content-stack">
					<div class="report-card" style="padding: 2rem;">
						<form action="{{ isset($report) ? route('reports.update', $report) : route('reports.store') }}" method="POST" enctype="multipart/form-data" class="module-form">
							@csrf
							@if(isset($report))
								@method('PATCH')
							@endif

							<div class="companies-form-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
								<!-- Mes -->
								<div class="form-group">
									<label for="period_month">Mes del Reporte <span style="color: red;">*</span></label>
									<select name="period_month" id="period_month" class="form-input @error('period_month') is-invalid @enderror" required>
										@foreach(range(1, 12) as $m)
											<option value="{{ $m }}" {{ old('period_month', $report->period_month ?? date('n')) == $m ? 'selected' : '' }}>
												{{ Str::ucfirst(Carbon\Carbon::create()->month($m)->translatedFormat('F')) }}
											</option>
										@endforeach
									</select>
									@error('period_month') <div style="color: red; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
								</div>

								<!-- Año -->
								<div class="form-group">
									<label for="period_year">Año del Reporte <span style="color: red;">*</span></label>
									<select name="period_year" id="period_year" class="form-input @error('period_year') is-invalid @enderror" required>
										@foreach(range(date('Y') - 5, date('Y') + 1) as $y)
											<option value="{{ $y }}" {{ old('period_year', $report->period_year ?? date('Y')) == $y ? 'selected' : '' }}>
												{{ $y }}
											</option>
										@endforeach
									</select>
									@error('period_year') <div style="color: red; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
								</div>

								<!-- Formato -->
								<div class="form-group">
									<label for="format">Formato <span style="color: red;">*</span></label>
									<select name="format" id="format" class="form-input @error('format') is-invalid @enderror" required>
										<option value="pdf" {{ old('format', $report->format ?? '') === 'pdf' ? 'selected' : '' }}>PDF Documento</option>
										<option value="excel" {{ old('format', $report->format ?? '') === 'excel' ? 'selected' : '' }}>Excel Contable</option>
										<option value="powerbi" {{ old('format', $report->format ?? '') === 'powerbi' ? 'selected' : '' }}>Enlace PowerBI</option>
									</select>
									@error('format') <div style="color: red; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
								</div>

								<!-- Título -->
								<div class="form-group" style="grid-column: 1 / -1;">
									<label for="title">Título del Reporte <span style="color: red;">*</span></label>
									<input type="text" name="title" id="title" class="form-input @error('title') is-invalid @enderror" value="{{ old('title', $report->title ?? '') }}" required>
									@error('title') <div style="color: red; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
								</div>

								<!-- Descripción -->
								<div class="form-group" style="grid-column: 1 / -1;">
									<label for="description">Descripción o Notas (Opcional)</label>
									<textarea name="description" id="description" class="form-input @error('description') is-invalid @enderror" rows="3">{{ old('description', $report->description ?? '') }}</textarea>
									@error('description') <div style="color: red; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
								</div>

								<!-- Archivo Adjunto (Solo PDF / Excel) -->
								<div class="form-group" id="file_container" style="grid-column: 1 / -1;">
									<label for="file">Subir Archivo <span style="color: red;">*</span></label>
									<input type="file" name="file" id="file" class="form-input @error('file') is-invalid @enderror" accept=".pdf,.xls,.xlsx,.csv">
									@if(isset($report) && in_array($report->format, ['pdf', 'excel']) && $report->file_path)
										<div style="font-size: 0.85rem; margin-top: 0.5rem; color: #15803d; font-weight: 500;">
											<i class='bx bx-check-circle'></i> Ya existe un archivo adjunto. Sube uno nuevo solo si quieres reemplazarlo.
										</div>
									@endif
									@error('file') <div style="color: red; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
								</div>

								<!-- Enlace PowerBI -->
								<div class="form-group" id="pbi_container" style="display: none; grid-column: 1 / -1;">
									<label for="external_url">Enlace de PowerBI <span style="color: red;">*</span></label>
									<input type="url" name="external_url" id="external_url" class="form-input @error('external_url') is-invalid @enderror" value="{{ old('external_url', $report->external_url ?? '') }}" placeholder="https://app.powerbi.com/view?r=...">
									<div style="font-size: 0.85rem; margin-top: 0.5rem; color: var(--clr-text-muted);">Asegúrate que el enlace sea seguro y accesible para la empresa.</div>
									@error('external_url') <div style="color: red; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div> @enderror
								</div>

								<div class="module-actions" style="grid-column: 1 / -1; display: flex; justify-content: flex-end;">
									<button type="submit" class="btn-primary">
										<i class='bx bx-save' style="font-size: 1.2rem; margin-right: 0.25rem;"></i> {{ isset($report) ? 'Guardar Cambios' : 'Registrar Reporte' }}
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formatSelect = document.getElementById('format');
            const fileContainer = document.getElementById('file_container');
            const pbiContainer = document.getElementById('pbi_container');
            const fileInput = document.getElementById('file');
            const urlInput = document.getElementById('external_url');

            function toggleFields() {
                const isPowerBi = formatSelect.value === 'powerbi';
                
                if (isPowerBi) {
                    fileContainer.style.display = 'none';
                    pbiContainer.style.display = 'block';
                    fileInput.removeAttribute('required');
                    urlInput.setAttribute('required', 'required');
                } else {
                    fileContainer.style.display = 'block';
                    pbiContainer.style.display = 'none';
                    urlInput.removeAttribute('required');
                    
                    @if(isset($report))
                        var hasReport = true;
                    @else
                        var hasReport = false;
                    @endif
                    
                    if (!hasReport) {
                        fileInput.setAttribute('required', 'required');
                    } else {
                        fileInput.removeAttribute('required'); // On update, file is optional if we already have one
                    }
                }
            }

            formatSelect.addEventListener('change', toggleFields);
            toggleFields();
        });
    </script>
			</div>
		</section>
	</div>
@endsection

@push('scripts')
	<script>
		document.querySelectorAll('.toggle-submenu').forEach((btn) => {
			btn.addEventListener('click', (event) => {
				event.preventDefault();
				btn.closest('.nav-item')?.classList.toggle('open');
			});
		});

		const themeToggleBtn = document.getElementById('theme-toggle');
		const themeIcon = document.getElementById('theme-icon');
		const applyTheme = (theme) => {
			const isDark = theme === 'dark';
			document.body.classList.toggle('dark-mode', isDark);
			if (themeIcon) {
				themeIcon.classList.toggle('bx-moon', !isDark);
				themeIcon.classList.toggle('bx-sun', isDark);
			}
		};
		applyTheme(localStorage.getItem('mendieta-theme') || 'light');
		if (themeToggleBtn) {
			themeToggleBtn.addEventListener('click', () => {
				const next = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
				localStorage.setItem('mendieta-theme', next);
				applyTheme(next);
			});
		}

		const profileBtn = document.getElementById('profile-btn');
		const profileDropdown = document.getElementById('profile-dropdown');
		if (profileBtn && profileDropdown) {
			profileBtn.addEventListener('click', () => profileDropdown.classList.toggle('show'));
			document.addEventListener('click', (event) => {
				const container = document.getElementById('profile-container');
				if (container && !container.contains(event.target)) {
					profileDropdown.classList.remove('show');
				}
			});
		}
	</script>
@endpush
