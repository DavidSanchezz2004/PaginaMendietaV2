@extends('layouts.app')

@section('title', 'Empresas | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .companies-page .module-content-stack { gap: 1.05rem; }
    .companies-hero {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 1.25rem;
      align-items: start;
      padding: 1.35rem 1.45rem;
      border: 1px solid rgba(15, 118, 110, .24);
      border-radius: 14px;
      background: linear-gradient(135deg, #064e43 0%, #0f766e 52%, #14b8a6 100%);
      color: #fff;
      box-shadow: 0 18px 42px rgba(15, 23, 42, .13);
      position: relative;
      overflow: hidden;
    }
    .companies-hero::after {
      content: "";
      position: absolute;
      right: -80px;
      top: -100px;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: rgba(255,255,255,.12);
    }
    .companies-hero > * { position: relative; z-index: 1; }
    .companies-hero h1 {
      margin: 0;
      display: flex;
      align-items: center;
      gap: .65rem;
      font-size: 1.75rem;
      line-height: 1.12;
    }
    .companies-hero h1 i {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,.16);
      font-size: 1.35rem;
    }
    .companies-hero p {
      margin: .55rem 0 0;
      max-width: 820px;
      color: rgba(255,255,255,.88);
      font-size: .94rem;
    }
    .companies-hero-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: .6rem;
    }
    .companies-hero-actions a {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      min-height: 42px;
      padding: .65rem .9rem;
      border-radius: 9px;
      border: 1px solid rgba(255,255,255,.28);
      background: rgba(255,255,255,.12);
      color: #fff;
      text-decoration: none;
      font-size: .83rem;
      font-weight: 900;
    }
    .companies-hero-actions a.primary {
      background: #fff;
      color: #064e43;
      border-color: #fff;
    }
    .companies-metrics {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .85rem;
    }
    .company-metric {
      display: flex;
      gap: .8rem;
      align-items: center;
      min-height: 104px;
      padding: 1rem;
      border: 1px solid #e1e8e6;
      border-radius: 13px;
      background: #fff;
      box-shadow: 0 8px 22px rgba(15,23,42,.045);
    }
    .company-metric i {
      width: 42px;
      height: 42px;
      border-radius: 11px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #e7f3ef;
      color: #0f766e;
      font-size: 1.25rem;
      flex: 0 0 auto;
    }
    .company-metric span {
      display: block;
      color: #64748b;
      font-size: .75rem;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: .04em;
    }
    .company-metric strong {
      display: block;
      margin-top: .18rem;
      color: #0f172a;
      font-size: 1.65rem;
      line-height: 1;
    }
    .companies-directory {
      border-top: 4px solid #0f766e;
      border-radius: 14px;
      padding: 1.25rem 1.35rem;
      box-shadow: 0 16px 34px rgba(15,23,42,.06);
    }
    .companies-head {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
      gap: 1rem;
      align-items: end;
      margin-bottom: 1.1rem;
    }
    .companies-head h2 {
      margin: 0;
      color: #0f172a;
      font-size: 1.25rem;
    }
    .companies-head p {
      margin: .25rem 0 0;
      color: #64748b;
      font-size: .86rem;
    }
    .form-control {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 0.55rem 1rem;
      font-size: 1rem;
      background: #f9fafb;
      color: #374151;
      box-shadow: 0 2px 8px rgba(0,0,0,0.03);
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }
    .form-control:focus {
      border-color: #1a6b57;
      box-shadow: 0 4px 12px rgba(26,107,87,0.08);
      background: #fff;
    }
    .form-control::placeholder {
      color: #9ca3af;
      font-weight: 400;
    }
    .companies-table {
      min-width: 980px;
    }
    .companies-table th {
      color: #64748b;
      font-size: .72rem;
      letter-spacing: .04em;
      text-transform: uppercase;
    }
    .companies-table td {
      vertical-align: middle;
      padding: .8rem .6rem;
    }
    .company-name-cell strong {
      display: block;
      color: #0f172a;
      font-size: .94rem;
      line-height: 1.2;
    }
    .company-name-cell span {
      display: block;
      margin-top: .15rem;
      color: #64748b;
      font-size: .76rem;
    }
    .company-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .35rem .65rem;
      border-radius: 999px;
      font-size: .76rem;
      font-weight: 900;
      white-space: nowrap;
    }
    .company-pill.ok { background: #e7f3ef; color: #0f766e; }
    .company-pill.warn { background: #fff7ed; color: #b45309; }
    .company-pill.muted { background: #f1f5f9; color: #64748b; }
    .company-actions {
      display: flex;
      justify-content: flex-end;
      gap: .45rem;
    }
    .company-actions .btn-action-icon {
      width: 36px;
      height: 36px;
      border-radius: 9px;
    }
    @media (max-width: 1100px) {
      .companies-hero,
      .companies-head { grid-template-columns: 1fr; }
      .companies-hero-actions { justify-content: flex-start; }
      .companies-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
      .companies-metrics { grid-template-columns: 1fr; }
      .companies-directory { padding: 1rem; }
    }
  </style>
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

      <main class="main-content companies-page">
        <div class="module-content-stack">
          @if (session('status'))
            <div class="placeholder-content module-alert module-flash" data-flash-message>
              <p>{{ session('status') }}</p>
              <button type="button" class="module-flash-close" aria-label="Cerrar mensaje" data-flash-close>
                <i class='bx bx-x'></i>
              </button>
            </div>
          @endif

          <section class="companies-hero">
            <div>
              <h1><i class='bx bx-buildings'></i> Empresas</h1>
              <p>Administra las empresas que atiendes desde el portal. Desde aquí revisas si están activas, si tienen facturador habilitado y si ya cuentan con credenciales SOL configuradas.</p>
            </div>
            <div class="companies-hero-actions">
              <a href="{{ route('companies.create') }}" class="primary"><i class='bx bx-plus'></i> Registrar empresa</a>
              <a href="{{ route('portal-sunat.index') }}"><i class='bx bx-shield-quarter'></i> Portal SUNAT</a>
            </div>
          </section>

          <div class="companies-metrics">
            <article class="company-metric">
              <i class='bx bx-buildings'></i>
              <div><span>Empresas</span><strong>{{ $totalCompanies }}</strong></div>
            </article>
            <article class="company-metric">
              <i class='bx bx-check-shield'></i>
              <div><span>Activas</span><strong>{{ $activeCompanies }}</strong></div>
            </article>
            <article class="company-metric">
              <i class='bx bx-pause-circle'></i>
              <div><span>Inactivas</span><strong>{{ $inactiveCompanies }}</strong></div>
            </article>
            <article class="company-metric">
              <i class='bx bx-receipt'></i>
              <div><span>Facturador ON</span><strong>{{ $facturadorEnabled }}</strong></div>
            </article>
          </div>

          <div class="placeholder-content module-card-wide companies-module-card companies-directory">
            <div class="companies-head">
              <div>
                <h2>Directorio de empresas</h2>
                <p>Busca por RUC o razón social y entra al detalle para actualizar datos, usuarios, claves SOL o configuración del facturador.</p>
              </div>
              <input type="text" id="company-search" class="form-control" placeholder="Buscar por RUC o razón social...">
            </div>

            <div class="module-table-wrap">
              <table class="module-table companies-table">
                <thead>
                  <tr>
                    <th>RUC</th>
                    <th>Empresa</th>
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
                      <td class="company-name-cell">
                        <strong>{{ $company->name }}</strong>
                        <span>{{ $company->razon_social ?? 'Sin razón social adicional' }}</span>
                      </td>
                      <td>
                        <span class="company-pill {{ $company->status === 'active' ? 'ok' : 'muted' }}">
                          <i class='bx {{ $company->status === 'active' ? 'bx-check-circle' : 'bx-pause-circle' }}'></i>
                          {{ $company->status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                      <td>
                        <span class="company-pill {{ $company->facturador_enabled ? 'ok' : 'muted' }}">
                          <i class='bx bx-receipt'></i>
                          {{ $company->facturador_enabled ? 'Habilitado' : 'Deshabilitado' }}
                        </span>
                      </td>
                      <td>
                        @if($company->hasSunatCredentials())
                          <span class="company-pill ok"><i class='bx bx-check-circle'></i> Configuradas</span>
                        @else
                          <span class="company-pill warn"><i class='bx bx-error-circle'></i> Pendiente</span>
                        @endif
                      </td>
                      <td>{{ optional($company->created_at)?->format('d/m/Y') }}</td>
                      <td class="cell-action">
                        <div class="company-actions">
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
                          @endcan
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
        event.preventDefault();
        Swal.fire({
          title: '¿Estás seguro?',
          text: '¿Seguro que deseas eliminar esta empresa? Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc2626',
          cancelButtonColor: '#6b7280',
          cancelButtonText: 'Cancelar',
          confirmButtonText: 'Sí, eliminar'
        }).then((result) => { if (result.isConfirmed) HTMLFormElement.prototype.submit.call(form); });
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
