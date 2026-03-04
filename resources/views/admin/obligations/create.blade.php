@extends('layouts.app')

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

        <main class="main-content">
            <div class="module-content-stack">
                <div class="dashboard-header">
                    <div class="dashboard-title">
                        <h2>Nueva Obligación</h2>
                        <p>Agendar un vencimiento o pago para una empresa</p>
                    </div>
                    <a href="{{ route('obligations.index') }}" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <i class='bx bx-arrow-back'></i> Volver al Calendario
                    </a>
                </div>

                <div class="dashboard-card form-container" style="max-width: 700px; padding: 2.5rem; margin: 0 auto; border-radius: 1.25rem; background: var(--clr-bg-card, #fff); border: 1px solid var(--clr-border-light, #f3f4f6); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <form action="{{ route('obligations.store') }}" method="POST" class="premium-form">
                        @csrf

                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="company_id" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Cliente / Empresa</label>
                                <div class="input-wrapper" style="position: relative;">
                                    <select name="company_id" id="company_id" class="form-select" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--clr-border-light, #e5e7eb); background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); transition: all 0.2s;">
                                        <option value="" disabled selected>Seleccione una empresa</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }} - {{ $company->ruc }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('company_id') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="title" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Título de la Obligación</label>
                                <input type="text" name="title" id="title" class="form-input" value="{{ old('title') }}" placeholder="Ej: Pago de Planilla Mes de Marzo" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--clr-border-light, #e5e7eb); background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); transition: all 0.2s;">
                                @error('title') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="due_date" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Fecha de Vencimiento</label>
                                <input type="date" name="due_date" id="due_date" class="form-input" value="{{ old('due_date') }}" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--clr-border-light, #e5e7eb); background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); transition: all 0.2s;">
                                @error('due_date') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Estado Inicial</label>
                                <select name="status" id="status" class="form-select" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--clr-border-light, #e5e7eb); background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); transition: all 0.2s;">
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pendiente (Normal)</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completado (Pagado)</option>
                                    <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Vencido (Atención)</option>
                                </select>
                                @error('status') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label for="description" class="form-label" style="font-weight: 600; color: var(--clr-text-main); margin-bottom: 0.5rem; display: block;">Notas adicionales (Opcional)</label>
                                <textarea name="description" id="description" class="form-input" rows="4" placeholder="Algún número de cuenta, monto u observación..." style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--clr-border-light, #e5e7eb); background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); transition: all 0.2s; resize: none;">{{ old('description') }}</textarea>
                                @error('description') <span class="text-danger" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--clr-border-light, #f3f4f6);">
                            <a href="{{ route('obligations.index') }}" class="btn-secondary" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem;">Cancelar</a>
                            <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem; border-radius: 0.75rem; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                                <i class='bx bx-check-circle'></i> Guardar Obligación
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
        background-color: var(--clr-bg-card, #fff) !important;
        box-shadow: 0 0 0 4px rgba(52, 103, 92, 0.1);
    }
    .form-label {
        transition: color 0.2s;
    }
    .form-group:focus-within .form-label {
        color: var(--clr-active-bg);
    }
</style>
