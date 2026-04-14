@extends('layouts.app')

@section('title', 'Reportes - Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
@endpush

@section('content')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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
						<h1 class="page-title">Reportes {{ $activeCompany ? '- ' . $activeCompany->name : '' }}</h1>
						<p class="page-description">Gestiona los reportes contables y financieros de la empresa.</p>
					</div>
					@if(! $isCliente)
						<div class="header-actions">
							<a href="{{ route('reports.create') }}" class="btn-primary">
								<i class='bx bx-plus' style="font-size: 1.2rem;"></i> Nuevo Reporte
							</a>
						</div>
					@endif
				</div>

				@if (session('status'))
					<div class="module-alert" style="color: #15803d; background-color: #dcfce7; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
						{{ session('status') }}
					</div>
				@endif

				<div class="module-content-stack">
					<div class="report-card">
						<div class="table-responsive">
							<table class="report-table">
								<thead>
									<tr>
										<th>Periodo</th>
										<th>Título</th>
										<th>Formato</th>
										<th>Estado</th>
										<th>Subido por</th>
										<th>Lecturas/Valoraciones</th>
										<th style="text-align: right;">Acciones</th>
									</tr>
								</thead>
								<tbody>
									@forelse ($reports as $report)
										<tr class="report-row">
											<td>
												<span class="period-pill">
													{{ str_pad($report->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $report->period_year }}
												</span>
											</td>
											<td class="row-title">{{ $report->title }}</td>
											<td>
												@if($report->format === 'pdf')
													<span class="format-badge format-pdf"><i class='bx bxs-file-pdf'></i> PDF</span>
												@elseif($report->format === 'excel')
													<span class="format-badge" style="background-color: rgba(34, 197, 94, 0.15); color: #15803d;"><i class='bx bxs-file-blank'></i> Excel</span>
												@else
													<span class="format-badge format-pbi"><i class='bx bx-bar-chart-alt-2'></i> PowerBI</span>
												@endif
											</td>
											<td>
												@if($report->status === 'published')
													<div class="status-new"><span class="status-dot"></span><span class="status-text">Publicado</span></div>
												@else
													<div class="status-viewed"><span class="status-dot" style="background-color: var(--clr-text-muted);"></span><span class="status-text">Borrador</span></div>
												@endif
											</td>
											<td style="color: var(--clr-text-muted); font-size: 0.9rem; font-weight: 500;">{{ $report->uploader->name ?? 'N/A' }}</td>
											<td style="font-weight: 600; color: var(--clr-text-main);">
												<i class='bx bx-show' style="color: var(--clr-text-muted); margin-right: 0.25rem;"></i> {{ $report->reportUserStatuses()->count() }}
											</td>
											<td class="cell-action">
												<div class="action-wrapper">
													@if($report->format === 'powerbi')
														<a href="{{ $report->external_url }}" target="_blank" class="btn-outline-sm"
															onclick="fetch('{{ route('reports.track-read', $report) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })" title="Abrir PowerBI">
															<i class='bx bx-link-external'></i> Abrir
														</a>
													@else
														<a href="{{ route('reports.download', $report) }}" class="btn-outline-sm" target="_blank" title="Descargar Documento">
															<i class='bx bx-download'></i> Descargar
														</a>
													@endif

													@can('update', $report)
														<a href="{{ route('reports.edit', $report) }}" class="btn-action-icon" title="Editar">
															<i class='bx bx-edit-alt'></i>
														</a>
													@endcan

													@can('publish', $report)
														@if($report->status === 'draft')
															<form action="{{ route('reports.publish', $report) }}" method="POST" style="display:inline-block;">
																@csrf
																<button type="submit" class="btn-action-icon" style="color: #15803d; background-color: rgba(34, 197, 94, 0.1);" title="Publicar">
																	<i class='bx bx-check-circle'></i>
																</button>
															</form>
														@else
															<form action="{{ route('reports.unpublish', $report) }}" method="POST" style="display:inline-block;">
																@csrf
																<button type="submit" class="btn-action-icon" style="color: #b48600; background-color: rgba(242, 200, 17, 0.1);" title="Ocultar (Borrador)">
																	<i class='bx bx-hide'></i>
																</button>
															</form>
														@endif
													@endcan

													@can('delete', $report)
														<form action="{{ route('reports.destroy', $report) }}" method="POST" style="display:inline-block;" data-confirm="¿Eliminar reporte permanentemente? Esta acción no se puede deshacer.">
															@csrf
															@method('DELETE')
															<button type="submit" class="btn-action-icon" style="color: #b91c1c; background-color: rgba(239, 68, 68, 0.1);" title="Eliminar">
																<i class='bx bx-trash'></i>
															</button>
														</form>
													@endcan
												</div>
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="7" style="text-align: center; padding: 3rem; color: var(--clr-text-muted); font-weight: 500;">
												<i class='bx bx-folder-open' style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem; display: block;"></i>
												No se encontraron reportes.
											</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>
						
						<div class="report-header" style="border-top: 1px solid rgba(0,0,0,0.05); border-bottom: none;">
							{{ $reports->links() }}
						</div>
					</div>
				</div>
			</div>
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
