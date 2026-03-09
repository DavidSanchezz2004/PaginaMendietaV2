@extends('layouts.app')

@section('title', 'Usuarios | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
  @php
    $isGlobalAdmin = (bool) ($isGlobalAdmin ?? false);
    $memberships = $memberships ?? collect();
    $globalUsers = $globalUsers ?? collect();

    $globalRows = $memberships
      ->filter(fn ($membership) => $membership->user && $membership->company)
      ->map(function ($membership) {
        return (object) [
          'user' => $membership->user,
          'company' => $membership->company,
          'role' => $membership->role,
          'status' => $membership->status,
          'created_at' => $membership->created_at,
        ];
      });

    $assignedUserIds = $globalRows->pluck('user.id')->filter()->unique()->values();

    $unassignedRows = $globalUsers
      ->whereNotIn('id', $assignedUserIds)
      ->map(function ($user) {
        return (object) [
          'user' => $user,
          'company' => null,
          'role' => null,
          'status' => 'unassigned',
          'created_at' => $user->created_at,
        ];
      });

    $rows = $isGlobalAdmin
      ? $globalRows->concat($unassignedRows)
      : $companyUsers->map(function ($companyUser) use ($activeCompany) {
          return (object) [
            'user' => $companyUser,
            'company' => $activeCompany,
            'role' => $companyUser->companyUsers->first()?->role,
            'status' => $companyUser->companyUsers->first()?->status ?? 'active',
            'created_at' => $companyUser->created_at,
          ];
        });

    $totalUsers = $rows->count();

    $companyRoleLabel = static function ($row): string {
      if (! $row->company) {
        return 'Sin asignar';
      }

      $rawRole = $row->role;
      if ($rawRole instanceof \App\Enums\RoleEnum) {
        return ucfirst($rawRole->value);
      }

      return ucfirst((string) ($rawRole ?? 'client'));
    };

    $systemRoleLabel = static function ($row): string {
      $role = $row->user?->role;
      if ($role instanceof \App\Enums\RoleEnum) {
        return ucfirst($role->value);
      }

      return ucfirst((string) $role);
    };

    if ($isGlobalAdmin) {
      $adminCount = $rows->filter(fn ($item) => $systemRoleLabel($item) === 'Admin')->count();
      $supervisorCount = $rows->filter(fn ($item) => $systemRoleLabel($item) === 'Supervisor')->count();
      $auxiliarCount = $rows->filter(fn ($item) => $systemRoleLabel($item) === 'Auxiliar')->count();
    } else {
      $adminCount = $rows->filter(fn ($item) => $companyRoleLabel($item) === 'Admin')->count();
      $supervisorCount = $rows->filter(fn ($item) => $companyRoleLabel($item) === 'Supervisor')->count();
      $auxiliarCount = $rows->filter(fn ($item) => $companyRoleLabel($item) === 'Auxiliar')->count();
    }
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
        @if (session('status'))
          <div class="placeholder-content module-alert module-flash" data-flash-message>
            <p>{{ session('status') }}</p>
            <button type="button" class="module-flash-close" aria-label="Cerrar mensaje" data-flash-close>
              <i class='bx bx-x'></i>
            </button>
          </div>
        @endif

        <div class="module-content-stack">
          <div class="module-stats-grid">
            <article class="module-stat-card">
              <span class="module-stat-label">Usuarios</span>
              <strong class="module-stat-value">{{ $totalUsers }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Admins</span>
              <strong class="module-stat-value">{{ $adminCount }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Supervisores</span>
              <strong class="module-stat-value">{{ $supervisorCount }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Auxiliares</span>
              <strong class="module-stat-value">{{ $auxiliarCount }}</strong>
            </article>
          </div>

          <div class="placeholder-content module-card-wide companies-module-card">
            <div class="module-toolbar">
              <div>
                <h1>{{ $isGlobalAdmin ? 'Gestión Global de Usuarios' : 'Gestión de Usuarios' }}</h1>
                @if($isGlobalAdmin)
                  <p style="margin-top:.35rem; color: var(--clr-text-muted);">
                    Vista administrador: usuarios activos de todas las empresas.
                  </p>
                @else
                  <p style="margin-top:.35rem; color: var(--clr-text-muted);">
                    Empresa activa: <strong>{{ $activeCompany->name }}</strong> · RUC: {{ $activeCompany->ruc }}
                  </p>
                @endif
              </div>
              <a href="{{ route('users.create') }}" class="btn-primary companies-create-btn">Invitar Usuario</a>
            </div>

            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    @if($isGlobalAdmin)
                      <th>Empresa</th>
                    @endif
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol Sistema</th>
                    <th>Rol Empresa</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    @if($isGlobalAdmin)
                      <th class="cell-action">Acciones</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @forelse($rows as $row)
                    @php
                      $pivotStatus = (string) ($row->status ?? 'active');
                      $systemRoleValue = $row->user->role instanceof \App\Enums\RoleEnum
                        ? $row->user->role->value
                        : (string) $row->user->role;
                      $isAdminRow = $systemRoleValue === \App\Enums\RoleEnum::ADMIN->value;
                    @endphp
                    <tr>
                      @if($isGlobalAdmin)
                          <td>
                            @if($row->company)
                              {{ $row->company->name }}<br><small>RUC: {{ $row->company->ruc }}</small>
                            @else
                              <span style="color: var(--clr-text-muted);">Sin asignar</span>
                            @endif
                          </td>
                      @endif
                      <td>{{ $row->user->name }}</td>
                      <td>{{ $row->user->email }}</td>
                      <td>{{ $systemRoleLabel($row) }}</td>
                      <td>{{ $companyRoleLabel($row) }}</td>
                      <td>
                        @if($pivotStatus === 'unassigned')
                          <span class="companies-status-pill is-inactive">Sin asignar</span>
                        @else
                          <span class="companies-status-pill {{ $pivotStatus === 'active' ? 'is-active' : 'is-inactive' }}">
                            {{ $pivotStatus === 'active' ? 'Activo' : 'Inactivo' }}
                          </span>
                        @endif
                      </td>
                      <td>{{ optional($row->created_at)?->format('d/m/Y') }}</td>
                      @if($isGlobalAdmin)
                        <td class="cell-action">
                          <div class="action-wrapper">
                            <a href="{{ route('users.edit', $row->user) }}" class="btn-action-icon" title="Editar usuario" aria-label="Editar usuario">
                              <i class='bx bx-pencil'></i>
                            </a>

                            @unless($isAdminRow)
                              <a href="{{ route('users.assignments.edit', $row->user) }}" class="btn-action-icon" title="Asignar empresas" aria-label="Asignar empresas">
                                <i class='bx bx-link-alt'></i>
                              </a>

                              @php $userStatus = (string) $row->user->status; @endphp
                              <form method="POST" action="{{ route('users.toggle-status', $row->user) }}" data-confirm-toggle>
                                @csrf
                                <button
                                  type="submit"
                                  class="btn-action-icon"
                                  title="{{ $userStatus === 'active' ? 'Desactivar usuario' : 'Activar usuario' }}"
                                  aria-label="{{ $userStatus === 'active' ? 'Desactivar' : 'Activar' }}"
                                  data-toggle-label="{{ $userStatus === 'active' ? 'desactivar' : 'activar' }}"
                                  style="{{ $userStatus === 'active' ? 'color:#dc2626;' : 'color:#16a34a;' }}">
                                  <i class='bx {{ $userStatus === "active" ? "bx-block" : "bx-check-circle" }}'></i>
                                </button>
                              </form>
                            @endunless
                          </div>
                        </td>
                      @endif
                    </tr>
                  @empty
                    <tr>
                      <td colspan="{{ $isGlobalAdmin ? '8' : '6' }}">
                        {{ $isGlobalAdmin ? 'No hay usuarios activos registrados en empresas.' : 'No hay usuarios asignados a esta empresa activa.' }}
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
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

    const hideFlashMessage = (flash) => {
      flash.classList.add('is-hiding');
      window.setTimeout(() => flash.remove(), 220);
    };

    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) {
        closeBtn.addEventListener('click', () => hideFlashMessage(flash));
      }

      window.setTimeout(() => {
        if (document.body.contains(flash)) {
          hideFlashMessage(flash);
        }
      }, 4000);
    });

    document.querySelectorAll('[data-confirm-toggle]').forEach((form) => {
      form.addEventListener('submit', (e) => {
        const btn = form.querySelector('button[data-toggle-label]');
        const label = btn?.dataset.toggleLabel ?? 'cambiar estado de';
        if (!confirm(`¿Estás seguro de que deseas ${label} a este usuario?`)) {
          e.preventDefault();
        }
      });
    });
  </script>
@endpush
