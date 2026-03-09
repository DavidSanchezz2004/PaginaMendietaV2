@extends('layouts.app')

@section('title', 'Portal SUNAT por Empresas | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .sunat-filter-grid {
      display:grid;
      grid-template-columns:minmax(240px, 1.5fr) minmax(120px, .6fr) auto;
      gap:.85rem;
      align-items:end;
    }
    .sunat-digit-row {
      display:flex;
      gap:.45rem;
      margin-top:.35rem;
      padding:.15rem .2rem;
      background:linear-gradient(90deg,#e0e7ff 0%,#f8fafc 100%);
      border-radius:12px;
      box-shadow:0 2px 8px rgba(60,90,180,.08);
      justify-content:center;
      flex-wrap:wrap;
    }
    .digit-btn {
      background:linear-gradient(135deg,#2563eb 0%,#1e293b 100%);
      color:#fff;
      border:none;
      border-radius:999px;
      padding:.55rem 1.1rem;
      font-size:1.05rem;
      font-weight:700;
      box-shadow:0 4px 16px rgba(37,99,235,.13);
      cursor:pointer;
      transition:transform .18s cubic-bezier(.4,1.4,.6,1),box-shadow .18s;
      outline:none;
      margin-bottom:.15rem;
      position:relative;
    }
    .digit-btn.active {
      background:linear-gradient(135deg,#fbbf24 0%,#f59e42 100%);
      color:#1e293b;
      box-shadow:0 6px 24px rgba(251,191,36,.18);
      transform:scale(1.08);
    }
    .digit-btn:hover:not(.active) {
      background:linear-gradient(135deg,#38bdf8 0%,#2563eb 100%);
      color:#fff;
      transform:scale(1.04);
      box-shadow:0 6px 24px rgba(56,189,248,.18);
    }
    .digit-btn:focus {
      outline:2px solid #fbbf24;
      outline-offset:2px;
    }
    @media (max-width: 600px) {
      .sunat-digit-row {
        gap:.18rem;
        padding:.08rem .1rem;
      }
      .digit-btn {
        font-size:.92rem;
        padding:.38rem .7rem;
      }
    }
    .sunat-company-list {
      display:grid;
      gap:1rem;
      margin-top:1.25rem;
    }
    .sunat-company-card {
      border:1px solid #e2e8f0;
      border-radius:18px;
      background:#fff;
      box-shadow:0 14px 32px rgba(15,23,42,.05);
      overflow:hidden;
    }
    .sunat-company-head {
      display:flex;
      justify-content:space-between;
      gap:1rem;
      padding:1.15rem 1.2rem;
      border-bottom:1px solid #eef2f7;
    }
    .sunat-company-meta {
      display:flex;
      flex-wrap:wrap;
      gap:.55rem;
      margin-top:.7rem;
    }
    .sunat-chip {
      display:inline-flex;
      align-items:center;
      gap:.35rem;
      border-radius:999px;
      padding:.35rem .7rem;
      font-size:.78rem;
      font-weight:700;
      background:#e2e8f0;
      color:#334155;
    }
    .sunat-chip.is-ready { background:#dcfce7; color:#166534; }
    .sunat-chip.is-warn { background:#fef3c7; color:#92400e; }
    .sunat-chip.is-off { background:#fee2e2; color:#b91c1c; }
    .sunat-company-body {
      padding:1rem 1.2rem 1.2rem;
      display:grid;
      grid-template-columns:minmax(0, 1.1fr) minmax(320px, .9fr);
      gap:1.25rem;
      align-items:start;
    }
    .sunat-actions {
      display:flex;
      gap:.6rem;
      flex-wrap:wrap;
      align-items:center;
    }
    .sunat-open-button {
      background:linear-gradient(135deg, #1a3a6b 0%, #24539a 100%);
      color:#fff;
      border:0;
      border-radius:12px;
      padding:.68rem 1rem;
      min-height:44px;
      display:inline-flex;
      align-items:center;
      gap:.45rem;
      font-size:.84rem;
      font-weight:700;
      cursor:pointer;
      box-shadow:0 10px 24px rgba(26,58,107,.22);
    }
    .sunat-credentials-card {
      border:1px solid #e2e8f0;
      border-radius:16px;
      padding:1rem;
      background:#f8fafc;
    }
    @media (max-width: 980px) {
      .sunat-filter-grid,
      .sunat-company-body {
        grid-template-columns:1fr;
      }
    }
  </style>
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
        'userName' => auth()->user()?->name,
        'userEmail' => auth()->user()?->email,
      ])

      <main class="main-content">
        <div class="module-content-stack">
          @foreach(['status' => null, 'success' => null, 'error' => 'module-alert--error'] as $flashKey => $flashClass)
            @if(session($flashKey))
              <div class="placeholder-content module-alert module-flash {{ $flashClass }}" data-flash-message>
                <p>{{ session($flashKey) }}</p>
                <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
              </div>
            @endif
          @endforeach

          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <div>
                <h1>Portal SUNAT por Empresas</h1>
                <p style="margin:.35rem 0 0; color:#6b7280; font-size:.92rem;">Revisa todas las empresas asociadas a tu cuenta, filtra por nombre o por el último dígito del RUC y abre SUNAT desde aquí.</p>
              </div>
            </div>

            <form method="GET" action="{{ route('companies.sunat.index') }}" class="sunat-filter-grid">
              <div class="form-group" style="margin:0;">
                <label>Buscar por nombre</label>
                <input type="text" name="q" class="form-input" value="{{ $filters['q'] }}" placeholder="Ej. Mendieta, Zaimar, Mac Press">
              </div>

              <div class="form-group" style="margin:0;">
                <label>Filtrar por último dígito RUC</label>
                <div class="sunat-digit-row">
                  <button type="submit" name="last_digit" value="" class="digit-btn {{ $filters['last_digit'] === '' ? 'active' : '' }}">Todos</button>
                  @for($digit = 0; $digit <= 9; $digit++)
                    <button type="submit" name="last_digit" value="{{ $digit }}" class="digit-btn {{ $filters['last_digit'] === (string) $digit ? 'active' : '' }}">{{ $digit }}</button>
                  @endfor
                </div>
              </div>

              <div class="sunat-actions">
                <button type="submit" class="btn-primary"><i class='bx bx-filter-alt'></i> Filtrar</button>
                <a href="{{ route('companies.sunat.index') }}" class="btn-secondary">Limpiar</a>
              </div>
            </form>

            <div class="sunat-company-list">
              @forelse($companies as $company)
                @php
                  $isSelected = $selectedCompany?->id === $company->id;
                  $queryBase = array_filter([
                    'q' => $filters['q'] ?: null,
                    'last_digit' => $filters['last_digit'] !== '' ? $filters['last_digit'] : null,
                    'company_id' => $company->id,
                  ]);
                @endphp
                <article class="sunat-company-card">
                  <div class="sunat-company-head">
                    <div>
                      <p style="margin:0; font-size:.8rem; letter-spacing:.06em; text-transform:uppercase; color:#64748b;">RUC {{ $company->ruc }} · Último dígito {{ substr($company->ruc, -1) }}</p>
                      <h2 style="margin:.35rem 0 0; font-size:1.08rem; line-height:1.35;">{{ $company->name }}</h2>
                      <div class="sunat-company-meta">
                        <span class="sunat-chip {{ $company->isApproved() ? 'is-ready' : 'is-warn' }}"><i class='bx bx-check-shield'></i>{{ $company->isApproved() ? 'Aprobada' : 'Pendiente de revisión' }}</span>
                        <span class="sunat-chip {{ $company->canUseSunatPortal() ? 'is-ready' : 'is-off' }}"><i class='bx bx-shield-quarter'></i>{{ $company->canUseSunatPortal() ? 'Portal SUNAT disponible' : 'Empresa inactiva' }}</span>
                        <span class="sunat-chip {{ $company->hasSunatCredentials() ? 'is-ready' : 'is-warn' }}"><i class='bx bx-key'></i>{{ $company->hasSunatCredentials() ? 'Credenciales configuradas' : 'Credenciales pendientes' }}</span>
                      </div>
                    </div>

                    <div class="sunat-actions">
                      @if($company->canUseSunatPortal() && $company->hasSunatCredentials())
                        <button
                          type="button"
                          class="sunat-open-button"
                          data-sunat-url="{{ route('companies.sunat.open', $company) }}"
                          data-sunat-nombre="{{ $company->name }}"
                          title="Ingresar al portal SUNAT SOL">
                          <i class='bx bx-shield-quarter'></i>
                          <span>Abrir SUNAT</span>
                        </button>
                      @endif
                      <a href="{{ route('companies.sunat.index', $queryBase) }}" class="btn-secondary">{{ $isSelected ? 'Ocultar credenciales' : 'Gestionar credenciales' }}</a>
                    </div>
                  </div>

                  @if($isSelected)
                    <div class="sunat-company-body">
                      <div>
                        @if(! $company->canUseSunatPortal())
                          <div class="placeholder-content module-alert module-alert--error" style="margin-bottom:1rem;">
                            <p>La empresa está inactiva. Reactívala para usar el Portal SUNAT.</p>
                          </div>
                        @endif

                        <div class="sunat-credentials-card">
                          <h3 style="margin:0 0 .85rem; font-size:1rem;">Credenciales SOL</h3>
                          <form method="POST" action="{{ route('companies.sunat.credentials.update', ['company' => $company] + array_filter(['q' => $filters['q'] ?: null, 'last_digit' => $filters['last_digit'] !== '' ? $filters['last_digit'] : null])) }}" class="module-form companies-form-grid">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                              <label>Usuario SOL</label>
                              <input type="text" name="usuario_sol" class="form-input" autocomplete="off" placeholder="Ej. USUARIO_SOL" value="{{ old('usuario_sol', $company->usuario_sol ?? '') }}">
                              @error('usuario_sol')<p class="form-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="form-group">
                              <label>Clave SOL</label>
                              <input type="password" name="clave_sol" class="form-input" autocomplete="new-password" placeholder="{{ $company->clave_sol ? '(guardada — dejar en blanco para no cambiar)' : 'Ingresar clave SOL' }}">
                              @error('clave_sol')<p class="form-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="form-group full-width profile-actions module-actions">
                              <button type="submit" class="btn-primary">
                                <i class='bx bx-save'></i> Guardar credenciales
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>

                      <div class="sunat-credentials-card">
                        <h3 style="margin:0 0 .85rem; font-size:1rem;">Resumen operativo</h3>
                        <p style="margin:0 0 .55rem; color:#334155;"><strong>Estado:</strong> {{ $company->status === 'active' ? 'Activa' : 'Inactiva' }}</p>
                        <p style="margin:0 0 .55rem; color:#334155;"><strong>Revisión:</strong> {{ $company->review_status }}</p>
                        <p style="margin:0 0 .55rem; color:#334155;"><strong>Portal SUNAT:</strong> {{ $company->canUseSunatPortal() ? 'Disponible' : 'No disponible' }}</p>
                        <p style="margin:0; color:#334155;"><strong>Facturador:</strong> {{ $company->facturador_enabled ? 'Sí' : 'No' }}</p>
                      </div>
                    </div>
                  @endif
                </article>
              @empty
                <p style="color:#64748b;">No se encontraron empresas con esos filtros.</p>
              @endforelse
            </div>

            @include('partials.sunat-modal')
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
      if (closeBtn) {
        closeBtn.addEventListener('click', () => flash.remove());
      }

      window.setTimeout(() => {
        if (document.body.contains(flash)) {
          flash.remove();
        }
      }, 4000);
    });
  </script>
@endpush
