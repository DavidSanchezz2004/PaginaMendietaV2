@extends('layouts.app')

@section('title', 'Supervisor Dashboard | Mendieta Estudio Contable')

@push('styles')
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
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
				<div class="placeholder-content">
					<h1>Dashboard Admin</h1>
					<p>Vista base operativa con sidebar y header cargados.</p>
				</div>
			</div>
		</section>
	</div>
@endsection

@push('scripts')
	<script>
		document.body.classList.add('mendieta-admin');

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
