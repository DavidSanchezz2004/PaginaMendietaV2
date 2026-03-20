@extends('layouts.app')

@section('title', 'Buzón SOL | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    /* ── Filtros ──────────────────────────────────────────────────────── */
    .bs-filter-wrap {
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
    .bs-filter-name { flex: 1 1 300px; }
    .bs-filter-name label {
      display: block;
      font-size: .75rem;
      font-weight: 700;
      color: #64748b;
      margin-bottom: .6rem;
      text-transform: uppercase;
      letter-spacing: .06em;
    }
    .bs-search-group { display: flex; gap: .5rem; }
    .bs-search-group .form-input {
      flex: 1;
      padding: .5rem .75rem;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: .9rem;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .bs-search-group .form-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .bs-btn-search {
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
    .bs-btn-search:hover { background: #1e293b; }
    .bs-btn-clear {
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
    .bs-btn-clear:hover { background: #f1f5f9; color: #475569; border-color: #94a3b8; }

    /* ── Tabla de empresas ────────────────────────────────────────────── */
    .bs-table-wrap { overflow-x: auto; margin-top: .5rem; }
    .bs-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    .bs-table th {
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
    .bs-table td {
      padding: .75rem 1rem;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
    }
    .bs-table tr:last-child td { border-bottom: none; }
    .bs-table tr:hover td { background: #f8fafc; }
    .bs-company-name { font-weight: 600; color: #0f172a; }
    .bs-company-ruc  { font-family: monospace; color: #475569; font-size: .88rem; }
    .bs-subtitle     { margin: .35rem 0 0; color: #6b7280; font-size: .92rem; }

    /* ── Chips ────────────────────────────────────────────────────────── */
    .bs-chip {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      border-radius: 999px;
      padding: .28rem .65rem;
      font-size: .75rem;
      font-weight: 700;
    }
    .bs-chip.ok   { background: #dcfce7; color: #166534; }
    .bs-chip.warn { background: #fef3c7; color: #92400e; }
    .bs-chip.off  { background: #fee2e2; color: #b91c1c; }

    /* ── Botón Buscar Buzón ───────────────────────────────────────────── */
    .bs-btn-buzon {
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
    .bs-btn-buzon:hover   { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(26,58,107,.25); }
    .bs-btn-buzon:disabled { opacity: .6; cursor: wait; transform: none !important; }

    /* ── Panel de mensajes ────────────────────────────────────────────── */
    .bs-panel {
      margin-top: 2rem;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      overflow: hidden;
    }
    .bs-panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: .75rem;
      padding: 1rem 1.5rem;
      background: #0f172a;
      color: #fff;
    }
    .bs-panel-title {
      font-size: 1rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .bs-panel-counter {
      background: rgba(255,255,255,.2);
      border-radius: 999px;
      padding: .2rem .65rem;
      font-size: .78rem;
      font-weight: 700;
    }
    .bs-tabs {
      display: flex;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
    }
    .bs-tab {
      padding: .75rem 1.5rem;
      font-size: .85rem;
      font-weight: 600;
      color: #64748b;
      cursor: pointer;
      border: none;
      border-bottom: 3px solid transparent;
      background: transparent;
      transition: all .15s;
      display: inline-flex;
      align-items: center;
      gap: .4rem;
    }
    .bs-tab:hover  { color: #0f172a; }
    .bs-tab.active { color: #1d4ed8; border-bottom-color: #1d4ed8; background: #fff; }

    /* ── Tabla de mensajes ────────────────────────────────────────────── */
    .bs-msg-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
    .bs-msg-table th {
      padding: .6rem 1rem;
      text-align: left;
      font-size: .72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #64748b;
      border-bottom: 2px solid #e2e8f0;
      white-space: nowrap;
    }
    .bs-msg-table td {
      padding: .7rem 1rem;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
    }
    .bs-msg-table tr:last-child td { border-bottom: none; }
    .bs-msg-table tr:hover td      { background: #f0f9ff; cursor: pointer; }
    .bs-msg-unread td              { font-weight: 700; }
    .bs-msg-unread td:first-child::before {
      content: '';
      display: inline-block;
      width: 7px;
      height: 7px;
      background: #2563eb;
      border-radius: 50%;
      margin-right: 6px;
      vertical-align: middle;
    }

    /* ── Etiquetas codEtiqueta ────────────────────────────────────────── */
    .bs-etiq {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      padding: .2rem .6rem;
      font-size: .72rem;
      font-weight: 700;
    }
    .bs-etiq.red    { background: #fee2e2; color: #b91c1c; }
    .bs-etiq.yellow { background: #fef3c7; color: #92400e; }
    .bs-etiq.gray   { background: #f1f5f9; color: #475569; }

    /* ── Modal detalle ────────────────────────────────────────────────── */
    .bs-modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.55);
      z-index: 1050;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .bs-modal-overlay.open { display: flex; }
    .bs-modal {
      background: #fff;
      border-radius: 14px;
      width: 100%;
      max-width: 680px;
      box-shadow: 0 25px 60px rgba(0,0,0,.25);
      overflow: hidden;
    }
    .bs-modal-header {
      background: #1e3a5f;
      color: #fff;
      padding: 1.1rem 1.5rem;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
    }
    .bs-modal-header h3 { margin: 0; font-size: .95rem; font-weight: 700; line-height: 1.4; }
    .bs-modal-close {
      background: rgba(255,255,255,.15);
      border: none;
      color: #fff;
      border-radius: 6px;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      font-size: 1.2rem;
    }
    .bs-modal-body { padding: 1.5rem; }
    .bs-detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .75rem 1.5rem;
    }
    .bs-detail-item label {
      display: block;
      font-size: .72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #64748b;
      margin-bottom: .25rem;
    }
    .bs-detail-item span { font-size: .9rem; color: #0f172a; font-weight: 500; }
    .bs-modal-footer {
      padding: 1rem 1.5rem;
      border-top: 1px solid #e2e8f0;
      display: flex;
      gap: .75rem;
      justify-content: flex-end;
      flex-wrap: wrap;
    }
    .bs-btn-doc {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #1d4ed8;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: .55rem 1.1rem;
      font-size: .85rem;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      transition: background .15s;
    }
    .bs-btn-doc:hover { background: #1e40af; color: #fff; }
    .bs-btn-close-modal {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #fff;
      color: #475569;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      padding: .55rem 1.1rem;
      font-size: .85rem;
      font-weight: 600;
      cursor: pointer;
    }
    .bs-btn-close-modal:hover { background: #f1f5f9; }

    /* ── Spinner / estados ────────────────────────────────────────────── */
    .bs-spinner {
      display: inline-block;
      width: 1em;
      height: 1em;
      border: 2px solid rgba(255,255,255,.4);
      border-top-color: #fff;
      border-radius: 50%;
      animation: bsSpin .6s linear infinite;
      vertical-align: middle;
    }
    .bs-spinner-dark { border-color: rgba(15,23,42,.15); border-top-color: #0f172a; }
    @keyframes bsSpin { to { transform: rotate(360deg); } }

    .bs-error-box {
      padding: 1rem 1.5rem;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      color: #b91c1c;
      font-size: .9rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .bs-empty-box { padding: 2.5rem; text-align: center; color: #94a3b8; }
    .bs-empty-box i { font-size: 2.5rem; display: block; margin-bottom: .5rem; }

    /* ── Dark Mode ────────────────────────────────────────────────────── */
    body.dark-mode .bs-filter-wrap { background-color: var(--clr-bg-card,#1e293b); border-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-filter-name label { color: #94a3b8; }
    body.dark-mode .bs-search-group .form-input { background: var(--clr-bg-body,#0f172a); border-color: var(--clr-border-light,#334155); color: var(--clr-text-main,#f8fafc); }
    body.dark-mode .bs-search-group .form-input::placeholder { color: #475569; }
    body.dark-mode .bs-btn-search { background: #f8fafc; color: #0f172a; border-color: #f8fafc; }
    body.dark-mode .bs-btn-clear  { background: transparent; color: #cbd5e1; border-color: #475569; }
    body.dark-mode .bs-btn-clear:hover { background: var(--clr-hover-bg,#1e293b); color: #fff; }
    body.dark-mode .bs-table th { color: #94a3b8; border-bottom-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-table td { border-bottom-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-table tr:hover td { background: var(--clr-hover-bg,#1e293b); }
    body.dark-mode .bs-company-name { color: var(--clr-text-main,#f8fafc); }
    body.dark-mode .bs-company-ruc  { color: #94a3b8; }
    body.dark-mode .bs-subtitle     { color: #9ca3af; }
    body.dark-mode .bs-chip.ok      { background: rgba(22,101,52,.2);  color: #4ade80; }
    body.dark-mode .bs-chip.warn    { background: rgba(146,64,14,.2);  color: #fbbf24; }
    body.dark-mode .bs-chip.off     { background: rgba(185,28,28,.2);  color: #f87171; }
    body.dark-mode .bs-btn-buzon    { background: linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); box-shadow: 0 6px 18px rgba(37,99,235,.2); }
    body.dark-mode .bs-panel        { border-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-tabs         { background: var(--clr-bg-card,#1e293b); border-bottom-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-tab          { color: #94a3b8; }
    body.dark-mode .bs-tab:hover    { color: #f8fafc; }
    body.dark-mode .bs-tab.active   { color: #60a5fa; border-bottom-color: #3b82f6; background: var(--clr-bg-body,#0f172a); }
    body.dark-mode .bs-msg-table th { color: #94a3b8; border-bottom-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-msg-table td { border-bottom-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-msg-table tr:hover td { background: rgba(37,99,235,.12); }
    body.dark-mode .bs-etiq.gray    { background: #1e293b; color: #94a3b8; }
    body.dark-mode .bs-modal        { background: var(--clr-bg-card,#1e293b); }
    body.dark-mode .bs-detail-item span { color: var(--clr-text-main,#f8fafc); }
    body.dark-mode .bs-modal-footer { border-top-color: var(--clr-border-light,#334155); }
    body.dark-mode .bs-btn-close-modal { background: var(--clr-bg-body,#0f172a); color: #cbd5e1; border-color: #475569; }
    body.dark-mode .bs-error-box    { background: rgba(185,28,28,.15); border-color: rgba(252,165,165,.3); color: #fca5a5; }
    body.dark-mode .bs-empty-box    { color: #64748b; }
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

          {{-- ── Contenido Principal ──────────────────────────────────── --}}
          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <div>
                <h1>Buzón SOL</h1>
                <p class="bs-subtitle">Consulta el buzón electrónico de SUNAT de cada empresa.</p>
              </div>
            </div>

            {{-- ── Filtros ── --}}
            <form method="GET" action="{{ route('bandeja-sunat.index') }}" class="bs-filter-wrap">
              <div class="bs-filter-name">
                <label>Buscar empresa</label>
                <div class="bs-search-group">
                  <input type="text" name="q" class="form-input"
                    value="{{ $filters['q'] }}"
                    placeholder="Nombre de la empresa…">
                  <button type="submit" class="bs-btn-search">
                    <i class='bx bx-search'></i> Buscar
                  </button>
                  @if($filters['q'])
                    <a href="{{ route('bandeja-sunat.index') }}" class="bs-btn-clear">
                      <i class='bx bx-eraser'></i> Limpiar
                    </a>
                  @endif
                </div>
              </div>
            </form>

            {{-- ── Tabla de empresas ── --}}
            <div class="bs-table-wrap">
              <table class="bs-table">
                <thead>
                  <tr>
                    <th>Empresa</th>
                    <th>RUC</th>
                    <th>Credenciales</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($companies as $company)
                    <tr>
                      <td class="bs-company-name">{{ $company->name }}</td>
                      <td class="bs-company-ruc">{{ $company->ruc }}</td>
                      <td>
                        <span class="bs-chip {{ $company->hasSunatCredentials() ? 'ok' : 'warn' }}">
                          <i class='bx {{ $company->hasSunatCredentials() ? "bx-key" : "bx-error-circle" }}'></i>
                          {{ $company->hasSunatCredentials() ? 'Configuradas' : 'Pendiente' }}
                        </span>
                      </td>
                      <td>
                        @if($company->canUseSunatPortal() && $company->hasSunatCredentials())
                          <button type="button"
                            class="bs-btn-buzon"
                            data-iniciar-url="{{ route('bandeja-sunat.iniciar', $company) }}"
                            data-mensajes-url="{{ route('bandeja-sunat.mensajes', $company) }}"
                            data-company-id="{{ $company->id }}"
                            data-company-nombre="{{ $company->name }}"
                            onclick="buscarBuzon(this)">
                            <i class='bx bx-envelope'></i> Buscar Buzón
                          </button>
                        @else
                          <span style="font-size:.82rem; color:#94a3b8;">Sin acceso</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" style="text-align:center; color:#94a3b8; padding:2rem;">
                        <i class='bx bx-buildings' style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        No se encontraron empresas.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <p style="margin-top:1rem; font-size:.8rem; color:#94a3b8;">
              {{ $companies->count() }} empresa(s) encontrada(s).
            </p>

            {{-- ── Panel de mensajes (oculto hasta hacer clic en "Buscar Buzón") ── --}}
            <div id="bs-panel" class="bs-panel" style="display:none;">
              <div class="bs-panel-header">
                <div class="bs-panel-title">
                  <i class='bx bx-envelope-open'></i>
                  <span id="bs-panel-empresa">—</span>
                  <span id="bs-panel-counter" class="bs-panel-counter" style="display:none;"></span>
                </div>
                <button type="button" onclick="cerrarPanel()" title="Cerrar panel"
                  style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:6px;
                         width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                  <i class='bx bx-x' style="font-size:1.2rem;"></i>
                </button>
              </div>

              {{-- Tabs Mensajes / Notificaciones --}}
              <div class="bs-tabs">
                <button class="bs-tab active" id="bs-tab-1" onclick="cambiarTipo(1, this)">
                  <i class='bx bx-message-square-detail'></i> Mensajes
                </button>
                <button class="bs-tab" id="bs-tab-2" onclick="cambiarTipo(2, this)">
                  <i class='bx bx-bell'></i> Notificaciones
                </button>
              </div>

              {{-- Área de contenido --}}
              <div id="bs-msgs-area" style="min-height:200px;">
                <div id="bs-msgs-loading" style="display:none; padding:2.5rem; text-align:center; color:#64748b;">
                  <span class="bs-spinner bs-spinner-dark"></span>
                  <span id="bs-loading-text" style="margin-left:.5rem; font-size:.9rem;">Cargando mensajes…</span>
                </div>
                <div id="bs-msgs-content" style="overflow-x:auto;"></div>
                <div id="bs-msgs-error"   style="display:none; padding:1rem 1.5rem;"></div>
              </div>
            </div>
            {{-- /panel --}}

          </div>
          {{-- /module-card-wide --}}

        </div>
      </main>
    </section>
  </div>

  {{-- ── Modal detalle del mensaje ── --}}
  <div class="bs-modal-overlay" id="bs-modal-overlay">
    <div class="bs-modal">
      <div class="bs-modal-header">
        <h3 id="bs-modal-asunto">Detalle del Mensaje</h3>
        <button class="bs-modal-close" onclick="cerrarDetalle()" title="Cerrar">&times;</button>
      </div>
      <div class="bs-modal-body">
        <div id="bs-modal-loading" style="text-align:center; padding:1rem; color:#64748b;">
          <span class="bs-spinner bs-spinner-dark"></span> Cargando detalle…
        </div>
        <div id="bs-modal-detail-content" style="display:none;">
          <div class="bs-detail-grid">
            <div class="bs-detail-item" style="grid-column: span 2;">
              <label>Empresa / RUC</label>
              <span id="bd-empresa-val">—</span>
            </div>
            <div class="bs-detail-item" id="bd-numero-wrap" style="display:none;">
              <label>Número</label>
              <span id="bd-numero">—</span>
            </div>
            <div class="bs-detail-item" id="bd-fecha-wrap" style="display:none;">
              <label>Fecha depósito</label>
              <span id="bd-fecha">—</span>
            </div>
            <div class="bs-detail-item" id="bd-dep-wrap" style="display:none;">
              <label>Dependencia</label>
              <span id="bd-dep">—</span>
            </div>
          </div>
        </div>
        <div id="bs-modal-error" style="display:none;"></div>
      </div>
      <div class="bs-modal-footer">
        <a id="bs-btn-doc" href="#" target="_blank" rel="noopener noreferrer" class="bs-btn-doc" style="display:none;">
          <i class='bx bx-file-html'></i> Ver documento
        </a>
        <button class="bs-btn-close-modal" onclick="cerrarDetalle()">
          <i class='bx bx-x'></i> Cerrar
        </button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  /* ── Variables globales ────────────────────────────────────────────── */
  var _currentTipo        = 1;
  var _currentMensajesUrl = null;
  var _currentCompanyId   = null;
  var _csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  /* Flash auto-cerrar */
  document.querySelectorAll('[data-flash-message]').forEach(function (flash) {
    var btn = flash.querySelector('[data-flash-close]');
    if (btn) btn.addEventListener('click', function () { flash.remove(); });
    setTimeout(function () { if (document.body.contains(flash)) flash.remove(); }, 4500);
  });

  /* ══════════════════════════════════════════════════════════════════════
     1. buscarBuzon(btn)
     ══════════════════════════════════════════════════════════════════════ */
  async function buscarBuzon(btn) {
    var iniciarUrl  = btn.dataset.iniciarUrl;
    var mensajesUrl = btn.dataset.mensajesUrl;
    var companyName = btn.dataset.companyNombre;

    /* Deshabilitar todos los botones durante el login (~20 seg) */
    document.querySelectorAll('.bs-btn-buzon').forEach(function (b) { b.disabled = true; });
    btn.innerHTML = '<span class="bs-spinner"></span> Iniciando sesión…';

    /* Mostrar panel con spinner */
    var panel = document.getElementById('bs-panel');
    panel.style.display = '';
    document.getElementById('bs-panel-empresa').textContent = companyName;
    document.getElementById('bs-panel-counter').style.display = 'none';
    setMsgsLoading('Iniciando sesión en SUNAT (~20 seg)…');
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });

    try {
      var res  = await fetch(iniciarUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': _csrfToken,
          'Accept':       'application/json',
          'Content-Type': 'application/json',
        },
      });
      var data = await res.json();

      if (!data.ok) {
        mostrarError(data.error || 'No se pudo iniciar sesión en el buzón.');
        restoreBtn(btn);
        return;
      }

      _currentMensajesUrl = mensajesUrl;
      _currentCompanyId   = mensajesUrl.split('/mensajes/')[1] || '';
      _currentTipo = 1;
      document.getElementById('bs-tab-1').classList.add('active');
      document.getElementById('bs-tab-2').classList.remove('active');

      /* ── Reintentos para rows_null ── */
      var textos = [
        'Conectando con el buzón…',
        'Verificando sesión SUNAT…',
        'Obteniendo mensajes…',
        'Casi listo…',
        'Un momento más…',
      ];
      var cargado = false;
      for (var i = 0; i < textos.length; i++) {
        setMsgsLoading(textos[i]);
        if (i > 0) {
          await new Promise(function (r) { setTimeout(r, 3000); });
        }
        var resultado = await cargarMensajes(mensajesUrl, 1, true);
        if (resultado === 'ok' || resultado === 'expired' || resultado === 'error') {
          cargado = true;
          break;
        }
        /* resultado === 'rows_null' → reintenta */
      }
      if (!cargado) {
        mostrarVacio('Este RUC no tiene buzón electrónico activo en SUNAT o los mensajes aún no están disponibles.');
      }

    } catch (err) {
      mostrarError('Error de red: ' + err.message);
    }

    restoreBtn(btn);
  }

  function restoreBtn(btn) {
    document.querySelectorAll('.bs-btn-buzon').forEach(function (b) { b.disabled = false; });
    btn.innerHTML = "<i class='bx bx-envelope'></i> Buscar Buzón";
  }

  function mostrarVacio(msg) {
    hideMsgsLoading();
    document.getElementById('bs-msgs-error').style.display = 'none';
    document.getElementById('bs-msgs-content').innerHTML =
      '<div class="bs-empty-box"><i class="bx bx-inbox"></i>' + escHtml(msg) + '</div>';
  }

  /* ══════════════════════════════════════════════════════════════════════
     2. cargarMensajes(mensajesUrl, tipo, todo)
     Retorna: 'ok' | 'rows_null' | 'expired' | 'error'
     ══════════════════════════════════════════════════════════════════════ */
  async function cargarMensajes(mensajesUrl, tipo, todo) {
    var qs = '?tipo=' + tipo + '&desde=2025-01-01' + (todo ? '&todo=true' : '');

    try {
      var res  = await fetch(mensajesUrl + qs, { headers: { 'Accept': 'application/json' } });
      var data = await res.json();

      if (res.status === 401 || data.expired) {
        mostrarError(data.error || 'Sesión expirada. Haz clic en "Buscar Buzón" nuevamente.');
        return 'expired';
      }

      if (!data.ok) {
        /* rows_null = el bot aún no tiene los datos listos → señal para reintentar */
        if (data.error === 'rows_null') {
          return 'rows_null';
        }
        mostrarError(data.error || 'Error al obtener mensajes.');
        return 'error';
      }

      var msgs  = data.mensajes || [];
      var total = data.total_buzon != null ? data.total_buzon : msgs.length;

      var counter = document.getElementById('bs-panel-counter');
      counter.textContent = total + ' mensajes';
      counter.style.display = '';

      renderMensajes(msgs, tipo);
      return 'ok';

    } catch (err) {
      mostrarError('Error de red: ' + err.message);
      return 'error';
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
     3. renderMensajes(msgs, tipo)
     ══════════════════════════════════════════════════════════════════════ */
  function renderMensajes(msgs, tipo) {
    hideMsgsLoading();
    document.getElementById('bs-msgs-error').style.display = 'none';
    var container = document.getElementById('bs-msgs-content');

    if (!msgs || msgs.length === 0) {
      container.innerHTML =
        '<div class="bs-empty-box"><i class="bx bx-inbox"></i>' +
        (tipo === 2 ? 'No hay notificaciones.' : 'No hay mensajes.') + '</div>';
      return;
    }

    /* Construir base URL desde _currentMensajesUrl:
       /bandeja-sunat/mensajes/{companyId}  →  /bandeja-sunat */
    var routeBase = _currentMensajesUrl.replace('/mensajes/' + _currentCompanyId, '');

    var rows = msgs.map(function (m) {
      var isUnread = m.indEstado === 1;

      var etiqClass = 'gray', etiqLabel = 'General';
      var c = String(m.codEtiqueta || '');
      if      (c === '11') { etiqClass = 'red';    etiqLabel = 'Coactiva';    }
      else if (c === '10') { etiqClass = 'red';    etiqLabel = 'Valores';     }
      else if (c === '03') { etiqClass = 'yellow'; etiqLabel = 'Valores';     }
      else if (c === '04') { etiqClass = 'yellow'; etiqLabel = 'Fraccionam.'; }

      var detalleUrl = routeBase + '/detalle/' + _currentCompanyId + '/' + m.codMensaje;
      var docUrl     = routeBase + '/documento/' + _currentCompanyId + '/' + m.codMensaje;

      var adj = m.cantidadArchAdj > 0
        ? '<i class="bx bx-paperclip" title="' + m.cantidadArchAdj + ' adjunto(s)"></i>'
        : '';

      return '<tr class="' + (isUnread ? 'bs-msg-unread' : '') + '" ' +
        'onclick="abrirDetalle(\'' + escAttr(detalleUrl) + '\',\'' + escAttr(docUrl) + '\',\'' + escAttr(m.desAsunto || '') + '\')">' +
        '<td style="white-space:nowrap;">' + escHtml(m.fecEnvio || '') + '</td>' +
        '<td>' + escHtml(m.desAsunto || '') + '</td>' +
        '<td><span class="bs-etiq ' + etiqClass + '">' + etiqLabel + '</span></td>' +
        '<td style="text-align:center;">' + adj + '</td>' +
        '</tr>';
    }).join('');

    container.innerHTML =
      '<table class="bs-msg-table">' +
        '<thead><tr>' +
          '<th style="width:110px;">Fecha</th>' +
          '<th>Asunto</th>' +
          '<th style="width:115px;">Tipo</th>' +
          '<th style="width:50px;text-align:center;">Adj.</th>' +
        '</tr></thead>' +
        '<tbody>' + rows + '</tbody>' +
      '</table>';
  }

  /* ══════════════════════════════════════════════════════════════════════
     4. cambiarTipo(tipo, tabEl)
     ══════════════════════════════════════════════════════════════════════ */
  async function cambiarTipo(tipo, tabEl) {
    if (_currentTipo === tipo || !_currentMensajesUrl) return;
    _currentTipo = tipo;
    document.querySelectorAll('.bs-tab').forEach(function (t) { t.classList.remove('active'); });
    tabEl.classList.add('active');

    var textos = [
      'Obteniendo mensajes\u2026',
      'Verificando sesi\u00f3n SUNAT\u2026',
      'Casi listo\u2026',
    ];
    for (var i = 0; i < textos.length; i++) {
      setMsgsLoading(textos[i]);
      if (i > 0) {
        await new Promise(function (r) { setTimeout(r, 3000); });
      }
      var resultado = await cargarMensajes(_currentMensajesUrl, tipo, true);
      if (resultado === 'ok' || resultado === 'expired' || resultado === 'error') break;
    }
  }

  /* ══════════════════════════════════════════════════════════════════════
     5. abrirDetalle(detalleUrl, docUrl, asunto)
     ══════════════════════════════════════════════════════════════════════ */
  async function abrirDetalle(detalleUrl, docUrl, asunto) {
    document.getElementById('bs-modal-asunto').textContent = asunto || 'Detalle del Mensaje';
    document.getElementById('bs-modal-loading').style.display = '';
    document.getElementById('bs-modal-detail-content').style.display = 'none';
    document.getElementById('bs-modal-error').style.display = 'none';
    document.getElementById('bs-btn-doc').style.display = 'none';
    document.getElementById('bs-modal-overlay').classList.add('open');

    try {
      var res  = await fetch(detalleUrl, { headers: { 'Accept': 'application/json' } });
      var data = await res.json();
      document.getElementById('bs-modal-loading').style.display = 'none';

      if (!data.ok) {
        showModalError(data.error || 'Error al cargar el detalle.');
        return;
      }

      var det    = data.detalle  || {};
      var parsed = det.msjMensaje_parsed || {};

      /* Empresa / RUC */
      var emp = [det.nombUsuario, det.codUsuario].filter(Boolean).join(' — ');
      document.getElementById('bd-empresa-val').textContent = emp || '—';

      /* Campos opcionales */
      toggleDetail('bd-numero-wrap', 'bd-numero', parsed.numero);
      toggleDetail('bd-fecha-wrap',  'bd-fecha',  parsed.fecha_deposito);
      toggleDetail('bd-dep-wrap',    'bd-dep',    parsed.dependencia);

      document.getElementById('bs-modal-detail-content').style.display = '';

      /* Botón documento */
      if ((det.adjuntos_clasificados || {}).documento_html) {
        var docBtn = document.getElementById('bs-btn-doc');
        docBtn.href = docUrl;
        docBtn.style.display = '';
      }

    } catch (err) {
      document.getElementById('bs-modal-loading').style.display = 'none';
      showModalError('Error de red: ' + err.message);
    }
  }

  function toggleDetail(wrapId, valId, value) {
    var wrap = document.getElementById(wrapId);
    if (value) {
      document.getElementById(valId).textContent = value;
      wrap.style.display = '';
    } else {
      wrap.style.display = 'none';
    }
  }

  function showModalError(msg) {
    var div = document.getElementById('bs-modal-error');
    div.innerHTML = '<div class="bs-error-box"><i class="bx bx-error"></i>' + escHtml(msg) + '</div>';
    div.style.display = '';
  }

  /* ══════════════════════════════════════════════════════════════════════
     6. cerrarDetalle / cerrarPanel
     ══════════════════════════════════════════════════════════════════════ */
  function cerrarDetalle() {
    document.getElementById('bs-modal-overlay').classList.remove('open');
  }

  function cerrarPanel() {
    document.getElementById('bs-panel').style.display = 'none';
    _currentMensajesUrl = null;
    _currentCompanyId   = null;
  }

  /* ══════════════════════════════════════════════════════════════════════
     7. mostrarError(msg)
     ══════════════════════════════════════════════════════════════════════ */
  function mostrarError(msg) {
    hideMsgsLoading();
    var errDiv = document.getElementById('bs-msgs-error');
    errDiv.innerHTML =
      '<div class="bs-error-box"><i class="bx bx-error-circle"></i>' + escHtml(msg) + '</div>';
    errDiv.style.display = '';
    document.getElementById('bs-msgs-content').innerHTML = '';
    document.getElementById('bs-panel-counter').style.display = 'none';
  }

  /* ── Helpers ───────────────────────────────────────────────────────── */
  function setMsgsLoading(txt) {
    document.getElementById('bs-loading-text').textContent = txt || 'Cargando…';
    document.getElementById('bs-msgs-loading').style.display = '';
    document.getElementById('bs-msgs-content').innerHTML = '';
    document.getElementById('bs-msgs-error').style.display = 'none';
  }
  function hideMsgsLoading() {
    document.getElementById('bs-msgs-loading').style.display = 'none';
  }
  function escHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function escAttr(s) {
    return String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
  }

  /* Cerrar modal con Escape o clic en el backdrop */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') cerrarDetalle();
  });
  document.getElementById('bs-modal-overlay').addEventListener('click', function (e) {
    if (e.target === this) cerrarDetalle();
  });
</script>
@endpush
