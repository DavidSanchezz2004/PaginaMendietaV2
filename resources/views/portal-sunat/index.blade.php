@extends('layouts.app')

@section('title', 'Portal SUNAT | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    /* ── Filtros ──────────────────────────────────────────────────────────── */
    .ps-filter-wrap {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      align-items: flex-end;
      margin-bottom: 2rem;
      padding: 1.25rem;
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
    }
    .ps-filter-name {
      flex: 1 1 300px;
    }
    .ps-filter-name label,
    .ps-digit-label {
      display: block;
      font-size: .75rem;
      font-weight: 700;
      color: #64748b;
      margin-bottom: .6rem;
      text-transform: uppercase;
      letter-spacing: .06em;
    }
    .ps-search-group {
      display: flex;
      gap: .5rem;
    }
    .ps-search-group .form-input {
      flex: 1;
      padding: .5rem .75rem;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: .9rem;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .ps-search-group .form-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .ps-digit-section {
      flex: 1 1 auto;
    }
    .ps-digit-row {
      display: flex;
      gap: .35rem;
      flex-wrap: wrap;
    }
    .digit-btn {
      background: #ffffff;
      color: #475569;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: .4rem .75rem;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all .15s ease;
      outline: none;
    }
    .digit-btn.active {
      background: #0f172a;
      color: #ffffff;
      border-color: #0f172a;
      box-shadow: 0 2px 4px rgba(15,23,42,.15);
    }
    .digit-btn:hover:not(.active) {
      background: #f1f5f9;
      border-color: #94a3b8;
      color: #1e293b;
    }
    .digit-btn:focus { 
      outline: 2px solid #3b82f6; 
      outline-offset: 1px; 
    }
    .ps-btn-search {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #0f172a;
      color: #fff;
      border: 1px solid #0f172a;
      border-radius: 6px;
      padding: .5rem 1rem;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .15s;
      white-space: nowrap;
    }
    .ps-btn-search:hover {
      background: #1e293b;
    }
    .ps-btn-clear {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #fff;
      color: #64748b;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: .5rem 1rem;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all .15s;
      white-space: nowrap;
      text-decoration: none;
    }
    .ps-btn-clear:hover {
      background: #f1f5f9;
      color: #475569;
      border-color: #94a3b8;
    }

    /* ── Tabla ────────────────────────────────────────────────────────────── */
    .ps-table-wrap { overflow-x: auto; margin-top: .5rem; }
    .ps-table {
      width: 100%;
      border-collapse: collapse;
      font-size: .9rem;
    }
    .ps-table th {
      padding: .65rem 1rem;
      text-align: left;
      font-size: .75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #64748b;
      border-bottom: 2px solid #e2e8f0;
      white-space: nowrap;
    }
    .ps-table td {
      padding: .75rem 1rem;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
    }
    .ps-table tr:last-child td { border-bottom: none; }
    .ps-table tr:hover td { background: #f8fafc; }

    /* ── Chips / badges ───────────────────────────────────────────────────── */
    .ps-chip {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      border-radius: 999px;
      padding: .28rem .65rem;
      font-size: .75rem;
      font-weight: 700;
    }
    .ps-chip.ok   { background: #dcfce7; color: #166534; }
    .ps-chip.warn { background: #fef3c7; color: #92400e; }
    .ps-chip.off  { background: #fee2e2; color: #b91c1c; }

    /* ── Acciones ─────────────────────────────────────────────────────────── */
    .ps-actions { display: flex; gap: .45rem; flex-wrap: wrap; align-items: center; }
    .ps-btn-sunat {
      display: inline-flex;
      align-items: center;
      gap: .38rem;
      background: linear-gradient(135deg, #1a3a6b 0%, #24539a 100%);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: .5rem .85rem;
      font-size: .82rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 6px 18px rgba(26,58,107,.2);
      transition: transform .15s, box-shadow .15s;
      white-space: nowrap;
    }
    .ps-btn-sunat:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(26,58,107,.25); }

    /* Botón Declaración y Pago — verde oscuro para diferenciarlo */
    .ps-btn-declaracion {
      display: inline-flex;
      align-items: center;
      gap: .38rem;
      background: linear-gradient(135deg, #14532d 0%, #16a34a 100%);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: .5rem .85rem;
      font-size: .82rem;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 6px 18px rgba(20,83,45,.2);
      transition: transform .15s, box-shadow .15s;
      white-space: nowrap;
    }
    .ps-btn-declaracion:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(20,83,45,.25); }

    .ps-btn-cred {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #fff;
      color: #1e293b;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      padding: .5rem .85rem;
      font-size: .82rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: border-color .15s, background .15s;
      white-space: nowrap;
    }
    .ps-btn-cred:hover { border-color: #2563eb; background: #eff6ff; color: #2563eb; }

    /* ── Navegación Horizontal (Enlaces Rápidos) ───────────────────────── */
    .ps-quick-nav {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1rem 1.5rem;
    }
    .ps-nav-group {
      display: flex;
      align-items: center;
      gap: .5rem;
      flex-wrap: wrap;
    }
    .ps-nav-title {
      font-size: .75rem;
      font-weight: 700;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-right: .25rem;
    }
    .ps-nav-item {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .45rem .85rem;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      color: #475569;
      text-decoration: none;
      font-size: .8rem;
      font-weight: 600;
      transition: all .2s ease;
      white-space: nowrap;
    }
    .ps-nav-item:hover {
      background: #eff6ff;
      border-color: #bfdbfe;
      color: #1d4ed8;
      transform: translateY(-1px);
      box-shadow: 0 4px 6px -1px rgba(37,99,235,0.08);
    }
    .ps-nav-ico {
      font-size: 1.1rem;
      color: #64748b;
    }
    .ps-nav-item:hover .ps-nav-ico {
      color: #2563eb;
    }
    .ps-nav-divider {
      width: 1px;
      height: 24px;
      background: #e2e8f0;
    }

    /* ── Widget Cronograma ──────────────────────────────────────────────── */
    .ps-crono-widget {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      flex-wrap: wrap;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-left: 4px solid #0f172a;
      border-radius: 10px;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
    }
    .ps-crono-widget-icon { font-size: 2rem; color: #0f172a; flex-shrink: 0; }
    .ps-crono-widget-info { flex: 1 1 160px; }
    .ps-crono-widget-title {
      display: block;
      font-size: .72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #64748b;
      margin-bottom: .2rem;
    }
    .ps-crono-widget-period { font-size: 1rem; font-weight: 700; color: #0f172a; }
    .ps-crono-stats-row { display: flex; gap: .65rem; flex-wrap: wrap; align-items: center; }
    .ps-crono-stat {
      display: flex;
      flex-direction: column;
      align-items: center;
      min-width: 62px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: .4rem .7rem;
    }
    .ps-crono-stat .num { font-size: 1.3rem; font-weight: 800; line-height: 1; }
    .ps-crono-stat .lbl { font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-top: .15rem; }
    .ps-crono-stat.declared .num { color: #166534; }
    .ps-crono-stat.pending  .num { color: #92400e; }
    .ps-crono-stat.total    .num { color: #0f172a; }
    .ps-crono-progress { flex: 1 1 130px; display: flex; flex-direction: column; gap: .3rem; }
    .ps-crono-progress-bar { height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
    .ps-crono-progress-fill { height: 100%; background: #16a34a; border-radius: 999px; transition: width .4s; }
    .ps-crono-progress-label { font-size: .72rem; color: #64748b; font-weight: 600; }
    .ps-crono-widget-link {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #0f172a;
      color: #fff;
      border-radius: 8px;
      padding: .5rem 1rem;
      font-size: .82rem;
      font-weight: 700;
      text-decoration: none;
      white-space: nowrap;
      flex-shrink: 0;
      transition: background .15s;
    }
    .ps-crono-widget-link:hover { background: #1e293b; color: #fff; }
    body.dark-mode .ps-crono-widget { background: var(--clr-bg-card, #1e293b); border-color: var(--clr-border-light, #334155); border-left-color: #3b82f6; }
    body.dark-mode .ps-crono-widget-icon { color: #60a5fa; }
    body.dark-mode .ps-crono-widget-period { color: var(--clr-text-main, #f8fafc); }
    body.dark-mode .ps-crono-stat { background: var(--clr-bg-body, #0f172a); border-color: var(--clr-border-light, #334155); }
    body.dark-mode .ps-crono-stat.declared .num { color: #4ade80; }
    body.dark-mode .ps-crono-stat.pending  .num { color: #fbbf24; }
    body.dark-mode .ps-crono-stat.total    .num { color: #f8fafc; }
    body.dark-mode .ps-crono-progress-bar { background: #334155; }
    body.dark-mode .ps-crono-widget-link { background: #3b82f6; }
    body.dark-mode .ps-crono-widget-link:hover { background: #2563eb; }

    /* ── Dark Mode Enhancements ── */
    body.dark-mode .ps-quick-nav { background: var(--clr-bg-card, #1e293b); border-color: var(--clr-border-light, #334155); }
    body.dark-mode .ps-nav-title { color: #64748b; }
    body.dark-mode .ps-nav-item { background: var(--clr-bg-body, #0f172a); border-color: var(--clr-border-light, #334155); color: #cbd5e1; }
    body.dark-mode .ps-nav-item:hover { background: var(--clr-hover-bg, #1e293b); border-color: #475569; color: #f8fafc; }
    body.dark-mode .ps-nav-item:hover .ps-nav-ico { color: #60a5fa; }
    body.dark-mode .ps-nav-divider { background: var(--clr-border-light, #334155); }
    body.dark-mode .ps-filter-wrap { background-color: var(--clr-bg-card, #1e293b); border-color: var(--clr-border-light, #334155); }
    body.dark-mode .ps-filter-name label, body.dark-mode .ps-digit-label { color: #94a3b8; }
    body.dark-mode .ps-search-group .form-input { background: var(--clr-bg-body, #0f172a); border-color: var(--clr-border-light, #334155); color: var(--clr-text-main, #f8fafc); }
    body.dark-mode .ps-search-group .form-input::placeholder { color: #475569; }
    body.dark-mode .ps-search-group .form-input:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.25); }
    body.dark-mode .digit-btn { background: var(--clr-bg-body, #0f172a); color: #cbd5e1; border-color: var(--clr-border-light, #334155); }
    body.dark-mode .digit-btn.active { background: #f8fafc; color: #0f172a; border-color: #f8fafc; }
    body.dark-mode .digit-btn:hover:not(.active) { background: var(--clr-hover-bg, #1e293b); color: #f8fafc; border-color: #475569; }
    body.dark-mode .ps-btn-search { background: #f8fafc; color: #0f172a; border-color: #f8fafc; }
    body.dark-mode .ps-btn-search:hover { background: #e2e8f0; }
    body.dark-mode .ps-btn-clear { background: transparent; color: #cbd5e1; border-color: var(--clr-border-light, #475569); }
    body.dark-mode .ps-btn-clear:hover { background: var(--clr-hover-bg, #1e293b); color: #fff; }
    body.dark-mode .ps-table th { color: #94a3b8; border-bottom-color: var(--clr-border-light, #334155); }
    body.dark-mode .ps-table td { border-bottom-color: var(--clr-border-light, #334155); }
    body.dark-mode .ps-table tr:hover td { background: var(--clr-hover-bg, #1e293b); }
    .ps-company-name { font-weight: 600; color: #0f172a; }
    body.dark-mode .ps-company-name { color: var(--clr-text-main, #f8fafc); }
    .ps-company-ruc { font-family: monospace; color: #475569; font-size: .88rem; }
    body.dark-mode .ps-company-ruc { color: #94a3b8; }
    .ps-subtitle { margin: .35rem 0 0; color: #6b7280; font-size: .92rem; }
    body.dark-mode .ps-subtitle { color: #9ca3af; }
    body.dark-mode .ps-chip.ok { background: rgba(22, 101, 52, 0.2); color: #4ade80; }
    body.dark-mode .ps-chip.warn { background: rgba(146, 64, 14, 0.2); color: #fbbf24; }
    body.dark-mode .ps-chip.off { background: rgba(185, 28, 28, 0.2); color: #f87171; }
    body.dark-mode .ps-btn-sunat { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); box-shadow: 0 6px 18px rgba(37,99,235,.2); }
    body.dark-mode .ps-btn-declaracion { background: linear-gradient(135deg, #15803d 0%, #16a34a 100%); box-shadow: 0 6px 18px rgba(21,128,61,.2); }
    body.dark-mode .ps-btn-cred { background: var(--clr-bg-body, #0f172a); color: #e2e8f0; border-color: #475569; }
    body.dark-mode .ps-btn-cred:hover { background: rgba(37, 99, 235, 0.1); border-color: #3b82f6; color: #60a5fa; }
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
        'userName'    => auth()->user()?->name,
        'userEmail'   => auth()->user()?->email,
      ])

      <main class="main-content">
        <div class="module-content-stack">

          {{-- Flash messages --}}
          @foreach(['success' => null, 'status' => null, 'error' => 'module-alert--error'] as $fk => $fc)
            @if(session($fk))
              <div class="placeholder-content module-alert module-flash {{ $fc }}" data-flash-message>
                <p>{{ session($fk) }}</p>
                <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close>
                  <i class='bx bx-x'></i>
                </button>
              </div>
            @endif
          @endforeach

          {{-- ── Contenido Principal ─────────────────────────────────── --}}
          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <div>
                <h1>Portal SUNAT</h1>
                <p class="ps-subtitle">
                  Accede a SUNAT SOL y Declaración y Pago directamente desde aquí. Configura las credenciales de cada empresa y abre la sesión con un clic.
                </p>
              </div>
            </div>

            {{-- ── Resumen Cronograma ───────────────────────────────────── --}}
            @if(isset($cronogramaStats) && $cronogramaStats)
              @php
                $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                              'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
              @endphp
              <div class="ps-crono-widget">
                <i class='bx bx-calendar-check ps-crono-widget-icon'></i>
                <div class="ps-crono-widget-info">
                  <span class="ps-crono-widget-title">Cronograma de declaraciones</span>
                  <span class="ps-crono-widget-period">Período: {{ $meses[$cronogramaStats['month']] }} {{ $cronogramaStats['year'] }}</span>
                </div>
                <div class="ps-crono-stats-row">
                  <div class="ps-crono-stat total">
                    <span class="num">{{ $cronogramaStats['total'] }}</span>
                    <span class="lbl">Total</span>
                  </div>
                  <div class="ps-crono-stat declared">
                    <span class="num">{{ $cronogramaStats['declared'] }}</span>
                    <span class="lbl">Declaradas</span>
                  </div>
                  <div class="ps-crono-stat pending">
                    <span class="num">{{ $cronogramaStats['pending'] }}</span>
                    <span class="lbl">Pendientes</span>
                  </div>
                </div>
                <div class="ps-crono-progress">
                  <div class="ps-crono-progress-bar">
                    <div class="ps-crono-progress-fill" style="width: {{ $cronogramaStats['pct'] }}%"></div>
                  </div>
                  <span class="ps-crono-progress-label">{{ $cronogramaStats['pct'] }}% completado</span>
                </div>
                <a href="{{ route('obligaciones.cronograma.index', ['year' => $cronogramaStats['year'], 'month' => $cronogramaStats['month']]) }}" class="ps-crono-widget-link">
                  Ver cronograma <i class='bx bx-right-arrow-alt'></i>
                </a>
              </div>
            @endif

            {{-- ── Filtros ─────────────────────────────────────────────── --}}
            <form method="GET" action="{{ route('portal-sunat.index') }}" class="ps-filter-wrap">
              <div class="ps-filter-name">
                <label>Buscar empresa</label>
                <div class="ps-search-group">
                  <input type="text" name="q" class="form-input" value="{{ $filters['q'] }}"
                    placeholder="Nombre de la empresa…">
                  <button type="submit" class="ps-btn-search">
                    <i class='bx bx-search'></i> Buscar
                  </button>
                  @if($filters['q'] || $filters['last_digit'] !== '')
                    <a href="{{ route('portal-sunat.index') }}" class="ps-btn-clear">
                      <i class='bx bx-eraser'></i> Limpiar
                    </a>
                  @endif
                </div>
              </div>

              <div class="ps-digit-section">
                <span class="ps-digit-label">Filtrar por último dígito RUC</span>
                <div class="ps-digit-row" style="margin-bottom:.5rem;">
                  <button type="submit" name="last_digit" value=""
                    class="digit-btn {{ $filters['last_digit'] === '' ? 'active' : '' }}">
                    Todos
                  </button>
                  @for($d = 0; $d <= 9; $d++)
                    <button type="submit" name="last_digit" value="{{ $d }}"
                      class="digit-btn {{ $filters['last_digit'] === (string) $d ? 'active' : '' }}">
                      {{ $d }}
                    </button>
                  @endfor
                </div>

                @php $currentView = $filters['view'] ?? 'active'; @endphp
                <span class="ps-digit-label">Vista de empresas</span>
                <div class="ps-digit-row">
                  <button type="submit" name="view" value="active"
                    class="digit-btn {{ $currentView === 'active' ? 'active' : '' }}">
                    Activas
                  </button>
                  <button type="submit" name="view" value="archived"
                    class="digit-btn {{ $currentView === 'archived' ? 'active' : '' }}">
                    Archivadas
                  </button>
                </div>
              </div>
            </form>

            {{-- ── Tabla de empresas ───────────────────────────────────── --}}
            <div class="ps-table-wrap">
              <table class="ps-table">
                <thead>
                  <tr>
                    <th>Empresa</th>
                    <th>RUC</th>
                    <th>Estado</th>
                    <th>Credenciales SOL</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($companies as $company)
                    <tr>
                      <td class="ps-company-name">{{ $company->name }}</td>
                      <td class="ps-company-ruc">{{ $company->ruc }}</td>
                      <td>
                        <span class="ps-chip {{ $company->canUseSunatPortal() ? 'ok' : 'off' }}">
                          <i class='bx {{ $company->canUseSunatPortal() ? "bx-check-circle" : "bx-x-circle" }}'></i>
                          {{ $company->canUseSunatPortal() ? 'Activa' : 'Inactiva' }}
                        </span>
                      </td>
                      <td>
                        <span class="ps-chip {{ $company->hasSunatCredentials() ? 'ok' : 'warn' }}">
                          <i class='bx {{ $company->hasSunatCredentials() ? "bx-key" : "bx-error-circle" }}'></i>
                          {{ $company->hasSunatCredentials() ? 'Configuradas' : 'Pendiente' }}
                        </span>
                      </td>
                      <td>
                        <div class="ps-actions">
                          @if($company->canUseSunatPortal() && $company->hasSunatCredentials())

                            {{-- Botón Menú SOL --}}
                            <button type="button"
                              class="ps-btn-sunat"
                              data-sunat-url="{{ route('portal-sunat.open', $company) }}"
                              data-sunat-portal="sunat"
                              data-sunat-nombre="{{ $company->name }}"
                              title="Abrir Menú SOL">
                              <i class='bx bx-shield-quarter'></i> Menú SOL
                            </button>

                            {{-- Botón Declaración y Pago --}}
                            <button type="button"
                              class="ps-btn-declaracion"
                              data-sunat-url="{{ route('portal-sunat.open', $company) }}?portal=declaracion"
                              data-sunat-portal="declaracion"
                              data-sunat-nombre="{{ $company->name }}"
                              data-sunat-ruc="{{ $company->ruc }}"
                              data-sunat-usuario="{{ $company->usuario_sol }}"
                              data-sunat-clave="{{ $company->clave_sol }}"
                              title="Abrir Declaración y Pago">
                              <i class='bx bx-receipt'></i> Declaración y Pago
                            </button>

                          @endif

                          {{-- Editar credenciales (NO auxiliar) --}}
                          @can('updateSunatCredentials', $company)
                            <a href="{{ route('portal-sunat.credentials', $company) }}" class="ps-btn-cred">
                              <i class='bx bx-edit-alt'></i>
                              {{ $company->hasSunatCredentials() ? 'Editar credenciales' : 'Configurar' }}
                            </a>
                          @endcan

                          @php
                            $authUser = auth()->user();
                            $authRole = $authUser?->role?->value ?? '';
                            $hiddenIds = ($hiddenCompanyIds ?? collect());
                            $isHidden  = $hiddenIds->contains($company->id);
                          @endphp
                          @if(in_array($authRole, ['admin', 'supervisor'], true))
                            <form method="POST" action="{{ $isHidden
                                  ? route('portal-sunat.unhide', $company)
                                  : route('portal-sunat.hide', $company) }}">
                              @csrf
                              <button type="submit" class="ps-btn-cred" style="border-style:dashed;">
                                <i class='bx {{ $isHidden ? "bx-show" : "bx-low-vision" }}'></i>
                                {{ $isHidden ? 'Mostrar en mi lista' : 'Ocultar para mí' }}
                              </button>
                            </form>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" style="text-align:center; color:#94a3b8; padding:2rem;">
                        <i class='bx bx-buildings' style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        No se encontraron empresas con esos filtros.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <p style="margin-top:1rem; font-size:.8rem; color:#94a3b8;">
              {{ $companies->count() }} empresa(s) encontrada(s).
            </p>
          </div>
          {{-- Fin Contenido Principal --}}

        </div>
      </main>
    </section>
  </div>

  @include('partials.sunat-modal')

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