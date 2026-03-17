@extends('layouts.app')

@section('title', 'Nueva Factura — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .items-table-wrapper {
        width: 100%;
        overflow-x: auto !important;
        display: block;
        padding-bottom: 0.5rem;
        background: transparent;
      }
      .items-table {
        min-width: 1200px;
        width: 100%;
      }
      .invoice-create-grid > div:first-child {
        overflow: visible !important;
      }
    .invoice-create-grid { display:grid; grid-template-columns: 1fr 320px; gap:1.5rem; align-items:start; }
    .invoice-create-grid > div:first-child { min-width:0; overflow:auto; }
    /* Permitir scroll horizontal en la tabla de ítems */
    .items-table-wrapper { overflow-x:auto; }
    @media (max-width:960px) { .invoice-create-grid { grid-template-columns:1fr; } }
    .items-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .items-table th { background:#f9fafb; padding:.5rem .6rem; text-align:left; font-weight:600; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
    .items-table td { padding:.4rem .5rem; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
    .items-table input, .items-table select { padding:.3rem .5rem; border:1px solid #e5e7eb; border-radius:6px; font-size:.83rem; width:100%; box-sizing:border-box; }
    .totals-box { background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:1.25rem; }
    .totals-row { display:flex; justify-content:space-between; margin-bottom:.5rem; font-size:.9rem; }
    .totals-row.grand { font-size:1.1rem; font-weight:700; color:#1a6b57; border-top:1px solid #e5e7eb; padding-top:.75rem; margin-top:.5rem; }
    .form-section-title { font-weight:600; font-size:.95rem; color:#374151; margin:1.25rem 0 .75rem; padding-bottom:.4rem; border-bottom:1px solid #e5e7eb; }

    /* ── Detracción SPOT ──────────────────────────────────────────────── */
    .detrac-box { background:#fffbeb; border:1px solid #fde68a; border-left:4px solid #f59e0b; border-radius:10px; padding:1rem 1.1rem; margin-top:1rem; }
    .detrac-box-off { background:#f9fafb; border:1px solid #e5e7eb; border-left:4px solid #d1d5db; border-radius:10px; padding:.75rem 1.1rem; margin-top:1rem; cursor:pointer; }
    .detrac-box-off:hover { border-left-color:#f59e0b; background:#fffdf0; }
    .detrac-toggle-row { display:flex; align-items:center; gap:.6rem; }
    .detrac-toggle-row label { font-weight:600; font-size:.9rem; color:#78350f; cursor:pointer; }
    .detrac-fields { margin-top:.85rem; display:flex; flex-direction:column; gap:.6rem; }
    .detrac-fields .form-group { margin:0; }
    .detrac-fields label { font-size:.78rem; }
    .detrac-monto-row { display:flex; align-items:center; gap:.5rem; background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:.5rem .85rem; margin-top:.5rem; }
    .detrac-monto-row .lbl { font-size:.78rem; font-weight:600; color:#78350f; flex:1; }
    .detrac-monto-row .val { font-size:1.1rem; font-weight:800; color:#92400e; font-family:monospace; }
    .detrac-alert { display:flex; align-items:center; gap:.4rem; font-size:.78rem; color:#78350f; margin-top:.5rem; }
    .detrac-alert i { color:#f59e0b; font-size:1rem; }
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

          @if ($errors->any())
            <div class="placeholder-content module-alert">
              @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
          @endif

          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <h1>Nuevo Comprobante</h1>
              <a href="{{ route('facturador.invoices.index') }}" class="btn-secondary">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
            </div>

            <form method="POST" action="{{ route('facturador.invoices.store') }}" id="invoice-form">
              @csrf

              <div class="invoice-create-grid">

                {{-- === IZQUIERDA: cabecera + items === --}}
                <div>
                  <p class="form-section-title">Datos del Comprobante</p>
                  <div class="companies-form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:.8rem;">

                    <div class="form-group">
                      <label>Tipo de Comprobante *</label>
                      <select name="codigo_tipo_documento" id="tipo-doc-select" class="form-input" required>
                        <option value="">— Seleccionar —</option>
                        <option value="01" {{ old('codigo_tipo_documento','01') == '01' ? 'selected' : '' }}>01 — Factura</option>
                        <option value="03" {{ old('codigo_tipo_documento') == '03' ? 'selected' : '' }}>03 — Boleta</option>
                        <option value="07" {{ old('codigo_tipo_documento') == '07' ? 'selected' : '' }}>07 — N. Crédito</option>
                        <option value="08" {{ old('codigo_tipo_documento') == '08' ? 'selected' : '' }}>08 — N. Débito</option>
                        <option value="09" {{ old('codigo_tipo_documento') == '09' ? 'selected' : '' }}>09 — Guía Remisión</option>
                      </select>
                      @error('codigo_tipo_documento')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                      <label>Serie *</label>
                      <input type="text" name="serie_documento" id="serie_documento" maxlength="4" class="form-input"
                        style="background:#f3f4f6; cursor:default;"
                        value="{{ old('serie_documento', $suggestions['01']['serie'] ?? 'F001') }}" readonly required>
                      @error('serie_documento')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                      <label>Número *</label>
                      <input type="number" name="numero_documento" id="numero_documento" min="1" class="form-input"
                        style="background:#f3f4f6; cursor:default;"
                        value="{{ old('numero_documento', $suggestions['01']['numero'] ?? 1) }}" readonly required>
                      @error('numero_documento')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                      <label>Código Interno</label>
                      <input type="text" name="codigo_interno" id="codigo_interno" class="form-input"
                        style="background:#f3f4f6; cursor:default; font-family:monospace; font-size:.82rem;"
                        value="{{ old('codigo_interno') }}" readonly>
                    </div>

                    <div class="form-group">
                      <label>Fecha Emisión *</label>
                      <input type="date" name="fecha_emision" class="form-input" value="{{ old('fecha_emision', date('Y-m-d')) }}" required>
                      @error('fecha_emision')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                      <label>Hora Emisión *</label>
                      <input type="time" name="hora_emision" class="form-input" value="{{ old('hora_emision', date('H:i')) }}" required>
                    </div>

                    <div class="form-group non-gre-field" id="div-fecha-vencimiento">
                      <label>Fecha Vencimiento</label>
                      <input type="date" name="fecha_vencimiento" class="form-input" value="{{ old('fecha_vencimiento') }}">
                    </div>

                    <div class="form-group non-gre-field">
                      <label>Moneda *</label>
                      <select name="codigo_moneda" class="form-input" required>
                        <option value="PEN" {{ old('codigo_moneda','PEN') == 'PEN' ? 'selected' : '' }}>PEN — Soles</option>
                        <option value="USD" {{ old('codigo_moneda') == 'USD' ? 'selected' : '' }}>USD — Dólares</option>
                        <option value="EUR" {{ old('codigo_moneda') == 'EUR' ? 'selected' : '' }}>EUR — Euros</option>
                      </select>
                    </div>

                    <div class="form-group non-gre-field">
                      <label>Forma de Pago *</label>
                      <select name="forma_pago" id="forma-pago-select" class="form-input" required>
                        <option value="1" {{ old('forma_pago','1') == '1' ? 'selected' : '' }}>Contado</option>
                        <option value="2" {{ old('forma_pago') == '2' ? 'selected' : '' }}>Crédito</option>
                      </select>
                    </div>

                    {{-- ══ CUOTAS (visible solo cuando forma_pago = Crédito) ══ --}}
                    <div id="cuotas-section"
                         style="grid-column:1/-1; display:{{ old('forma_pago') == '2' ? 'block' : 'none' }}; margin-top:.2rem;">
                      <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:.5rem; padding:.9rem 1rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.7rem;">
                          <span style="font-weight:600; font-size:.9rem; color:#166534;">
                            <i class='bx bx-calendar-check' style="vertical-align:middle;"></i>
                            Plan de Cuotas
                          </span>
                          <div style="display:flex; gap:.5rem; align-items:center;">
                            <span style="font-size:.78rem; color:#64748b;">Suma cuotas:</span>
                            <strong id="cuotas-suma" style="font-size:.88rem; color:#166534;">S/ 0.00</strong>
                          </div>
                        </div>

                        <div id="cuotas-body" style="display:flex; flex-direction:column; gap:.4rem;">
                          @foreach(old('lista_cuotas', []) as $ci => $cuota)
                            <div class="cuota-row" style="display:flex; gap:.5rem; align-items:center;">
                              <span style="flex:0 0 24px; text-align:center; font-size:.78rem; color:#94a3b8; font-weight:600;">{{ $ci + 1 }}</span>
                              <input type="date" name="lista_cuotas[{{ $ci }}][fecha_pago]"
                                class="form-input cuota-fecha"
                                style="flex:1; font-size:.85rem;"
                                value="{{ $cuota['fecha_pago'] ?? '' }}"
                                placeholder="Fecha de pago">
                              <input type="number" name="lista_cuotas[{{ $ci }}][monto]"
                                class="form-input cuota-monto"
                                style="flex:1; font-size:.85rem;"
                                step="0.01" min="0.01"
                                value="{{ $cuota['monto'] ?? '' }}"
                                placeholder="Monto S/">
                              <button type="button" class="btn-action-icon cuota-remove" title="Quitar cuota">
                                <i class='bx bx-trash'></i>
                              </button>
                            </div>
                          @endforeach
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:.7rem;">
                          <button type="button" id="cuota-add" class="btn-secondary" style="font-size:.82rem; padding:.3rem .8rem;">
                            <i class='bx bx-plus'></i> Agregar cuota
                          </button>
                          <span style="font-size:.76rem; color:#9ca3af;">Máx. 12 cuotas. La suma debe igualar el monto total del comprobante.</span>
                        </div>
                      </div>
                    </div>

                    <div class="form-group non-gre-field">
                      <label>IGV % *</label>
                      <select name="porcentaje_igv" id="igv-pct" class="form-input" required>
                        <option value="18" {{ old('porcentaje_igv', config('facturador.igv_porcentaje', 18)) == '18' ? 'selected' : '' }}>18% — IGV Regular</option>
                        <option value="10.5" {{ old('porcentaje_igv') == '10.5' ? 'selected' : '' }}>10.5% — IGV Reducido</option>
                        <option value="0" {{ old('porcentaje_igv') == '0' ? 'selected' : '' }}>0% — Exonerado / Inafecto</option>
                      </select>
                    </div>

                    <div class="form-group non-gre-field">
                      <label>N° Orden Compra</label>
                      <input type="text" name="numero_orden_compra" id="numero_orden_compra" class="form-input"
                        value="{{ old('numero_orden_compra') }}">
                    </div>

                    <div class="form-group">
                      <label>Correo del cliente</label>
                      <input type="email" name="correo" class="form-input" value="{{ old('correo') }}">
                    </div>

                    <div class="form-group" style="grid-column:1/-1;">
                      <label>Observación</label>
                      <textarea name="observacion" class="form-input" rows="2">{{ old('observacion') }}</textarea>
                    </div>

                  </div>

                  {{-- === CAMPOS GRE (ocultos para 01/03, visibles para 09) === --}}
                  <div id="gre-fields" style="display:none;">
                    <p class="form-section-title" style="margin-top:1rem;">Datos del Traslado</p>
                    <div class="companies-form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:.8rem;">

                      <div class="form-group">
                        <label>Motivo de Traslado *</label>
                        <select name="codigo_motivo_traslado" class="form-input">
                          <option value="">-- Seleccionar --</option>
                          <option value="01" {{ old('codigo_motivo_traslado') == '01' ? 'selected' : '' }}>01 — Venta</option>
                          <option value="02" {{ old('codigo_motivo_traslado') == '02' ? 'selected' : '' }}>02 — Compra</option>
                          <option value="04" {{ old('codigo_motivo_traslado') == '04' ? 'selected' : '' }}>04 — Traslado entre establecimientos</option>
                          <option value="08" {{ old('codigo_motivo_traslado') == '08' ? 'selected' : '' }}>08 — Importación</option>
                          <option value="09" {{ old('codigo_motivo_traslado') == '09' ? 'selected' : '' }}>09 — Exportación</option>
                          <option value="13" {{ old('codigo_motivo_traslado') == '13' ? 'selected' : '' }}>13 — Otros</option>
                        </select>
                      </div>

                      <div class="form-group">
                        <label>Descripción del Motivo *</label>
                        <input type="text" name="descripcion_motivo_traslado" class="form-input"
                          value="{{ old('descripcion_motivo_traslado') }}" maxlength="200">
                      </div>

                      <div class="form-group">
                        <label>Modalidad de Traslado *</label>
                        <select name="codigo_modalidad_traslado" class="form-input">
                          <option value="">-- Seleccionar --</option>
                          <option value="01" {{ old('codigo_modalidad_traslado','02') == '01' ? 'selected' : '' }}>01 — Transporte público</option>
                          <option value="02" {{ old('codigo_modalidad_traslado','02') == '02' ? 'selected' : '' }}>02 — Transporte privado</option>
                        </select>
                      </div>

                      <div class="form-group">
                        <label>Fecha Inicio Traslado *</label>
                        <input type="date" name="fecha_inicio_traslado" class="form-input"
                          value="{{ old('fecha_inicio_traslado', date('Y-m-d')) }}">
                      </div>

                      <div class="form-group">
                        <label>Unidad de Peso *</label>
                        <select name="codigo_unidad_medida_peso_bruto" class="form-input">
                          <option value="KGM" selected>KGM — Kilogramos</option>
                          <option value="TNE" {{ old('codigo_unidad_medida_peso_bruto') == 'TNE' ? 'selected' : '' }}>TNE — Toneladas</option>
                        </select>
                      </div>

                      <div class="form-group">
                        <label>Peso Bruto Total *</label>
                        <input type="number" name="peso_bruto_total" class="form-input"
                          min="0" step="0.01" value="{{ old('peso_bruto_total', 1) }}">
                      </div>

                    </div>
                  </div>

                  {{-- === ÍTEMS === --}}
                  <div style="display:flex; justify-content:space-between; align-items:center; margin:1.25rem 0 .75rem;">
                    <p class="form-section-title" style="margin:0;">Ítems del Comprobante</p>
                    <button type="button" id="add-item-btn" class="btn-primary" style="font-size:.83rem; padding:.35rem .9rem;">
                      <i class='bx bx-plus'></i> Agregar ítem
                    </button>
                  </div>

                  <div class="items-table-wrapper">
                    <table class="items-table" id="items-table" style="min-width:950px;">
                      <thead>
                        <tr>
                          <th style="min-width:170px;">Producto/Servicio</th>
                          <th style="min-width:80px;">Código</th>
                          <th class="item-monetary-th">Tipo</th>
                          <th style="min-width:160px;">Descripción *</th>
                          <th>Unidad</th>
                          <th style="min-width:75px;">Cant *</th>
                          <th class="item-monetary-th" style="min-width:100px;">P. Unitario *</th>
                          <th class="item-monetary-th">Afecto</th>
                          <th class="item-monetary-th" style="min-width:85px; text-align:right;">Total</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody id="items-body">
                        @php $oldItems = old('items', [[]]); @endphp
                        @foreach($oldItems as $i => $item)
                          <tr class="item-row" data-index="{{ $i }}">
                            <td>
                              <select class="product-picker" style="font-size:.8rem;">
                                <option value="">— Seleccionar —</option>
                                @foreach($products as $prod)
                                  <option value="{{ $prod->id }}"
                                    data-codigo="{{ $prod->codigo_interno }}"
                                    data-tipo="{{ $prod->tipo ?? 'S' }}"
                                    data-desc="{{ $prod->descripcion }}"
                                    data-unidad="{{ $prod->codigo_unidad_medida }}"
                                    data-precio="{{ $prod->precio_unitario }}"
                                    data-afecto="{{ $prod->codigo_indicador_afecto ?? '10' }}"
                                  >{{ $prod->descripcion }}</option>
                                @endforeach
                              </select>
                            </td>
                            <td><input type="text" name="items[{{ $i }}][codigo_interno]" value="{{ $item['codigo_interno'] ?? '' }}"></td>
                            <td class="item-monetary-td">
                              <select name="items[{{ $i }}][tipo]">
                                <option value="P" {{ ($item['tipo'] ?? 'P') == 'P' ? 'selected' : '' }}>Prod</option>
                                <option value="S" {{ ($item['tipo'] ?? '') == 'S' ? 'selected' : '' }}>Serv</option>
                              </select>
                            </td>
                            <td><input type="text" name="items[{{ $i }}][descripcion]" class="descripcion" value="{{ $item['descripcion'] ?? '' }}" required></td>
                            <td>
                              <select name="items[{{ $i }}][codigo_unidad_medida]">
                                @foreach(['NIU','ZZ','KGM','MTR','LTR'] as $u)
                                  <option value="{{ $u }}" {{ ($item['codigo_unidad_medida'] ?? 'NIU') == $u ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                              </select>
                            </td>
                            <td><input type="number" name="items[{{ $i }}][cantidad]" class="qty" min="0.001" step="0.001" value="{{ $item['cantidad'] ?? '' }}" required></td>
                            <td class="item-monetary-td"><input type="number" name="items[{{ $i }}][monto_precio_unitario]" class="price" min="0" step="0.0001" value="{{ $item['monto_precio_unitario'] ?? '' }}"></td>
                            <td class="item-monetary-td">
                              <select name="items[{{ $i }}][codigo_indicador_afecto]" class="afecto">
                                <option value="10" {{ ($item['codigo_indicador_afecto'] ?? '10') == '10' ? 'selected' : '' }}>10-Grav</option>
                                <option value="20" {{ ($item['codigo_indicador_afecto'] ?? '') == '20' ? 'selected' : '' }}>20-Exon</option>
                                <option value="30" {{ ($item['codigo_indicador_afecto'] ?? '') == '30' ? 'selected' : '' }}>30-Ina</option>
                                <option value="40" {{ ($item['codigo_indicador_afecto'] ?? '') == '40' ? 'selected' : '' }}>40-Exp</option>
                              </select>
                            </td>
                            <td class="item-monetary-td row-total" style="text-align:right; font-weight:600;">{{ $item['monto_total'] ?? '0.00' }}</td>
                            <td><button type="button" class="btn-action-icon remove-item" title="Quitar"><i class='bx bx-trash'></i></button></td>
                            <td style="display:none;"><input type="hidden" name="items[{{ $i }}][monto_valor_unitario]" class="valor_unitario" value="{{ $item['monto_valor_unitario'] ?? '' }}"></td>
                            <td style="display:none;"><input type="hidden" name="items[{{ $i }}][monto_igv]" class="monto_igv_input" value="{{ $item['monto_igv'] ?? '' }}"></td>
                            <td style="display:none;"><input type="hidden" name="items[{{ $i }}][monto_valor_total]" class="monto_valor_total_input" value="{{ $item['monto_valor_total'] ?? '' }}"></td>
                            <td style="display:none;"><input type="hidden" name="items[{{ $i }}][monto_total]" class="monto_total_input" value="{{ $item['monto_total'] ?? '' }}"></td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>

                {{-- === DERECHA: cliente + totales (no-GRE) | GRE data (GRE) === --}}
                <div>

                  {{-- Panel NON-GRE: cliente + resumen financiero --}}
                  <div id="panel-no-gre">
                    <p class="form-section-title">Cliente</p>
                    <div class="form-group">
                      <label>Seleccionar cliente *</label>
                      <select name="client_id" class="form-input">
                        <option value="">— Seleccionar —</option>
                        @foreach($clients as $client)
                          <option value="{{ $client->id }}"
                            data-email="{{ $client->correo ?? '' }}"
                            data-tipo-doc="{{ $client->codigo_tipo_documento }}"
                            {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->numero_documento }} — {{ $client->nombre_razon_social }}
                          </option>
                        @endforeach
                      </select>
                      @error('client_id')<p class="form-error">{{ $message }}</p>@enderror
                      <a href="{{ route('facturador.clients.create') }}" style="font-size:.82rem; color:#1a6b57; text-decoration:none; display:inline-block; margin-top:.4rem;">
                        <i class='bx bx-plus'></i> Nuevo cliente
                      </a>
                      <p id="client-doc-warning" style="display:none; font-size:.82rem; color:#dc2626; margin-top:.4rem; background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:.4rem .7rem;"></p>
                    </div>

                    <p class="form-section-title" style="margin-top:1.5rem;">Resumen</p>
                    <div class="totals-box">
                      <div class="totals-row"><span>Gravado</span><span id="lbl-gravado">0.00</span></div>
                      <div class="totals-row"><span>Exonerado</span><span id="lbl-exonerado">0.00</span></div>
                      <div class="totals-row"><span>Inafecto</span><span id="lbl-inafecto">0.00</span></div>
                      <div class="totals-row"><span>IGV</span><span id="lbl-igv">0.00</span></div>
                      <div class="totals-row grand"><span>Total</span><span id="lbl-total">0.00</span></div>
                    </div>

                    {{-- Campos ocultos totales --}}
                    <input type="hidden" name="monto_total_gravado"   id="h-gravado"   value="{{ old('monto_total_gravado', 0) }}">
                    <input type="hidden" name="monto_total_exonerado" id="h-exonerado" value="{{ old('monto_total_exonerado', 0) }}">
                    <input type="hidden" name="monto_total_inafecto"  id="h-inafecto"  value="{{ old('monto_total_inafecto', 0) }}">
                    <input type="hidden" name="monto_total_igv"       id="h-igv"       value="{{ old('monto_total_igv', 0) }}">
                    <input type="hidden" name="monto_total"           id="h-total"     value="{{ old('monto_total', 0) }}">

                    {{-- ── Detracción SPOT ─────────────────────────────── --}}
                    {{-- Campo indicador SIEMPRE presente (fuera de contenedor oculto) --}}
                    <input type="hidden" name="indicador_detraccion" value="0" id="h-detrac-indicator">

                    <div id="detrac-wrapper" style="display:none;">

                      {{-- Caja colapsada (toggle apagado) --}}
                      <div id="detrac-box-off" class="detrac-box-off" onclick="toggleDetrac(true)">
                        <div class="detrac-toggle-row">
                          <i class='bx bx-transfer-alt' style="color:#d97706; font-size:1.1rem;"></i>
                          <label>Operación sujeta a detracción (SPOT)</label>
                          <span style="margin-left:auto; font-size:.75rem; color:#92400e; font-weight:600;">Click para activar</span>
                        </div>
                      </div>

                      {{-- Caja expandida (toggle encendido) --}}
                      <div id="detrac-box-on" class="detrac-box" style="display:none;">
                        <div class="detrac-toggle-row" style="margin-bottom:.1rem;">
                          <i class='bx bx-transfer-alt' style="color:#d97706; font-size:1.1rem;"></i>
                          <label style="color:#78350f;">Detracción SPOT activa</label>
                          <button type="button" onclick="toggleDetrac(false)" style="margin-left:auto; font-size:.75rem; background:none; border:none; color:#92400e; cursor:pointer; font-weight:600;">✕ Quitar</button>
                        </div>

                        <div class="detrac-fields">
                          <div class="form-group">
                            <label>Bien / Servicio sujeto a detracción *</label>
                            <select name="informacion_detraccion[codigo_bbss_sujeto_detraccion]"
                                    id="detrac-codigo" class="form-input" onchange="onDetracCodigoChange(this)">
                              <option value="">— Seleccionar código SUNAT —</option>
                              @foreach($spotDetracciones as $spot)
                                <option value="{{ $spot->codigo }}"
                                  data-pct="{{ $spot->porcentaje }}"
                                  {{ old('informacion_detraccion.codigo_bbss_sujeto_detraccion') === $spot->codigo ? 'selected' : '' }}>
                                  {{ $spot->codigo }} — {{ $spot->descripcion }} ({{ $spot->porcentaje }}%)
                                </option>
                              @endforeach
                            </select>
                          </div>

                          <div class="form-group">
                            <label>% Detracción</label>
                            <input type="number" name="informacion_detraccion[porcentaje_detraccion]"
                                   id="detrac-pct" class="form-input" min="0" max="100" step="0.01"
                                   value="{{ old('informacion_detraccion.porcentaje_detraccion', 0) }}"
                                   oninput="recalcDetrac()">
                          </div>

                          <div class="form-group">
                            <label>Cuenta Banco de la Nación * <small style="color:#888;font-weight:normal;">(11 dígitos)</small></label>
                            <input type="text" name="informacion_detraccion[cuenta_banco_detraccion]"
                                   id="detrac-cuenta" class="form-input" maxlength="11"
                                   inputmode="numeric" autocomplete="off"
                                   placeholder="Ej: 00001545000"
                                   value="{{ preg_replace('/[^0-9]/', '', old('informacion_detraccion.cuenta_banco_detraccion', '')) }}">
                          </div>

                          <div class="form-group">
                            <label>Medio de pago detracción</label>
                            <select name="informacion_detraccion[codigo_medio_pago_detraccion]" class="form-input">
                              <option value="001" {{ old('informacion_detraccion.codigo_medio_pago_detraccion','001') == '001' ? 'selected' : '' }}>001 — Depósito en cuenta</option>
                              <option value="002" {{ old('informacion_detraccion.codigo_medio_pago_detraccion') == '002' ? 'selected' : '' }}>002 — Giro</option>
                              <option value="003" {{ old('informacion_detraccion.codigo_medio_pago_detraccion') == '003' ? 'selected' : '' }}>003 — Transferencia de fondos</option>
                              <option value="004" {{ old('informacion_detraccion.codigo_medio_pago_detraccion') == '004' ? 'selected' : '' }}>004 — Orden de pago</option>
                              <option value="005" {{ old('informacion_detraccion.codigo_medio_pago_detraccion') == '005' ? 'selected' : '' }}>005 — Tarjeta de débito</option>
                            </select>
                          </div>

                          {{-- Monto calculado (solo visual + hidden) --}}
                          <input type="hidden" name="informacion_detraccion[monto_detraccion]"
                                 id="h-detrac-monto" value="{{ old('informacion_detraccion.monto_detraccion', 0) }}">
                          <div class="detrac-monto-row">
                            <span class="lbl"><i class='bx bx-calculator' style="vertical-align:middle;"></i> Monto a detraer:</span>
                            <span class="val" id="lbl-detrac-monto">S/ 0.00</span>
                          </div>

                          <div class="detrac-alert">
                            <i class='bx bx-info-circle'></i>
                            El comprador debe depositar este monto en la cuenta de detracciones del Banco de la Nación <strong>antes o durante el pago</strong>.
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                  {{-- Panel GRE: destinatario + puntos + vehículos --}}
                  <div id="panel-gre" style="display:none;">

                    <p class="form-section-title">Destinatario</p>
                    <div class="form-group">
                      <label>Tipo Documento *</label>
                      <select name="gre_destinatario[codigo_tipo_documento_destinatario]" class="form-input">
                        <option value="1" {{ old('gre_destinatario.codigo_tipo_documento_destinatario','1') == '1' ? 'selected' : '' }}>1 — DNI</option>
                        <option value="6" {{ old('gre_destinatario.codigo_tipo_documento_destinatario') == '6' ? 'selected' : '' }}>6 — RUC</option>
                        <option value="4" {{ old('gre_destinatario.codigo_tipo_documento_destinatario') == '4' ? 'selected' : '' }}>4 — Carnet Extranjería</option>
                        <option value="7" {{ old('gre_destinatario.codigo_tipo_documento_destinatario') == '7' ? 'selected' : '' }}>7 — Pasaporte</option>
                        <option value="0" {{ old('gre_destinatario.codigo_tipo_documento_destinatario') == '0' ? 'selected' : '' }}>0 — Sin documento</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>N° Documento *</label>
                      <input type="text" name="gre_destinatario[numero_documento_destinatario]" class="form-input"
                        value="{{ old('gre_destinatario.numero_documento_destinatario') }}">
                    </div>
                    <div class="form-group">
                      <label>Razón Social / Nombre *</label>
                      <input type="text" name="gre_destinatario[nombre_razon_social_destinatario]" class="form-input"
                        value="{{ old('gre_destinatario.nombre_razon_social_destinatario') }}">
                    </div>

                    <p class="form-section-title" style="margin-top:1rem;">Punto de Partida</p>
                    <div class="form-group">
                      <label>Ubigeo *</label>
                      <input type="text" name="gre_punto_partida[ubigeo_punto_partida]" class="form-input"
                        maxlength="6" placeholder="Ej: 150101"
                        value="{{ old('gre_punto_partida.ubigeo_punto_partida') }}">
                    </div>
                    <div class="form-group">
                      <label>Dirección *</label>
                      <input type="text" name="gre_punto_partida[direccion_punto_partida]" class="form-input"
                        value="{{ old('gre_punto_partida.direccion_punto_partida') }}">
                    </div>

                    <p class="form-section-title" style="margin-top:1rem;">Punto de Llegada</p>
                    <div class="form-group">
                      <label>Ubigeo *</label>
                      <input type="text" name="gre_punto_llegada[ubigeo_punto_llegada]" class="form-input"
                        maxlength="6" placeholder="Ej: 150101"
                        value="{{ old('gre_punto_llegada.ubigeo_punto_llegada') }}">
                    </div>
                    <div class="form-group">
                      <label>Dirección *</label>
                      <input type="text" name="gre_punto_llegada[direccion_punto_llegada]" class="form-input"
                        value="{{ old('gre_punto_llegada.direccion_punto_llegada') }}">
                    </div>

                    <p class="form-section-title" style="margin-top:1rem;">Vehículos</p>
                    <table class="items-table" style="font-size:.82rem; margin-bottom:.5rem;">
                      <thead><tr><th>Placa *</th><th style="width:60px; text-align:center;">Principal</th><th></th></tr></thead>
                      <tbody id="vehiculos-body">
                        <tr>
                          <td><input type="text" name="gre_vehiculos[0][numero_placa]" class="form-input" placeholder="ABC123" value="{{ old('gre_vehiculos.0.numero_placa') }}"></td>
                          <td style="text-align:center;"><input type="hidden" name="gre_vehiculos[0][indicador_principal]" value="0"><input type="checkbox" name="gre_vehiculos[0][indicador_principal]" value="1" checked></td>
                          <td><button type="button" class="btn-action-icon remove-v" title="Quitar"><i class='bx bx-trash'></i></button></td>
                        </tr>
                      </tbody>
                    </table>
                    <button type="button" id="add-vehiculo-btn" class="btn-secondary" style="font-size:.82rem; padding:.35rem .9rem; width:100%; margin-bottom:.5rem;">
                      <i class='bx bx-plus'></i> Agregar vehículo
                    </button>

                  </div>

                  {{-- Botones siempre visibles --}}
                  <div style="display:flex; flex-direction:column; gap:.5rem; margin-top:1.25rem;">
                    <button type="submit" class="btn-primary">
                      <i class='bx bx-save'></i> Guardar Comprobante
                    </button>
                    <a href="{{ route('facturador.invoices.index') }}" class="btn-secondary" style="text-align:center;">Cancelar</a>
                  </div>

                </div>

              </div>
            </form>
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@php
$productsJson = $products->keyBy('id')->map(fn($p) => [
    'codigo' => $p->codigo_interno,
    'tipo'   => $p->tipo ?? 'S',
    'desc'   => $p->descripcion,
    'unidad' => $p->codigo_unidad_medida,
    'precio' => (float) $p->precio_unitario,
    'afecto' => $p->codigo_indicador_afecto ?? '10',
])->toArray();
@endphp

@push('scripts')
<script>
let itemIndex = {{ count(old('items', [[]])) }};

// Sugerencias de serie+número por tipo (desde PHP)
const docSuggestions = @json($suggestions);

// Datos de productos para auto-fill (desde PHP)
const productsData = @json($productsJson);

// Auto-fill serie + número al cambiar tipo de comprobante
const tipoSelect   = document.getElementById('tipo-doc-select');
const serieInput   = document.getElementById('serie_documento');
const numeroInput  = document.getElementById('numero_documento');

// Calcula y rellena código interno y N° Orden Compra según tipo+serie+número
function updateDocCodes() {
  const tipo   = tipoSelect?.value || '01';
  const serie  = serieInput?.value || '';
  const numero = parseInt(numeroInput?.value || '1', 10);
  const numPad = String(numero).padStart(8, '0');

  const codigoInterno = document.getElementById('codigo_interno');
  if (codigoInterno && ! codigoInterno.dataset.userEdited) {
    codigoInterno.value = tipo + serie + numPad;
  }

  const ordenCompra = document.getElementById('numero_orden_compra');
  // N° Orden Compra es opcional, no se auto-rellena
}

// Solo auto-fill si no es un repoblado de old() (i.e. primer render)
@if(! old('serie_documento'))
tipoSelect?.addEventListener('change', function () {
  const s = docSuggestions[this.value];
  if (s) {
    serieInput.value  = s.serie;
    numeroInput.value = s.numero;
  }
  updateDocCodes();
  checkClientDocWarning();
  updateGreVisibility();
});
// Inicializar al cargar
updateDocCodes();
updateGreVisibility();
@else
tipoSelect?.addEventListener('change', () => { checkClientDocWarning(); updateGreVisibility(); });
updateGreVisibility();
@endif

// N° Orden Compra: si el usuario lo edita, no sobreescribir
document.getElementById('numero_orden_compra')?.addEventListener('input', function () {
  this.dataset.userEdited = this.value ? '1' : '';
});

function newItemRow(i) {
  const isGre = tipoSelect?.value === '09';
  const productOptions = Object.entries(productsData)
    .map(([id, p]) => `<option value="${id}" data-codigo="${p.codigo}" data-tipo="${p.tipo}" data-desc="${p.desc}" data-unidad="${p.unidad}" data-precio="${p.precio}" data-afecto="${p.afecto}">${p.desc}</option>`)
    .join('');
  const mHide = isGre ? ' style="display:none;"' : '';

  return `<tr class="item-row" data-index="${i}">
    <td><select class="product-picker" style="font-size:.8rem;"><option value="">— Seleccionar —</option>${productOptions}</select></td>
    <td><input type="text" name="items[${i}][codigo_interno]"></td>
    <td class="item-monetary-td"${mHide}><select name="items[${i}][tipo]"><option value="P">Prod</option><option value="S">Serv</option></select></td>
    <td><input type="text" name="items[${i}][descripcion]" class="descripcion" required></td>
    <td><select name="items[${i}][codigo_unidad_medida]">
      <option value="NIU">NIU</option><option value="ZZ">ZZ</option>
      <option value="KGM">KGM</option><option value="MTR">MTR</option><option value="LTR">LTR</option>
    </select></td>
    <td><input type="number" name="items[${i}][cantidad]" class="qty" min="0.001" step="0.001" required></td>
    <td class="item-monetary-td"${mHide}><input type="number" name="items[${i}][monto_precio_unitario]" class="price" min="0" step="0.0001" ${isGre ? '' : 'required'}></td>
    <td class="item-monetary-td"${mHide}><select name="items[${i}][codigo_indicador_afecto]" class="afecto">
      <option value="10">10-Grav</option><option value="20">20-Exon</option>
      <option value="30">30-Ina</option><option value="40">40-Exp</option>
    </select></td>
    <td class="item-monetary-td row-total"${mHide} style="text-align:right; font-weight:600;">0.00</td>
    <td><button type="button" class="btn-action-icon remove-item" title="Quitar"><i class='bx bx-trash'></i></button></td>
    <td style="display:none;"><input type="hidden" name="items[${i}][monto_valor_unitario]" class="valor_unitario"></td>
    <td style="display:none;"><input type="hidden" name="items[${i}][monto_igv]" class="monto_igv_input"></td>
    <td style="display:none;"><input type="hidden" name="items[${i}][monto_valor_total]" class="monto_valor_total_input"></td>
    <td style="display:none;"><input type="hidden" name="items[${i}][monto_total]" class="monto_total_input"></td>
  </tr>`;
}

document.getElementById('add-item-btn').addEventListener('click', function () {
  document.getElementById('items-body').insertAdjacentHTML('beforeend', newItemRow(itemIndex++));
  bindRowEvents(document.querySelector('#items-body tr:last-child'));
});

function recalcRow(row) {
  const qty    = parseFloat(row.querySelector('.qty')?.value)   || 0;
  const price  = parseFloat(row.querySelector('.price')?.value)  || 0;
  const igvPct = parseFloat(document.getElementById('igv-pct').value) || 0;
  const afecto = row.querySelector('.afecto')?.value || '10';
  const isGravado = afecto === '10';

  let valorUnitario, igv, total;
  if (isGravado) {
    valorUnitario = price / (1 + igvPct / 100);
    igv   = valorUnitario * (igvPct / 100) * qty;
    total = price * qty;
  } else {
    valorUnitario = price;
    igv   = 0;
    total = price * qty;
  }

  row.querySelector('.valor_unitario').value         = valorUnitario.toFixed(4);
  row.querySelector('.monto_igv_input').value         = igv.toFixed(2);
  row.querySelector('.monto_valor_total_input').value = (valorUnitario * qty).toFixed(2);
  row.querySelector('.monto_total_input').value       = total.toFixed(2);
  row.querySelector('.row-total').textContent  = total.toFixed(2);
  recalcTotals();
}

function recalcTotals() {
  let gravado = 0, exonerado = 0, inafecto = 0, igvSum = 0;
  document.querySelectorAll('#items-body .item-row').forEach(row => {
    const afecto = row.querySelector('.afecto')?.value || '10';
    const total  = parseFloat(row.querySelector('.monto_total_input')?.value) || 0;
    const igv    = parseFloat(row.querySelector('.monto_igv_input')?.value)   || 0;
    if (afecto === '10')      { gravado   += (total - igv); igvSum += igv; }
    else if (afecto === '20') { exonerado += total; }
    else if (afecto === '30') { inafecto  += total; }
    else if (afecto === '40') { gravado   += total; }
  });
  const totalGeneral = gravado + igvSum + exonerado + inafecto;
  
  // Obtener símbolo de moneda
  const moneda = document.querySelector('select[name="codigo_moneda"]')?.value || 'PEN';
  const simbolos = { 'PEN': 'S/ ', 'USD': '$ ', 'EUR': '€ ' };
  const simbolo = simbolos[moneda] || moneda + ' ';
  
  document.getElementById('lbl-gravado').textContent   = simbolo + gravado.toFixed(2);
  document.getElementById('lbl-exonerado').textContent = simbolo + exonerado.toFixed(2);
  document.getElementById('lbl-inafecto').textContent  = simbolo + inafecto.toFixed(2);
  document.getElementById('lbl-igv').textContent       = simbolo + igvSum.toFixed(2);
  document.getElementById('lbl-total').textContent     = simbolo + totalGeneral.toFixed(2);
  document.getElementById('h-gravado').value   = gravado.toFixed(2);
  document.getElementById('h-exonerado').value = exonerado.toFixed(2);
  document.getElementById('h-inafecto').value  = inafecto.toFixed(2);
  document.getElementById('h-igv').value       = igvSum.toFixed(2);
  document.getElementById('h-total').value     = totalGeneral.toFixed(2);

  // ── Detracción: mostrar/ocultar bloque y recalcular ─────────────────
  updateDetracWrapper(totalGeneral);
  recalcDetrac();

  // ── Si hay cuotas activas, redistribuir automáticamente ─────────────
  if (typeof cuotasDistribuir === 'function' && document.getElementById('forma-pago-select')?.value === '2') {
    cuotasDistribuir();
  }
}

function bindRowEvents(row) {
  row.querySelector('.qty')?.addEventListener('input',     () => recalcRow(row));
  row.querySelector('.qty')?.addEventListener('change',    () => recalcRow(row));
  row.querySelector('.price')?.addEventListener('input',   () => recalcRow(row));
  row.querySelector('.price')?.addEventListener('change',  () => recalcRow(row));
  row.querySelector('.afecto')?.addEventListener('change', () => recalcRow(row));
  row.querySelector('.remove-item')?.addEventListener('click', () => { row.remove(); recalcTotals(); });

  // Selector de producto: auto-fill al elegir
  row.querySelector('.product-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (! opt.value) return;
    const p = productsData[opt.value];
    if (! p) return;
    const setVal = (sel, val) => { const el = row.querySelector(sel); if (el) el.value = val; };
    setVal('input[name$="[codigo_interno]"]', p.codigo);
    setVal('input[name$="[descripcion]"]',    p.desc);
    setVal('input[name$="[monto_precio_unitario]"]', p.precio);
    // tipo select
    const tipoSel = row.querySelector('select[name$="[tipo]"]');
    if (tipoSel) [...tipoSel.options].forEach(o => o.selected = (o.value === p.tipo));
    // unidad
    const unidadSel = row.querySelector('select[name$="[codigo_unidad_medida]"]');
    if (unidadSel) [...unidadSel.options].forEach(o => o.selected = (o.value === p.unidad));
    // afecto
    const afectoSel = row.querySelector('.afecto');
    if (afectoSel) [...afectoSel.options].forEach(o => o.selected = (o.value === p.afecto));
    // foco a cantidad
    row.querySelector('.qty')?.focus();
    recalcRow(row);
  });
}

document.querySelectorAll('#items-body .item-row').forEach(row => { bindRowEvents(row); recalcRow(row); });
document.getElementById('igv-pct').addEventListener('input', () => {
  document.querySelectorAll('#items-body .item-row').forEach(row => recalcRow(row));
});
document.querySelector('select[name="client_id"]')?.addEventListener('change', function () {
  const email = this.options[this.selectedIndex]?.dataset?.email || '';
  const correoInput = document.querySelector('input[name="correo"]');
  if (correoInput) correoInput.value = email;
  checkClientDocWarning();
});
document.querySelector('select[name="codigo_tipo_documento"]')?.addEventListener('change', checkClientDocWarning);

function checkClientDocWarning() {
  const tipoDoc    = document.querySelector('select[name="codigo_tipo_documento"]')?.value || '';
  const clientSel  = document.querySelector('select[name="client_id"]');
  const tipoClient = clientSel?.options[clientSel.selectedIndex]?.dataset?.tipoDoc || '';
  const warning    = document.getElementById('client-doc-warning');
  if (!warning) return;
  if (tipoDoc === '01' && tipoClient && tipoClient !== '6') {
    warning.style.display = 'block';
    warning.textContent = '⚠ La Factura requiere un receptor con RUC. Este cliente tiene tipo "' + tipoClient + '". Use Boleta (03) o seleccione un cliente con RUC.';
  } else if (tipoDoc === '03' && tipoClient === '6') {
    warning.style.display = 'block';
    warning.textContent = '⚠ La Boleta no puede emitirse a un receptor con RUC. Use Factura (01).';
  } else {
    warning.style.display = 'none';
    warning.textContent = '';
  }
}

function updateGreVisibility() {
  const isGre = tipoSelect?.value === '09';

  // Paneles derecho
  const panelNoGre = document.getElementById('panel-no-gre');
  const panelGre   = document.getElementById('panel-gre');
  if (panelNoGre) panelNoGre.style.display = isGre ? 'none' : '';
  if (panelGre)   panelGre.style.display   = isGre ? '' : 'none';

  // Sección GRE (campos de traslado izquierda)
  const greFields = document.getElementById('gre-fields');
  if (greFields) greFields.style.display = isGre ? '' : 'none';

  // Campos no aplican para GRE (moneda, forma pago, igv, etc.)
  document.querySelectorAll('.non-gre-field').forEach(el => { el.style.display = isGre ? 'none' : ''; });

  // Columnas monetarias en tabla de ítems
  document.querySelectorAll('.item-monetary-th, .item-monetary-td').forEach(el => {
    el.style.display = isGre ? 'none' : '';
  });

  // Hacer requerido precio sólo para no-GRE
  document.querySelectorAll('.price').forEach(el => { el.required = !isGre; });
}

// Vehículos dinámicos
let vIndex = 1;
document.getElementById('add-vehiculo-btn')?.addEventListener('click', function () {
  const i = vIndex++;
  const row = `<tr>
    <td><input type="text" name="gre_vehiculos[${i}][numero_placa]" class="form-input" placeholder="ABC123"></td>
    <td style="text-align:center;"><input type="hidden" name="gre_vehiculos[${i}][indicador_principal]" value="0"><input type="checkbox" name="gre_vehiculos[${i}][indicador_principal]" value="1"></td>
    <td><button type="button" class="btn-action-icon remove-v"><i class='bx bx-trash'></i></button></td>
  </tr>`;
  document.getElementById('vehiculos-body').insertAdjacentHTML('beforeend', row);
  document.querySelector('#vehiculos-body tr:last-child .remove-v')?.addEventListener('click', function () { this.closest('tr').remove(); });
});
document.querySelectorAll('#vehiculos-body .remove-v').forEach(btn => {
  btn.addEventListener('click', function () { this.closest('tr').remove(); });
});

// ── Mostrar/ocultar Fecha Vencimiento según Forma de Pago ─────────────
function toggleFechaVencimiento() {
  const formaPago = document.getElementById('forma-pago-select')?.value;
  const divFecha  = document.getElementById('div-fecha-vencimiento');
  if (!divFecha) return;
  const isCredito = formaPago === '2';
  divFecha.style.display = isCredito ? '' : 'none';
  const input = divFecha.querySelector('input');
  if (input) input.required = isCredito;
  if (!isCredito && input) input.value = '';
}
document.getElementById('forma-pago-select')?.addEventListener('change', toggleFechaVencimiento);
toggleFechaVencimiento();

// ── Detracción SPOT ────────────────────────────────────────────────────
// El bloque solo es visible cuando: tipo=01 (Factura) y total > 700 PEN.
function updateDetracWrapper(total) {
  const tipo    = document.getElementById('tipo-doc-select')?.value;
  const moneda  = document.querySelector('select[name="codigo_moneda"]')?.value || 'PEN';
  const wrapper = document.getElementById('detrac-wrapper');
  if (!wrapper) return;
  const visible = (tipo === '01' && moneda === 'PEN' && total > 700);
  wrapper.style.display = visible ? '' : 'none';
  // Si se oculta, desactivar detracción automáticamente
  if (!visible) toggleDetrac(false);
}

function toggleDetrac(activate) {
  const boxOff    = document.getElementById('detrac-box-off');
  const boxOn     = document.getElementById('detrac-box-on');
  const indicator = document.getElementById('h-detrac-indicator');
  if (!boxOff || !boxOn) return;
  boxOff.style.display = activate ? 'none' : '';
  boxOn.style.display  = activate ? '' : 'none';
  if (indicator) indicator.value = activate ? '1' : '0';
  if (activate) {
    recalcDetrac(); // recalcDetrac ya llama a cuotasDistribuir internamente
  } else {
    // Al desactivar: resetear monto detracción y recalcular cuotas con total completo
    const hid = document.getElementById('h-detrac-monto');
    if (hid) hid.value = '0';
    if (document.getElementById('forma-pago-select')?.value === '2') {
      cuotasDistribuir();
    }
  }
}

function onDetracCodigoChange(sel) {
  const pct = parseFloat(sel.options[sel.selectedIndex]?.dataset?.pct || '0');
  const pctInput = document.getElementById('detrac-pct');
  if (pctInput) { pctInput.value = pct; }
  recalcDetrac();
}

// Devuelve la base real sobre la que se calculan las cuotas:
// Si hay detracción activa → Total - Detracción (neto a cobrar)
// Si no → Total
function getBaseParaCuotas() {
  const total  = parseFloat(document.getElementById('h-total')?.value || '0');
  const active = document.getElementById('h-detrac-indicator')?.value === '1';
  if (active) {
    const monto = parseFloat(document.getElementById('h-detrac-monto')?.value || '0');
    return Math.round((total - monto) * 100) / 100;
  }
  return total;
}

function recalcDetrac() {
  const active = document.getElementById('h-detrac-indicator')?.value === '1';
  if (!active) return;
  const total = parseFloat(document.getElementById('h-total')?.value || '0');
  const pct   = parseFloat(document.getElementById('detrac-pct')?.value || '0');
  const monto = Math.round(total * pct / 100 * 100) / 100;
  const neto  = Math.round((total - monto) * 100) / 100;
  const lbl   = document.getElementById('lbl-detrac-monto');
  const hid   = document.getElementById('h-detrac-monto');
  if (lbl) lbl.textContent = 'S/ ' + monto.toFixed(2);
  if (hid) hid.value = monto.toFixed(2);
  // Recalcular cuotas con el neto si están activas
  if (document.getElementById('forma-pago-select')?.value === '2') {
    cuotasDistribuir();
  }
}

// Actualizar visibilidad al cambiar tipo de doc o moneda
document.getElementById('tipo-doc-select')?.addEventListener('change', () => {
  const total = parseFloat(document.getElementById('h-total')?.value || '0');
  updateDetracWrapper(total);
});
document.querySelector('select[name="codigo_moneda"]')?.addEventListener('change', () => {
  const total = parseFloat(document.getElementById('h-total')?.value || '0');
  updateDetracWrapper(total);
  recalcTotals(); // Actualizar símbolos de moneda en totales
});

// Restaurar estado si hay old() (repoblado tras error de validación)
@if(old('indicador_detraccion'))
  document.addEventListener('DOMContentLoaded', () => {
    const total = parseFloat(document.getElementById('h-total')?.value || '0');
    updateDetracWrapper(total);
    toggleDetrac(true);
    recalcDetrac();
  });
@endif

// ── Cuenta Banco de la Nación: solo dígitos, exactamente 11 ──────────────
(function () {
  const cuentaInput = document.getElementById('detrac-cuenta');
  if (!cuentaInput) return;

  function soloDigitos(input) {
    const pos    = input.selectionStart;
    const antes  = input.value;
    const limpio = antes.replace(/[^0-9]/g, '').slice(0, 11);
    if (antes !== limpio) {
      input.value = limpio;
      // Restaurar cursor aproximado
      const diff = antes.length - limpio.length;
      input.setSelectionRange(Math.max(0, pos - diff), Math.max(0, pos - diff));
    }
  }

  cuentaInput.addEventListener('input',   function () { soloDigitos(this); });
  cuentaInput.addEventListener('paste',   function () { setTimeout(() => soloDigitos(this), 0); });
  cuentaInput.addEventListener('keydown', function (e) {
    // Permitir: retroceso, supr, flechas, tab, ctrl/cmd combos
    const allow = ['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Tab','Home','End'];
    if (allow.includes(e.key) || e.ctrlKey || e.metaKey) return;
    // Bloquear si no es dígito
    if (!/^[0-9]$/.test(e.key)) { e.preventDefault(); return; }
    // Bloquear si ya tiene 11 dígitos y no hay selección
    if (this.value.length >= 11 && this.selectionStart === this.selectionEnd) e.preventDefault();
  });
})();

// Validación pre-submit: cuenta BN obligatoria si hay detracción activa
document.getElementById('invoice-form')?.addEventListener('submit', function(e) {
  if (document.getElementById('h-detrac-indicator')?.value === '1') {
    const cuenta = document.getElementById('detrac-cuenta')?.value?.trim();
    const codigo = document.getElementById('detrac-codigo')?.value;
    if (!cuenta) {
      e.preventDefault();
      alert('⚠️ Detracción SPOT: ingrese la cuenta del Banco de la Nación antes de guardar.');
      document.getElementById('detrac-cuenta')?.focus();
      return;
    }
    if (cuenta.length !== 11) {
      e.preventDefault();
      alert('⚠️ La cuenta del Banco de la Nación debe tener exactamente 11 dígitos.');
      document.getElementById('detrac-cuenta')?.focus();
      return;
    }
    if (!codigo) {
      e.preventDefault();
      alert('⚠️ Detracción SPOT: seleccione el código del bien o servicio sujeto a detracción.');
      return;
    }
  }
});

// ── Cuotas de crédito ─────────────────────────────────────────────────────
const MAX_CUOTAS = 12;
const formaPagoSel = document.getElementById('forma-pago-select');
const cuotasSection = document.getElementById('cuotas-section');
const cuotasBody   = document.getElementById('cuotas-body');
const cuotasSuma   = document.getElementById('cuotas-suma');

function cuotasCount() { return cuotasBody.querySelectorAll('.cuota-row').length; }

function cuotasUpdateSuma() {
  let total = 0;
  cuotasBody.querySelectorAll('.cuota-monto').forEach(inp => {
    const v = parseFloat(inp.value);
    if (!isNaN(v)) total += v;
  });
  cuotasSuma.textContent = 'S/ ' + total.toFixed(2);
}

function cuotasDistribuir() {
  const total = getBaseParaCuotas(); // neto si hay detracción, total si no
  const rows  = cuotasBody.querySelectorAll('.cuota-row');
  if (!rows.length) return;

  // ── Distribuir montos ────────────────────────────────────────────────
  if (total > 0) {
    const base  = Math.floor(total / rows.length * 100) / 100;
    const resto = Math.round((total - base * rows.length) * 100) / 100;
    rows.forEach((row, i) => {
      const inp = row.querySelector('.cuota-monto');
      if (inp) inp.value = (i === rows.length - 1 ? (base + resto).toFixed(2) : base.toFixed(2));
    });
  }

  // ── Auto-rellenar fechas desde fecha_vencimiento + N meses ───────────
  const fechaVenc = document.querySelector('[name="fecha_vencimiento"]')?.value;
  if (fechaVenc) {
    const base = new Date(fechaVenc + 'T00:00:00');
    if (!isNaN(base)) {
      rows.forEach((row, i) => {
        const inp = row.querySelector('.cuota-fecha');
        if (inp && !inp.value) { // solo si está vacío
          const d = new Date(base);
          d.setMonth(d.getMonth() + i);
          inp.value = d.toISOString().slice(0, 10);
        }
      });
    }
  }

  cuotasUpdateSuma();
}

function cuotasRenumber() {
  cuotasBody.querySelectorAll('.cuota-row').forEach((row, i) => {
    const lbl = row.querySelector('span');
    if (lbl) lbl.textContent = (i + 1);
    // Renombrar inputs con índice correcto
    const fecha = row.querySelector('.cuota-fecha');
    const monto = row.querySelector('.cuota-monto');
    if (fecha) fecha.name = `lista_cuotas[${i}][fecha_pago]`;
    if (monto) monto.name = `lista_cuotas[${i}][monto]`;
  });
}

function cuotasAddRow() {
  if (cuotasCount() >= MAX_CUOTAS) { alert('Máximo ' + MAX_CUOTAS + ' cuotas.'); return; }
  const i = cuotasCount();
  const row = document.createElement('div');
  row.className = 'cuota-row';
  row.style.cssText = 'display:flex; gap:.5rem; align-items:center;';
  row.innerHTML = `
    <span style="flex:0 0 24px; text-align:center; font-size:.78rem; color:#94a3b8; font-weight:600;">${i+1}</span>
    <input type="date" name="lista_cuotas[${i}][fecha_pago]" class="form-input cuota-fecha" style="flex:1; font-size:.85rem;">
    <input type="number" name="lista_cuotas[${i}][monto]" class="form-input cuota-monto" style="flex:1; font-size:.85rem;" step="0.01" min="0.01" placeholder="Monto S/">
    <button type="button" class="btn-action-icon cuota-remove" title="Quitar cuota"><i class='bx bx-trash'></i></button>`;
  cuotasBody.appendChild(row);
  row.querySelector('.cuota-remove').addEventListener('click', () => { row.remove(); cuotasRenumber(); cuotasDistribuir(); });
  row.querySelector('.cuota-monto').addEventListener('input', cuotasUpdateSuma);
  cuotasDistribuir();
}

// Ligar botones y montos existentes (cargados desde old())
cuotasBody.querySelectorAll('.cuota-row').forEach((row, i) => {
  row.querySelector('.cuota-remove')?.addEventListener('click', () => { row.remove(); cuotasRenumber(); cuotasUpdateSuma(); });
  row.querySelector('.cuota-monto')?.addEventListener('input', cuotasUpdateSuma);
});

document.getElementById('cuota-add')?.addEventListener('click', cuotasAddRow);

// Mostrar/ocultar sección según forma_pago
formaPagoSel?.addEventListener('change', function () {
  if (this.value === '2') {
    cuotasSection.style.display = 'block';
    if (cuotasCount() === 0) cuotasAddRow(); // agregar 1 fila vacía al abrir
    cuotasDistribuir(); // rellenar monto con el total actual
  } else {
    cuotasSection.style.display = 'none';
  }
});

// Inicializar suma en carga
cuotasUpdateSuma();
</script>
@endpush
