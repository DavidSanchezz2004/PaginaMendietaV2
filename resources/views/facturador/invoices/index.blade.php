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
            <div class="module-toolbar">
              <h1 style="display:flex; align-items:center; gap:0.5rem;"><i class='bx bx-receipt' style="color:var(--clr-text-main);"></i> Comprobantes Emitidos</h1>
              <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                <button type="button" id="btn-toggle-detalle" class="btn-secondary" style="font-size:.85rem;" title="Ver todos los comprobantes con más columnas">
                  <i class='bx bx-table'></i> Más detalle
                </button>
                <a href="{{ route('facturador.index') }}" class="btn-secondary" style="font-size:.85rem;">
                  <i class='bx bx-building'></i> Cambiar empresa
                </a>
                @can('create', \App\Models\Invoice::class)
                  <a href="{{ route('facturador.invoices.create') }}" class="btn-primary">
                    <i class='bx bx-plus'></i> Nueva Factura
                  </a>
                @endcan
              </div>
            </div>

            {{-- ── Tarjetas de resumen ── --}}
            @php
              $mesTxt  = now()->locale('es')->translatedFormat('F Y');
              $tipoMap = ['01'=>'Facturas','03'=>'Boletas','07'=>'N. Crédito','08'=>'N. Débito','09'=>'Guías'];
            @endphp
            <div class="stat-cards">

              <div class="stat-card sc-green">
                <div class="stat-card__icon"><i class='bx bx-dollar-circle'></i></div>
                <span class="stat-card__lbl">Total Facturado</span>
                <span class="stat-card__val">PEN {{ number_format($stats['total_mes'], 2) }}</span>
                <span class="stat-card__sub" style="line-height:1.6;">
                  Sin IGV: <strong>{{ number_format($stats['total_mes_sin_igv'], 2) }}</strong><br>
                  IGV: <strong>{{ number_format($stats['total_mes_igv'], 2) }}</strong><br>
                  <em style="font-size:.75rem; color:var(--clr-text-muted);">{{ $mesTxt }}</em>
                </span>
              </div>

              <div class="stat-card sc-blue">
                <div class="stat-card__icon"><i class='bx bx-check-shield'></i></div>
                <span class="stat-card__lbl">Aceptados SUNAT</span>
                <span class="stat-card__val">{{ $stats['aceptados_count'] }}</span>
                <span class="stat-card__sub">PEN {{ number_format($stats['aceptados_monto'], 2) }} este mes</span>
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
                      <td class="cell-action">
                        <div class="action-wrapper">
                          <a href="{{ route('facturador.invoices.show', $invoice) }}" class="btn-action-icon" title="Ver detalle">
                            <i class='bx bx-show'></i>
                          </a>
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
                    <tr><td colspan="7">No hay comprobantes registrados.</td></tr>
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
                      <td style="text-align:center;">
                        <div class="action-wrapper" style="justify-content:center;">
                          <a href="{{ route('facturador.invoices.show', $invoice) }}" class="btn-action-icon" title="Ver detalle">
                            <i class='bx bx-show'></i>
                          </a>
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
                    <tr><td colspan="15" style="text-align:center; color:var(--clr-text-muted);">No hay comprobantes registrados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($invoices->hasPages())
              <div style="margin-top:1rem;">{{ $invoices->links() }}</div>
            @endif
          </div>

          {{-- ── Configuración: Información Adicional ─────────────────────── --}}
          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar" style="margin-bottom:1rem;">
              <div>
                <h2 style="margin:0; font-size:1.05rem; display:flex; align-items:center; gap:.5rem;">
                  <i class='bx bx-info-circle' style="color:var(--clr-active-bg,#1a6b57);"></i>
                  Información Adicional (Feasy)
                </h2>
                <p style="margin:.3rem 0 0; color:#64748b; font-size:.88rem;">
                  Estos valores se envían automáticamente en el bloque <code>informacion_adicional</code>
                  del JSON a SUNAT/Feasy al emitir cualquier Factura, Boleta o comprobante con SPOT.<br>
                  Los <strong>nombres</strong> de los campos se configuran en el portal web de Feasy
                  (<em>Configuración → Campos Adicionales</em>).
                </p>
              </div>
            </div>

            <form method="POST" action="{{ route('facturador.config.informacion-adicional.update') }}" class="module-form">
              @csrf
              @method('PUT')

              @php
                $configAdicional = $company->informacion_adicional_config ?? [];
                // Garantizar al menos 1 fila visible por UX
                $slots = max(1, count($configAdicional));
                $configValues = array_values($configAdicional);
              @endphp

              <div id="ia-body" style="display:flex; flex-direction:column; gap:.5rem;">
                @for($i = 0; $i < $slots; $i++)
                  <div class="ia-row" style="display:flex; gap:.5rem; align-items:center;">
                    <span style="flex:0 0 160px; font-size:.82rem; color:#64748b; font-weight:600; white-space:nowrap;">
                      informacion_adicional_{{ $i + 1 }}
                    </span>
                    <input type="text"
                      name="informacion_adicional[]"
                      class="form-input"
                      style="flex:1; font-size:.88rem;"
                      placeholder="Valor (dejar vacío para no enviar)"
                      value="{{ old("informacion_adicional.$i", $configValues[$i] ?? '') }}">
                    <button type="button" class="btn-action-icon ia-remove" title="Quitar fila">
                      <i class='bx bx-trash'></i>
                    </button>
                  </div>
                @endfor
              </div>

              <div style="display:flex; justify-content:space-between; align-items:center; margin-top:.85rem;">
                <button type="button" id="ia-add" class="btn-secondary" style="font-size:.83rem; padding:.35rem .9rem;">
                  <i class='bx bx-plus'></i> Agregar campo
                </button>
                <button type="submit" class="btn-primary" style="font-size:.88rem;">
                  <i class='bx bx-save'></i> Guardar configuración
                </button>
              </div>

              <p style="font-size:.76rem; color:#9ca3af; margin-top:.6rem;">
                <i class='bx bx-shield-quarter' style="vertical-align:middle;"></i>
                Máximo 10 campos. Los campos vacíos se omiten del JSON enviado.
              </p>
            </form>
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
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
    // ── Información Adicional — tabla dinámica (Feasy) ──────────────────
    const MAX_IA = 10;
    function iaCount() { return document.querySelectorAll('#ia-body .ia-row').length; }

    function iaRenumber() {
      document.querySelectorAll('#ia-body .ia-row').forEach((row, i) => {
        const lbl = row.querySelector('span');
        if (lbl) lbl.textContent = 'informacion_adicional_' + (i + 1);
      });
    }

    function iaAddRow() {
      if (iaCount() >= MAX_IA) { alert('Máximo ' + MAX_IA + ' campos permitidos.'); return; }
      const n = iaCount() + 1;
      const row = document.createElement('div');
      row.className = 'ia-row';
      row.style.cssText = 'display:flex; gap:.5rem; align-items:center;';
      row.innerHTML = `
        <span style="flex:0 0 160px; font-size:.82rem; color:#64748b; font-weight:600; white-space:nowrap;">
          informacion_adicional_${n}
        </span>
        <input type="text" name="informacion_adicional[]" class="form-input"
          style="flex:1; font-size:.88rem;" placeholder="Valor (dejar vacío para no enviar)">
        <button type="button" class="btn-action-icon ia-remove" title="Quitar fila">
          <i class='bx bx-trash'></i>
        </button>`;
      document.getElementById('ia-body').appendChild(row);
      row.querySelector('.ia-remove').addEventListener('click', () => { row.remove(); iaRenumber(); });
    }

    // Ligar botones quitar a filas existentes (las del server-side Blade)
    document.querySelectorAll('#ia-body .ia-remove').forEach(btn => {
      btn.addEventListener('click', function() { this.closest('.ia-row').remove(); iaRenumber(); });
    });
    document.getElementById('ia-add')?.addEventListener('click', iaAddRow);
  </script>
@endpush
