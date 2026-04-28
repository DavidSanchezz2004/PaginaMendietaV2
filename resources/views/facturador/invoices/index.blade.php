@extends('layouts.app')

@section('title', 'Comprobantes — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    /* Colors adapt to dark mode via system variables defined in dashboard.css */
    .badge-draft     { background:rgba(107, 114, 128, 0.1); color:var(--clr-text-muted, #374151); border:1px solid rgba(107, 114, 128, 0.2); }
    .badge-ready     { background:rgba(30, 64, 175, 0.1); color:#3b82f6; border:1px solid rgba(59, 130, 246, 0.2); }
    .badge-sent      { background:rgba(6, 95, 70, 0.1); color:var(--clr-active-bg, #065f46); border:1px solid rgba(16, 185, 129, 0.2); }
    .badge-error     { background:rgba(153, 27, 27, 0.1); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.2); }
    .badge-consulted { background:rgba(91, 33, 182, 0.1); color:#8b5cf6; border:1px solid rgba(139, 92, 246, 0.2); }
    .badge-voided    { background:rgba(107, 114, 128, 0.12); color:#6b7280; border:1px solid rgba(107, 114, 128, 0.25); text-decoration:line-through; }
    
    .invoice-badge   { display:inline-flex; align-items:center; gap:0.35rem; padding:.35rem .85rem; border-radius:20px; font-size:.78rem; font-weight:700; white-space:nowrap; }
    
    .filter-bar      { display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.5rem; align-items:center; background: var(--clr-bg-card, #ffffff); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--clr-border-light, rgba(0,0,0,0.06)); box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: all 0.3s ease; }
    body.dark-mode .filter-bar { background: var(--clr-bg-card); border-color: var(--clr-border-light); }
    
    .filter-bar input, .filter-bar select { padding:.55rem .85rem; border:1px solid var(--clr-border-light, #e5e7eb); border-radius:8px; font-size:.9rem; font-family: inherit; color: var(--clr-text-main, #111827); background: transparent; outline: none; transition: all 0.2s ease; }
    body.dark-mode .filter-bar input, body.dark-mode .filter-bar select { border-color: rgba(255,255,255,0.1); }
    .filter-bar input:focus, .filter-bar select:focus { border-color: var(--clr-active-bg, #1a6b57); box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1); }
    body.dark-mode .filter-bar input:focus, body.dark-mode .filter-bar select:focus { border-color: var(--clr-text-accent); box-shadow: 0 0 0 3px rgba(163, 204, 170, 0.1); }
    
    .module-table th { color: var(--clr-text-muted, #6b7280); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .module-table td { color: var(--clr-text-main, #111827); font-weight: 500; font-size: 0.9rem; }
    body.dark-mode .module-table td { color: var(--clr-text-main); }
    body.dark-mode .module-table th { color: var(--clr-text-muted); }
    
    .btn-action-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: rgba(0,0,0,0.04); color: var(--clr-text-main, #374151); transition: all 0.2s; text-decoration: none; font-size: 1.15rem; }
    .btn-action-icon:hover { background: rgba(0,0,0,0.08); color: var(--clr-active-bg, #1a6b57); transform: translateY(-2px); }
    body.dark-mode .btn-action-icon { background: rgba(255,255,255,0.05); color: var(--clr-text-muted); }
    body.dark-mode .btn-action-icon:hover { background: rgba(255,255,255,0.1); color: var(--clr-text-accent); }
    .action-wrapper { display: flex; gap: 0.4rem; justify-content: flex-end; }

    /* ── Vista Detalle Horizontal ── */
    #tabla-compacta  { display: block; }
    #tabla-detallada { display: none; }
    #tabla-detallada.active { display: block; }
    #tabla-compacta.hidden  { display: none; }
    .det-table { width:100%; border-collapse:collapse; font-size:.78rem; white-space:nowrap; }
    .det-table th { background:var(--clr-bg-card,#f9fafb); padding:.4rem .55rem; text-align:left; font-weight:700; font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; color:var(--clr-text-muted,#6b7280); border-bottom:2px solid var(--clr-border-light,#e5e7eb); white-space:nowrap; }
    .det-table td { padding:.42rem .55rem; border-bottom:1px solid var(--clr-border-light,#f3f4f6); color:var(--clr-text-main,#111827); vertical-align:middle; }
    .det-table tr:hover td { background:rgba(0,0,0,.025); }
    body.dark-mode .det-table th { background:var(--clr-bg-card); }
    body.dark-mode .det-table tr:hover td { background:rgba(255,255,255,.04); }
    #btn-toggle-detalle.active { background:var(--clr-active-bg,#1a6b57); color:#fff; border-color:var(--clr-active-bg,#1a6b57); }

    /* ── Badges de estado contable ── */
    .accounting-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .65rem; border-radius:12px; font-size:.72rem; font-weight:700; white-space:nowrap; }
    .accounting-badge--incompleto { background:rgba(239,68,68,.1); color:#dc2626; border:1px solid rgba(239,68,68,.25); }
    .accounting-badge--pendiente  { background:rgba(245,158,11,.1); color:#d97706; border:1px solid rgba(245,158,11,.25); }
    .accounting-badge--listo      { background:rgba(16,185,129,.1); color:#059669; border:1px solid rgba(16,185,129,.25); }
    body.dark-mode .accounting-badge--incompleto { background:rgba(239,68,68,.15); }
    body.dark-mode .accounting-badge--pendiente  { background:rgba(245,158,11,.15); }
    body.dark-mode .accounting-badge--listo      { background:rgba(16,185,129,.15); }
    .btn-completar { display:inline-flex; align-items:center; gap:.3rem; padding:.28rem .7rem; background:rgba(245,158,11,.12); color:#d97706; border:1px solid rgba(245,158,11,.3); border-radius:8px; font-size:.72rem; font-weight:700; cursor:pointer; white-space:nowrap; transition:all .15s; }
    .btn-completar:hover { background:rgba(245,158,11,.2); transform:translateY(-1px); }
    body.dark-mode .btn-completar { background:rgba(245,158,11,.15); color:#fbbf24; }

    /* ── Tarjetas de resumen ── */
    .stat-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
    @media(max-width:900px){ .stat-cards { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:520px){ .stat-cards { grid-template-columns:1fr; } }
    .stat-card { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; padding:1.1rem 1.25rem; display:flex; flex-direction:column; gap:.25rem; box-shadow:0 4px 15px rgba(0,0,0,.03); transition:transform .2s; }
    .stat-card:hover { transform:translateY(-2px); }
    .stat-card__icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.35rem; margin-bottom:.35rem; }
    .stat-card__val  { font-size:1.45rem; font-weight:800; color:var(--clr-text-main,#111827); line-height:1.15; }
    .stat-card__lbl  { font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; letter-spacing:.05em; }
    .stat-card__sub  { font-size:.82rem; color:var(--clr-text-muted,#6b7280); margin-top:.1rem; }
    .sc-green .stat-card__icon { background:rgba(16,185,129,.12); color:#059669; }
    .sc-blue  .stat-card__icon { background:rgba(59,130,246,.12); color:#3b82f6; }
    .sc-amber .stat-card__icon { background:rgba(245,158,11,.12); color:#d97706; }
    .sc-slate .stat-card__icon { background:rgba(107,114,128,.12); color:#6b7280; }
    body.dark-mode .stat-card  { background:var(--clr-bg-card); border-color:var(--clr-border-light); }
    .month-summary-head { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .month-selector { display:inline-flex; align-items:center; gap:.45rem; background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; padding:.45rem .6rem; }
    .month-selector label { font-size:.78rem; font-weight:800; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; letter-spacing:.04em; }
    .month-selector input { border:none; outline:none; background:transparent; color:var(--clr-text-main,#111827); font-weight:700; font-size:.9rem; }
    .invoice-head { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:1.35rem; }
    .invoice-head h1 { margin:0; display:flex; align-items:center; gap:.55rem; font-size:1.65rem; line-height:1.1; color:var(--clr-text-main,#111827); }
    .invoice-actions { display:flex; justify-content:flex-end; gap:.55rem; align-items:center; flex-wrap:wrap; max-width:760px; }
    .invoice-actions .btn-primary, .invoice-actions .btn-secondary, .invoice-actions button { min-height:42px; }
    @media(max-width:860px){ .invoice-head { flex-direction:column; } .invoice-actions { justify-content:flex-start; width:100%; } }
    .btn-letter-exchange { color:#0f766e; }
    .letter-modal-backdrop { position:fixed; inset:0; z-index:90; display:none; align-items:center; justify-content:center; padding:1rem; background:rgba(15,23,42,.55); }
    .letter-modal-backdrop.is-open { display:flex; }
    .letter-modal { width:min(760px,100%); max-height:92vh; overflow:auto; background:var(--clr-bg-card,#fff); border-radius:10px; box-shadow:0 24px 80px rgba(15,23,42,.28); }
    .letter-modal__head { display:flex; justify-content:space-between; gap:1rem; align-items:center; padding:1rem 1.2rem; border-bottom:1px solid var(--clr-border-light,#e5e7eb); }
    .letter-modal__head h2 { margin:0; font-size:1.05rem; color:var(--clr-text-main,#111827); }
    .letter-modal__body { padding:1.2rem; display:grid; gap:.85rem; }
    .letter-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.7rem; }
    .letter-summary div { border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px; padding:.7rem; background:rgba(15,23,42,.02); }
    .letter-summary span { display:block; color:var(--clr-text-muted,#6b7280); font-size:.72rem; text-transform:uppercase; font-weight:800; }
    .letter-summary strong { color:var(--clr-text-main,#111827); font-size:.95rem; overflow-wrap:anywhere; }
    .letter-grid { display:grid; grid-template-columns:1fr 130px 150px 38px; gap:.55rem; align-items:end; }
    .letter-grid label { font-size:.72rem; text-transform:uppercase; color:var(--clr-text-muted,#6b7280); font-weight:800; }
    .letter-input { width:100%; border:1px solid var(--clr-border-light,#d1d5db); border-radius:7px; min-height:38px; padding:.5rem .65rem; background:transparent; color:var(--clr-text-main,#111827); }
    .letter-remove { width:38px; height:38px; border:1px solid #fecaca; color:#dc2626; background:#fff; border-radius:7px; cursor:pointer; }
    .letter-total-line { display:flex; justify-content:space-between; align-items:center; gap:1rem; border-top:1px solid var(--clr-border-light,#e5e7eb); padding-top:.75rem; font-weight:800; }
    .letter-total-line.is-invalid { color:#dc2626; }
    .letter-modal__footer { display:flex; justify-content:flex-end; gap:.6rem; padding:1rem 1.2rem; border-top:1px solid var(--clr-border-light,#e5e7eb); background:rgba(15,23,42,.03); }
    @media(max-width:720px){ .letter-summary { grid-template-columns:1fr; } .letter-grid { grid-template-columns:1fr; } .letter-remove { width:100%; } }
  </style>
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
        'userName'    => auth()->user()?->name,
        'userEmail'   => auth()->user()?->email,
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
            <div class="invoice-head">
              <h1><i class='bx bx-receipt'></i> Comprobantes Emitidos</h1>
              <div class="invoice-actions">
                @can('create', \App\Models\Invoice::class)
                  <a href="{{ route('facturador.invoices.create') }}" class="btn-primary">
                    <i class='bx bx-plus'></i> Nueva Factura/Boleta
                  </a>
                @endcan
                <button type="button" id="btn-toggle-detalle" class="btn-secondary" style="font-size:.85rem;" title="Ver todos los comprobantes con más columnas">
                  <i class='bx bx-table'></i> Más detalle
                </button>
                @php
                  $listoCount = $invoices->where('accounting_status', \App\Enums\AccountingStatusEnum::LISTO)->count();
                @endphp
                <button type="button" id="btn-export-excel"
                        style="display:{{ $listoCount > 0 ? 'inline-flex' : 'none' }}; align-items:center; gap:.4rem; padding:.55rem 1.1rem; background:#059669; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.85rem; font-weight:600; transition:all .15s;"
                        title="{{ $listoCount }} comprobante(s) listo(s) para exportar">
                  <i class='bx bx-file-export'></i>
                  Exportar Excel <span id="export-ready-count" style="background:rgba(255,255,255,.25); border-radius:10px; padding:.05rem .45rem; font-size:.75rem;">{{ $listoCount }}</span>
                </button>
                <a href="{{ route('facturador.index') }}" class="btn-secondary" style="font-size:.85rem;">
                  <i class='bx bx-building'></i> Cambiar empresa
                </a>
                @can('create', \App\Models\Invoice::class)
                  <a href="{{ route('facturador.invoices.import-template') }}" class="btn-secondary" style="font-size:.85rem;">
                    <i class='bx bx-download'></i> Plantilla Excel
                  </a>
                  <form method="POST" action="{{ route('facturador.invoices.import-excel') }}" enctype="multipart/form-data" id="invoice-import-form" style="display:inline-flex;">
                    @csrf
                    <input type="file" name="archivo" id="invoice-import-file" accept=".xlsx,.xls" style="display:none;">
                    <button type="button" class="btn-secondary" style="font-size:.85rem;" onclick="document.getElementById('invoice-import-file')?.click();">
                      <i class='bx bx-upload'></i> Importar Excel
                    </button>
                  </form>
                @endcan
              </div>
            </div>

            {{-- ── Tarjetas de resumen ── --}}
            @php
              $mesTxt  = $stats['selected_month_label'];
              $tipoMap = ['01'=>'Facturas','03'=>'Boletas','07'=>'N. Crédito','08'=>'N. Débito','09'=>'Guías'];
            @endphp
            <div class="month-summary-head">
              <div>
                <h2 style="margin:0; font-size:1rem; color:var(--clr-text-main,#111827);">Resumen mensual</h2>
                <p style="margin:.15rem 0 0; color:var(--clr-text-muted,#6b7280); font-size:.82rem;">Indicadores de {{ $mesTxt }}</p>
              </div>
              <form method="GET" class="month-selector">
                <label for="month">Mes</label>
                <input type="month" id="month" name="month" value="{{ $stats['selected_month'] }}" onchange="this.form.submit()">
                @if(!empty($filters['search']))<input type="hidden" name="search" value="{{ $filters['search'] }}">@endif
                @if(!empty($filters['serie']))<input type="hidden" name="serie" value="{{ $filters['serie'] }}">@endif
                @if(!empty($filters['estado']))<input type="hidden" name="estado" value="{{ $filters['estado'] }}">@endif
              </form>
            </div>
            <div class="stat-cards">

              <div class="stat-card sc-green">
                <div class="stat-card__icon"><i class='bx bx-dollar-circle'></i></div>
                <span class="stat-card__lbl">Total Facturado</span>
                <span class="stat-card__val" style="font-size:1.1rem; line-height:1.35;">
                  @forelse($stats['totals_by_currency'] as $currency => $amount)
                    <span style="display:block;">{{ $currency }} {{ number_format($amount, 2) }}</span>
                  @empty
                    <span>PEN 0.00</span>
                  @endforelse
                </span>
                <span class="stat-card__sub" style="line-height:1.6;">
                  Sin IGV: <strong>{{ number_format($stats['total_mes_sin_igv'], 2) }}</strong><br>
                  IGV: <strong>{{ number_format($stats['total_mes_igv'], 2) }}</strong><br>
                  @if($stats['month_variation_pct'] !== null)
                    <span style="color:{{ $stats['month_variation_pct'] >= 0 ? '#059669' : '#dc2626' }}; font-weight:800;">
                      {{ $stats['month_variation_pct'] >= 0 ? '+' : '' }}{{ $stats['month_variation_pct'] }}%
                    </span>
                    vs {{ $stats['previous_month_label'] }}
                  @else
                    <em style="font-size:.75rem; color:var(--clr-text-muted);">Sin comparación previa</em>
                  @endif
                </span>
              </div>

              <div class="stat-card sc-blue">
                <div class="stat-card__icon"><i class='bx bx-check-shield'></i></div>
                <span class="stat-card__lbl">Aceptados SUNAT</span>
                <span class="stat-card__val">{{ $stats['aceptados_count'] }}</span>
                <span class="stat-card__sub">Monto {{ number_format($stats['aceptados_monto'], 2) }} en {{ $mesTxt }}</span>
              </div>

              <div class="stat-card sc-amber">
                <div class="stat-card__icon"><i class='bx bx-error-circle'></i></div>
                <span class="stat-card__lbl">Requieren Atención</span>
                <span class="stat-card__val">{{ $stats['atencion_count'] }}</span>
                <span class="stat-card__sub">
                  @if($stats['error_count'] > 0)
                    <span style="color:#ef4444;font-weight:700;">{{ $stats['error_count'] }} con error</span>
                  @else
                    borradores / pendientes
                  @endif
                </span>
              </div>

              <div class="stat-card sc-slate">
                <div class="stat-card__icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                <span class="stat-card__lbl">Por Tipo &mdash; {{ $mesTxt }}</span>
                <span class="stat-card__val" style="font-size:.92rem; line-height:1.7;">
                  @forelse($stats['por_tipo'] as $tipo => $cnt)
                    <span style="display:block;">{{ $tipoMap[$tipo] ?? $tipo }}: <strong style="color:var(--clr-active-bg,#1a6b57);">{{ $cnt }}</strong></span>
                  @empty
                    <span style="font-size:.82rem;color:var(--clr-text-muted);">Sin comprobantes</span>
                  @endforelse
                </span>
              </div>

            </div>

            {{-- Filtros --}}
            <form method="GET" class="filter-bar">
              <input type="hidden" name="month" value="{{ $stats['selected_month'] }}">
              <i class='bx bx-filter-alt' style="font-size: 1.25rem; color: var(--clr-text-muted);"></i>
              <input type="text" name="search" placeholder="Buscar cliente..." value="{{ $filters['search'] ?? '' }}">
              <input type="text" name="serie" placeholder="Serie (F001...)" value="{{ $filters['serie'] ?? '' }}" style="max-width:130px;">
              <select name="estado">
                <option value="">Todos los estados</option>
                @foreach(\App\Enums\InvoiceStatusEnum::cases() as $status)
                  <option value="{{ $status->value }}" {{ ($filters['estado'] ?? '') === $status->value ? 'selected' : '' }}>
                    {{ $status->label() }}
                  </option>
                @endforeach
              </select>
              <button type="submit" class="btn-primary" style="font-size:.85rem; padding: 0.55rem 1.2rem;"><i class='bx bx-search'></i> Filtrar</button>
              <a href="{{ route('facturador.invoices.index') }}" class="btn-secondary" style="font-size:.85rem; padding: 0.55rem 1.2rem;"><i class='bx bx-eraser'></i> Limpiar</a>
            </form>

            {{-- ── Vista compacta (defecto) ── --}}
            <div id="tabla-compacta" class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>Serie-Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th style="text-align:right;">Total</th>
                    <th>Estado</th>
                    <th>SUNAT</th>
                    <th>Contable</th>
                    <th class="cell-action">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($invoices as $invoice)
                    <tr>
                      <td><code>{{ $invoice->serie_numero }}</code></td>
                      <td>{{ $invoice->fecha_emision->format('d/m/Y') }}</td>
                      <td>
                        <div style="font-weight:600; color:var(--clr-text-main);">{{ $invoice->client->nombre_razon_social ?? '—' }}</div>
                        <small style="color:var(--clr-text-muted);">{{ $invoice->client->numero_documento ?? '' }}</small>
                      </td>
                      <td style="text-align:right; font-weight:700; color:var(--clr-active-bg, #1a6b57);">
                        {{ $invoice->codigo_moneda }} {{ number_format($invoice->monto_total, 2) }}
                      </td>
                      <td>
                        <span class="invoice-badge badge-{{ $invoice->estado->value }}">
                          {{ $invoice->estado->label() }}
                        </span>
                      </td>
                      <td>
                        <span class="invoice-badge {{ $invoice->estado_feasy->isAccepted() ? 'badge-sent' : 'badge-draft' }}">
                          {{ $invoice->estado_feasy->label() }}
                        </span>
                      </td>
                      <td>
                        @php $accStatus = $invoice->accounting_status ?? \App\Enums\AccountingStatusEnum::INCOMPLETO; @endphp
                        <span class="accounting-badge accounting-badge--{{ $accStatus->value }}"
                              data-accounting-badge="{{ $invoice->id }}"
                              title="{{ $accStatus->label() }}">
                          {{ $accStatus->icon() }} {{ $accStatus->label() }}
                        </span>
                      </td>
                      <td class="cell-action">
                        <div class="action-wrapper">
                          @if($invoice->accounting_status !== \App\Enums\AccountingStatusEnum::LISTO)
                            <button type="button"
                                    class="btn-completar"
                                    data-completar-btn="{{ $invoice->id }}"
                                    onclick="openAccountingModal({{ $invoice->id }})"
                                    title="Completar información contable">
                              ⚠ Completar
                            </button>
                          @endif
                          <a href="{{ route('facturador.invoices.show', $invoice) }}" class="btn-action-icon" title="Ver detalle">
                            <i class='bx bx-show'></i>
                          </a>
                          @if($invoice->canBeExchangedToLetters())
                            <button type="button"
                                    class="btn-action-icon btn-letter-exchange"
                                    title="Canjear a letras"
                                    data-open-letter-exchange
                                    data-invoice-id="{{ $invoice->id }}"
                                    data-invoice-number="{{ $invoice->serie_numero }}"
                                    data-client="{{ $invoice->client->nombre_razon_social ?? '—' }}"
                                    data-currency="{{ $invoice->codigo_moneda }}"
                                    data-pending="{{ number_format($invoice->pendingAmountForLetters(), 2, '.', '') }}">
                              <i class='bx bx-transfer'></i>
                            </button>
                          @elseif($invoice->hasBeenExchangedToLetters())
                            <a href="{{ route('facturador.letras.index', ['search' => $invoice->serie_numero]) }}" class="btn-action-icon" title="Canjeada a letras">
                              <i class='bx bx-check-double'></i>
                            </a>
                          @endif
                          @if($invoice->xml_path)
                            <a href="{{ route('facturador.invoices.xml', $invoice) }}" class="btn-action-icon" title="Descargar XML">
                              <i class='bx bx-download'></i>
                            </a>
                          @endif
                          @can('delete', $invoice)
                            <form method="POST" action="{{ route('facturador.invoices.destroy', $invoice) }}"
                                  data-confirm="¿Eliminar {{ $invoice->serie_numero }}?" style="display:inline;">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn-action-icon" title="Eliminar" style="color:#ef4444;">
                                <i class='bx bx-trash'></i>
                              </button>
                            </form>
                          @endcan
                          @can('void', $invoice)
                            @if($invoice->canBeVoided())
                              <form method="POST" action="{{ route('facturador.invoices.void', $invoice) }}"
                                    id="form-void-{{ $invoice->id }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="motivo" id="void-motivo-{{ $invoice->id }}" value="">
                                <button type="button" class="btn-action-icon btn-void-idx"
                                        data-void-id="{{ $invoice->id }}"
                                        data-serie="{{ $invoice->serie_numero }}"
                                        title="Anular" style="color:#dc2626;">
                                  <i class='bx bx-x-circle'></i>
                                </button>
                              </form>
                            @endif
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="8">No hay comprobantes registrados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            {{-- ── Vista detallada horizontal (oculta por defecto) ── --}}
            <div id="tabla-detallada" style="overflow-x:auto;">
              <table class="det-table">
                <thead>
                  <tr>
                    <th>Serie-Número</th>
                    <th>Tipo</th>
                    <th>Fecha Emisión</th>
                    <th>Vencimiento</th>
                    <th>Cliente</th>
                    <th>RUC / DNI</th>
                    <th>Moneda</th>
                    <th>Forma Pago</th>
                    <th style="text-align:right;">Op. Gravadas</th>
                    <th style="text-align:right;">IGV%</th>
                    <th style="text-align:right;">IGV</th>
                    <th style="text-align:right;">Total</th>
                    <th>Estado</th>
                    <th>SUNAT</th>
                    <th>Contable</th>
                    <th style="text-align:center;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($invoices as $invoice)
                    <tr>
                      <td><code style="font-size:.78rem;">{{ $invoice->serie_numero }}</code></td>
                      <td>
                        @php
                          $tipoMap = ['01'=>'Factura','03'=>'Boleta','07'=>'N.Crédito','08'=>'N.Débito','09'=>'Guía'];
                        @endphp
                        <small>{{ $tipoMap[$invoice->codigo_tipo_documento] ?? $invoice->codigo_tipo_documento }}</small>
                      </td>
                      <td>{{ $invoice->fecha_emision->format('d/m/Y') }}</td>
                      <td style="color:var(--clr-text-muted);">{{ $invoice->fecha_vencimiento ? $invoice->fecha_vencimiento->format('d/m/Y') : '—' }}</td>
                      <td style="font-weight:600; max-width:170px; overflow:hidden; text-overflow:ellipsis;">{{ $invoice->client->nombre_razon_social ?? '—' }}</td>
                      <td style="font-family:monospace; font-size:.75rem;">{{ $invoice->client->numero_documento ?? '—' }}</td>
                      <td style="text-align:center;">{{ $invoice->codigo_moneda }}</td>
                      <td style="text-align:center;">
                        @if($invoice->forma_pago == '1')
                          <span style="background:rgba(16,185,129,.1); color:#059669; padding:.15rem .55rem; border-radius:12px; font-size:.72rem; font-weight:700;">Contado</span>
                        @elseif($invoice->forma_pago == '2')
                          <span style="background:rgba(59,130,246,.1); color:#3b82f6; padding:.15rem .55rem; border-radius:12px; font-size:.72rem; font-weight:700;">Crédito</span>
                        @else
                          <span style="color:var(--clr-text-muted);">—</span>
                        @endif
                      </td>
                      <td style="text-align:right;">{{ number_format($invoice->monto_total_gravado, 2) }}</td>
                      <td style="text-align:right; color:var(--clr-text-muted);">{{ $invoice->porcentaje_igv }}%</td>
                      <td style="text-align:right;">{{ number_format($invoice->monto_total_igv, 2) }}</td>
                      <td style="text-align:right; font-weight:700; color:var(--clr-active-bg, #1a6b57);">{{ number_format($invoice->monto_total, 2) }}</td>
                      <td>
                        <span class="invoice-badge badge-{{ $invoice->estado->value }}" style="font-size:.68rem; padding:.2rem .6rem;">
                          {{ $invoice->estado->label() }}
                        </span>
                      </td>
                      <td>
                        <span class="invoice-badge {{ $invoice->estado_feasy->isAccepted() ? 'badge-sent' : 'badge-draft' }}" style="font-size:.68rem; padding:.2rem .6rem;">
                          {{ $invoice->estado_feasy->label() }}
                        </span>
                      </td>
                      <td>
                        @php $accStatusDet = $invoice->accounting_status ?? \App\Enums\AccountingStatusEnum::INCOMPLETO; @endphp
                        <span class="accounting-badge accounting-badge--{{ $accStatusDet->value }}"
                              data-accounting-badge="{{ $invoice->id }}"
                              style="font-size:.68rem; padding:.2rem .55rem;"
                              title="{{ $accStatusDet->label() }}">
                          {{ $accStatusDet->icon() }} {{ $accStatusDet->label() }}
                        </span>
                      </td>
                      <td style="text-align:center;">
                        <div class="action-wrapper" style="justify-content:center;">
                          @if($invoice->accounting_status !== \App\Enums\AccountingStatusEnum::LISTO)
                            <button type="button"
                                    class="btn-completar"
                                    data-completar-btn="{{ $invoice->id }}"
                                    onclick="openAccountingModal({{ $invoice->id }})"
                                    title="Completar información contable"
                                    style="font-size:.68rem; padding:.2rem .55rem;">
                              ⚠
                            </button>
                          @endif
                          <a href="{{ route('facturador.invoices.show', $invoice) }}" class="btn-action-icon" title="Ver detalle">
                            <i class='bx bx-show'></i>
                          </a>
                          @if($invoice->canBeExchangedToLetters())
                            <button type="button"
                                    class="btn-action-icon btn-letter-exchange"
                                    title="Canjear a letras"
                                    data-open-letter-exchange
                                    data-invoice-id="{{ $invoice->id }}"
                                    data-invoice-number="{{ $invoice->serie_numero }}"
                                    data-client="{{ $invoice->client->nombre_razon_social ?? '—' }}"
                                    data-currency="{{ $invoice->codigo_moneda }}"
                                    data-pending="{{ number_format($invoice->pendingAmountForLetters(), 2, '.', '') }}">
                              <i class='bx bx-transfer'></i>
                            </button>
                          @elseif($invoice->hasBeenExchangedToLetters())
                            <a href="{{ route('facturador.letras.index', ['search' => $invoice->serie_numero]) }}" class="btn-action-icon" title="Canjeada a letras">
                              <i class='bx bx-check-double'></i>
                            </a>
                          @endif
                          @if($invoice->xml_path)
                            <a href="{{ route('facturador.invoices.xml', $invoice) }}" class="btn-action-icon" title="Descargar XML">
                              <i class='bx bx-download'></i>
                            </a>
                          @endif
                          @can('delete', $invoice)
                            <form method="POST" action="{{ route('facturador.invoices.destroy', $invoice) }}"
                                  data-confirm="¿Eliminar {{ $invoice->serie_numero }}?" style="display:inline;">
                              @csrf @method('DELETE')
                              <button type="submit" class="btn-action-icon" title="Eliminar" style="color:#ef4444;"><i class='bx bx-trash'></i></button>
                            </form>
                          @endcan
                          @can('void', $invoice)
                            @if($invoice->canBeVoided())
                              <form method="POST" action="{{ route('facturador.invoices.void', $invoice) }}"
                                    id="form-void-det-{{ $invoice->id }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="motivo" id="void-motivo-det-{{ $invoice->id }}" value="">
                                <button type="button" class="btn-action-icon btn-void-idx"
                                        data-void-id="det-{{ $invoice->id }}"
                                        data-serie="{{ $invoice->serie_numero }}"
                                        title="Anular" style="color:#dc2626;">
                                  <i class='bx bx-x-circle'></i>
                                </button>
                              </form>
                            @endif
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="16" style="text-align:center; color:var(--clr-text-muted);">No hay comprobantes registrados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($invoices->hasPages())
              <div style="margin-top:1rem;">{{ $invoices->links() }}</div>
            @endif
          </div>

        </div>
      </main>
    </section>
  </div>

  {{-- ── Modales contables ── --}}
  @include('facturador.invoices.partials.accounting-modal')
  @include('facturador.invoices.partials.export-modal')

  <div class="letter-modal-backdrop" data-letter-modal>
    <form method="POST" class="letter-modal" data-letter-form>
      @csrf
      <div class="letter-modal__head">
        <h2>Canjear factura a letras</h2>
        <button type="button" class="btn-action-icon" data-letter-close aria-label="Cerrar"><i class='bx bx-x'></i></button>
      </div>
      <div class="letter-modal__body">
        <div class="letter-summary">
          <div><span>Comprobante</span><strong data-letter-invoice>—</strong></div>
          <div><span>Cliente</span><strong data-letter-client>—</strong></div>
          <div><span>Total pendiente</span><strong data-letter-pending>—</strong></div>
        </div>

        <div class="letter-grid" style="grid-template-columns:150px 1fr;">
          <div>
            <label>Moneda</label>
            <select name="currency" class="letter-input" data-letter-currency>
              <option value="PEN">PEN</option>
              <option value="USD">USD</option>
            </select>
          </div>
          <div>
            <label>Observación general</label>
            <input type="text" name="observation" class="letter-input" maxlength="500" placeholder="Opcional">
          </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem;">
          <strong style="color:var(--clr-text-main,#111827);">Letras</strong>
          <button type="button" class="btn-secondary" data-add-letter style="font-size:.82rem;">
            <i class='bx bx-plus'></i> Agregar letra
          </button>
        </div>

        <div data-letter-rows></div>

        <div class="letter-total-line" data-letter-total-line>
          <span>Suma de letras</span>
          <span><strong data-letter-total>0.00</strong> / <strong data-letter-pending-inline>0.00</strong></span>
        </div>
      </div>
      <div class="letter-modal__footer">
        <button type="button" class="btn-secondary" data-letter-close>Cancelar</button>
        <button type="submit" class="btn-primary" data-letter-submit>
          <i class='bx bx-save'></i> Confirmar canje
        </button>
      </div>
    </form>
  </div>

@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Rutas disponibles para los partials (sin exponer tokens en el HTML)
    window.AccountingRoutes = {
      get:         '{{ url('facturador/invoices') }}/:id/accounting',
      save:        '{{ url('facturador/invoices') }}/:id/accounting',
      exportCount: '{{ route('facturador.invoices.export-count') }}',
    };
    const letterModal = document.querySelector('[data-letter-modal]');
    const letterForm = document.querySelector('[data-letter-form]');
    const letterRows = document.querySelector('[data-letter-rows]');
    const letterTotal = document.querySelector('[data-letter-total]');
    const letterPendingInline = document.querySelector('[data-letter-pending-inline]');
    const letterTotalLine = document.querySelector('[data-letter-total-line]');
    const letterSubmit = document.querySelector('[data-letter-submit]');
    let letterPendingAmount = 0;

    const money = (value) => Number(value || 0).toFixed(2);
    const recalcLetters = () => {
      const total = Array.from(letterRows?.querySelectorAll('[data-letter-amount]') || [])
        .reduce((sum, input) => sum + Number(input.value || 0), 0);
      const valid = Math.abs(total - letterPendingAmount) <= 0.01;
      if (letterTotal) letterTotal.textContent = money(total);
      letterTotalLine?.classList.toggle('is-invalid', !valid);
      if (letterSubmit) letterSubmit.disabled = !valid;
    };
    const addLetterRow = (amount = '') => {
      const index = letterRows.children.length;
      const row = document.createElement('div');
      row.className = 'letter-grid';
      row.innerHTML = `
        <div>
          <label>Fecha de vencimiento</label>
          <input type="date" name="letters[${index}][due_date]" class="letter-input" required>
        </div>
        <div>
          <label>Monto</label>
          <input type="number" name="letters[${index}][amount]" class="letter-input" step="0.01" min="0.01" value="${amount}" data-letter-amount required>
        </div>
        <div>
          <label>Observación</label>
          <input type="text" name="letters[${index}][observation]" class="letter-input" maxlength="500" placeholder="Opcional">
        </div>
        <button type="button" class="letter-remove" data-remove-letter title="Quitar"><i class='bx bx-trash'></i></button>`;
      letterRows.appendChild(row);
      row.querySelector('[data-letter-amount]')?.addEventListener('input', recalcLetters);
      row.querySelector('[data-remove-letter]')?.addEventListener('click', () => {
        row.remove();
        recalcLetters();
      });
      recalcLetters();
    };
    document.querySelectorAll('[data-open-letter-exchange]').forEach((button) => {
      button.addEventListener('click', () => {
        letterPendingAmount = Number(button.dataset.pending || 0);
        letterForm.action = `{{ url('facturador/invoices') }}/${button.dataset.invoiceId}/exchange-letters`;
        document.querySelector('[data-letter-invoice]').textContent = button.dataset.invoiceNumber || '—';
        document.querySelector('[data-letter-client]').textContent = button.dataset.client || '—';
        document.querySelector('[data-letter-pending]').textContent = `${button.dataset.currency} ${money(letterPendingAmount)}`;
        if (letterPendingInline) letterPendingInline.textContent = money(letterPendingAmount);
        const currency = document.querySelector('[data-letter-currency]');
        if (currency) currency.value = button.dataset.currency || 'PEN';
        letterRows.innerHTML = '';
        addLetterRow(money(letterPendingAmount));
        letterModal?.classList.add('is-open');
      });
    });
    document.querySelector('[data-add-letter]')?.addEventListener('click', () => addLetterRow(''));
    document.querySelectorAll('[data-letter-close]').forEach((button) => {
      button.addEventListener('click', () => letterModal?.classList.remove('is-open'));
    });
    letterModal?.addEventListener('click', (event) => {
      if (event.target === letterModal) letterModal.classList.remove('is-open');
    });

    document.getElementById('invoice-import-file')?.addEventListener('change', function () {
      if (this.files?.length) {
        document.getElementById('invoice-import-form')?.submit();
      }
    });
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4000);
    });

    // ── Toggle vista compacta / detallada ────────────────────────────────
    const btnToggle       = document.getElementById('btn-toggle-detalle');
    const tablaCompacta   = document.getElementById('tabla-compacta');
    const tablaDetallada  = document.getElementById('tabla-detallada');

    btnToggle?.addEventListener('click', function () {
      const isDetailed = tablaDetallada?.classList.contains('active');
      if (isDetailed) {
        tablaDetallada.classList.remove('active');
        tablaCompacta.classList.remove('hidden');
        this.classList.remove('active');
        this.innerHTML = "<i class='bx bx-table'></i> Más detalle";
      } else {
        tablaDetallada.classList.add('active');
        tablaCompacta.classList.add('hidden');
        this.classList.add('active');
        this.innerHTML = "<i class='bx bx-list-ul'></i> Vista compacta";
      }
    });

    document.querySelectorAll('.btn-void-idx').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const id    = this.dataset.voidId;
        const serie = this.dataset.serie;
        Swal.fire({
          title: 'Anular ' + serie,
          html: '<p style="margin-bottom:.5rem;font-size:.9rem;">Indica el motivo de anulación:</p>',
          input: 'text',
          inputPlaceholder: 'Ej: Error en el monto',
          inputAttributes: { maxlength: 200 },
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Anular',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#dc2626',
          inputValidator: function(value) {
            if (!value || !value.trim()) return 'Debes ingresar un motivo.';
          }
        }).then(function(result) {
          if (result.isConfirmed) {
            document.getElementById('void-motivo-' + id).value = result.value.trim();
            document.getElementById('form-void-' + id).submit();
          }
        });
      });
    });
  </script>
@endpush
