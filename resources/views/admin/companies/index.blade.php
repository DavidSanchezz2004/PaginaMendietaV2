@extends('layouts.app')

@section('title', 'Empresas | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
  @php
    $totalCompanies = $companies->count();
    $activeCompanies = $companies->where('status', 'active')->count();
    $inactiveCompanies = $companies->where('status', 'inactive')->count();
    $facturadorEnabled = $companies->where('facturador_enabled', true)->count();
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
        <div class="module-content-stack">
          @if (session('status'))
            <div class="placeholder-content module-alert module-flash" data-flash-message>
              <p>{{ session('status') }}</p>
              <button type="button" class="module-flash-close" aria-label="Cerrar mensaje" data-flash-close>
                <i class='bx bx-x'></i>
              </button>
            </div>
          @endif

          <div class="module-stats-grid">
            <article class="module-stat-card">
              <span class="module-stat-label">Empresas</span>
              <strong class="module-stat-value">{{ $totalCompanies }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Activas</span>
              <strong class="module-stat-value">{{ $activeCompanies }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Inactivas</span>
              <strong class="module-stat-value">{{ $inactiveCompanies }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Facturador ON</span>
              <strong class="module-stat-value">{{ $facturadorEnabled }}</strong>
            </article>
          </div>

          <div class="placeholder-content module-card-wide companies-module-card">
            <div class="module-toolbar">
              <h1>Empresas</h1>
              <a href="{{ route('companies.create') }}" class="btn-primary companies-create-btn">Registrar Empresa</a>
            </div>

            <div class="module-toolbar" style="margin-bottom: 1rem; display: flex; justify-content: flex-end;">
              <input type="text" id="company-search" class="form-control" placeholder="Buscar por RUC o Razón Social..." style="max-width: 320px;">
            </div>

            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>RUC</th>
                    <th>Razón Social</th>
                    <th>Estado</th>
                    <th>Facturador</th>
                    <th>Claves SOL</th>
                    <th>Registro</th>
                    <th class="cell-action">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($companies as $company)
                    <tr>
                      <td>{{ $company->ruc }}</td>
                      <td>{{ $company->name }}</td>
                      <td>
                        <span class="companies-status-pill {{ $company->status === 'active' ? 'is-active' : 'is-inactive' }}">
                          {{ $company->status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                      <td>{{ $company->facturador_enabled ? 'Habilitado' : 'Deshabilitado' }}</td>
                      <td style="text-align: center;">
                        @if($company->hasSunatCredentials())
                          <i class='bx bx-check-circle' style="color: #4ade80; font-size: 1.3em;" title="Claves SOL configuradas"></i>
                        @else
                          <i class='bx bx-x-circle' style="color: #a1a1aa; font-size: 1.3em;" title="Sin claves SOL"></i>
                        @endif
                      </td>
                      <td>{{ optional($company->created_at)?->format('d/m/Y') }}</td>
                      <td class="cell-action">
                        <div class="action-wrapper">
                          @can('update', $company)
                          <a href="{{ route('companies.edit', $company) }}" class="btn-action-icon" title="Editar empresa" aria-label="Editar empresa">
                            <i class='bx bx-pencil'></i>
                          </a>
                          @endcan

                          @can('delete', $company)
                          <form method="POST" action="{{ route('companies.destroy.post', $company) }}" data-company-delete-form>
                            @csrf
                            <button type="submit" class="btn-action-icon" title="Eliminar empresa" aria-label="Eliminar empresa">
                              <i class='bx bx-trash'></i>
                            </button>
                          </form>
                          @endcan>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7">No se encontraron empresas registradas.</td>
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

    document.querySelectorAll('[data-company-delete-form]').forEach((form) => {
      form.addEventListener('submit', (event) => {
        const ok = confirm('¿Seguro que deseas eliminar esta empresa? Esta acción no se puede deshacer.');
        if (!ok) {
          event.preventDefault();
        }
      });
    });

    // Filtro de empresas por RUC o Razón Social
    document.getElementById('company-search').addEventListener('input', function() {
      const search = this.value.toLowerCase();
      document.querySelectorAll('.module-table tbody tr').forEach(function(row) {
        // Si es la fila de "no se encontraron empresas", no la ocultes
        if (row.children.length === 1) return;
        const ruc = row.children[0].textContent.toLowerCase();
        const razon = row.children[1].textContent.toLowerCase();
        if (ruc.includes(search) || razon.includes(search)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

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
  </script>
@endpush
