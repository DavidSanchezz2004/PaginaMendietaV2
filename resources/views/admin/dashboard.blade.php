@extends('layouts.app')

@section('title', 'Admin Dashboard | Mendieta Estudio Contable')

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
		// Scripts específicos del Dashboard Admin
	</script>
@endpush
