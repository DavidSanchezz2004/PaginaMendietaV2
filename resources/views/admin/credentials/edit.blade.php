@extends('layouts.app')

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

    @include('partials.header', [
        'welcomeName' => auth()->user()?->name,
        'userName' => auth()->user()?->name,
        'userEmail' => auth()->user()?->email,
    ])

    <main class="main-content">
        <div class="module-content-stack">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h2>Editar Credencial</h2>
                    <p>Modificando detalles o clave para {{ $credential->platform }}</p>
                </div>
                <a href="{{ route('credentials.index') }}" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <i class='bx bx-arrow-back'></i> Volver
                </a>
            </div>

            <div class="dashboard-card" style="max-width: 700px; padding: 2.5rem; margin: 0 auto; border-radius: 1.25rem;">
                <form action="{{ route('credentials.update', $credential) }}" method="POST" class="premium-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="company_id" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Cliente / Empresa</label>
                            <select name="company_id" id="company_id" class="form-select" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $credential->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }} - {{ $company->ruc }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="platform" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Plataforma / Institución</label>
                            <input type="text" name="platform" id="platform" class="form-input" value="{{ old('platform', $credential->platform) }}" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                            @error('platform') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="username" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Usuario / RUC</label>
                            <input type="text" name="username" id="username" class="form-input" value="{{ old('username', $credential->username) }}" style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                            @error('username') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
                            <div style="position: relative;">
                                <input type="text" name="password" id="password" class="form-input" placeholder="Ingresa la nueva contraseña para sobreescribir" style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s;">
                                <i class='bx bx-refresh' style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                            </div>
                            <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">La contraseña actual está oculta y segura. Solo llena este campo si deseas cambiarla.</p>
                            @error('password') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Notas Adicionales</label>
                            <textarea name="notes" id="notes" class="form-input" rows="3" style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s; resize: none;">{{ old('notes', $credential->notes) }}</textarea>
                            @error('notes') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6;">
                        <a href="{{ route('credentials.index') }}" class="btn-secondary" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem;">Cancelar</a>
                        <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem; border-radius: 0.75rem; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                            <i class='bx bx-save'></i> Guardar Cambios
                        </button>
                    </div>
                </form>

                <div style="margin-top: 2rem; border-top: 1px solid #f3f4f6; padding-top: 1.5rem;">
                    <form action="{{ route('credentials.destroy', $credential) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta credencial permanentemente? No se puede recuperar.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 0.875rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; border-radius: 0.5rem; transition: background 0.2s;">
                            <i class='bx bx-trash' style="font-size: 1.1rem;"></i> Eliminar de la Bóveda Segura
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
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
    button[style*="color: #ef4444"]:hover {
        background-color: #fef2f2 !important;
    }
</style>
@endsection
