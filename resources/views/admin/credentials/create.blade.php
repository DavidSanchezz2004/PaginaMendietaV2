@extends('layouts.app')

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

        <main class="main-content">
            <div class="module-content-stack">
                <div class="dashboard-header">
                    <div class="dashboard-title">
                        <h2>Nueva Credencial</h2>
                        <p>Añadir un acceso a la bóveda segura</p>
                    </div>
                    <a href="{{ route('credentials.index') }}" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <i class='bx bx-arrow-back'></i> Volver a la Bóveda
                    </a>
                </div>

                <div class="dashboard-card" style="max-width: 700px; padding: 2.5rem; margin: 0 auto; border-radius: 1.25rem;">
                    <form action="{{ route('credentials.store') }}" method="POST" class="premium-form">
                        @csrf

                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label for="company_id" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Cliente / Empresa</label>
                                <select name="company_id" id="company_id" class="form-select" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                                    <option value="" disabled selected>Seleccione una empresa</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }} - {{ $company->ruc }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="platform" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Plataforma / Institución</label>
                                <input type="text" name="platform" id="platform" class="form-input" value="{{ old('platform') }}" placeholder="Ej: SUNAT, Sistema de Planillas, AFP Net" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                                @error('platform') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="username" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Usuario / RUC (Opcional)</label>
                                <input type="text" name="username" id="username" class="form-input" value="{{ old('username') }}" placeholder="Ej: 20123456789MOD" style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                                @error('username') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Contraseña / Clave (Sifrada)</label>
                                <div style="position: relative;">
                                    <input type="text" name="password" id="password" class="form-input" value="{{ old('password') }}" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                                    <i class='bx bx-lock-alt' style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                                </div>
                                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class='bx bx-shield-quarter' style="color: #10b981; font-size: 1rem;"></i> Esta contraseña será cifrada con AES-256 de forma segura.
                                </p>
                                @error('password') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="notes" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Notas Adicionales (Opcional)</label>
                                <textarea name="notes" id="notes" class="form-input" rows="3" placeholder="Pregunta secreta, correo de recuperación asociado..." style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s; resize: none;">{{ old('notes') }}</textarea>
                                @error('notes') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6;">
                            <a href="{{ route('credentials.index') }}" class="btn-secondary" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem;">Cancelar</a>
                            <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem; border-radius: 0.75rem; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                                <i class='bx bx-lock-open'></i> Guardar y Cifrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </section>
</div>

<style>
    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--clr-active-bg) !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 4px rgba(52, 103, 92, 0.1);
    }
    .form-label {
        transition: color 0.2s;
    }
    .form-group:focus-within .form-label {
        color: var(--clr-active-bg);
    }
</style>
@endsection
