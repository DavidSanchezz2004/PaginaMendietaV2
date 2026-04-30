@extends('layouts.app')

@section('title', 'Comprobante ' . $invoice->serie_numero . ' — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    /* Colors automatically adapt to dark mode via system variables defined in dashboard.css */
    .badge-draft     { background:rgba(107, 114, 128, 0.1); color:var(--clr-text-muted, #374151); border:1px solid rgba(107, 114, 128, 0.2); }
    .badge-ready     { background:rgba(30, 64, 175, 0.1); color:#3b82f6; border:1px solid rgba(59, 130, 246, 0.2); }
    .badge-sent      { background:rgba(6, 95, 70, 0.1); color:var(--clr-active-bg, #065f46); border:1px solid rgba(16, 185, 129, 0.2); }
    .badge-error     { background:rgba(153, 27, 27, 0.1); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.2); }
    .badge-consulted { background:rgba(91, 33, 182, 0.1); color:#8b5cf6; border:1px solid rgba(139, 92, 246, 0.2); }
    .badge-voided    { background:rgba(107, 114, 128, 0.12); color:#6b7280; border:1px solid rgba(107, 114, 128, 0.25); text-decoration:line-through; }

    .invoice-badge   { display:inline-flex; align-items:center; gap:0.35rem; padding:.35rem .85rem; border-radius:20px; font-size:.78rem; font-weight:700; }
    .show-grid       { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
    @media (max-width:768px) { .show-grid { grid-template-columns:1fr; } }
    
    .info-card       { background:var(--clr-bg-card, #ffffff); border:1px solid var(--clr-border-light, #e5e7eb); border-radius:16px; padding:1.5rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    body.dark-mode .info-card { background: var(--clr-bg-card); border-color: var(--clr-border-light); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    
    .info-card h3    { font-size:.85rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:var(--clr-text-muted, #6b7280); margin:0 0 1.2rem; display: flex; align-items: center; gap: 0.5rem; }
    .info-card h3 i  { font-size: 1.1rem; color: var(--clr-text-main); }
    
    .dl-row          { display:flex; font-size:.9rem; margin-bottom:.6rem; gap:.5rem; }
    .dl-row dt       { min-width:160px; color:var(--clr-text-muted, #6b7280); flex-shrink:0; font-weight: 500;}
    .dl-row dd       { margin:0; font-weight:600; color:var(--clr-text-main, #111827); word-break:break-word; }
    
    .action-strip    { display:flex; flex-wrap:wrap; gap:.75rem; margin-bottom:1.5rem; }
    .action-strip form { display: inline-block; margin: 0; }
    .invoice-actions-panel {
      display: grid;
      grid-template-columns: 1fr;
      gap: .75rem;
      padding: .9rem;
      border: 1px solid #e3e9e8;
      border-radius: 12px;
      background: #fbfcfc;
      margin-bottom: 1.5rem;
    }
    .invoice-action-group {
      display: flex;
      flex-wrap: wrap;
      gap: .65rem;
      align-items: center;
      padding-bottom: .7rem;
      border-bottom: 1px solid #edf2f1;
    }
    .invoice-action-group:last-child {
      padding-bottom: 0;
      border-bottom: 0;
    }
    .invoice-action-group__label {
      min-width: 94px;
      color: var(--clr-text-muted, #64748b);
      font-size: .72rem;
      font-weight: 800;
      letter-spacing: .05em;
      text-transform: uppercase;
    }
    .invoice-action-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: .6rem;
      align-items: center;
      flex: 1;
    }
    .pdf-custom-action {
      display: flex;
      flex-wrap: wrap;
      gap: .6rem .8rem;
      align-items: center;
    }
    .pdf-custom-help {
      max-width: 520px;
      color: #64748b;
      font-size: .78rem;
      line-height: 1.35;
    }
    .pdf-custom-help a {
      color: #0f766e;
      font-weight: 800;
      text-decoration: none;
    }
    .pdf-custom-help a:hover { text-decoration: underline; }
    @media (max-width: 760px) {
      .invoice-action-group__label { width: 100%; min-width: 0; }
      .pdf-custom-help { max-width: 100%; }
    }
    
    .module-table th { color: var(--clr-text-muted, #6b7280); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .module-table td { color: var(--clr-text-main, #111827); font-weight: 500; font-size: 0.9rem; }
    body.dark-mode .module-table td { color: var(--clr-text-main); }
    body.dark-mode .module-table th { color: var(--clr-text-muted); }
    .letter-panel { border:1px solid var(--clr-border-light,#e5e7eb); border-radius:12px; padding:1rem; margin:0 0 1.25rem; background:rgba(15,23,42,.02); }
    .letter-modal-grid { display:grid; grid-template-columns:1fr 130px 150px 38px; gap:.55rem; align-items:end; margin-bottom:.55rem; }
    .letter-modal-grid label { font-size:.72rem; text-transform:uppercase; color:var(--clr-text-muted,#6b7280); font-weight:800; }
    .letter-input { width:100%; border:1px solid var(--clr-border-light,#d1d5db); border-radius:7px; min-height:38px; padding:.5rem .65rem; background:transparent; color:var(--clr-text-main,#111827); }
    .letter-remove { width:38px; height:38px; border:1px solid #fecaca; color:#dc2626; background:#fff; border-radius:7px; cursor:pointer; }
    .letter-total-line { display:flex; justify-content:space-between; gap:1rem; font-weight:800; padding:.75rem 0; border-top:1px solid var(--clr-border-light,#e5e7eb); }
    .letter-total-line.is-invalid { color:#dc2626; }
    .invoice-show-page.main-content {
      justify-content: stretch;
      padding: 1.35rem 1.6rem;
      background: #f3f6f6;
    }
    .invoice-show-page .module-content-stack {
      width: 100%;
      max-width: none;
    }
    .invoice-show-shell {
      width: 100%;
      max-width: none;
      padding: 1.35rem 1.45rem;
      border: 1px solid #dce7e4;
      border-top: 4px solid #0f766e;
      border-radius: 12px;
      box-shadow: 0 16px 34px rgba(15, 23, 42, .07);
    }
    .invoice-show-shell:hover {
      transform: none;
      box-shadow: 0 16px 34px rgba(15, 23, 42, .07);
    }
    .invoice-show-shell .module-toolbar {
      margin-bottom: 1rem;
    }
    .invoice-show-shell .action-strip { margin-bottom: 0; }
    .invoice-show-shell .info-card {
      border-radius: 12px;
      box-shadow: none;
    }
    .invoice-show-shell .module-table {
      border: 1px solid #e3e9e8;
      border-radius: 12px;
      overflow: hidden;
    }
    @media(max-width:720px){ .letter-modal-grid { grid-template-columns:1fr; } .letter-remove { width:100%; } }
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

      <main class="main-content invoice-show-page">
        <div class="module-content-stack">

          @foreach(['status' => null, 'success' => null, 'error' => 'module-alert--error'] as $flashKey => $flashClass)
            @if(session($flashKey))
              <div class="placeholder-content module-alert module-flash {{ $flashClass }}" data-flash-message>
                <p>{{ session($flashKey) }}</p>
                <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
              </div>
            @endif
          @endforeach

          <div class="placeholder-content module-card-wide invoice-show-shell">

            <div class="module-toolbar">
              <div style="display:flex; align-items:center; gap:.75rem;">
                <h1>{{ $invoice->serie_numero }}</h1>
                <span class="invoice-badge badge-{{ $invoice->estado->value }}">{{ $invoice->estado->label() }}</span>
              </div>
              <a href="{{ route('facturador.invoices.index') }}" class="btn-secondary">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
            </div>

            {{-- Acciones --}}
            <div class="invoice-actions-panel">
              <div class="invoice-action-group">
                <span class="invoice-action-group__label">Operación</span>
                <div class="invoice-action-buttons">
                  @can('emit', $invoice)
                    @if($invoice->canBeEmitted())
                      <form method="POST" action="{{ route('facturador.invoices.emit', $invoice) }}" id="form-emit">
                        @csrf
                        <button type="button" class="btn-primary" id="btn-emit">
                          <i class='bx bx-send'></i> Emitir a SUNAT
                        </button>
                      </form>
                    @endif
                  @endcan

                  @can('void', $invoice)
                    @if($invoice->canBeVoided())
                      <form method="POST" action="{{ route('facturador.invoices.void', $invoice) }}" id="form-void">
                        @csrf
                        <input type="hidden" name="motivo" id="void-motivo" value="">
                        <button type="button" class="btn-secondary" id="btn-void" style="color:#dc2626; border-color:#fca5a5;">
                          <i class='bx bx-x-circle'></i> Anular
                        </button>
                      </form>
                    @endif
                  @endcan

                  @if($invoice->canBeExchangedToLetters())
                    <button type="button" id="btn-exchange-letters" class="btn-primary">
                      <i class='bx bx-transfer'></i> Canjear a letras
                    </button>
                  @elseif($invoice->hasBeenExchangedToLetters())
                    <a href="{{ route('facturador.letras.index', ['search' => $invoice->serie_numero]) }}" class="btn-secondary">
                      <i class='bx bx-check-double'></i> Canjeada a letras
                    </a>
                  @endif
                </div>
              </div>

              <div class="invoice-action-group">
                <span class="invoice-action-group__label">SUNAT</span>
                <div class="invoice-action-buttons">
                  @can('consult', $invoice)
                    @if($invoice->canBeConsulted())
                      <form method="POST" action="{{ route('facturador.invoices.consult', $invoice) }}">
                        @csrf
                        <button type="submit" class="btn-secondary">
                          <i class='bx bx-refresh'></i> Consultar SUNAT
                        </button>
                      </form>
                    @endif
                  @endcan

                  <a href="{{ route('sunat.comprobantes.validar.index', ['invoice_id' => $invoice->id]) }}" class="btn-secondary">
                    <i class='bx bx-search-alt'></i> Validar SUNAT
                  </a>

                  @can('releaseFailedEmission', $invoice)
                    <form method="POST" action="{{ route('facturador.invoices.release-failed-emission', $invoice) }}" id="form-release-failed-emission">
                      @csrf
                      <input type="hidden" name="motivo" id="release-failed-emission-motivo" value="">
                      <button type="button" class="btn-secondary" id="btn-release-failed-emission" style="color:#b45309; border-color:#fbbf24;">
                        <i class='bx bx-block'></i> Marcar no emitido
                      </button>
                    </form>
                  @endcan
                </div>
              </div>

              <div class="invoice-action-group">
                <span class="invoice-action-group__label">Descargas</span>
                <div class="invoice-action-buttons">
                  @if($invoice->ruta_reporte)
                    <a href="{{ $invoice->ruta_reporte }}" target="_blank" rel="noopener" class="btn-primary">
                      <i class='bx bx-file-pdf'></i> Ver PDF Feasy
                    </a>
                  @endif

                  <div class="pdf-custom-action">
                    <a href="{{ route('facturador.invoices.custom-pdf', $invoice) }}" class="btn-secondary">
                      <i class='bx bx-download'></i> PDF personalizado
                    </a>
                    <span class="pdf-custom-help">
                      Usa el logo y estilo de <a href="{{ route('facturador.quote-settings.edit') }}">Config. Comprobante/Cotizador</a>.
                    </span>
                  </div>

                  @if($invoice->ruta_xml)
                    <a href="{{ $invoice->ruta_xml }}" target="_blank" rel="noopener" class="btn-secondary">
                      <i class='bx bx-download'></i> XML
                    </a>
                  @endif

                  @if($invoice->ruta_cdr)
                    <a href="{{ $invoice->ruta_cdr }}" target="_blank" rel="noopener" class="btn-secondary">
                      <i class='bx bx-shield-check'></i> CDR
                    </a>
                  @endif

                  @if($invoice->xml_path)
                    @can('downloadXml', $invoice)
                      <a href="{{ route('facturador.invoices.xml', $invoice) }}" class="btn-secondary">
                        <i class='bx bx-file'></i> XML local
                      </a>
                    @endcan
                  @endif
                </div>
              </div>

              <div class="invoice-action-group">
                <span class="invoice-action-group__label">Herramientas</span>
                <div class="invoice-action-buttons">
                  <a href="{{ route('facturador.invoices.duplicate', $invoice) }}" class="btn-secondary">
                    <i class='bx bx-copy'></i> Duplicar
                  </a>

                  <button type="button" id="btn-ver-detalle" class="btn-secondary">
                    <i class='bx bx-table'></i> Más detalle
                  </button>

                  @can('delete', $invoice)
                    <form method="POST" action="{{ route('facturador.invoices.destroy', $invoice) }}"
                          data-confirm="¿Eliminar este comprobante? Esta acción no se puede deshacer.">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn-secondary" style="color:#ef4444; border-color:#fca5a5;">
                        <i class='bx bx-trash'></i> Eliminar
                      </button>
                    </form>
                  @endcan
                </div>
              </div>
            </div>

            {{-- Info cabecera + cliente --}}
            <div class="show-grid" style="margin-bottom:1.5rem;">
              <div class="info-card">
                <h3><i class='bx bx-receipt'></i> Detalles del Comprobante</h3>
                <div class="dl-row"><dt>Tipo</dt><dd>{{ $invoice->codigo_tipo_documento }}</dd></div>
                <div class="dl-row"><dt>Serie-Número</dt><dd><code>{{ $invoice->serie_numero }}</code></dd></div>
                <div class="dl-row"><dt>Código interno</dt><dd>{{ $invoice->codigo_interno ?? '—' }}</dd></div>
                <div class="dl-row"><dt>Fecha emisión</dt><dd>{{ $invoice->fecha_emision->format('d/m/Y') }} <span style="color:var(--clr-text-muted); font-size:0.8rem; font-weight:500;">{{ $invoice->hora_emision }}</span></dd></div>
                <div class="dl-row"><dt>Fecha vencimiento</dt><dd>{{ $invoice->fecha_vencimiento ? $invoice->fecha_vencimiento->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Moneda</dt><dd>{{ $invoice->codigo_moneda }}</dd></div>
                <div class="dl-row"><dt>Forma de pago</dt><dd>{{ $invoice->forma_pago == 1 ? 'Contado' : 'Crédito' }}</dd></div>
                <div class="dl-row"><dt>N° OC</dt><dd>{{ $invoice->numero_orden_compra ?? '—' }}</dd></div>
                @if($invoice->observacion)
                  <div class="dl-row"><dt>Observación</dt><dd>{{ $invoice->observacion }}</dd></div>
                @endif
              </div>

              <div class="info-card">
                <h3><i class='bx bx-user'></i> Datos del Receptor</h3>
                @if($invoice->client)
                  <div class="dl-row"><dt>Razón Social</dt><dd><strong>{{ $invoice->client->nombre_razon_social }}</strong></dd></div>
                  <div class="dl-row"><dt>Tipo Documento</dt><dd>{{ $invoice->client->codigo_tipo_documento }}</dd></div>
                  <div class="dl-row"><dt>Número Documento</dt><dd>{{ $invoice->client->numero_documento }}</dd></div>
                  <div class="dl-row"><dt>Dirección</dt><dd>{{ $invoice->client->direccion ?? '—' }}</dd></div>
                  <div class="dl-row"><dt>Correo</dt><dd>{{ $invoice->correo ?? $invoice->client->correo_electronico ?? '—' }}</dd></div>
                @else
                  <p style="color:#9ca3af; font-size:.875rem;">Cliente no disponible.</p>
                @endif
              </div>
            </div>

            {{-- Ítems --}}
            <div class="module-table-wrap" style="margin-bottom:1.25rem;">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Unidad</th>
                    <th style="text-align:right;">Cant.</th>
                    <th style="text-align:right;">P. Unit. (s/IGV)</th>
                    <th>Afecto</th>
                    <th style="text-align:right;">IGV</th>
                    <th style="text-align:right;">Total</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($invoice->items as $i => $item)
                    <tr>
                      <td style="color:#9ca3af;">{{ $i + 1 }}</td>
                      <td><small>{{ $item->codigo_interno ?? '—' }}</small></td>
                      <td>{{ $item->descripcion }}</td>
                      <td>{{ $item->codigo_unidad_medida }}</td>
                      <td style="text-align:right;">{{ $item->cantidad }}</td>
                      <td style="text-align:right;">{{ number_format($item->monto_valor_unitario, 4) }}</td>
                      <td><small>{{ $item->codigo_indicador_afecto }}</small></td>
                      <td style="text-align:right;">{{ number_format($item->monto_igv, 2) }}</td>
                      <td style="text-align:right; font-weight:600;">{{ number_format($item->monto_total, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="7" style="text-align:right; color:#6b7280; font-size:.85rem;">OP. Gravadas</td>
                    <td colspan="2" style="text-align:right; font-size:.85rem;">{{ number_format($invoice->monto_total_gravado, 2) }}</td>
                  </tr>
                  @if($invoice->monto_total_exonerado > 0)
                    <tr>
                      <td colspan="7" style="text-align:right; color:#6b7280; font-size:.85rem;">OP. Exoneradas</td>
                      <td colspan="2" style="text-align:right; font-size:.85rem;">{{ number_format($invoice->monto_total_exonerado, 2) }}</td>
                    </tr>
                  @endif
                  @if($invoice->monto_total_inafecto > 0)
                    <tr>
                      <td colspan="7" style="text-align:right; color:#6b7280; font-size:.85rem;">OP. Inafectas</td>
                      <td colspan="2" style="text-align:right; font-size:.85rem;">{{ number_format($invoice->monto_total_inafecto, 2) }}</td>
                    </tr>
                  @endif
                  <tr>
                    <td colspan="7" style="text-align:right; color:var(--clr-text-muted, #6b7280); font-size:.85rem; font-weight:600;">IGV ({{ $invoice->porcentaje_igv }}%)</td>
                    <td colspan="2" style="text-align:right; font-size:.9rem; font-weight:600; color:var(--clr-text-main, #111827);">{{ number_format($invoice->monto_total_igv, 2) }}</td>
                  </tr>
                  <tr>
                    <td colspan="7" style="text-align:right; font-weight:800; color:var(--clr-text-main, #111827); font-size:1.05rem;">TOTAL ({{ $invoice->codigo_moneda }})</td>
                    <td colspan="2" style="text-align:right; font-weight:800; font-size:1.2rem; color:var(--clr-active-bg, #1a6b57);">{{ number_format($invoice->monto_total, 2) }}</td>
                  </tr>
                  @if($invoice->indicador_detraccion && $invoice->informacion_detraccion)
                    @php $detFoot = $invoice->informacion_detraccion; @endphp
                  <tr>
                    <td colspan="7" style="text-align:right; color:#b45309; font-size:.88rem;">
                      Detracción ({{ $detFoot['porcentaje_detraccion'] ?? 0 }}%)
                    </td>
                    <td colspan="2" style="text-align:right; font-size:.9rem; font-weight:600; color:#b45309;">
                      – {{ number_format($detFoot['monto_detraccion'] ?? 0, 2) }}
                    </td>
                  </tr>
                  <tr style="background:rgba(16,185,129,.05);">
                    <td colspan="7" style="text-align:right; font-weight:800; color:var(--clr-active-bg, #1a6b57); font-size:1rem;">
                      Monto neto pendiente de pago
                    </td>
                    <td colspan="2" style="text-align:right; font-weight:800; font-size:1.1rem; color:var(--clr-active-bg, #1a6b57);">
                      {{ number_format($invoice->monto_total - ($detFoot['monto_detraccion'] ?? 0), 2) }}
                    </td>
                  </tr>
                  @endif
                </tfoot>
              </table>
            </div>

            {{-- ── Detracción SPOT ─────────────────────────────────────────── --}}
            @if($invoice->indicador_detraccion && $invoice->informacion_detraccion)
              @php
                $det  = $invoice->informacion_detraccion;
                $neto = $invoice->monto_total - ($det['monto_detraccion'] ?? 0);
                $mediosPagoDet = ['001' => 'Depósito en cuenta', '002' => 'Giro', '003' => 'Transferencia de fondos', '004' => 'Orden de pago'];
                $descMedioDet  = ($mediosPagoDet[$det['codigo_medio_pago_detraccion'] ?? ''] ?? ($det['codigo_medio_pago_detraccion'] ?? '—'));
              @endphp
              <div class="info-card" style="border-left:4px solid #f59e0b; margin-top:1.25rem;">
                <h3><i class='bx bx-transfer-alt'></i> Información de la Detracción (SPOT)</h3>
                <div class="show-grid">
                  <div>
                    <div class="dl-row">
                      <dt>Bien o Servicio</dt>
                      <dd><strong>{{ $det['codigo_bbss_sujeto_detraccion'] ?? '—' }}</strong></dd>
                    </div>
                    <div class="dl-row">
                      <dt>Porcentaje de detracción</dt>
                      <dd>{{ $det['porcentaje_detraccion'] ?? 0 }}%</dd>
                    </div>
                    <div class="dl-row">
                      <dt>Monto detracción</dt>
                      <dd><strong style="font-size:1.05rem; color:#b45309;">
                        {{ $invoice->codigo_moneda }} {{ number_format($det['monto_detraccion'] ?? 0, 2) }}
                      </strong></dd>
                    </div>
                  </div>
                  <div>
                    <div class="dl-row">
                      <dt>Nro. Cta. Banco de la Nación</dt>
                      <dd><span style="font-family:monospace;">{{ $det['cuenta_banco_detraccion'] ?? '—' }}</span></dd>
                    </div>
                    <div class="dl-row">
                      <dt>Medio de pago</dt>
                      <dd>{{ $det['codigo_medio_pago_detraccion'] ?? '—' }} — {{ $descMedioDet }}</dd>
                    </div>
                    <div class="dl-row">
                      <dt>Monto neto pendiente de pago</dt>
                      <dd><strong style="color:var(--clr-active-bg, #1a6b57); font-size:1.05rem;">
                        {{ $invoice->codigo_moneda }} {{ number_format($neto, 2) }}
                      </strong></dd>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            {{-- ── Retención ──────────────────────────────────────────────── --}}
            @if($invoice->retention_enabled && $invoice->retention_info)
              @php
                $ret = $invoice->retention_info ?? [];
                $codigoRetMapping = ['61' => 'Remesas al exterior', '62' => 'Retención a terceros', '09' => 'Otros'];
                $descCodigoRet = $codigoRetMapping[$ret['codigo_retencion'] ?? ''] ?? ($ret['codigo_retencion'] ?? '—');
              @endphp
              <div class="info-card" style="border-left:4px solid #dc2626; margin-top:1.25rem;">
                <h3><i class='bx bx-receipt'></i> Información de Retención</h3>
                <div class="show-grid">
                  <div>
                    <div class="dl-row">
                      <dt>Tipo de Retención</dt>
                      <dd><strong>{{ $ret['codigo_retencion'] ?? '—' }} — {{ $descCodigoRet }}</strong></dd>
                    </div>
                    <div class="dl-row">
                      <dt>Porcentaje de Retención</dt>
                      <dd>{{ $invoice->retention_percentage ?? 0 }}%</dd>
                    </div>
                    <div class="dl-row">
                      <dt>Base Imponible</dt>
                      <dd><strong>
                        {{ $invoice->codigo_moneda }} {{ number_format($invoice->retention_base ?? 0, 2) }}
                      </strong></dd>
                    </div>
                  </div>
                  <div>
                    <div class="dl-row">
                      <dt>Monto Retención</dt>
                      <dd><strong style="font-size:1.05rem; color:#991b1b;">
                        {{ $invoice->codigo_moneda }} {{ number_format($invoice->retention_amount ?? 0, 2) }}
                      </strong></dd>
                    </div>
                    <div class="dl-row">
                      <dt>Neto a Pagar</dt>
                      <dd><strong style="color:#15803d; font-size:1.05rem;">
                        {{ $invoice->codigo_moneda }} {{ number_format($invoice->net_total ?? 0, 2) }}
                      </strong></dd>
                    </div>
                    <div class="dl-row">
                      <dt>Concepto</dt>
                      <dd>{{ $ret['concepto'] ?? '(Sin concepto)' }}</dd>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            {{-- ── Cuotas de crédito ───────────────────────────────────────── --}}
            @if($invoice->forma_pago == 2 && !empty($invoice->lista_cuotas))
              <div class="info-card" style="border-left:4px solid #6366f1; margin-top:1.25rem;">
                <h3><i class='bx bx-calendar-check'></i> Información de Cuotas</h3>
                <div class="module-table-wrap">
                  <table class="module-table">
                    <thead>
                      <tr>
                        <th style="width:60px;">#</th>
                        <th>Fecha de pago</th>
                        <th style="text-align:right;">Monto</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($invoice->lista_cuotas as $idx => $cuota)
                        <tr>
                          <td style="color:#9ca3af; font-weight:600;">{{ $idx + 1 }}</td>
                          <td>
                            @php
                              try { $fc = \Carbon\Carbon::parse($cuota['fecha_pago'])->format('d/m/Y'); }
                              catch(\Exception $e) { $fc = $cuota['fecha_pago'] ?? '—'; }
                            @endphp
                            {{ $fc }}
                          </td>
                          <td style="text-align:right; font-weight:700; color:var(--clr-active-bg, #1a6b57);">
                            {{ $invoice->codigo_moneda }} {{ number_format($cuota['monto'] ?? 0, 2) }}
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="2" style="text-align:right; font-weight:700; font-size:.9rem;">Total cuotas:</td>
                        <td style="text-align:right; font-weight:800; font-size:1rem; color:var(--clr-active-bg, #1a6b57);">
                          {{ $invoice->codigo_moneda }}
                          {{ number_format(collect($invoice->lista_cuotas)->sum('monto'), 2) }}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            @endif

            {{-- Trazabilidad Feasy / SUNAT --}}
            <div class="info-card">
              <h3 style="display:flex; align-items:center; gap:.75rem; justify-content: space-between; flex-wrap: wrap;">
                <span><i class='bx bx-check-shield'></i> Trazabilidad SUNAT</span>
                <span class="invoice-badge {{ $invoice->estado_feasy->isAccepted() ? 'badge-sent' : 'badge-draft' }}">
                  {{ $invoice->estado_feasy->label() }}
                </span>
              </h3>
              <div class="show-grid">
                <div>
                  <div class="dl-row"><dt>Estado interno</dt><dd><span class="invoice-badge badge-{{ $invoice->estado->value }}">{{ $invoice->estado->label() }}</span></dd></div>
                  <div class="dl-row"><dt>Cód. respuesta SUNAT</dt><dd>{{ $invoice->codigo_respuesta_sunat ?? '—' }}</dd></div>
                  <div class="dl-row"><dt>Mensaje SUNAT</dt><dd>{{ $invoice->mensaje_respuesta_sunat ?? '—' }}</dd></div>
                </div>
                <div>
                  <div class="dl-row"><dt>Archivo XML</dt><dd><small style="font-family:monospace;">{{ $invoice->nombre_archivo_xml ?? '—' }}</small></dd></div>
                  <div class="dl-row"><dt>Hash CPE</dt><dd><small style="font-family:monospace; font-size:.78rem; word-break:break-all;">{{ $invoice->hash_cpe ?? '—' }}</small></dd></div>
                  <div class="dl-row"><dt>Enviado</dt><dd>{{ $invoice->sent_at ? $invoice->sent_at->format('d/m/Y H:i:s') : '—' }}</dd></div>
                  <div class="dl-row"><dt>Consultado</dt><dd>{{ $invoice->consulted_at ? $invoice->consulted_at->format('d/m/Y H:i:s') : '—' }}</dd></div>
                  @if($invoice->mensaje_observacion)
                    <div class="dl-row"><dt>Observación</dt><dd style="color:var(--clr-text-accent, #92400e);">{{ $invoice->mensaje_observacion }}</dd></div>
                  @endif
                </div>
              </div>
              @if($invoice->last_error)
                <div style="margin-top:1.5rem; background:rgba(239, 68, 68, 0.05); border:1px solid rgba(239, 68, 68, 0.2); border-radius:12px; padding:1.25rem;">
                  <p style="font-weight:700; color:#ef4444; margin:0 0 .5rem; font-size:.9rem; display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-error'></i> Último error reportado:</p>
                  <pre style="margin:0; font-size:.85rem; color:var(--clr-text-main, #7f1d1d); white-space:pre-wrap; word-break:break-word; font-family: monospace;">{{ $invoice->last_error }}</pre>
                </div>
              @endif
              @if($invoice->sendLogs->isNotEmpty())
                <div class="module-table-wrap" style="margin-top:1.5rem;">
                  <table class="module-table">
                    <thead>
                      <tr>
                        <th>Intento</th>
                        <th>Acción</th>
                        <th>Fecha</th>
                        <th>HTTP</th>
                        <th>Código</th>
                        <th>Resultado</th>
                        <th>Usuario</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($invoice->sendLogs as $log)
                        <tr>
                          <td>#{{ $log->attempt_number }}</td>
                          <td>{{ strtoupper($log->action) }}</td>
                          <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                          <td>{{ $log->http_status ?? '—' }}</td>
                          <td>{{ $log->codigo_respuesta ?? '—' }}</td>
                          <td>{{ $log->success ? 'OK' : ($log->mensaje_respuesta ?? 'Error') }}</td>
                          <td>{{ $log->user?->name ?? '—' }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>

            {{-- ── Letras generadas / Canje ──────────────────────────────── --}}
            <div class="info-card" style="margin-top:1.25rem;">
              <h3><i class='bx bx-transfer'></i> Canje a letras</h3>

              @if($invoice->letras->count())
                <div class="module-table-wrap">
                  <table class="module-table">
                    <thead>
                      <tr>
                        <th>Número</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Monto</th>
                        <th style="text-align:right;">Saldo</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($invoice->letras as $letra)
                        <tr>
                          <td><a href="{{ route('facturador.letras.show', $letra) }}" style="font-weight:800; color:var(--clr-active-bg,#1a6b57);">{{ $letra->numero_letra }}</a></td>
                          <td>{{ $letra->fecha_vencimiento?->format('d/m/Y') }}</td>
                          <td>{{ $letra->estado_label }}</td>
                          <td style="text-align:right; font-weight:800;">{{ $letra->codigo_moneda }} {{ number_format($letra->monto, 2) }}</td>
                          <td style="text-align:right;">{{ $letra->codigo_moneda }} {{ number_format($letra->saldo, 2) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                @if($invoice->canBeExchangedToLetters())
                  <form method="POST" action="{{ route('facturador.invoices.exchange-letters', $invoice) }}" class="letter-panel" id="exchange-letters-form">
                    @csrf
                    <div class="show-grid" style="margin-bottom:.85rem;">
                      <div class="dl-row"><dt>Total pendiente</dt><dd>{{ $invoice->codigo_moneda }} <span id="letter-pending">{{ number_format($invoice->pendingAmountForLetters(), 2, '.', '') }}</span></dd></div>
                      <div class="dl-row"><dt>Moneda</dt><dd>
                        <select name="currency" class="letter-input" style="max-width:140px;">
                          <option value="{{ $invoice->codigo_moneda }}">{{ $invoice->codigo_moneda }}</option>
                        </select>
                      </dd></div>
                    </div>
                    <div class="form-group" style="margin-bottom:.85rem;">
                      <label>Observación general</label>
                      <input type="text" name="observation" class="form-input" maxlength="500" placeholder="Opcional">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:.55rem;">
                      <strong>Letras</strong>
                      <button type="button" class="btn-secondary" id="add-letter-row" style="font-size:.82rem;"><i class='bx bx-plus'></i> Agregar letra</button>
                    </div>
                    <div id="letter-rows"></div>
                    <div class="letter-total-line" id="letter-total-line">
                      <span>Suma de letras</span>
                      <span><strong id="letter-total">0.00</strong> / {{ number_format($invoice->pendingAmountForLetters(), 2) }}</span>
                    </div>
                    <button type="submit" class="btn-primary" id="letter-submit"><i class='bx bx-save'></i> Confirmar canje</button>
                  </form>
                @else
                  <p style="margin:0; color:var(--clr-text-muted,#6b7280); font-size:.9rem;">
                    La factura debe estar emitida y sin canje previo para generar letras.
                  </p>
                @endif
              @endif
            </div>

            {{-- ── Sección Cobros ─────────────────────────────────────────── --}}
            <div class="info-card" style="margin-top:1.25rem;">
              <h3><i class='bx bx-wallet'></i> Cobros Registrados</h3>

              @if($invoice->payments->count())
                <div class="module-table-wrap" style="margin-bottom:1.25rem;">
                  <table class="module-table">
                    <thead>
                      <tr>
                        <th>Método</th>
                        <th style="text-align:right;">Monto</th>
                        <th>Referencia</th>
                        <th>Notas</th>
                        <th>Fecha</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($invoice->payments as $payment)
                        <tr>
                          <td>
                            <span style="display:inline-flex; align-items:center; gap:.4rem; background:rgba(16,185,129,.1); color:#059669; padding:.25rem .65rem; border-radius:20px; font-size:.8rem; font-weight:700;">
                              <i class='bx {{ $payment->metodoIcon() }}'></i>
                              {{ $payment->metodoLabel() }}
                            </span>
                          </td>
                          <td style="text-align:right; font-weight:700; color:var(--clr-active-bg, #1a6b57);">
                            {{ $invoice->codigo_moneda }} {{ number_format($payment->monto, 2) }}
                          </td>
                          <td><small style="font-family:monospace;">{{ $payment->referencia ?? '—' }}</small></td>
                          <td style="font-size:.85rem; color:var(--clr-text-muted);">{{ $payment->notas ?? '—' }}</td>
                          <td style="font-size:.8rem; color:var(--clr-text-muted);">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                          <td>
                            <form method="POST"
                                  action="{{ route('facturador.invoices.payments.destroy', [$invoice, $payment]) }}"
                                  data-confirm="¿Eliminar este cobro?">
                              @csrf @method('DELETE')
                              <button type="submit" class="btn-action-icon" title="Eliminar" style="color:#ef4444;">
                                <i class='bx bx-trash'></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <td style="text-align:right; font-weight:700; font-size:.9rem;" colspan="1">Total cobrado:</td>
                        <td style="text-align:right; font-weight:800; font-size:1rem; color:var(--clr-active-bg,#1a6b57);" colspan="5">
                          {{ $invoice->codigo_moneda }} {{ number_format($invoice->payments->sum('monto'), 2) }}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              @else
                <p style="color:var(--clr-text-muted); font-size:.875rem; margin-bottom:1rem;">Aún no hay cobros registrados para este comprobante.</p>
              @endif

              {{-- Formulario nuevo cobro --}}
              <form method="POST" action="{{ route('facturador.invoices.payments.store', $invoice) }}"
                    style="background:var(--clr-bg-main,#f9fafb); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:12px; padding:1.25rem;">
                @csrf
                <p style="font-size:.85rem; font-weight:700; color:var(--clr-text-muted); margin:0 0 .75rem; text-transform:uppercase; letter-spacing:.04em;">
                  <i class='bx bx-plus-circle'></i> Registrar cobro
                </p>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.75rem; align-items:end;">
                  <div class="form-group" style="margin:0;">
                    <label>Método *</label>
                    <select name="metodo" class="form-input" required>
                      <option value="">— Seleccionar —</option>
                      <option value="efectivo">Efectivo</option>
                      <option value="yape">Yape</option>
                      <option value="plin">Plin</option>
                      <option value="transferencia">Transferencia Bancaria</option>
                      <option value="deposito">Depósito</option>
                      <option value="tarjeta">Tarjeta</option>
                      <option value="otro">Otro</option>
                    </select>
                    @error('metodo')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label>Monto *</label>
                    <input type="number" name="monto" class="form-input" step="0.01" min="0.01"
                      value="{{ number_format($invoice->monto_total, 2) }}" required>
                    @error('monto')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label>Referencia / N° Operación</label>
                    <input type="text" name="referencia" class="form-input" maxlength="150"
                      placeholder="Código de transacción, Nro. operación...">
                  </div>
                  <div class="form-group" style="margin:0; grid-column:1/-1;">
                    <label>Notas (opcional)</label>
                    <input type="text" name="notas" class="form-input" maxlength="500"
                      placeholder="Información adicional...">
                  </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top:.75rem; font-size:.85rem;">
                  <i class='bx bx-save'></i> Guardar cobro
                </button>
              </form>
            </div>

          </div>
        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const pendingForLetters = Number('{{ number_format($invoice->pendingAmountForLetters(), 2, '.', '') }}');
    const letterRows = document.getElementById('letter-rows');
    const letterTotal = document.getElementById('letter-total');
    const letterTotalLine = document.getElementById('letter-total-line');
    const letterSubmit = document.getElementById('letter-submit');
    const money = (value) => Number(value || 0).toFixed(2);
    const recalcLetterRows = () => {
      const total = Array.from(document.querySelectorAll('[data-letter-amount]'))
        .reduce((sum, input) => sum + Number(input.value || 0), 0);
      const valid = Math.abs(total - pendingForLetters) <= 0.01;
      if (letterTotal) letterTotal.textContent = money(total);
      letterTotalLine?.classList.toggle('is-invalid', !valid);
      if (letterSubmit) letterSubmit.disabled = !valid;
    };
    const addLetterRow = (amount = '') => {
      if (!letterRows) return;
      const index = letterRows.children.length;
      const row = document.createElement('div');
      row.className = 'letter-modal-grid';
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
        <button type="button" class="letter-remove" title="Quitar"><i class='bx bx-trash'></i></button>`;
      letterRows.appendChild(row);
      row.querySelector('[data-letter-amount]')?.addEventListener('input', recalcLetterRows);
      row.querySelector('.letter-remove')?.addEventListener('click', () => {
        row.remove();
        recalcLetterRows();
      });
      recalcLetterRows();
    };
    document.getElementById('add-letter-row')?.addEventListener('click', () => addLetterRow(''));
    document.getElementById('btn-exchange-letters')?.addEventListener('click', () => {
      document.getElementById('exchange-letters-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    if (letterRows && letterRows.children.length === 0) {
      addLetterRow(money(pendingForLetters));
    }

    // — Confirmar EMISIÓN con preview completo
    document.getElementById('btn-emit')?.addEventListener('click', function () {
      @php
        $previewItems = $invoice->items->map(fn($i) => [
          'desc'  => $i->descripcion,
          'qty'   => $i->cantidad,
          'price' => number_format($i->monto_precio_unitario, 2),
          'igv'   => number_format($i->monto_igv, 2),
          'total' => number_format($i->monto_total, 2),
        ])->values();
      @endphp
      const items = @json($previewItems);
      const rows  = items.map(it =>
        `<tr>
          <td style="padding:.35rem .5rem; border-bottom:1px solid #f3f4f6; font-size:.83rem;">${it.desc}</td>
          <td style="padding:.35rem .5rem; border-bottom:1px solid #f3f4f6; text-align:right; font-size:.83rem;">${it.qty}</td>
          <td style="padding:.35rem .5rem; border-bottom:1px solid #f3f4f6; text-align:right; font-size:.83rem;">${it.price}</td>
          <td style="padding:.35rem .5rem; border-bottom:1px solid #f3f4f6; text-align:right; font-size:.83rem;">${it.igv}</td>
          <td style="padding:.35rem .5rem; border-bottom:1px solid #f3f4f6; text-align:right; font-weight:700; font-size:.83rem;">${it.total}</td>
        </tr>`
      ).join('');

      Swal.fire({
        title: 'Vista previa — {{ $invoice->serie_numero }}',
        width: '700px',
        html: `
          <div style="text-align:left; font-size:.9rem;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-bottom:1rem;">
              <div><span style="color:#6b7280; font-size:.78rem; text-transform:uppercase; font-weight:700;">Cliente</span><br><strong>{{ $invoice->client?->nombre_razon_social ?? 'N/A' }}</strong><br><small style="color:#6b7280;">{{ $invoice->client?->numero_documento ?? '' }}</small></div>
              <div><span style="color:#6b7280; font-size:.78rem; text-transform:uppercase; font-weight:700;">Comprobante</span><br><strong>{{ $invoice->serie_numero }}</strong><br><small style="color:#6b7280;">{{ $invoice->fecha_emision->format('d/m/Y') }} &mdash; {{ $invoice->forma_pago == 1 ? 'Contado' : 'Crédito' }}</small></div>
            </div>
            <table style="width:100%; border-collapse:collapse; margin-bottom:1rem;">
              <thead>
                <tr style="background:#f9fafb;">
                  <th style="padding:.4rem .5rem; text-align:left; font-size:.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Descripción</th>
                  <th style="padding:.4rem .5rem; text-align:right; font-size:.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Cant.</th>
                  <th style="padding:.4rem .5rem; text-align:right; font-size:.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">P.Unit.</th>
                  <th style="padding:.4rem .5rem; text-align:right; font-size:.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">IGV</th>
                  <th style="padding:.4rem .5rem; text-align:right; font-size:.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Total</th>
                </tr>
              </thead>
              <tbody>${rows}</tbody>
            </table>
            <div style="display:flex; justify-content:flex-end; flex-direction:column; align-items:flex-end; gap:.3rem; border-top:2px solid #e5e7eb; padding-top:.75rem;">
              <div style="font-size:.85rem; color:#6b7280;">Op. Gravadas: <strong>{{ number_format($invoice->monto_total_gravado, 2) }}</strong></div>
              <div style="font-size:.85rem; color:#6b7280;">IGV ({{ $invoice->porcentaje_igv }}%): <strong>{{ number_format($invoice->monto_total_igv, 2) }}</strong></div>
              <div style="font-size:1.1rem; font-weight:800; color:#1a6b57;">TOTAL {{ $invoice->codigo_moneda }}: {{ number_format($invoice->monto_total, 2) }}</div>
              @if($invoice->indicador_detraccion && $invoice->informacion_detraccion)
                @php $detPrev = $invoice->informacion_detraccion; $netoPrev = $invoice->monto_total - ($detPrev['monto_detraccion'] ?? 0); @endphp
              <div style="font-size:.9rem; color:#b45309;">Detracci&oacute;n ({{ $detPrev['porcentaje_detraccion'] ?? 0 }}%): <strong>&minus; {{ number_format($detPrev['monto_detraccion'] ?? 0, 2) }}</strong></div>
              <div style="font-size:1rem; font-weight:800; color:#1a6b57; border-top:1px dashed #d1fae5; padding-top:.3rem;">Neto a cobrar: {{ number_format($netoPrev, 2) }}</div>
              @endif
            </div>
            @if($invoice->forma_pago == 2 && !empty($invoice->lista_cuotas))
            <div style="margin-top:.75rem; border:1px solid #e0e7ff; border-radius:8px; overflow:hidden;">
              <div style="background:#eef2ff; padding:.4rem .75rem; font-size:.75rem; font-weight:700; color:#4f46e5; text-transform:uppercase; letter-spacing:.04em;">
                Cuotas de cr&eacute;dito
              </div>
              @foreach($invoice->lista_cuotas as $idx => $cuota)
                @php try { $fc = \Carbon\Carbon::parse($cuota['fecha_pago'])->format('d/m/Y'); } catch(\Exception $e) { $fc = $cuota['fecha_pago'] ?? '—'; } @endphp
              <div style="display:flex; justify-content:space-between; padding:.3rem .75rem; font-size:.82rem; border-top:1px solid #e0e7ff; {{ $loop->even ? 'background:#f5f3ff;' : '' }}">
                <span style="color:#6b7280;">Cuota {{ $idx + 1 }} &mdash; {{ $fc }}</span>
                <strong style="color:#4f46e5;">{{ $invoice->codigo_moneda }} {{ number_format($cuota['monto'] ?? 0, 2) }}</strong>
              </div>
              @endforeach
              <div style="display:flex; justify-content:space-between; padding:.35rem .75rem; font-size:.83rem; border-top:2px solid #c7d2fe; background:#e0e7ff;">
                <span style="font-weight:700; color:#4f46e5;">Total cuotas</span>
                <strong style="color:#4f46e5;">{{ $invoice->codigo_moneda }} {{ number_format(collect($invoice->lista_cuotas)->sum('monto'), 2) }}</strong>
              </div>
            </div>
            @endif
            <p style="margin-top:.75rem; font-size:.8rem; color:#9ca3af; text-align:center;">Esta acción enviará el comprobante a SUNAT. <strong>No se puede deshacer.</strong></p>
          </div>`,
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-send"></i> Confirmar y emitir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1a6b57',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
      }).then(result => {
        if (result.isConfirmed) document.getElementById('form-emit').submit();
      });
    });

    // — Confirmar ANULACIÓN con motivo
    document.getElementById('btn-void')?.addEventListener('click', function () {
      Swal.fire({
        title: '¿Anular comprobante?',
        html: 'Ingresa el motivo de anulación de <strong>{{ $invoice->serie_numero }}</strong>',
        icon: 'warning',
        input: 'text',
        inputLabel: 'Motivo',
        inputPlaceholder: 'Ej: Error en datos del cliente',
        inputAttributes: { maxlength: 150 },
        inputValidator: value => { if (!value || !value.trim()) return 'El motivo es requerido.'; },
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-x-circle"></i> Anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
      }).then(result => {
        if (result.isConfirmed) {
          document.getElementById('void-motivo').value = result.value;
          document.getElementById('form-void').submit();
        }
      });
    });

    // — Retirar falso duplicado Feasy/SUNAT con motivo auditado
    document.getElementById('btn-release-failed-emission')?.addEventListener('click', function () {
      Swal.fire({
        title: 'Marcar correlativo como no emitido',
        html: 'Usa esta acción solo si verificaste que <strong>{{ $invoice->serie_numero }}</strong> no existe en SUNAT ni en Feasy. El número quedará ocupado y no volverá a sugerirse.',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Motivo y evidencia',
        inputPlaceholder: 'Ej: SUNAT indica NO EXISTE con total 1180.00 y Feasy no muestra registros del 28/04 al 30/04.',
        inputAttributes: { maxlength: 500 },
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-block"></i> Marcar no emitido',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#b45309',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        inputValidator: value => {
          if (!value || value.trim().length < 10) return 'El motivo debe tener al menos 10 caracteres.';
        },
      }).then(result => {
        if (result.isConfirmed) {
          document.getElementById('release-failed-emission-motivo').value = result.value;
          document.getElementById('form-release-failed-emission').submit();
        }
      });
    });

    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4000);
    });

    // ── Confirmación eliminar cobro ────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
          title: this.dataset.confirm ?? '¿Confirmar?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#dc2626',
          cancelButtonColor: '#6b7280',
          reverseButtons: true,
        }).then(r => { if (r.isConfirmed) this.submit(); });
      });
    });

    // ── Modal Más Detalle (horizontal) ────────────────────────────────────
    document.getElementById('btn-ver-detalle')?.addEventListener('click', function () {
      @php
        $detItems = $invoice->items->map(fn($i) => [
          'cor'   => $i->correlativo,
          'cod'   => $i->codigo_interno ?? '—',
          'desc'  => $i->descripcion,
          'unidad'=> $i->codigo_unidad_medida,
          'qty'   => $i->cantidad,
          'valor' => number_format($i->monto_valor_unitario, 4),
          'precio'=> number_format($i->monto_precio_unitario, 4),
          'afecto'=> $i->codigo_indicador_afecto,
          'igv'   => number_format($i->monto_igv, 2),
          'total' => number_format($i->monto_total, 2),
        ])->values();
      @endphp
      const items = @json($detItems);

      const rows = items.map(it => `
        <tr style="border-bottom:1px solid #f3f4f6;">
          <td style="padding:.3rem .45rem; font-size:.78rem; text-align:center; color:#6b7280;">${it.cor}</td>
          <td style="padding:.3rem .45rem; font-size:.78rem; font-family:monospace;">${it.cod}</td>
          <td style="padding:.3rem .45rem; font-size:.8rem; min-width:180px;">${it.desc}</td>
          <td style="padding:.3rem .45rem; font-size:.78rem; text-align:center;">${it.unidad}</td>
          <td style="padding:.3rem .45rem; font-size:.78rem; text-align:right;">${it.qty}</td>
          <td style="padding:.3rem .45rem; font-size:.78rem; text-align:right;">${it.valor}</td>
          <td style="padding:.3rem .45rem; font-size:.78rem; text-align:right;">${it.precio}</td>
          <td style="padding:.3rem .45rem; font-size:.78rem; text-align:center; color:#6b7280;">${it.afecto}</td>
          <td style="padding:.3rem .45rem; font-size:.78rem; text-align:right;">${it.igv}</td>
          <td style="padding:.3rem .45rem; font-size:.8rem; font-weight:700; text-align:right; color:#1a6b57;">${it.total}</td>
        </tr>`).join('');

      Swal.fire({
        title: '{{ $invoice->serie_numero }} — Detalle completo',
        width: Math.min(window.innerWidth * 0.96, 1100) + 'px',
        html: `
          <div style="text-align:left; font-size:.85rem;">
            {{-- Cabecera en columnas --}}
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:.5rem 1.5rem; background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:1rem; margin-bottom:1rem;">
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280; letter-spacing:.04em;">Tipo</span><br><strong>{{ $invoice->codigo_tipo_documento }}</strong></div>
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280;">Serie-Número</span><br><strong style="font-family:monospace;">{{ $invoice->serie_numero }}</strong></div>
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280;">Fecha Emisión</span><br><strong>{{ $invoice->fecha_emision->format('d/m/Y') }}</strong></div>
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280;">Forma de Pago</span><br><strong>{{ $invoice->forma_pago == 1 ? 'Contado' : 'Crédito' }}</strong>{{ $invoice->fecha_vencimiento ? '<br><small style=\"color:#6b7280;\">Vence: '.$invoice->fecha_vencimiento->format('d/m/Y').'</small>' : '' }}</div>
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280;">Cliente</span><br><strong>{{ $invoice->client?->nombre_razon_social ?? '—' }}</strong></div>
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280;">RUC / DNI</span><br><strong>{{ $invoice->client?->numero_documento ?? '—' }}</strong></div>
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280;">Moneda</span><br><strong>{{ $invoice->codigo_moneda }}</strong></div>
              <div><span style="font-size:.7rem; text-transform:uppercase; font-weight:700; color:#6b7280;">IGV</span><br><strong>{{ $invoice->porcentaje_igv }}%</strong></div>
            </div>

            {{-- Tabla de ítems --}}
            <div style="overflow-x:auto; margin-bottom:1rem;">
              <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
                <thead>
                  <tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase;">#</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Código</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase; text-align:left;">Descripción</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase;">UM</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase; text-align:right;">Cant.</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase; text-align:right;">V. Unit.</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase; text-align:right;">P. Unit.</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Afecto</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase; text-align:right;">IGV</th>
                    <th style="padding:.35rem .45rem; font-size:.7rem; color:#6b7280; font-weight:700; text-transform:uppercase; text-align:right;">Total</th>
                  </tr>
                </thead>
                <tbody>${rows}</tbody>
              </table>
            </div>

            {{-- Totales --}}
            <div style="display:flex; justify-content:flex-end;">
              <table style="border-collapse:collapse; font-size:.85rem; min-width:280px;">
                <tr>
                  <td style="padding:.25rem .75rem; color:#6b7280; text-align:right;">Op. Gravadas:</td>
                  <td style="padding:.25rem .75rem; text-align:right; font-weight:600;">{{ $invoice->codigo_moneda }} {{ number_format($invoice->monto_total_gravado, 2) }}</td>
                </tr>
                @if($invoice->monto_total_exonerado > 0)
                <tr>
                  <td style="padding:.25rem .75rem; color:#6b7280; text-align:right;">Op. Exoneradas:</td>
                  <td style="padding:.25rem .75rem; text-align:right; font-weight:600;">{{ $invoice->codigo_moneda }} {{ number_format($invoice->monto_total_exonerado, 2) }}</td>
                </tr>
                @endif
                @if($invoice->monto_total_inafecto > 0)
                <tr>
                  <td style="padding:.25rem .75rem; color:#6b7280; text-align:right;">Op. Inafectas:</td>
                  <td style="padding:.25rem .75rem; text-align:right; font-weight:600;">{{ $invoice->codigo_moneda }} {{ number_format($invoice->monto_total_inafecto, 2) }}</td>
                </tr>
                @endif
                <tr>
                  <td style="padding:.25rem .75rem; color:#6b7280; text-align:right;">IGV ({{ $invoice->porcentaje_igv }}%):</td>
                  <td style="padding:.25rem .75rem; text-align:right; font-weight:600;">{{ $invoice->codigo_moneda }} {{ number_format($invoice->monto_total_igv, 2) }}</td>
                </tr>
                <tr style="border-top:2px solid #e5e7eb;">
                  <td style="padding:.5rem .75rem; font-weight:800; font-size:1rem; text-align:right;">TOTAL:</td>
                  <td style="padding:.5rem .75rem; font-weight:800; font-size:1.1rem; text-align:right; color:#1a6b57;">{{ $invoice->codigo_moneda }} {{ number_format($invoice->monto_total, 2) }}</td>
                </tr>
              </table>
            </div>
          </div>`,
        showConfirmButton: false,
        showCloseButton: true,
        focusConfirm: false,
      });
    });
  </script>
@endpush
