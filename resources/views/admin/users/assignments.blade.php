@extends('layouts.app')

@section('title', 'Asignar Empresas | Portal Mendieta')

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
                <h1>Asignar Empresas a Usuario</h1>
                <p style="margin-top:.35rem; color: var(--clr-text-muted);">
                  Usuario: <strong>{{ $managedUser->name }}</strong> · {{ $managedUser->email }}
                </p>
              </div>
            </div>

            <form method="POST" action="{{ route('users.assignments.update', $managedUser) }}" class="module-form">
              @csrf
              @method('PATCH')

              <div class="module-table-wrap">
                <table class="module-table">
                  <thead>
                    <tr>
                      <th>Asignar</th>
                      <th>Empresa</th>
                      <th>RUC</th>
                      <th>Rol Empresa</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($companies as $company)
                      @php
                        $assignment = $currentAssignments[$company->id] ?? null;
                        $checked = in_array($company->id, old('company_ids', array_keys($currentAssignments)), true);
                        $roleValue = old('roles.'.$company->id, $assignment['role'] ?? 'client');
                        $statusValue = old('statuses.'.$company->id, $assignment['status'] ?? 'active');
                      @endphp
                      <tr>
                        <td>
                          <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" @checked($checked)>
                        </td>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->ruc }}</td>
                        <td>
                          <select name="roles[{{ $company->id }}]" class="form-input">
                            @foreach($roles as $role)
                              <option value="{{ $role->value }}" @selected($roleValue === $role->value)>{{ ucfirst($role->value) }}</option>
                            @endforeach
                          </select>
                        </td>
                        <td>
                          <select name="statuses[{{ $company->id }}]" class="form-input">
                            <option value="active" @selected($statusValue === 'active')>Activo</option>
                            <option value="inactive" @selected($statusValue === 'inactive')>Inactivo</option>
                          </select>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <div class="module-actions" style="display:flex; justify-content:flex-end; gap:.75rem;">
                <a href="{{ route('users.index') }}" class="btn-secondary companies-btn-link">Cancelar</a>
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Guardar Asignaciones
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
      const dark = theme === 'dark';
      document.body.classList.toggle('dark-mode', dark);
      if (themeIcon) {
        themeIcon.classList.toggle('bx-moon', !dark);
        themeIcon.classList.toggle('bx-sun', dark);
      }
    };

    const savedTheme = localStorage.getItem('mendieta-theme') || 'light';
    applyTheme(savedTheme);

    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', () => {
        const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
        localStorage.setItem('mendieta-theme', nextTheme);
        applyTheme(nextTheme);
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
