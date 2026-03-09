@extends('layouts.app')

@section('title', 'Configuración Feasy | Portal Mendieta')

@push('styles')
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}">
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
            'userName'    => auth()->user()?->name,
            'userEmail'   => auth()->user()?->email,
        ])

        <div class="main-content">
            <div class="placeholder-content">

                {{-- Breadcrumb --}}
                <div class="module-toolbar" style="margin-bottom:1.5rem;">
                    <div>
                        <h1 style="font-size:1.3rem; font-weight:700; color:#1a1a2e;">
                            <i class='bx bx-key' style="color:#1a6b57; margin-right:.4rem;"></i>
                            Configuración Global de Feasy
                        </h1>
                        <p style="font-size:.85rem; color:#6b7280; margin-top:.2rem;">
                            Un solo token de Feasy gestiona todas las empresas del portal.<br>
                            Las empresas se identifican por su RUC en cada solicitud a la API.
                        </p>
                    </div>
                </div>

                {{-- Alertas --}}
                @if(session('success'))
                    <div class="alert-success" style="margin-bottom:1rem; padding:.75rem 1rem;
                         background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px;
                         color:#065f46; font-size:.9rem;">
                        <i class='bx bx-check-circle'></i> {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-error" style="margin-bottom:1rem; padding:.75rem 1rem;
                         background:#fee2e2; border:1px solid #fca5a5; border-radius:8px;
                         color:#991b1b; font-size:.9rem;">
                        @foreach($errors->all() as $err)
                            <p><i class='bx bx-error-circle'></i> {{ $err }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Estado actual --}}
                <div style="margin-bottom:1.5rem; padding:1rem 1.25rem;
                     border:1px solid {{ $hasToken ? '#6ee7b7' : '#fca5a5' }};
                     border-radius:10px;
                     background:{{ $hasToken ? '#f0fdf4' : '#fff7f7' }};">
                    @if($hasToken)
                        <p style="color:#065f46; font-size:.9rem;">
                            <i class='bx bx-check-circle' style="font-size:1.1rem;"></i>
                            <strong>Token configurado</strong> — {{ $tokenLen }} caracteres.
                            El Facturador puede emitir comprobantes.
                        </p>
                    @else
                        <p style="color:#991b1b; font-size:.9rem;">
                            <i class='bx bx-error-circle' style="font-size:1.1rem;"></i>
                            <strong>Sin token</strong> — ninguna empresa podrá emitir comprobantes hasta configurarlo.
                        </p>
                    @endif
                </div>

                {{-- Formulario --}}
                <form method="POST" action="{{ route('configuracion.feasy.update') }}" class="module-form">
                    @csrf

                    <div class="companies-form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="feasy_token" style="font-weight:600;">
                                Token de la API Feasy
                            </label>
                            <input
                                type="password"
                                id="feasy_token"
                                name="feasy_token"
                                class="form-input @error('feasy_token') is-invalid @enderror"
                                placeholder="{{ $hasToken ? 'Ingresa un nuevo token para reemplazar el actual' : 'Pega aquí el token de tu cuenta Feasy' }}"
                                autocomplete="new-password"
                                style="font-family: monospace; letter-spacing:.05em;"
                            >
                            <p style="font-size:.78rem; color:#6b7280; margin-top:.35rem;">
                                Obtén el token en tu cuenta de
                                <a href="https://feasyperu.com" target="_blank" rel="noopener" style="color:#1a6b57;">feasyperu.com</a>
                                → Configuración → API Access.
                                Dejar vacío borra el token actual.
                            </p>
                        </div>
                    </div>

                    <div class="form-group full-width profile-actions module-actions" style="margin-top:1.25rem;">
                        <button type="submit" class="btn-primary">
                            <i class='bx bx-save'></i> Guardar Token
                        </button>
                        <a href="{{ url()->previous() }}" class="btn-secondary" style="margin-left:.5rem;">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>
@endsection
