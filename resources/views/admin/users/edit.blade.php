@extends('layouts.app')

@section('title', 'Editar Usuario | Portal Mendieta')

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
          <p>Panel interno</p>
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
        @if ($errors->any())
          <div class="placeholder-content module-alert">
            @foreach ($errors->all() as $error)
              <p>{{ $error }}</p>
            @endforeach
          </div>
        @endif

        <div class="module-content-stack">
          <div class="placeholder-content module-card-wide companies-module-card">
            <div class="module-toolbar">
              <div>
                <h1>Editar Usuario</h1>
                <p style="margin-top:.35rem; color: var(--clr-text-muted);">Actualiza datos base y rol de sistema.</p>
              </div>
            </div>

            <form method="POST" action="{{ route('users.update', $managedUser) }}" class="module-form companies-form-grid">
              @csrf
              @method('PATCH')

              <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $managedUser->name) }}" required>
              </div>

              <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $managedUser->email) }}" required>
              </div>

              <div class="form-group">
                <label>Rol de sistema</label>
                <select name="role" class="form-input" required>
                  @foreach($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $managedUser->role?->value ?? (string) $managedUser->role) === $role->value)>{{ ucfirst($role->value) }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label>Nueva contraseña (opcional)</label>
                <input type="password" name="password" class="form-input" minlength="8" autocomplete="new-password">
              </div>

              <div class="form-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-input" minlength="8" autocomplete="new-password">
              </div>

              <div class="form-group full-width module-actions" style="display:flex; justify-content:flex-end; gap:.75rem;">
                <a href="{{ route('users.index') }}" class="btn-secondary companies-btn-link">Cancelar</a>
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Guardar Cambios
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </section>
  </div>
@endsection
