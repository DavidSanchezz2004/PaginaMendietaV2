@extends('layouts.app')

@section('title', 'Preview Guía de Remisión — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .preview-card {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .preview-header {
      border-bottom: 1px solid var(--clr-border-light, #e2e8f0);
      padding-bottom: 1rem;
      margin-bottom: 1rem;
    }

    .preview-header h3 {
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1rem;
      font-weight: 600;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .info-item {
      display: flex;
      flex-direction: column;
    }

    .info-label {
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--clr-text-muted, #6b7280);
      margin-bottom: 0.3rem;
    }

    .info-value {
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--clr-text-main, #374151);
    }

    .items-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }

    .items-table thead {
      background: #f8fafc;
      border-bottom: 1px solid var(--clr-border-light, #e2e8f0);
    }

    .items-table th {
      padding: 0.75rem;
      text-align: left;
      font-weight: 600;
      color: var(--clr-text-muted, #6b7280);
      font-size: 0.8rem;
      text-transform: uppercase;
    }

    .items-table td {
      padding: 0.75rem;
      border-bottom: 1px solid var(--clr-border-light, #e2e8f0);
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-label {
      font-size: 0.85rem;
      font-weight: 600;
      display: block;
      margin-bottom: 0.4rem;
      color: var(--clr-text-main, #374151);
    }

    .form-input,
    .form-select {
      width: 100%;
      padding: 0.6rem 0.85rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.9rem;
      outline: none;
      font-family: inherit;
      box-sizing: border-box;
    }

    .form-input:focus,
    .form-select:focus {
      border-color: var(--clr-active-bg, #1a6b57);
      box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1);
    }

    .form-error {
      font-size: 0.8rem;
      color: #dc2626;
      margin-top: 0.3rem;
    }

    .alert-box {
      border-radius: 10px;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }

    .alert-danger {
      background: rgba(220, 38, 38, 0.08);
      border: 1px solid rgba(220, 38, 38, 0.3);
      color: #991b1b;
    }

    .alert-danger ul {
      margin: 0.5rem 0 0 0;
      padding-left: 1.5rem;
    }

    .alert-danger li {
      margin-bottom: 0.3rem;
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      justify-content: flex-end;
    }

    .quick-load {
      display:grid;
      grid-template-columns:minmax(220px,1fr) auto auto;
      gap:.7rem;
      align-items:end;
      padding:1rem;
      border:1px solid #dbeafe;
      border-radius:10px;
      background:#eff6ff;
      margin-bottom:1rem;
    }

    .quick-load__label {
      display:block;
      font-size:.78rem;
      font-weight:800;
      color:#1e3a8a;
      margin-bottom:.35rem;
      text-transform:uppercase;
    }

    @media (max-width:760px) {
      .quick-load { grid-template-columns:1fr; }
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
        <div class="placeholder-content module-card-wide">

          {{-- Header --}}
          <div class="module-toolbar">
            <div>
              <h1 style="display:flex; align-items:center; gap:.5rem; margin-bottom:.15rem;">
                <i class='bx bx-file-blank'></i> Preview Guía de Remisión
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Compra {{ $purchase->serie_numero }}</small>
            </div>
            <a href="{{ route('facturador.purchases.guia-flow', $purchase) }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
              <i class='bx bx-arrow-back'></i> Volver
            </a>
          </div>

          {{-- Errores --}}
          @if($errors->any())
            <div class="alert-box alert-danger">
              <i class='bx bx-x-circle' style="margin-right:.4rem;"></i>
              <strong>Errores encontrados:</strong>
              <ul>
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Card: Información de Compra --}}
          <div class="preview-card">
            <div class="preview-header">
              <h3><i class='bx bx-shopping-bag'></i> Información de Compra</h3>
            </div>
            <div class="info-grid">
              <div class="info-item">
                <div class="info-label">Compra</div>
                <div class="info-value">{{ $purchase->serie_numero }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">Proveedor</div>
                <div class="info-value">{{ $purchase->provider?->nombre_razon_social ?? '—' }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">Fecha</div>
                <div class="info-value">{{ $purchase->fecha_emision?->format('d/m/Y') ?? '—' }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">Total</div>
                <div class="info-value">{{ $purchase->codigo_moneda }} {{ number_format($purchase->monto_total, 2) }}</div>
              </div>
            </div>
          </div>

          {{-- Card: Cliente y Dirección --}}
          <div class="preview-card">
            <div class="preview-header">
              <h3><i class='bx bx-user'></i> Cliente y Dirección</h3>
            </div>
            <div class="info-grid">
              <div class="info-item" style="grid-column: span 2;">
                <div class="info-label">Razón Social</div>
                <div class="info-value">{{ $purchase->client->nombre_razon_social }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">RUC/DNI</div>
                <div class="info-value">{{ $purchase->client->numero_documento }}</div>
              </div>
            </div>

            {{-- Formulario de Generación --}}
            <form method="POST" action="{{ route('facturador.compras.guia.generate', $purchase) }}" id="guiaForm">
              @csrf
              <input type="hidden" name="gre_payload" id="gre_payload">

              <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-top: 1.5rem;">
                <div class="form-group">
                  <label class="form-label">Dirección de Entrega <span style="color:#dc2626;">*</span></label>
                  <select name="client_address_id" id="client_address_id" class="form-select @error('client_address_id') is-invalid @enderror" required>
                    <option value="">-- Seleccionar dirección --</option>
                    @foreach($addresses as $address)
                      <option value="{{ $address->id }}"
                        @if($address->is_default) selected @endif
                        @if(count($addresses) == 1 && !old('client_address_id')) selected @endif
                        @error('client_address_id') @if($address->id == old('client_address_id')) selected @endif @enderror>
                        {{ $address->type_name }} - {{ $address->full_address }}
                      </option>
                    @endforeach
                  </select>
                  @error('client_address_id')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="form-group">
                  <label class="form-label">Motivo <span style="color:#dc2626;">*</span></label>
                  <input type="text" name="motivo" id="motivo" class="form-input @error('motivo') is-invalid @enderror"
                    value="{{ old('motivo', 'Venta') }}" required maxlength="100">
                  @error('motivo')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </form>
          </div>

          {{-- Card: Estructura GRE Feasy --}}
          <div class="preview-card">
            <div class="preview-header">
              <h3><i class='bx bx-code-curly'></i> Estructura GRE para Feasy
                <span style="font-size:.75rem; font-weight:400; color:var(--clr-text-muted,#6b7280); margin-left:.5rem;">
                  Validación local, no envía a SUNAT
                </span>
              </h3>
            </div>

            <div class="alert-box" style="background:#fef3c7; border:1px solid #fde68a; color:#92400e; margin-bottom:1rem;">
              <i class='bx bx-info-circle' style="margin-right:.35rem;"></i>
              Como Feasy no tiene sandbox GRE, aquí armamos el JSON compatible y revisamos campos antes de generar la guía.
            </div>

            <div class="quick-load">
              <div>
                <label class="quick-load__label" for="gre_preset_select">Carga rápida GRE</label>
                <select id="gre_preset_select" class="form-select">
                  <option value="">— Seleccionar configuración guardada —</option>
                  @foreach($grePresets as $preset)
                    <option value="{{ $preset->id }}" @selected($preset->is_default)>
                      {{ $preset->name }} @if($preset->is_default) — predeterminada @endif
                    </option>
                  @endforeach
                </select>
              </div>
              <button type="button" id="gre_apply_preset" class="btn-secondary" style="height:41px; padding:.55rem 1rem;">
                <i class='bx bx-bolt-circle'></i> Aplicar
              </button>
              <button type="button" id="gre_save_preset" class="btn-primary" style="height:41px; padding:.55rem 1rem;">
                <i class='bx bx-save'></i> Guardar actual
              </button>
            </div>

            <div style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; margin-bottom:1rem;">
              <div class="form-group">
                <label class="form-label">Fecha inicio traslado *</label>
                <input type="date" id="gre_fecha_inicio" class="form-input gre-field" value="{{ old('gre_fecha_inicio', now()->format('Y-m-d')) }}">
              </div>
              <div class="form-group">
                <label class="form-label">Peso bruto *</label>
                <input type="number" id="gre_peso" class="form-input gre-field" step="0.001" min="0" value="{{ old('gre_peso', $purchase->gre_peso_bruto ?? '') }}" placeholder="Ej: 900">
              </div>
              <div class="form-group">
                <label class="form-label">Unidad peso *</label>
                <select id="gre_unidad_peso" class="form-select gre-field">
                  <option value="KGM" selected>KGM - Kilogramos</option>
                  <option value="TNE">TNE - Toneladas</option>
                  <option value="LBR">LBR - Libras</option>
                </select>
              </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; margin-bottom:1rem;">
              <div class="form-group">
                <label class="form-label">Punto de partida - Ubigeo *</label>
                <input type="text" id="gre_partida_ubigeo" class="form-input gre-field" maxlength="10" placeholder="Ej: 150101">
              </div>
              <div class="form-group">
                <label class="form-label">Punto de llegada - Ubigeo *</label>
                <input type="text" id="gre_llegada_ubigeo" class="form-input gre-field" maxlength="10" placeholder="Ej: 150101">
              </div>
              <div class="form-group">
                <label class="form-label">Dirección partida *</label>
                <input type="text" id="gre_partida_dir" class="form-input gre-field" value="{{ old('gre_partida_dir', $purchase->gre_punto_partida ?? ($purchase->company->direccion_fiscal ?? '')) }}">
              </div>
              <div class="form-group">
                <label class="form-label">Dirección llegada *</label>
                <input type="text" id="gre_llegada_dir" class="form-input gre-field" value="{{ old('gre_llegada_dir', $addresses->first()?->full_address ?? '') }}">
              </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; margin-bottom:1rem;">
              <div class="form-group">
                <label class="form-label">Modalidad *</label>
                <select id="gre_modalidad" class="form-select gre-field">
                  <option value="02" selected>02 - Transporte Privado</option>
                  <option value="01">01 - Transporte Público</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Placa principal</label>
                <input type="text" id="gre_placa" class="form-input gre-field" placeholder="ABC123">
              </div>
              <div class="form-group">
                <label class="form-label">Licencia conductor</label>
                <input type="text" id="gre_licencia" class="form-input gre-field" placeholder="P19891727">
              </div>
              <div class="form-group">
                <label class="form-label">DNI conductor</label>
                <input type="text" id="gre_conductor_dni" class="form-input gre-field" placeholder="19891727">
              </div>
              <div class="form-group">
                <label class="form-label">Nombre conductor</label>
                <input type="text" id="gre_conductor_nombre" class="form-input gre-field" placeholder="Nombre">
              </div>
              <div class="form-group">
                <label class="form-label">Apellido conductor</label>
                <input type="text" id="gre_conductor_apellido" class="form-input gre-field" placeholder="Apellido">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">JSON que se guardará</label>
              <pre id="gre-json-preview" style="white-space:pre-wrap; max-height:260px; overflow:auto; background:#0f172a; color:#e5e7eb; border-radius:10px; padding:1rem; font-size:.78rem;"></pre>
            </div>
          </div>

          {{-- Card: Items --}}
          @if($purchase->items->count())
            <div class="preview-card">
              <div class="preview-header">
                <h3><i class='bx bx-list-ul'></i> Items ({{ $preview['items_count'] }})
                  <span style="font-size:.75rem; font-weight:400; color:var(--clr-text-muted,#6b7280); margin-left:.5rem;">
                    <i class='bx bx-edit-alt'></i> Puedes editar el precio antes de generar
                  </span>
                </h3>
              </div>
              <div style="overflow-x: auto;">
                <table class="items-table">
                  <thead>
                    <tr>
                      <th>Descripción</th>
                      <th style="text-align: right;">Cantidad</th>
                      <th>Unidad</th>
                      <th style="text-align: right; min-width:130px;">Precio unit. (s/IGV)</th>
                      <th style="text-align: right;">Total (s/IGV)</th>
                    </tr>
                  </thead>
                  <tbody id="items-tbody">
                    @foreach($purchase->items as $item)
                      <tr>
                        <td>{{ $item->descripcion }}</td>
                        <td style="text-align: right;" class="item-qty" data-qty="{{ $item->cantidad }}">{{ number_format($item->cantidad, 2) }}</td>
                        <td>{{ $item->unidad_medida }}</td>
                        <td style="text-align: right; padding:.5rem .75rem;">
                          {{-- Hidden input enviado al form --}}
                          <input type="hidden"
                                 form="guiaForm"
                                 name="items_prices[{{ $item->id }}]"
                                 class="price-hidden"
                                 value="{{ old('items_prices.'.$item->id, $item->valor_unitario) }}">
                          <input type="number"
                                 step="0.0001"
                                 min="0"
                                 class="price-input"
                                 value="{{ old('items_prices.'.$item->id, $item->valor_unitario) }}"
                                 style="width:120px; padding:.35rem .5rem; border:1px solid #e2e8f0; border-radius:6px; text-align:right; font-size:.9rem;">
                        </td>
                        <td style="text-align: right; font-weight: 600;" class="item-total">
                          {{ number_format($item->cantidad * $item->valor_unitario, 2) }}
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot style="background:#f8fafc; border-top:2px solid var(--clr-border-light,rgba(0,0,0,.06));">
                    <tr>
                      <th colspan="4" style="text-align:right; padding:.75rem 1rem; font-size:.85rem; font-weight:600; color:var(--clr-text-muted,#6b7280);">Total s/IGV:</th>
                      <th id="grand-total" style="text-align:right; padding:.75rem 1rem; font-size:1rem; color:var(--clr-active-bg,#1a6b57);">
                        {{ number_format($purchase->items->sum(fn($i) => $i->cantidad * $i->valor_unitario), 2) }}
                      </th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          @endif

          {{-- Botones de Acción --}}
          <div class="form-actions">
            <a href="{{ route('facturador.purchases.guia-flow', $purchase) }}" class="btn-secondary" style="text-decoration: none; padding: 0.6rem 1.5rem;">
              <i class='bx bx-x'></i> Cancelar
            </a>
            <button type="submit" form="guiaForm" class="btn-primary" style="padding: 0.6rem 1.5rem;">
              <i class='bx bx-check'></i> Generar Guía
            </button>
          </div>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}

@php
  $greContext = [
    'company' => [
      'ruc' => $purchase->company?->ruc,
      'name' => $purchase->company?->razon_social ?? $purchase->company?->name,
    ],
    'client' => [
      'tipo_doc' => $purchase->client?->codigo_tipo_documento ?? '6',
      'numero' => $purchase->client?->numero_documento,
      'name' => $purchase->client?->nombre_razon_social,
    ],
    'purchase' => [
      'tipo' => $purchase->codigo_tipo_documento,
      'serie' => $purchase->serie_documento,
      'numero' => $purchase->numero_documento,
    ],
    'items' => $purchase->items->map(function ($item) {
      return [
        'id' => $item->id,
        'codigo' => $item->codigo_interno ?? ('P' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT)),
        'unidad' => $item->unidad_medida,
        'descripcion' => $item->descripcion,
        'cantidad' => (float) $item->cantidad,
      ];
    })->values(),
  ];

  $grePresetPayload = $grePresets->map(function ($preset) {
    return [
      'id' => $preset->id,
      'name' => $preset->name,
      'partida_ubigeo' => $preset->partida_ubigeo,
      'partida_direccion' => $preset->partida_direccion,
      'llegada_ubigeo' => $preset->llegada_ubigeo,
      'modalidad' => $preset->modalidad,
      'unidad_peso' => $preset->unidad_peso,
      'placa' => $preset->placa,
      'conductor_dni' => $preset->conductor_dni,
      'conductor_nombre' => $preset->conductor_nombre,
      'conductor_apellido' => $preset->conductor_apellido,
      'conductor_licencia' => $preset->conductor_licencia,
      'is_default' => (bool) $preset->is_default,
    ];
  })->values();
@endphp

<script>
  console.log('Preview page loaded');
  console.log('Purchase:', {!! json_encode($purchase->only(['id', 'status', 'client_id', 'serie_numero'])) !!});
  console.log('Addresses count:', {{ count($addresses) }});
  console.log('Preview data:', {!! json_encode($preview) !!});

  // Recalculo en tiempo real al editar precio
  const greContext = @json($greContext);
  let grePresets = @json($grePresetPayload);

  function pad8(value) {
    const raw = String(value || '').replace(/\D/g, '');
    return raw.padStart(8, '0');
  }

  function getGreValue(id) {
    return document.getElementById(id)?.value?.trim() || '';
  }

  function setGreValue(id, value) {
    const el = document.getElementById(id);
    if (el && value !== null && value !== undefined) el.value = value;
  }

  function applyGrePreset(preset) {
    if (!preset) return;
    setGreValue('gre_partida_ubigeo', preset.partida_ubigeo || '');
    setGreValue('gre_partida_dir', preset.partida_direccion || '');
    setGreValue('gre_llegada_ubigeo', preset.llegada_ubigeo || '');
    setGreValue('gre_modalidad', preset.modalidad || '02');
    setGreValue('gre_unidad_peso', preset.unidad_peso || 'KGM');
    setGreValue('gre_placa', preset.placa || '');
    setGreValue('gre_conductor_dni', preset.conductor_dni || '');
    setGreValue('gre_conductor_nombre', preset.conductor_nombre || '');
    setGreValue('gre_conductor_apellido', preset.conductor_apellido || '');
    setGreValue('gre_licencia', preset.conductor_licencia || '');
    updateGrePayloadPreview();
  }

  function currentGrePresetPayload(name, isDefault = false) {
    return {
      _token: document.querySelector('meta[name="csrf-token"]').content,
      name,
      partida_ubigeo: getGreValue('gre_partida_ubigeo'),
      partida_direccion: getGreValue('gre_partida_dir'),
      llegada_ubigeo: getGreValue('gre_llegada_ubigeo'),
      modalidad: getGreValue('gre_modalidad') || '02',
      unidad_peso: getGreValue('gre_unidad_peso') || 'KGM',
      placa: getGreValue('gre_placa'),
      conductor_dni: getGreValue('gre_conductor_dni'),
      conductor_nombre: getGreValue('gre_conductor_nombre'),
      conductor_apellido: getGreValue('gre_conductor_apellido'),
      conductor_licencia: getGreValue('gre_licencia'),
      is_default: isDefault,
    };
  }

  function buildGrePayload() {
    const modalidad = getGreValue('gre_modalidad') || '02';
    const fecha = new Date();
    const numero = pad8({{ (int) $purchase->id }});
    const payload = {
      informacion_documento: {
        codigo_interno: '09T001' + numero,
        fecha_emision: fecha.toISOString().slice(0, 10),
        hora_emision: fecha.toTimeString().slice(0, 8),
        codigo_tipo_documento: '09',
        serie_documento: 'T001',
        numero_documento: numero,
        observacion: null,
        correo: null,
        codigo_motivo_traslado: '01',
        descripcion_motivo_traslado: getGreValue('motivo') || 'Venta',
        codigo_modalidad_traslado: modalidad,
        fecha_inicio_traslado: getGreValue('gre_fecha_inicio'),
        codigo_unidad_medida_peso_bruto_total: getGreValue('gre_unidad_peso') || 'KGM',
        peso_bruto_total: parseFloat(getGreValue('gre_peso') || '0') || 0,
      },
      informacion_remitente: {
        codigo_tipo_documento_remitente: '6',
        numero_documento_remitente: greContext.company.ruc || '',
        nombre_razon_social_remitente: greContext.company.name || '',
      },
      informacion_destinatario: {
        codigo_tipo_documento_destinatario: greContext.client.tipo_doc || '6',
        numero_documento_destinatario: greContext.client.numero || '',
        nombre_razon_social_destinatario: greContext.client.name || '',
      },
      informacion_punto_partida: {
        ubigeo_punto_partida: getGreValue('gre_partida_ubigeo'),
        direccion_punto_partida: getGreValue('gre_partida_dir'),
      },
      informacion_punto_llegada: {
        ubigeo_punto_llegada: getGreValue('gre_llegada_ubigeo'),
        direccion_punto_llegada: getGreValue('gre_llegada_dir'),
      },
      lista_documentos_relacionados: [{
        correlativo: 1,
        codigo_tipo_documento: greContext.purchase.tipo || '01',
        descripcion_tipo_documento: 'Factura',
        serie_documento: greContext.purchase.serie || '',
        numero_documento: pad8(greContext.purchase.numero || ''),
        codigo_tipo_documento_emisor: '6',
        numero_documento_emisor: greContext.company.ruc || '',
      }],
      lista_items: greContext.items.map(function(item, index) {
        return {
          correlativo: index + 1,
          codigo_interno: item.codigo || ('P' + String(index + 1).padStart(3, '0')),
          codigo_unidad_medida: item.unidad || 'NIU',
          descripcion: item.descripcion || '',
          cantidad: Number(item.cantidad || 0),
        };
      }),
    };

    if (modalidad === '01') {
      payload.informacion_transportista = {
        codigo_tipo_documento_transportista: '6',
        numero_documento_transportista: '',
        nombre_razon_social_transportista: '',
      };
    } else {
      payload.lista_vehiculos = [{
        correlativo: 1,
        numero_placa: getGreValue('gre_placa').replace(/[-\s]/g, '').toUpperCase(),
        indicador_principal: true,
      }];
      payload.lista_conductores = [{
        correlativo: 1,
        codigo_tipo_documento: '1',
        numero_documento: getGreValue('gre_conductor_dni'),
        nombre: getGreValue('gre_conductor_nombre'),
        apellido: getGreValue('gre_conductor_apellido'),
        numero_licencia: getGreValue('gre_licencia'),
        indicador_principal: true,
      }];
    }

    return payload;
  }

  function updateGrePayloadPreview() {
    const payload = buildGrePayload();
    document.getElementById('gre_payload').value = JSON.stringify(payload);
    document.getElementById('gre-json-preview').textContent = JSON.stringify(payload, null, 2);
  }

  document.querySelectorAll('.gre-field, #motivo').forEach(function(el) {
    el.addEventListener('input', updateGrePayloadPreview);
    el.addEventListener('change', updateGrePayloadPreview);
  });

  document.getElementById('gre_apply_preset')?.addEventListener('click', function() {
    const preset = grePresets.find(item => String(item.id) === document.getElementById('gre_preset_select').value);
    if (!preset) {
      Swal.fire({ icon:'info', title:'Selecciona una carga rápida', text:'Elige una configuración guardada para aplicarla.' });
      return;
    }
    applyGrePreset(preset);
  });

  document.getElementById('gre_save_preset')?.addEventListener('click', async function() {
    const { value: formValues } = await Swal.fire({
      title: 'Guardar carga rápida GRE',
      html: `
        <input id="gre-preset-name" class="swal2-input" placeholder="Nombre. Ej: Transporte Lima">
        <label style="display:flex;align-items:center;gap:.5rem;justify-content:center;font-size:.9rem;">
          <input id="gre-preset-default" type="checkbox"> Usar como predeterminada
        </label>
      `,
      focusConfirm: false,
      confirmButtonText: 'Guardar',
      showCancelButton: true,
      preConfirm: () => {
        const name = document.getElementById('gre-preset-name').value.trim();
        if (!name) {
          Swal.showValidationMessage('Ingresa un nombre para la carga rápida.');
          return false;
        }
        return {
          name,
          isDefault: document.getElementById('gre-preset-default').checked,
        };
      },
    });

    if (!formValues) return;

    try {
      const res = await fetch('{{ route("facturador.gre-presets.store") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(currentGrePresetPayload(formValues.name, formValues.isDefault)),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'No se pudo guardar.');

      const preset = data.preset;
      grePresets = grePresets.filter(item => item.id !== preset.id);
      if (preset.is_default) grePresets = grePresets.map(item => ({ ...item, is_default: false }));
      grePresets.push(preset);

      const select = document.getElementById('gre_preset_select');
      select.innerHTML = '<option value="">— Seleccionar configuración guardada —</option>' + grePresets
        .sort((a, b) => Number(b.is_default) - Number(a.is_default) || a.name.localeCompare(b.name))
        .map(item => `<option value="${item.id}" ${item.is_default ? 'selected' : ''}>${item.name}${item.is_default ? ' — predeterminada' : ''}</option>`)
        .join('');

      Swal.fire({ icon:'success', title:'Guardado', text:data.message, timer:1400, showConfirmButton:false });
    } catch (error) {
      Swal.fire({ icon:'error', title:'No se pudo guardar', text:error.message });
    }
  });

  document.getElementById('guiaForm')?.addEventListener('submit', function(e) {
    updateGrePayloadPreview();
    const missing = [];
    if (!getGreValue('gre_partida_ubigeo')) missing.push('Ubigeo del punto de partida');
    if (!getGreValue('gre_llegada_ubigeo')) missing.push('Ubigeo del punto de llegada');
    if (!getGreValue('gre_peso')) missing.push('Peso bruto total');
    if (getGreValue('gre_modalidad') === '02' && !getGreValue('gre_placa')) missing.push('Placa del vehículo');

    if (missing.length) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Faltan datos para la estructura GRE',
        html: '<ul style="text-align:left;margin:0;padding-left:1.1rem;">' + missing.map(v => '<li>' + v + '</li>').join('') + '</ul>',
        confirmButtonText: 'Revisar',
      });
    }
  });

  function recalcGrandTotal() {
    let grand = 0;
    document.querySelectorAll('#items-tbody tr').forEach(function(row) {
      const totalCell = row.querySelector('.item-total');
      if (totalCell) {
        grand += parseFloat(totalCell.dataset.raw) || 0;
      }
    });
    document.getElementById('grand-total').textContent = grand.toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  document.querySelectorAll('.price-input').forEach(function(input) {
    input.addEventListener('input', function() {
      const row   = this.closest('tr');
      const qty   = parseFloat(row.querySelector('.item-qty').dataset.qty) || 0;
      const price = parseFloat(this.value) || 0;
      const total = qty * price;

      // Actualizar hidden input del form
      row.querySelector('.price-hidden').value = this.value;

      // Mostrar total recalculado
      const totalCell = row.querySelector('.item-total');
      totalCell.dataset.raw = total;
      totalCell.textContent = total.toLocaleString('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      recalcGrandTotal();
    });
  });

  // Inicializar data-raw en cada celda de total
  document.querySelectorAll('.item-total').forEach(function(cell) {
    cell.dataset.raw = parseFloat(cell.textContent.replace(/,/g, '')) || 0;
  });
  recalcGrandTotal();
  const defaultGrePreset = grePresets.find(item => item.is_default);
  if (defaultGrePreset) {
    applyGrePreset(defaultGrePreset);
  }
  updateGrePayloadPreview();
</script>

@endsection
