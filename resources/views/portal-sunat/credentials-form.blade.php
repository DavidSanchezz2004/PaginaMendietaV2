@extends('layouts.app')

@section('title', 'Credenciales SOL — ' . $company->name . ' | Portal Mendieta')

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
          <p>{{ auth()->user()?->role?->value === 'client' ? 'Panel cliente' : 'Panel interno' }}</p>
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

      <main class="main-content">
        <div class="module-content-stack">

          @if ($errors->any())
            <div class="placeholder-content module-alert">
              @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
          @endif

          <div class="placeholder-content module-card-wide">

            {{-- Header con breadcrumb --}}
            <div class="module-toolbar" style="margin-bottom:1.25rem;">
              <div>
                <p style="margin:0; font-size:.8rem; color:#64748b;">
                  <a href="{{ route('portal-sunat.index') }}" style="color:#2563eb; text-decoration:none;">
                    <i class='bx bx-arrow-back'></i> Portal SUNAT
                  </a>
                </p>
                <h1 style="margin:.35rem 0 0;">
                  Credenciales SOL
                </h1>
                <p style="margin:.35rem 0 0; color:#6b7280; font-size:.92rem;">
                  {{ $company->name }} &mdash; RUC {{ $company->ruc }}
                </p>
              </div>
            </div>

            {{-- Info card empresa --}}
            <div style="display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.5rem;">
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:.85rem 1.1rem; flex:1 1 200px;">
                <p style="margin:0; font-size:.75rem; color:#64748b; text-transform:uppercase; letter-spacing:.06em;">Estado empresa</p>
                <p style="margin:.3rem 0 0; font-weight:700; color:{{ $company->canUseSunatPortal() ? '#166534' : '#b91c1c' }};">
                  {{ $company->canUseSunatPortal() ? 'Activa' : 'Inactiva' }}
                </p>
              </div>
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:.85rem 1.1rem; flex:1 1 200px;">
                <p style="margin:0; font-size:.75rem; color:#64748b; text-transform:uppercase; letter-spacing:.06em;">Credenciales SOL</p>
                <p style="margin:.3rem 0 0; font-weight:700; color:{{ $company->hasSunatCredentials() ? '#166534' : '#92400e' }};">
                  {{ $company->hasSunatCredentials() ? 'Configuradas' : 'Pendientes' }}
                </p>
              </div>
            </div>

            {{-- Formulario SOL --}}
            <form method="POST" action="{{ route('portal-sunat.credentials.update', $company) }}"
              class="module-form companies-form-grid">
              @csrf
              @method('PUT')

              <div class="form-group">
                <label>Usuario SOL <span style="color:#dc2626;">*</span></label>
                <input
                  type="text"
                  name="usuario_sol"
                  class="form-input"
                  autocomplete="off"
                  placeholder="Ej. MIUSUARIOSOL"
                  value="{{ old('usuario_sol', $company->usuario_sol ?? '') }}"
                  required>
                @error('usuario_sol')<p class="form-error">{{ $message }}</p>@enderror
                <p style="font-size:.78rem; color:#94a3b8; margin-top:.3rem;">
                  El usuario SOL asignado por SUNAT para esta empresa.
                </p>
              </div>

              <div class="form-group">
                <label>Clave SOL</label>
                <input
                  type="password"
                  name="clave_sol"
                  class="form-input"
                  autocomplete="new-password"
                  placeholder="{{ $company->clave_sol ? '(guardada — dejar en blanco para no cambiar)' : 'Ingresa la clave SOL' }}">
                @error('clave_sol')<p class="form-error">{{ $message }}</p>@enderror
                <p style="font-size:.78rem; color:#94a3b8; margin-top:.3rem;">
                  Déjalo en blanco para conservar la clave actual.
                </p>
              </div>

              <div class="form-group full-width profile-actions module-actions">
                <a href="{{ route('portal-sunat.index') }}" class="btn-secondary">
                  <i class='bx bx-arrow-back'></i> Cancelar
                </a>
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Guardar credenciales
                </button>
              </div>
            </form>

            {{-- ── Separador AFPnet ─────────────────────────────────────── --}}
            <hr style="border:none; border-top:1px solid #e2e8f0; margin:2rem 0;">
            <div style="margin-bottom:1.25rem;">
              <h2 style="margin:0 0 .35rem; font-size:1.1rem;">Credenciales AFPnet</h2>
              <p style="margin:0; color:#6b7280; font-size:.92rem;">
                Credenciales del portal AFPnet (diferentes a las de SUNAT). Solo el RUC se comparte.
              </p>
            </div>

            {{-- Info cards AFPnet --}}
            <div style="display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.5rem;">
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:.85rem 1.1rem; flex:1 1 200px;">
                <p style="margin:0; font-size:.75rem; color:#64748b; text-transform:uppercase; letter-spacing:.06em;">Estado AFPnet</p>
                <p style="margin:.3rem 0 0; font-weight:700; color:{{ $company->hasAfpnetCredentials() ? '#166534' : '#92400e' }};">
                  {{ $company->hasAfpnetCredentials() ? 'Configuradas' : 'Pendientes' }}
                </p>
              </div>
            </div>

            {{-- Formulario AFPnet (formulario separado) --}}
            <form method="POST" action="{{ route('portal-sunat.credentials.update', $company) }}"
              class="module-form companies-form-grid">
              @csrf
              @method('PUT')

              <div class="form-group">
                <label>Usuario AFPnet</label>
                <input
                  type="text"
                  name="afpnet_usuario"
                  class="form-input"
                  autocomplete="off"
                  placeholder="Ej. usuario@empresa.com"
                  value="{{ old('afpnet_usuario', $company->afpnet_usuario ?? '') }}">
                @error('afpnet_usuario')<p class="form-error">{{ $message }}</p>@enderror
                <p style="font-size:.78rem; color:#94a3b8; margin-top:.3rem;">
                  El usuario de acceso al portal AFPnet.
                </p>
              </div>

              <div class="form-group">
                <label>Clave AFPnet</label>
                <input
                  type="password"
                  name="afpnet_clave"
                  class="form-input"
                  autocomplete="new-password"
                  placeholder="{{ $company->afpnet_clave ? '(guardada — dejar en blanco para no cambiar)' : 'Ingresa la contraseña AFPnet' }}">
                @error('afpnet_clave')<p class="form-error">{{ $message }}</p>@enderror
                <p style="font-size:.78rem; color:#94a3b8; margin-top:.3rem;">
                  Déjalo en blanco para conservar la clave actual.
                </p>
              </div>

              {{-- Campo usuario_sol oculto requerido por el controlador --}}
              <input type="hidden" name="usuario_sol" value="{{ $company->usuario_sol ?? '' }}">

              <div class="form-group full-width profile-actions module-actions">
                <a href="{{ route('portal-sunat.index') }}" class="btn-secondary">
                  <i class='bx bx-arrow-back'></i> Cancelar
                </a>
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Guardar credenciales AFPnet
                </button>
              </div>
            </form>

          </div>
        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4500);
    });
  </script>
@endpush
