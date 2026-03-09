@extends('layouts.app')

@section('title', 'Invitar Usuario | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('content')
  @php
    $isGlobalAdmin = (bool) ($isGlobalAdmin ?? false);
  @endphp

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
          <div class="placeholder-content" style="margin-bottom: 1rem;">
            @foreach ($errors->all() as $error)
              <p>{{ $error }}</p>
            @endforeach
          </div>
        @endif

        <div class="profile-details-card" style="max-width: 820px; margin: 0 auto;">
          <h3 class="card-title">
            {{ $isGlobalAdmin ? 'Invitar Usuario (Admin Global)' : 'Invitar Usuario a '.$activeCompany->name }}
          </h3>

          @if($isGlobalAdmin)
          <div class="module-alert" style="color: #0369a1; background-color: #e0f2fe; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.95rem;">
            <strong><i class='bx bx-info-circle'></i> Información:</strong> En este paso solicitarás los datos básicos del usuario. Al hacer clic en "Guardar Usuario", el sistema te redirigirá a la pantalla donde podrás elegir a qué empresas tendrá acceso.
          </div>
          @endif

          <form class="form-grid is-editing" method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="form-group">
              <label>Nombres y Apellidos</label>
              <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
              <label>Correo Electrónico</label>
              <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
              <label>{{ $isGlobalAdmin ? 'Rol de Sistema' : 'Rol en Empresa' }}</label>
              <select name="role" class="form-input" required>
                @foreach($roles as $role)
                  <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ ucfirst($role->value) }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label>Contraseña Temporal</label>
              <input type="password" name="password" class="form-input" required>
            </div>

            <div class="form-group full-width profile-actions" style="margin-top: 0.5rem;">
              <a href="{{ route('users.index') }}" class="btn-secondary" style="text-align:center; text-decoration:none;">Cancelar</a>
              <button type="submit" class="btn-primary">
                <i class='bx bx-save'></i> Guardar Usuario
              </button>
            </div>
          </form>
        </div>
      </main>
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
