@extends('layouts.app')

@section('title', 'Letras de Cambio — Facturador')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/facturador.css') }}">
  <style>
    .main-wrapper { min-width:0; }
    .main-content { padding:1.35rem 1.5rem; overflow-x:hidden; }
    .letras-shell { max-width:1280px; margin:0 auto; padding:1.45rem 1.55rem; border-radius:14px; }
    .letras-shell:hover { transform:none; }
    .letras-header { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; margin-bottom:1.2rem; }
    .letras-title { display:flex; align-items:center; gap:.65rem; margin:0; font-size:1.55rem; line-height:1.1; }
    .letras-title i { width:38px; height:38px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:#e7f3ef; color:#16614f; font-size:1.35rem; }
    .letras-subtitle { margin:.35rem 0 0; color:#64748b; font-size:.88rem; }
    .letras-actions-top { display:flex; gap:.55rem; flex-wrap:wrap; justify-content:flex-end; }
    .letras-shell .stat-cards { grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; margin-bottom:1rem; }
    .letras-shell .stat-card { border-radius:10px; padding:.9rem 1rem; box-shadow:none; }
    .letras-shell .stat-card__icon { width:32px; height:32px; border-radius:8px; font-size:1.1rem; margin-bottom:.15rem; }
    .letras-shell .stat-card__val { font-size:1.2rem; }
    .letras-shell .filter-bar { margin-bottom:1rem; padding:.85rem; border-radius:10px; box-shadow:none; }
    .letras-shell .filter-bar input,
    .letras-shell .filter-bar select { min-height:38px; background:#fff; }
    .letras-note { display:flex; align-items:center; gap:.5rem; margin:1rem 0; padding:.75rem .9rem; border:1px solid #e5eaf0; border-radius:8px; background:#fbfdff; color:#475569; font-size:.84rem; }
    .letras-table-panel { border:1px solid #e5eaf0; border-radius:10px; overflow:auto; background:#fff; }
    .letra-table { min-width:920px; }
    .letra-table thead th { position:sticky; top:0; z-index:1; background:#f8fafc; color:#64748b; font-size:.72rem; text-transform:uppercase; letter-spacing:.02em; border-bottom:1px solid #e5eaf0; }
    .letra-table tbody tr.letra-row:hover { background:#fbfdff; }
    .letra-table td { vertical-align:middle; }
    .letra-group-row { background:#f8fafc; }
    .letra-group-row td { padding:.75rem 1rem; border-top:1px solid #e5eaf0; border-bottom:1px solid #e5eaf0; }
    .letra-group-box { display:flex; align-items:center; justify-content:space-between; gap:1rem; }
    .letra-group-main { min-width:0; }
    .letra-group-title { display:flex; align-items:center; gap:.45rem; font-weight:900; color:#0f172a; }
    .letra-group-title span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .letra-group-sub { margin-top:.18rem; color:#64748b; font-size:.78rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .letra-group-stats { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; justify-content:flex-end; color:#475569; font-size:.78rem; white-space:nowrap; }
    .letra-group-stats strong { color:#0f172a; }
    .letra-group-link { color:#16614f; text-decoration:none; font-weight:800; }
    .letra-money { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .letra-money strong { display:block; }
    .letra-actions { display:flex; justify-content:flex-end; gap:.35rem; }
    .letra-actions .btn-action-icon { width:34px; height:34px; border-radius:8px; }
    .letra-doc { max-width:260px; }
    .letra-doc strong { display:block; color:#0f172a; }
    .letra-doc a { color:#16614f; font-weight:800; text-decoration:none; font-size:.78rem; }
    .letra-client { max-width:320px; }
    .letra-client strong { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .letra-muted { color:#64748b; font-size:.78rem; line-height:1.35; }
    .truncate-text { display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:bottom; }
    .letra-table th:last-child { min-width:120px; }
    .letra-code { font-family:ui-monospace,SFMono-Regular,Consolas,monospace; font-weight:800; color:#0f172a; }
    .letra-date { font-size:.86rem; white-space:nowrap; color:#1f2937; }
    .letras-scroll-hint { display:none; color:#64748b; font-size:.76rem; margin:.55rem .15rem 0; }
    .comp-info { display:none; margin-bottom:1rem; padding:.75rem .9rem; background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; border-radius:8px; font-size:.82rem; line-height:1.45; }
    .comp-party { display:none; margin-bottom:1rem; padding:.75rem .9rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; }
    .comp-party__label { font-size:.74rem; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:.2rem; }
    .comp-party__value { font-weight:800; color:#111827; }
    .comp-party__sub { font-size:.8rem; color:#64748b; margin-top:.1rem; }
    .comp-fields { display:none; }
    .comp-table-wrap { border:1px solid #e5e7eb; border-radius:8px; overflow:auto; max-height:260px; }
    .comp-table { width:100%; border-collapse:collapse; font-size:.8rem; min-width:760px; }
    .comp-table th { background:#f8fafc; color:#64748b; text-align:left; padding:.55rem .65rem; font-size:.72rem; text-transform:uppercase; }
    .comp-table td { padding:.55rem .65rem; border-top:1px solid #f1f5f9; vertical-align:middle; }
    .comp-table .num { text-align:right; font-variant-numeric:tabular-nums; }
    .comp-amount { width:120px; padding:.42rem .55rem; border:1px solid #dbe3ef; border-radius:6px; text-align:right; }
    .comp-total { display:flex; justify-content:space-between; gap:1rem; align-items:center; margin-top:.7rem; font-size:.86rem; }
    @media (max-width:1100px) {
      .letras-shell .stat-cards { grid-template-columns:repeat(2,minmax(0,1fr)); }
      .letra-group-box { align-items:flex-start; flex-direction:column; }
      .letra-group-stats { justify-content:flex-start; }
      .letras-scroll-hint { display:block; }
    }
    @media (max-width:720px) {
      .main-content { padding:.75rem; }
      .letras-shell { padding:1rem; }
      .letras-header { flex-direction:column; }
      .letras-actions-top { justify-content:flex-start; }
      .letras-shell .stat-cards { grid-template-columns:1fr; }
    }
  </style>
@endpush

@section('content')
<div class="app-layout">
  <aside class="sidebar-premium">
    <div class="sidebar-header">
      <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
      <div class="header-text"><h2>Portal Mendieta</h2><p>Panel interno</p></div>
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
        <div class="placeholder-content module-card-wide letras-shell">

  {{-- Header --}}
  <div class="letras-header">
    <div>
      <h1 class="letras-title">
        <i class='bx bx-file'></i> Letras de Cambio
      </h1>
      <p class="letras-subtitle">Control de vencimientos, saldos, pagos y compensaciones de letras por cobrar.</p>
    </div>
    <div class="letras-actions-top">
      <a href="{{ route('facturador.compras.index') }}" class="btn-secondary" style="font-size:.85rem; padding:.5rem .9rem;">
        <i class='bx bx-arrow-back'></i> Volver a Compras
      </a>
    </div>
  </div>

  {{-- Alertas --}}
  @if(session('success'))
    <div class="placeholder-content module-alert" data-flash-message style="margin-bottom:1rem;">
      <p>{{ session('success') }}</p>
      <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
    </div>
  @endif

  {{-- Stats --}}
  <div class="stat-cards">
    <div class="stat-card sc-amber">
      <div class="stat-card__icon"><i class='bx bx-time-five'></i></div>
      <div class="stat-card__val">{{ number_format($stats['total_pendiente'], 2) }}</div>
      <div class="stat-card__lbl">Total Pendiente</div>
      <div class="stat-card__sub">{{ $stats['count_pendiente'] }} letra(s)</div>
    </div>
    <div class="stat-card sc-red">
      <div class="stat-card__icon"><i class='bx bx-error-circle'></i></div>
      <div class="stat-card__val">{{ $stats['count_vencidas'] }}</div>
      <div class="stat-card__lbl">Vencidas</div>
      <div class="stat-card__sub">Requieren gestión urgente</div>
    </div>
    <div class="stat-card sc-green">
      <div class="stat-card__icon"><i class='bx bx-check-circle'></i></div>
      <div class="stat-card__val">{{ number_format($stats['total_cobrado_mes'], 2) }}</div>
      <div class="stat-card__lbl">Cobrado este mes</div>
    </div>
    <div class="stat-card sc-blue">
      <div class="stat-card__icon"><i class='bx bxs-file-doc'></i></div>
      <div class="stat-card__val">{{ $letras->total() }}</div>
      <div class="stat-card__lbl">Total letras</div>
    </div>
  </div>

  {{-- Filtros --}}
  <form method="GET" action="{{ route('facturador.letras.index') }}" class="filter-bar">
    <i class='bx bx-filter-alt' style="font-size:1.25rem; color:var(--clr-text-muted);"></i>
    <input type="text" name="search" placeholder="Buscar por número, aceptante o RUC..."
           value="{{ $filters['search'] ?? '' }}" style="min-width:220px;">
    <select name="estado">
      <option value="">Todos los estados</option>
      <option value="pendiente"  @selected(($filters['estado'] ?? '') === 'pendiente')>Pendiente</option>
      <option value="compensada_parcial" @selected(($filters['estado'] ?? '') === 'compensada_parcial')>Parcialmente compensada</option>
      <option value="compensada" @selected(($filters['estado'] ?? '') === 'compensada')>Endosada / Compensada</option>
      <option value="cobrado"    @selected(($filters['estado'] ?? '') === 'cobrado')>Cobrado</option>
      <option value="protestado" @selected(($filters['estado'] ?? '') === 'protestado')>Protestado</option>
    </select>
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" title="Vencimiento desde">
    <input type="date" name="to"   value="{{ $filters['to'] ?? '' }}"   title="Vencimiento hasta">
    <button type="submit" class="btn-primary" style="font-size:.85rem; padding:.5rem .9rem;">
      <i class='bx bx-search'></i> Filtrar
    </button>
    @if(array_filter($filters))
      <a href="{{ route('facturador.letras.index') }}" class="btn-secondary" style="font-size:.85rem; padding:.5rem .9rem;">
        <i class='bx bx-x'></i> Limpiar
      </a>
    @endif
  </form>

  <div class="letras-note">
    <i class='bx bx-info-circle'></i>
    <span>Agrupado por comprobante: primero ves el total de la factura y debajo sus letras pendientes.</span>
  </div>

  {{-- Tabla --}}
  <div class="letras-table-panel">
    <table class="module-table letra-table" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th style="padding:.75rem 1rem; text-align:left;">Letra / Comprobante</th>
          <th style="padding:.75rem 1rem; text-align:left;">Aceptante</th>
          <th style="padding:.75rem 1rem; text-align:left;">Vencimiento</th>
          <th style="padding:.75rem 1rem; text-align:right;">Importe</th>
          <th style="padding:.75rem 1rem; text-align:left;">Estado</th>
          <th style="padding:.75rem 1rem; text-align:right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($letras->getCollection()->groupBy(fn ($item) => ($item->invoice_id ?: 'sin_factura') . '|' . $item->codigo_moneda) as $group)
          @php
            $first = $group->first();
            $groupTotal = $group->sum('monto');
            $groupSaldo = $group->sum('saldo');
            $groupPaid = $group->sum('monto_pagado');
          @endphp
          <tr class="letra-group-row">
            <td colspan="6">
              <div class="letra-group-box">
                <div class="letra-group-main">
                  <div class="letra-group-title">
                    <i class='bx bx-receipt'></i>
                  <span>{{ $first->invoice?->serie_numero ?? $first->referencia ?? 'Sin comprobante' }}</span>
                </div>
                <div class="letra-group-sub">
                    Cliente al que se emitió: {{ $first->aceptante_nombre }} · {{ $first->aceptante_ruc }}
                </div>
                </div>
                <div class="letra-group-stats">
                  <span>{{ $group->count() }} letra(s)</span>
                  <span>Total: <strong>{{ $first->codigo_moneda }} {{ number_format($groupTotal, 2) }}</strong></span>
                  <span>Pagado/comp.: <strong>{{ $first->codigo_moneda }} {{ number_format($groupPaid, 2) }}</strong></span>
                  <span>Saldo: <strong>{{ $first->codigo_moneda }} {{ number_format($groupSaldo, 2) }}</strong></span>
                  @if($first->invoice)
                    <a href="{{ route('facturador.invoices.show', $first->invoice) }}" class="letra-group-link">Ver comprobante <i class='bx bx-link-external'></i></a>
                  @endif
                </div>
              </div>
            </td>
          </tr>

          @foreach($group as $letra)
            @php $vencida = $letra->esta_vencida; @endphp
            <tr class="letra-row" style="border-bottom:1px solid var(--clr-border-light,#f3f4f6); {{ $vencida ? 'background:rgba(245,158,11,.04);' : '' }}">
              <td style="padding:.8rem 1rem;" class="letra-doc">
                <strong>{{ $letra->numero_letra }}</strong>
                <div class="letra-muted">Giro: {{ $letra->fecha_giro->format('d/m/Y') }}</div>
              </td>
              <td style="padding:.7rem 1rem;" class="letra-client">
                <strong title="{{ $letra->aceptante_nombre }}">{{ $letra->aceptante_nombre }}</strong>
                <div class="letra-muted">{{ $letra->aceptante_ruc }}</div>
              </td>
              <td style="padding:.7rem 1rem;">
                <span class="letra-date" style="{{ $vencida ? 'color:#dc2626; font-weight:700;' : '' }}">
                  {{ $letra->fecha_vencimiento->format('d/m/Y') }}
                </span>
                @if($vencida)
                  <div style="font-size:.75rem; color:#dc2626;">{{ $letra->fecha_vencimiento->diffForHumans() }}</div>
                @endif
              </td>
              <td style="padding:.7rem 1rem;" class="letra-money">
                <strong>{{ $letra->codigo_moneda }} {{ number_format($letra->monto, 2) }}</strong>
                @if($letra->monto_pagado > 0)
                  <div class="letra-muted">Saldo: <span style="font-weight:800; color:{{ $letra->saldo > 0 ? '#d97706' : '#059669' }};">{{ $letra->codigo_moneda }} {{ number_format($letra->saldo, 2) }}</span></div>
                  <div class="progress-bar-wrap">
                    <div class="progress-bar-fill"
                         style="width:{{ min(100, round($letra->monto_pagado / $letra->monto * 100)) }}%;
                                background:{{ $letra->saldo == 0 ? '#059669' : '#d97706' }};"></div>
                  </div>
                @endif
              </td>
              <td style="padding:.75rem 1rem;">
                <span class="letra-badge badge-{{ $letra->estado }}">
                  {{ $letra->estado_label }}
                </span>
              </td>
              <td style="padding:.75rem 1rem;">
                <div class="letra-actions">
                  <a href="{{ route('facturador.letras.show', $letra) }}"
                     class="btn-action-icon" title="Ver detalle">
                    <i class='bx bx-show'></i>
                  </a>
                  <a href="{{ route('facturador.letras.imprimir', $letra) }}"
                     class="btn-action-icon" title="Imprimir letra" target="_blank">
                    <i class='bx bx-printer'></i>
                  </a>
                  @if(in_array($letra->estado, ['pendiente', 'compensada_parcial'], true))
                    <button type="button" class="btn-action-icon btn-pago"
                            style="color:#059669;"
                            data-id="{{ $letra->id }}"
                            data-monto="{{ $letra->monto }}"
                            data-saldo="{{ $letra->saldo }}"
                            data-moneda="{{ $letra->codigo_moneda }}"
                            data-aceptante="{{ $letra->aceptante_nombre }}"
                            data-aceptante-ruc="{{ $letra->aceptante_ruc }}"
                            data-comprobante="{{ $letra->invoice?->serie_numero ?? $letra->referencia ?? '' }}"
                            title="Registrar pago">
                      <i class='bx bx-dollar'></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        @empty
          <tr>
            <td colspan="6" style="padding:2.5rem; text-align:center; color:var(--clr-text-muted,#6b7280);">
              <i class='bx bx-file-blank' style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
              No hay letras de cambio con los filtros seleccionados.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="letras-scroll-hint">Desliza horizontalmente la tabla para ver todas las acciones.</div>

  <div style="margin-top:1rem;">
    {{ $letras->links() }}
  </div>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}

{{-- Modal Registrar Pago --}}
<div class="modal-overlay" id="modalPago" onclick="if(event.target===this) cerrarModalPago()">
  <div class="modal-card" style="max-width:860px;">
    <div class="modal-header">
      <h3 id="pagoModalTitle" style="font-size:.95rem;margin:0;"><i class='bx bx-dollar' style="margin-right:.35rem;"></i>Registrar Pago</h3>
      <button type="button" onclick="cerrarModalPago()" style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:#64748b;line-height:1;">
        <i class="bx bx-x"></i>
      </button>
    </div>
    <div class="modal-body" style="padding:1.25rem 1.5rem;">
      <div style="margin-bottom:1rem;padding:.75rem 1rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
        <span style="font-size:.82rem;color:#64748b;">Saldo pendiente:</span>
        <span id="pagoSaldoLabel" style="font-weight:700;margin-left:.35rem;color:#111827;"></span>
      </div>
      <div id="compInfo" class="comp-info">
        Esta operación no cobra al cliente en banco. Endosa la letra a un proveedor y la aplica contra una o más facturas pendientes por pagar.
      </div>
      <div id="compParty" class="comp-party">
        <div class="comp-party__label">Cliente / aceptante de la letra</div>
        <div id="compPartyName" class="comp-party__value">—</div>
        <div id="compPartyDoc" class="comp-party__sub">—</div>
      </div>
      <div style="margin-bottom:1rem;">
        <label id="pagoFechaLabel" style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Fecha de pago <span style="color:#ef4444;">*</span></label>
        <input type="date" id="pagoFecha" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;" value="{{ now()->toDateString() }}">
      </div>
      <div id="pagoNormalMonto" style="margin-bottom:1rem;">
        <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Monto pagado <span style="color:#ef4444;">*</span></label>
        <input type="number" id="pagoMonto" step="0.01" min="0.01" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Medio de pago</label>
        <select id="pagoMedio" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;background:#fff;">
          <option value="transferencia">Transferencia bancaria</option>
          <option value="efectivo">Efectivo</option>
          <option value="cheque">Cheque</option>
          <option value="yape">Yape</option>
          <option value="plin">Plin</option>
          <option value="compensacion">Endoso de letra / Compensación</option>
        </select>
      </div>
      <div id="pagoNormalReferencia" style="margin-bottom:1rem;">
        <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Referencia / N° operación</label>
        <input type="text" id="pagoReferencia" placeholder="Ej: 00123456" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;">
      </div>
      <div id="compFields" class="comp-fields">
        <div style="margin-bottom:1rem;">
          <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Proveedor que recibe la letra para compensar <span style="color:#ef4444;">*</span></label>
          <select id="compProvider" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;background:#fff;">
            <option value="">— Primero selecciona Endoso / Compensación —</option>
          </select>
          <div id="compProviderHint" style="font-size:.78rem;color:#64748b;margin-top:.35rem;">
            Solo se mostrarán proveedores con facturas de compra pendientes en la misma moneda de la letra.
          </div>
        </div>
        <div class="comp-table-wrap">
          <table class="comp-table">
            <thead>
              <tr>
                <th>Comprobante</th>
                <th>Emisión</th>
                <th>Moneda</th>
                <th class="num">Total</th>
                <th class="num">Saldo</th>
                <th>Estado</th>
                <th class="num">Monto a compensar</th>
              </tr>
            </thead>
            <tbody id="compInvoicesBody">
              <tr><td colspan="7" style="text-align:center;color:#64748b;padding:1rem;">Selecciona un proveedor para ver sus facturas pendientes.</td></tr>
            </tbody>
          </table>
        </div>
        <div class="comp-total">
          <span id="compHelper" style="color:#64748b;">Solo se muestran facturas en la misma moneda de la letra.</span>
          <strong>Total a compensar: <span id="compTotalLabel">0.00</span></strong>
        </div>
        <div style="margin-top:1rem;">
          <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Observación o referencia interna</label>
          <textarea id="compObservation" rows="2" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;resize:vertical;" placeholder="Ej: Aplicar letra contra factura pendiente del proveedor"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:.5rem;padding:1rem 1.5rem;border-top:1px solid #e2e8f0;">
      <button type="button" onclick="cerrarModalPago()" style="padding:.55rem 1.25rem;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#374151;font-size:.85rem;font-weight:600;cursor:pointer;">
        Cancelar
      </button>
      <button type="button" id="btnConfirmarPago" style="padding:.55rem 1.25rem;border:none;border-radius:8px;background:#10b981;color:#fff;font-size:.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;">
        <span id="btnPagoSpinner" style="display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-right-color:#fff;border-radius:50%;animation:spinner-border .75s linear infinite;"></span>
        <span id="btnPagoText">Confirmar Pago</span>
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let pagoLetraId = null;
  let pagoSaldo = 0;
  let pagoMoneda = 'PEN';

  const pagoMedio = document.getElementById('pagoMedio');
  const compProvider = document.getElementById('compProvider');
  const compInvoicesBody = document.getElementById('compInvoicesBody');

  function abrirModalPago() {
    document.getElementById('modalPago').classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function cerrarModalPago() {
    document.getElementById('modalPago').classList.remove('show');
    document.body.style.overflow = '';
  }

  function isCompensacionMode() {
    return pagoMedio.value === 'compensacion';
  }

  function togglePagoMode() {
    const comp = isCompensacionMode();
    document.getElementById('pagoModalTitle').innerHTML = comp
      ? "<i class='bx bx-transfer' style='margin-right:.35rem;'></i>Compensar letra con factura de proveedor"
      : "<i class='bx bx-dollar' style='margin-right:.35rem;'></i>Registrar Pago";
    document.getElementById('pagoFechaLabel').innerHTML = comp
      ? 'Fecha de compensación <span style="color:#ef4444;">*</span>'
      : 'Fecha de pago <span style="color:#ef4444;">*</span>';
    document.getElementById('pagoNormalMonto').style.display = comp ? 'none' : 'block';
    document.getElementById('pagoNormalReferencia').style.display = comp ? 'none' : 'block';
    document.getElementById('compInfo').style.display = comp ? 'block' : 'none';
    document.getElementById('compParty').style.display = comp ? 'block' : 'none';
    document.getElementById('compFields').style.display = comp ? 'block' : 'none';
    document.getElementById('btnPagoText').textContent = comp ? 'Confirmar compensación' : 'Confirmar Pago';
    recalcCompTotal();
  }

  function resetCompensationFields() {
    compProvider.innerHTML = '<option value="">— Primero selecciona Endoso / Compensación —</option>';
    compProvider.value = '';
    document.getElementById('compObservation').value = '';
    compInvoicesBody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#64748b;padding:1rem;">Selecciona un proveedor para ver sus facturas pendientes.</td></tr>';
    recalcCompTotal();
  }

  function formatMoney(value) {
    return Number(value || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function recalcCompTotal() {
    const total = Array.from(document.querySelectorAll('.comp-amount'))
      .reduce((sum, input) => sum + Number(input.value || 0), 0);
    document.getElementById('compTotalLabel').textContent = `${pagoMoneda} ${formatMoney(total)}`;
    document.getElementById('compHelper').textContent = total > pagoSaldo
      ? `El total supera el saldo disponible de la letra (${pagoMoneda} ${formatMoney(pagoSaldo)}).`
      : 'Solo se muestran facturas en la misma moneda de la letra.';
    document.getElementById('compHelper').style.color = total > pagoSaldo ? '#dc2626' : '#64748b';
    return Math.round(total * 100) / 100;
  }

  async function loadProviderInvoices() {
    const providerId = compProvider.value;
    if (!providerId) {
      resetCompensationFields();
      return;
    }

    compInvoicesBody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#64748b;padding:1rem;">Cargando facturas pendientes...</td></tr>';

    try {
      const res = await fetch(`/facturador/letras/${pagoLetraId}/compensation-candidates?provider_id=${encodeURIComponent(providerId)}`);
      const data = await res.json();

      if (!data.ok || !data.purchases.length) {
        compInvoicesBody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#64748b;padding:1rem;">No hay facturas pendientes en la misma moneda.</td></tr>';
        recalcCompTotal();
        return;
      }

      compInvoicesBody.innerHTML = data.purchases.map(purchase => `
        <tr>
          <td><strong>${purchase.serie_numero}</strong></td>
          <td>${purchase.fecha_emision ?? '—'}</td>
          <td>${purchase.moneda}</td>
          <td class="num">${formatMoney(purchase.total)}</td>
          <td class="num"><strong>${formatMoney(purchase.saldo)}</strong></td>
          <td>${purchase.estado}</td>
          <td class="num">
            <input type="number" class="comp-amount" min="0" step="0.01" max="${purchase.saldo}" data-purchase-id="${purchase.id}" data-saldo="${purchase.saldo}" placeholder="0.00">
          </td>
        </tr>
      `).join('');

      document.querySelectorAll('.comp-amount').forEach(input => {
        input.addEventListener('input', function () {
          const max = Number(this.dataset.saldo || 0);
          if (Number(this.value || 0) > max) this.value = max.toFixed(2);
          recalcCompTotal();
        });
      });
      recalcCompTotal();
    } catch (e) {
      compInvoicesBody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#dc2626;padding:1rem;">No se pudieron cargar las facturas pendientes.</td></tr>';
    }
  }

  async function loadCompensationSuppliers() {
    compProvider.innerHTML = '<option value="">Cargando proveedores con facturas pendientes...</option>';
    compInvoicesBody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#64748b;padding:1rem;">Selecciona un proveedor para ver sus facturas pendientes.</td></tr>';

    try {
      const res = await fetch(`/facturador/letras/${pagoLetraId}/compensation-suppliers`, {
        headers: { 'Accept': 'application/json' },
      });
      const data = await res.json();

      if (!data.ok || !data.suppliers.length) {
        compProvider.innerHTML = '<option value="">— No hay proveedores con facturas pendientes en esta moneda —</option>';
        document.getElementById('compProviderHint').textContent = `No hay facturas de compra pendientes en ${pagoMoneda} para compensar esta letra.`;
        return;
      }

      compProvider.innerHTML = '<option value="">— Seleccionar proveedor con deuda pendiente —</option>' + data.suppliers.map(supplier => {
        const label = `${supplier.name}${supplier.document ? ' — ' + supplier.document : ''} · ${supplier.pending_invoices} factura(s) · ${supplier.currency} ${formatMoney(supplier.pending_balance)}`;
        return `<option value="${supplier.id}">${label}</option>`;
      }).join('');
      document.getElementById('compProviderHint').textContent = `Mostrando solo proveedores con facturas de compra pendientes en ${pagoMoneda}.`;
    } catch (e) {
      compProvider.innerHTML = '<option value="">— No se pudieron cargar proveedores —</option>';
      document.getElementById('compProviderHint').textContent = 'No se pudieron cargar los proveedores pendientes. Intenta nuevamente.';
    }
  }

  document.querySelectorAll('.btn-pago').forEach(btn => {
    btn.addEventListener('click', function () {
      pagoLetraId = this.dataset.id;
      pagoSaldo = parseFloat(this.dataset.saldo);
      pagoMoneda = this.dataset.moneda;
      document.getElementById('pagoSaldoLabel').textContent = pagoMoneda + ' ' + pagoSaldo.toFixed(2);
      document.getElementById('pagoMonto').value = pagoSaldo.toFixed(2);
      document.getElementById('pagoMonto').max   = pagoSaldo;
      pagoMedio.value = 'transferencia';
      document.getElementById('pagoReferencia').value = '';
      document.getElementById('compPartyName').textContent = this.dataset.aceptante || '—';
      document.getElementById('compPartyDoc').textContent = [
        this.dataset.aceptanteRuc ? `RUC/DNI: ${this.dataset.aceptanteRuc}` : null,
        this.dataset.comprobante ? `Comprobante: ${this.dataset.comprobante}` : null,
      ].filter(Boolean).join(' · ') || '—';
      resetCompensationFields();
      togglePagoMode();
      abrirModalPago();
    });
  });

  pagoMedio.addEventListener('change', function () {
    togglePagoMode();
    if (isCompensacionMode()) {
      loadCompensationSuppliers();
    }
  });
  compProvider.addEventListener('change', loadProviderInvoices);

  document.getElementById('btnConfirmarPago').addEventListener('click', async function () {
    const spinner = document.getElementById('btnPagoSpinner');
    spinner.style.display = 'inline-block';
    this.disabled = true;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const comp = isCompensacionMode();
    let url = `/facturador/letras/${pagoLetraId}/pago`;
    let body = {
      _token: csrf,
      fecha_pago: document.getElementById('pagoFecha').value,
      monto_pagado: document.getElementById('pagoMonto').value,
      medio_pago: pagoMedio.value,
      referencia_pago: document.getElementById('pagoReferencia').value,
    };

    if (comp) {
      const details = Array.from(document.querySelectorAll('.comp-amount'))
        .map(input => ({
          purchase_invoice_id: input.dataset.purchaseId,
          amount: Number(input.value || 0),
        }))
        .filter(detail => detail.amount > 0);
      const total = recalcCompTotal();

      if (!compProvider.value) {
        Swal.fire({icon:'warning', title:'Proveedor requerido', text:'Selecciona el proveedor a compensar.'});
        spinner.style.display = 'none';
        this.disabled = false;
        return;
      }
      if (!details.length) {
        Swal.fire({icon:'warning', title:'Facturas requeridas', text:'Indica al menos una factura y un monto a compensar.'});
        spinner.style.display = 'none';
        this.disabled = false;
        return;
      }
      if (total > pagoSaldo) {
        Swal.fire({icon:'warning', title:'Monto excedido', text:'El total aplicado no puede superar el saldo de la letra.'});
        spinner.style.display = 'none';
        this.disabled = false;
        return;
      }

      url = `/facturador/letras/${pagoLetraId}/compensate`;
      body = {
        _token: csrf,
        compensation_date: document.getElementById('pagoFecha').value,
        supplier_id: compProvider.value,
        details,
        observation: document.getElementById('compObservation').value,
      };
    }

    try {
      const res  = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (data.ok) {
        cerrarModalPago();
        location.reload();
      } else {
        Swal.fire({icon:'error', title:'Error', text: data.message ?? JSON.stringify(data)});
      }
    } catch (e) {
      Swal.fire({icon:'error', title:'Error de red', text:'Intenta nuevamente.'});
    } finally {
      spinner.style.display = 'none';
      this.disabled = false;
    }
  });
</script>
@endpush
