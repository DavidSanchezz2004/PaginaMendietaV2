@extends('layouts.app')

@section('title', 'Nueva Guía de Remisión — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .form-section { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:14px; padding:1.5rem; margin-bottom:1.25rem; }
    .form-section h4 { font-size:.82rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:var(--clr-text-muted,#6b7280); margin:0 0 1.2rem; display:flex; align-items:center; gap:.5rem; }
    .form-section h4 i { font-size:1.1rem; color:var(--clr-text-main,#111827); }
    .field-group { display:grid; gap:1rem; }
    .field-group.cols-2 { grid-template-columns:1fr 1fr; }
    .field-group.cols-3 { grid-template-columns:1fr 1fr 1fr; }
    @media(max-width:768px){ .field-group.cols-2,.field-group.cols-3{ grid-template-columns:1fr; } }
    .field-label { display:block; font-size:.8rem; font-weight:700; color:var(--clr-text-muted,#6b7280); margin-bottom:.35rem; }
    .form-control { width:100%; border:1px solid var(--clr-border-light,#d1d5db); border-radius:9px; padding:.55rem .8rem; font-size:.9rem; background:var(--clr-bg-input,#fff); color:var(--clr-text-main,#111827); transition:border-color .15s; }
    .form-control:focus { outline:none; border-color:var(--clr-active-bg,#1a6b57); box-shadow:0 0 0 3px rgba(26,107,87,.12); }
    .invalid-feedback { color:#dc2626; font-size:.78rem; margin-top:.25rem; }
    .ubigeo-picker { position:relative; }
    .ubigeo-results { position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:40; display:none; max-height:260px; overflow:auto; background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#d1d5db); border-radius:10px; box-shadow:0 14px 34px rgba(15,23,42,.16); }
    .ubigeo-results.is-open { display:block; }
    .ubigeo-option { width:100%; border:0; background:transparent; padding:.65rem .75rem; text-align:left; cursor:pointer; color:var(--clr-text-main,#111827); border-bottom:1px solid var(--clr-border-light,#f1f5f9); }
    .ubigeo-option:hover { background:rgba(26,107,87,.08); }
    .ubigeo-option strong { display:block; font-size:.86rem; }
    .ubigeo-option span { display:block; font-size:.76rem; color:var(--clr-text-muted,#64748b); margin-top:.12rem; }
    .ubigeo-help { display:block; margin-top:.25rem; font-size:.74rem; color:var(--clr-text-muted,#64748b); }
    .ubigeo-error { display:none; margin-top:.25rem; font-size:.76rem; color:#dc2626; font-weight:700; }
    .js-ubigeo-search.is-invalid-client { border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.12); }
    .js-ubigeo-search.is-invalid-client ~ .ubigeo-results + .ubigeo-error { display:block; }
    @media(max-width:900px){ .related-doc-row { grid-template-columns:1fr !important; } }

    /* Modalidad toggle */
    .modal-tabs { display:flex; gap:.5rem; margin-bottom:1rem; }
    .modal-tab  { flex:1; padding:.6rem 1rem; border-radius:9px; font-size:.85rem; font-weight:700; cursor:pointer; border:2px solid var(--clr-border-light,#d1d5db); background:transparent; color:var(--clr-text-muted,#6b7280); transition:all .2s; text-align:center; }
    .modal-tab.active { border-color:var(--clr-active-bg,#1a6b57); background:rgba(26,107,87,.08); color:var(--clr-active-bg,#1a6b57); }

    /* Item rows */
    .item-row  { display:grid; grid-template-columns:40px 170px 1fr 80px 100px 110px 40px; gap:.5rem; align-items:center; padding:.4rem 0; border-bottom:1px solid var(--clr-border-light,#f3f4f6); }
    .item-row .form-control { padding:.4rem .6rem; font-size:.82rem; }
    @media(max-width:900px){ .item-row { grid-template-columns:1fr; } }

    .btn-add-row { display:inline-flex; align-items:center; gap:.35rem; padding:.4rem .85rem; font-size:.82rem; font-weight:600; border-radius:9px; border:1px dashed var(--clr-active-bg,#1a6b57); color:var(--clr-active-bg,#1a6b57); background:transparent; cursor:pointer; }
    .btn-add-row:hover { background:rgba(26,107,87,.06); }
    .btn-remove-row { background:none; border:none; color:#ef4444; cursor:pointer; font-size:1.1rem; padding:0 .2rem; }

    .vehiculo-row, .conductor-row { display:grid; grid-template-columns:1fr 120px 40px; gap:.5rem; align-items:center; margin-bottom:.5rem; }
    @media(max-width:700px){ .vehiculo-row,.conductor-row{ grid-template-columns:1fr; } }

    .conductor-row-full { display:grid; grid-template-columns:80px 1fr 1fr 1fr 1fr 120px 40px; gap:.5rem; align-items:center; margin-bottom:.5rem; }
    @media(max-width:900px){ .conductor-row-full{ grid-template-columns:1fr 1fr; } }
    .ai-extractor { border:1px solid rgba(26,107,87,.22); background:rgba(26,107,87,.04); }
    .ai-extractor__body { display:grid; grid-template-columns:minmax(220px,1fr) auto; gap:.75rem; align-items:end; }
    @media(max-width:768px){ .ai-extractor__body{ grid-template-columns:1fr; } }
    .ai-status { font-size:.8rem; color:var(--clr-text-muted,#6b7280); margin-top:.5rem; min-height:1.2rem; }
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

        @if($errors->any())
          <div class="placeholder-content module-alert module-alert--error" data-flash-message style="display:none;">
            <p><strong>Corrige los errores antes de continuar:</strong></p>
            <ul style="margin:.4rem 0 0 1rem;">
              @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
            <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
          </div>
        @endif

        <div class="placeholder-content module-card-wide">
          <div class="module-toolbar">
            <h1><i class='bx bx-map-alt' style="font-size:1.4rem; vertical-align:middle;"></i> Nueva Guía de Remisión</h1>
            <a href="{{ route('facturador.gre.index') }}" class="btn-secondary">
              <i class='bx bx-arrow-back'></i> Volver
            </a>
          </div>

          <form method="POST" action="{{ route('facturador.gre.store') }}" id="gre-form" novalidate>
            @csrf

            <div class="form-section ai-extractor">
              <h4><i class='bx bx-chip'></i> Extraer desde PDF GRE</h4>
              <div class="ai-extractor__body">
                <div>
                  <label class="field-label">PDF de guía de remisión</label>
                  <input type="file" id="gre-pdf-input" accept="application/pdf,.pdf" class="form-control">
                  <div id="gre-ai-status" class="ai-status">No cambia serie, número ni fecha de emisión; esos datos se mantienen con tu correlativo.</div>
                </div>
                <button type="button" id="gre-ai-extract-btn" class="btn-primary" style="height:40px; white-space:nowrap;">
                  <i class='bx bx-search-alt'></i> Extraer con IA
                </button>
              </div>
            </div>

            {{-- ── Datos del documento ─────────────────────────────── --}}
            <div class="form-section">
              <h4><i class='bx bx-file-blank'></i> Datos del Documento</h4>
              <div class="field-group cols-3">
                <div>
                  <label class="field-label">Código interno *</label>
                  <input type="text" name="codigo_interno" id="codigo_interno" value="{{ old('codigo_interno') }}"
                         class="form-control @error('codigo_interno') is-invalid @enderror" readonly required>
                  @error('codigo_interno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Serie *</label>
                  <input type="text" name="serie_documento"
                         value="{{ old('serie_documento', $suggestion09->serie_documento ?? 'T001') }}"
                         class="form-control @error('serie_documento') is-invalid @enderror"
                         maxlength="5" required>
                  @error('serie_documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Número *</label>
                  <input type="text" name="numero_documento"
                         value="{{ old('numero_documento', $suggestion09->numero_documento ?? '1') }}"
                         class="form-control @error('numero_documento') is-invalid @enderror"
                         maxlength="10" required>
                  @error('numero_documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Fecha emisión *</label>
                  <input type="date" name="fecha_emision"
                         value="{{ old('fecha_emision', now()->format('Y-m-d')) }}"
                         class="form-control @error('fecha_emision') is-invalid @enderror" required>
                  @error('fecha_emision')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Hora emisión *</label>
                  <input type="time" name="hora_emision"
                         value="{{ old('hora_emision', now()->format('H:i')) }}"
                         class="form-control @error('hora_emision') is-invalid @enderror" required>
                  @error('hora_emision')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Correo destinatario</label>
                  <input type="email" name="correo" value="{{ old('correo') }}"
                         class="form-control @error('correo') is-invalid @enderror">
                  @error('correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div style="grid-column:1/-1;">
                  <label class="field-label">Observación</label>
                  <textarea name="observacion" rows="2"
                            class="form-control @error('observacion') is-invalid @enderror">{{ old('observacion') }}</textarea>
                  @error('observacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

            {{-- ── Motivo y modalidad ─────────────────────────────── --}}
            <div class="form-section">
              <h4><i class='bx bx-transfer-alt'></i> Traslado</h4>
              <div class="field-group cols-3" style="margin-bottom:1rem;">
                <div>
                  <label class="field-label">Motivo traslado (código) *</label>
                  <select name="codigo_motivo_traslado"
                          class="form-control @error('codigo_motivo_traslado') is-invalid @enderror" required>
                    <option value="">— Selecciona —</option>
                    <option value="01" {{ old('codigo_motivo_traslado') == '01' ? 'selected' : '' }}>01 - Venta</option>
                    <option value="02" {{ old('codigo_motivo_traslado') == '02' ? 'selected' : '' }}>02 - Compra</option>
                    <option value="03" {{ old('codigo_motivo_traslado') == '03' ? 'selected' : '' }}>03 - Devolución</option>
                    <option value="04" {{ old('codigo_motivo_traslado') == '04' ? 'selected' : '' }}>04 - Traslado entre establecimientos</option>
                    <option value="05" {{ old('codigo_motivo_traslado') == '05' ? 'selected' : '' }}>05 - Consignación</option>
                    <option value="06" {{ old('codigo_motivo_traslado') == '06' ? 'selected' : '' }}>06 - Proceso maquila</option>
                    <option value="13" {{ old('codigo_motivo_traslado') == '13' ? 'selected' : '' }}>13 - Otros</option>
                  </select>
                  @error('codigo_motivo_traslado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Descripción motivo *</label>
                  <input type="text" name="descripcion_motivo_traslado"
                         value="{{ old('descripcion_motivo_traslado') }}"
                         class="form-control @error('descripcion_motivo_traslado') is-invalid @enderror"
                         maxlength="200" required>
                  @error('descripcion_motivo_traslado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Fecha inicio traslado *</label>
                  <input type="date" name="fecha_inicio_traslado"
                         value="{{ old('fecha_inicio_traslado', now()->format('Y-m-d')) }}"
                         class="form-control @error('fecha_inicio_traslado') is-invalid @enderror" required>
                  @error('fecha_inicio_traslado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Peso bruto total *</label>
                  <input type="number" name="peso_bruto_total" step="0.001" min="0"
                         value="{{ old('peso_bruto_total') }}"
                         class="form-control @error('peso_bruto_total') is-invalid @enderror" required>
                  @error('peso_bruto_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Unidad medida peso *</label>
                  <select name="codigo_unidad_medida_peso_bruto"
                          class="form-control @error('codigo_unidad_medida_peso_bruto') is-invalid @enderror" required>
                    <option value="">— Selecciona —</option>
                    <option value="KGM" {{ old('codigo_unidad_medida_peso_bruto') == 'KGM' ? 'selected' : '' }}>KGM - Kilogramos</option>
                    <option value="TNE" {{ old('codigo_unidad_medida_peso_bruto') == 'TNE' ? 'selected' : '' }}>TNE - Toneladas</option>
                    <option value="LBR" {{ old('codigo_unidad_medida_peso_bruto') == 'LBR' ? 'selected' : '' }}>LBR - Libras</option>
                  </select>
                  @error('codigo_unidad_medida_peso_bruto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              {{-- Modalidad de traslado --}}
              <label class="field-label">Modalidad de traslado *</label>
              <div class="modal-tabs">
                <button type="button" class="modal-tab {{ old('codigo_modalidad_traslado', '02') === '02' ? 'active' : '' }}"
                        data-modalidad="02">
                  <i class='bx bx-car'></i> Transporte Privado (02)
                </button>
                <button type="button" class="modal-tab {{ old('codigo_modalidad_traslado') === '01' ? 'active' : '' }}"
                        data-modalidad="01">
                  <i class='bx bx-bus'></i> Transporte Público (01)
                </button>
              </div>
              <input type="hidden" name="codigo_modalidad_traslado" id="input-modalidad"
                     value="{{ old('codigo_modalidad_traslado', '02') }}">
              @error('codigo_modalidad_traslado')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
            </div>

            {{-- ── Destinatario ─────────────────────────────────────── --}}
            <div class="form-section">
              <h4><i class='bx bx-user-check'></i> Destinatario</h4>
              <div class="field-group cols-3">
                <div>
                  <label class="field-label">Tipo documento *</label>
                  <select name="gre_destinatario[codigo_tipo_documento_destinatario]"
                          class="form-control @error('gre_destinatario.codigo_tipo_documento_destinatario') is-invalid @enderror" required>
                    <option value="6" {{ old('gre_destinatario.codigo_tipo_documento_destinatario', '6') == '6' ? 'selected' : '' }}>6 - RUC</option>
                    <option value="1" {{ old('gre_destinatario.codigo_tipo_documento_destinatario') == '1' ? 'selected' : '' }}>1 - DNI</option>
                    <option value="4" {{ old('gre_destinatario.codigo_tipo_documento_destinatario') == '4' ? 'selected' : '' }}>4 - Carnet de extranjería</option>
                    <option value="7" {{ old('gre_destinatario.codigo_tipo_documento_destinatario') == '7' ? 'selected' : '' }}>7 - Pasaporte</option>
                  </select>
                  @error('gre_destinatario.codigo_tipo_documento_destinatario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Número documento *</label>
                  <div style="display:flex;gap:.35rem;">
                    <input type="text" name="gre_destinatario[numero_documento_destinatario]"
                           id="dest-numero"
                           value="{{ old('gre_destinatario.numero_documento_destinatario') }}"
                           class="form-control @error('gre_destinatario.numero_documento_destinatario') is-invalid @enderror"
                           maxlength="20" required>
                    <button type="button" onclick="lookupDestinatario()" class="btn-secondary"
                            style="white-space:nowrap;padding:.3rem .65rem;" title="Consultar documento">
                      <i class='bx bx-search'></i>
                    </button>
                  </div>
                  @error('gre_destinatario.numero_documento_destinatario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Razón social / Nombre *</label>
                  <input type="text" name="gre_destinatario[nombre_razon_social_destinatario]"
                         value="{{ old('gre_destinatario.nombre_razon_social_destinatario') }}"
                         class="form-control @error('gre_destinatario.nombre_razon_social_destinatario') is-invalid @enderror"
                         maxlength="200" required>
                  @error('gre_destinatario.nombre_razon_social_destinatario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

            {{-- ── Puntos de partida y llegada ──────────────────────── --}}
            <div class="form-section">
              <h4><i class='bx bx-map-pin'></i> Punto de Partida y Llegada</h4>
              <div class="field-group cols-2">
                <div>
                  <p style="font-size:.8rem; font-weight:700; color:var(--clr-active-bg,#1a6b57); margin-bottom:.5rem;">PUNTO DE PARTIDA</p>
                  <div style="margin-bottom:.75rem;">
                    <label class="field-label">Ubigeo *</label>
                    <div class="ubigeo-picker">
                      <input type="text" name="gre_punto_partida[ubigeo_punto_partida]"
                             value="{{ old('gre_punto_partida.ubigeo_punto_partida') }}"
                             class="form-control js-ubigeo-search @error('gre_punto_partida.ubigeo_punto_partida') is-invalid @enderror"
                             placeholder="Código, distrito, provincia..." maxlength="6" pattern="[0-9]{6}" autocomplete="off" required>
                      <div class="ubigeo-results" role="listbox"></div>
                      <div class="ubigeo-error">El ubigeo debe tener exactamente 6 dígitos.</div>
                    </div>
                    <small class="ubigeo-help">Busca por código, distrito, provincia o departamento.</small>
                    @error('gre_punto_partida.ubigeo_punto_partida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div>
                    <label class="field-label">Dirección *</label>
                    <input type="text" name="gre_punto_partida[direccion_punto_partida]"
                           value="{{ old('gre_punto_partida.direccion_punto_partida') }}"
                           class="form-control @error('gre_punto_partida.direccion_punto_partida') is-invalid @enderror"
                           maxlength="300" required>
                    @error('gre_punto_partida.direccion_punto_partida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
                <div>
                  <p style="font-size:.8rem; font-weight:700; color:var(--clr-active-bg,#1a6b57); margin-bottom:.5rem;">PUNTO DE LLEGADA</p>
                  <div style="margin-bottom:.75rem;">
                    <label class="field-label">Ubigeo *</label>
                    <div class="ubigeo-picker">
                      <input type="text" name="gre_punto_llegada[ubigeo_punto_llegada]"
                             value="{{ old('gre_punto_llegada.ubigeo_punto_llegada') }}"
                             class="form-control js-ubigeo-search @error('gre_punto_llegada.ubigeo_punto_llegada') is-invalid @enderror"
                             placeholder="Código, distrito, provincia..." maxlength="6" pattern="[0-9]{6}" autocomplete="off" required>
                      <div class="ubigeo-results" role="listbox"></div>
                      <div class="ubigeo-error">El ubigeo debe tener exactamente 6 dígitos.</div>
                    </div>
                    <small class="ubigeo-help">Busca por código, distrito, provincia o departamento.</small>
                    @error('gre_punto_llegada.ubigeo_punto_llegada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div>
                    <label class="field-label">Dirección *</label>
                    <input type="text" name="gre_punto_llegada[direccion_punto_llegada]"
                           value="{{ old('gre_punto_llegada.direccion_punto_llegada') }}"
                           class="form-control @error('gre_punto_llegada.direccion_punto_llegada') is-invalid @enderror"
                           maxlength="300" required>
                    @error('gre_punto_llegada.direccion_punto_llegada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
              </div>
            </div>

            {{-- ── Transportista (Modalidad 01) ─────────────────────── --}}
            <div class="form-section" id="section-transportista"
                 style="{{ old('codigo_modalidad_traslado', '02') !== '01' ? 'display:none;' : '' }}">
              <h4><i class='bx bx-bus'></i> Datos del Transportista (Transporte Público)</h4>
              <div class="field-group cols-3">
                <div>
                  <label class="field-label">Tipo documento transportista *</label>
                  <select name="gre_transportista[codigo_tipo_documento_transportista]"
                          class="form-control @error('gre_transportista.codigo_tipo_documento_transportista') is-invalid @enderror">
                    <option value="6" {{ old('gre_transportista.codigo_tipo_documento_transportista','6') == '6' ? 'selected' : '' }}>6 - RUC</option>
                    <option value="1" {{ old('gre_transportista.codigo_tipo_documento_transportista') == '1' ? 'selected' : '' }}>1 - DNI</option>
                  </select>
                  @error('gre_transportista.codigo_tipo_documento_transportista')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Número documento transportista *</label>
                  <div style="display:flex;gap:.35rem;">
                    <input type="text" name="gre_transportista[numero_documento_transportista]"
                           id="trans-numero"
                           value="{{ old('gre_transportista.numero_documento_transportista') }}"
                           class="form-control @error('gre_transportista.numero_documento_transportista') is-invalid @enderror"
                           maxlength="20">
                    <button type="button" onclick="lookupTransportista()" class="btn-secondary"
                            style="white-space:nowrap;padding:.3rem .65rem;" title="Consultar documento">
                      <i class='bx bx-search'></i>
                    </button>
                  </div>
                  @error('gre_transportista.numero_documento_transportista')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                  <label class="field-label">Razón social transportista *</label>
                  <input type="text" name="gre_transportista[nombre_razon_social_transportista]"
                         value="{{ old('gre_transportista.nombre_razon_social_transportista') }}"
                         class="form-control @error('gre_transportista.nombre_razon_social_transportista') is-invalid @enderror"
                         maxlength="200">
                  @error('gre_transportista.nombre_razon_social_transportista')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

            {{-- ── Vehículos y Conductores (Modalidad 02) ───────────── --}}
            <div id="section-privado"
                 style="{{ old('codigo_modalidad_traslado', '02') !== '02' ? 'display:none;' : '' }}">

              {{-- Vehículos --}}
              <div class="form-section">
                <h4><i class='bx bx-car'></i> Vehículos (Transporte Privado)</h4>
                <div id="vehiculos-container">
                  @if(old('gre_vehiculos'))
                    @foreach(old('gre_vehiculos') as $vi => $v)
                      <div class="vehiculo-row" data-vehiculo>
                        <div>
                          <label class="field-label">Placa *</label>
                          <input type="text" name="gre_vehiculos[{{ $vi }}][numero_placa]"
                                 value="{{ $v['numero_placa'] ?? '' }}"
                                 class="form-control" placeholder="Ej: ABC-123" maxlength="10" required>
                        </div>
                        <div style="padding-top:1.4rem;">
                          <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;">
                            <input type="checkbox" name="gre_vehiculos[{{ $vi }}][indicador_principal]"
                                   value="1" {{ ($v['indicador_principal'] ?? false) ? 'checked' : '' }}>
                            Principal
                          </label>
                        </div>
                        <div style="padding-top:1.4rem;">
                          <button type="button" class="btn-remove-row" onclick="removeRow(this, 'vehiculo')">
                            <i class='bx bx-trash'></i>
                          </button>
                        </div>
                      </div>
                    @endforeach
                  @else
                    <div class="vehiculo-row" data-vehiculo>
                      <div>
                        <label class="field-label">Placa *</label>
                        <input type="text" name="gre_vehiculos[0][numero_placa]"
                               class="form-control" placeholder="Ej: ABC-123" maxlength="10" required>
                      </div>
                      <div style="padding-top:1.4rem;">
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;">
                          <input type="checkbox" name="gre_vehiculos[0][indicador_principal]" value="1" checked>
                          Principal
                        </label>
                      </div>
                      <div style="padding-top:1.4rem;">
                        <button type="button" class="btn-remove-row" onclick="removeRow(this, 'vehiculo')">
                          <i class='bx bx-trash'></i>
                        </button>
                      </div>
                    </div>
                  @endif
                </div>
                <button type="button" class="btn-add-row" style="margin-top:.5rem;" onclick="addVehiculo()">
                  <i class='bx bx-plus'></i> Agregar Vehículo
                </button>
                @error('gre_vehiculos')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
              </div>

              {{-- Conductores --}}
              <div class="form-section">
                <h4><i class='bx bx-id-card'></i> Conductores (opcional)</h4>
                <div style="overflow-x:auto;">
                  <div style="min-width:700px;">
                    <div style="display:grid; grid-template-columns:80px 1fr 1fr 1fr 1fr 120px 40px; gap:.5rem; margin-bottom:.25rem;">
                      <span class="field-label">Tipo Doc</span>
                      <span class="field-label">N° Doc</span>
                      <span class="field-label">Nombre</span>
                      <span class="field-label">Apellido</span>
                      <span class="field-label">N° Licencia</span>
                      <span class="field-label">Principal</span>
                      <span></span>
                    </div>
                    <div id="conductores-container">
                      @if(old('gre_conductores'))
                        @foreach(old('gre_conductores') as $ci => $c)
                          <div style="display:grid;grid-template-columns:80px 1fr 1fr 1fr 1fr 120px 40px;gap:.5rem;margin-bottom:.5rem;" data-conductor>
                            <select name="gre_conductores[{{ $ci }}][codigo_tipo_documento]" class="form-control">
                              <option value="1" {{ ($c['codigo_tipo_documento'] ?? '1') == '1' ? 'selected' : '' }}>DNI</option>
                              <option value="4" {{ ($c['codigo_tipo_documento'] ?? '') == '4' ? 'selected' : '' }}>C.E.</option>
                              <option value="7" {{ ($c['codigo_tipo_documento'] ?? '') == '7' ? 'selected' : '' }}>Pasaporte</option>
                            </select>
                            <div style="display:flex;gap:.35rem;">
                              <input type="text" name="gre_conductores[{{ $ci }}][numero_documento]" value="{{ $c['numero_documento'] ?? '' }}" class="form-control cond-numero" placeholder="N° Doc">
                              <button type="button" class="btn-secondary btn-lookup-cond" onclick="lookupConductor(this)" style="white-space:nowrap;padding:.3rem .5rem;" title="Consultar DNI"><i class='bx bx-search'></i></button>
                            </div>
                            <input type="text" name="gre_conductores[{{ $ci }}][nombre]" value="{{ $c['nombre'] ?? '' }}" class="form-control cond-nombre" placeholder="Nombre">
                            <input type="text" name="gre_conductores[{{ $ci }}][apellido]" value="{{ $c['apellido'] ?? '' }}" class="form-control" placeholder="Apellido">
                            <input type="text" name="gre_conductores[{{ $ci }}][numero_licencia]" value="{{ $c['numero_licencia'] ?? '' }}" class="form-control" placeholder="Licencia">
                            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer;padding-top:.4rem;">
                              <input type="checkbox" name="gre_conductores[{{ $ci }}][indicador_principal]" value="1" {{ ($c['indicador_principal'] ?? false) ? 'checked' : '' }}>
                              Principal
                            </label>
                            <button type="button" class="btn-remove-row" style="padding-top:.4rem;" onclick="removeRow(this, 'conductor')">
                              <i class='bx bx-trash'></i>
                            </button>
                          </div>
                        @endforeach
                      @endif
                    </div>
                  </div>
                </div>
                <button type="button" class="btn-add-row" style="margin-top:.5rem;" onclick="addConductor()">
                  <i class='bx bx-plus'></i> Agregar Conductor
                </button>
              </div>
            </div>

            {{-- ── Documentos relacionados ─────────────────────────── --}}
            <div class="form-section">
              <h4><i class='bx bx-link'></i> Documento Relacionado</h4>
              <div style="display:flex; justify-content:space-between; gap:1rem; align-items:center; flex-wrap:wrap; margin-bottom:.75rem;">
                <p style="margin:0; color:var(--clr-text-muted,#6b7280); font-size:.84rem;">Opcional. Úsalo cuando la guía está relacionada a una factura, boleta, nota de crédito o nota de débito.</p>
                <button type="button" class="btn-add-row" onclick="addRelatedDocument()">
                  <i class='bx bx-plus'></i> Agregar documento
                </button>
              </div>
              <div id="related-documents-container">
                @foreach(old('gre_documentos_relacionados', []) as $di => $document)
                  <div class="related-doc-row" data-related-document style="display:grid; grid-template-columns:140px 90px 130px 90px 150px 40px; gap:.5rem; align-items:end; margin-bottom:.55rem;">
                    <div>
                      <label class="field-label">Tipo</label>
                      <select name="gre_documentos_relacionados[{{ $di }}][codigo_tipo_documento]" class="form-control related-doc-type">
                        <option value="01" @selected(($document['codigo_tipo_documento'] ?? '') === '01')>Factura</option>
                        <option value="03" @selected(($document['codigo_tipo_documento'] ?? '') === '03')>Boleta</option>
                        <option value="07" @selected(($document['codigo_tipo_documento'] ?? '') === '07')>N. Crédito</option>
                        <option value="08" @selected(($document['codigo_tipo_documento'] ?? '') === '08')>N. Débito</option>
                      </select>
                      <input type="hidden" name="gre_documentos_relacionados[{{ $di }}][descripcion_tipo_documento]" value="{{ $document['descripcion_tipo_documento'] ?? 'Factura' }}" class="related-doc-label">
                    </div>
                    <div>
                      <label class="field-label">Serie</label>
                      <input type="text" name="gre_documentos_relacionados[{{ $di }}][serie_documento]" value="{{ $document['serie_documento'] ?? '' }}" class="form-control" maxlength="10" placeholder="F001">
                    </div>
                    <div>
                      <label class="field-label">Número</label>
                      <input type="text" name="gre_documentos_relacionados[{{ $di }}][numero_documento]" value="{{ $document['numero_documento'] ?? '' }}" class="form-control" maxlength="20" placeholder="00000001">
                    </div>
                    <div>
                      <label class="field-label">Tipo emisor</label>
                      <select name="gre_documentos_relacionados[{{ $di }}][codigo_tipo_documento_emisor]" class="form-control">
                        <option value="6" @selected(($document['codigo_tipo_documento_emisor'] ?? '6') === '6')>RUC</option>
                        <option value="1" @selected(($document['codigo_tipo_documento_emisor'] ?? '') === '1')>DNI</option>
                      </select>
                    </div>
                    <div>
                      <label class="field-label">Doc. emisor</label>
                      <input type="text" name="gre_documentos_relacionados[{{ $di }}][numero_documento_emisor]" value="{{ $document['numero_documento_emisor'] ?? '' }}" class="form-control" maxlength="20" placeholder="RUC emisor">
                    </div>
                    <button type="button" class="btn-remove-row" onclick="removeRelatedDocument(this)"><i class='bx bx-trash'></i></button>
                  </div>
                @endforeach
              </div>
            </div>

            {{-- ── Ítems de la guía ─────────────────────────────────── --}}
            <div class="form-section">
              <h4><i class='bx bx-list-ul'></i> Ítems</h4>

              <div style="overflow-x:auto; min-width:0;">
                <div style="min-width:620px;">
                  <div style="display:grid; grid-template-columns:40px 170px 1fr 80px 100px 110px 40px; gap:.5rem; padding-bottom:.3rem; margin-bottom:.3rem; border-bottom:2px solid var(--clr-border-light,#e5e7eb);">
                    <span class="field-label">#</span>
                    <span class="field-label">Producto</span>
                    <span class="field-label">Descripción *</span>
                    <span class="field-label">Unidad *</span>
                    <span class="field-label">Cód. interno *</span>
                    <span class="field-label">Cantidad *</span>
                    <span></span>
                  </div>
                  <div id="items-container">
                    @if(old('items'))
                      @foreach(old('items') as $ii => $item)
                        <div class="item-row" data-item>
                          <span style="font-size:.8rem; color:#9ca3af; text-align:center;">{{ $ii + 1 }}</span>
                          <select class="gre-product-picker form-control">
                            <option value="">— Producto —</option>
                            @foreach($products as $prod)
                              <option value="{{ $prod->id }}"
                                      data-desc="{{ $prod->descripcion }}"
                                      data-codigo="{{ $prod->codigo_interno ?? '' }}"
                                      data-unidad="{{ $prod->codigo_unidad_medida ?? 'NIU' }}">{{ $prod->descripcion }}</option>
                            @endforeach
                          </select>
                          <input type="text" name="items[{{ $ii }}][descripcion]"
                                 value="{{ $item['descripcion'] ?? '' }}"
                                 class="form-control" placeholder="Describir mercancía" required>
                          <select name="items[{{ $ii }}][codigo_unidad_medida]" class="form-control" required>
                            @include('facturador.gre._unidades_options', ['selected' => $item['codigo_unidad_medida'] ?? 'NIU'])
                          </select>
                          <input type="text" name="items[{{ $ii }}][codigo_interno]"
                                 value="{{ $item['codigo_interno'] ?? '' }}"
                                 class="form-control" placeholder="Código" required>
                          <input type="number" name="items[{{ $ii }}][cantidad]"
                                 value="{{ $item['cantidad'] ?? '' }}"
                                 class="form-control" step="0.0001" min="0.0001" required>
                          <button type="button" class="btn-remove-row" onclick="removeRow(this, 'item')">
                            <i class='bx bx-trash'></i>
                          </button>
                        </div>
                      @endforeach
                    @else
                      <div class="item-row" data-item>
                        <span style="font-size:.8rem; color:#9ca3af; text-align:center;">1</span>
                        <select class="gre-product-picker form-control">
                          <option value="">— Producto —</option>
                          @foreach($products as $prod)
                            <option value="{{ $prod->id }}"
                                    data-desc="{{ $prod->descripcion }}"
                                    data-codigo="{{ $prod->codigo_interno ?? '' }}"
                                    data-unidad="{{ $prod->codigo_unidad_medida ?? 'NIU' }}">{{ $prod->descripcion }}</option>
                          @endforeach
                        </select>
                        <input type="text" name="items[0][descripcion]"
                               class="form-control" placeholder="Describir mercancía" required>
                        <select name="items[0][codigo_unidad_medida]" class="form-control" required>
                          @include('facturador.gre._unidades_options', ['selected' => 'NIU'])
                        </select>
                        <input type="text" name="items[0][codigo_interno]"
                               class="form-control" placeholder="Código" required>
                        <input type="number" name="items[0][cantidad]"
                               class="form-control" step="0.0001" min="0.0001" placeholder="1" required>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this, 'item')">
                          <i class='bx bx-trash'></i>
                        </button>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
              <button type="button" class="btn-add-row" style="margin-top:.75rem;" onclick="addItem()">
                <i class='bx bx-plus'></i> Agregar Ítem
              </button>
              @error('items')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
            </div>

            {{-- ── Submit ────────────────────────────────────────────── --}}
            <div style="display:flex; justify-content:flex-end; gap:.75rem; margin-top:1rem;">
              <a href="{{ route('facturador.gre.index') }}" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">
                <i class='bx bx-save'></i> Guardar Guía
              </button>
            </div>

          </form>
        </div>
      </div>
    </main>
  </section>
</div>
@endsection

@push('scripts')
<script>
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    icon: 'warning',
    title: 'Faltan datos para guardar la guía',
    html: `{!! '<ul style="text-align:left;margin:0;padding-left:1.1rem;">' . collect($errors->all())->map(fn($error) => '<li>' . e($error) . '</li>')->implode('') . '</ul>' !!}`,
    confirmButtonText: 'Revisar campos',
    customClass: { popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : '' }
  });
});
@endif

function padGreNumber(value) {
  const raw = String(value || '').trim();
  return /^\d+$/.test(raw) ? raw.padStart(8, '0') : raw;
}

function updateGreInternalCode() {
  const serie = document.querySelector('[name="serie_documento"]')?.value || '';
  const numero = document.querySelector('[name="numero_documento"]')?.value || '';
  const codigo = document.getElementById('codigo_interno');
  if (codigo) codigo.value = '09' + serie + padGreNumber(numero);
}

document.querySelector('[name="serie_documento"]')?.addEventListener('input', updateGreInternalCode);
document.querySelector('[name="numero_documento"]')?.addEventListener('input', updateGreInternalCode);
document.addEventListener('DOMContentLoaded', updateGreInternalCode);

// ── Modalidad toggle ────────────────────────────────────────────────────────
document.querySelectorAll('.modal-tab').forEach(function(btn) {
  btn.addEventListener('click', function() {
    const modalidad = this.dataset.modalidad;
    document.getElementById('input-modalidad').value = modalidad;
    document.querySelectorAll('.modal-tab').forEach(b => b.classList.remove('active'));
    this.classList.add('active');

    if (modalidad === '01') {
      document.getElementById('section-transportista').style.display = '';
      document.getElementById('section-privado').style.display = 'none';
    } else {
      document.getElementById('section-transportista').style.display = 'none';
      document.getElementById('section-privado').style.display = '';
    }
  });
});

// ── Helpers de índice ────────────────────────────────────────────────────────
function reindex(container, prefix, fields) {
  const rows = container.querySelectorAll('[data-' + prefix + ']');
  const numberSpans = container.querySelectorAll('[data-rownum]');
  rows.forEach(function(row, idx) {
    fields.forEach(function(field) {
      const el = row.querySelector('[name*="[' + field + ']"]');
      if (el) el.name = prefix === 'item'
        ? 'items[' + idx + '][' + field + ']'
        : 'gre_' + prefix + 's[' + idx + '][' + field + ']';
    });
  });
  if (container.querySelectorAll('[data-rownum]').length) {
    numberSpans.forEach(function(sp, idx) { sp.textContent = idx + 1; });
  }
}

function removeRow(btn, type) {
  if (type === 'item') {
    const container = document.getElementById('items-container');
    if (container.querySelectorAll('[data-item]').length <= 1) {
      Swal.fire({
        title: 'Ítem requerido',
        text: 'La guía debe tener al menos un ítem.',
        icon: 'warning',
        confirmButtonText: 'Entendido',
        customClass: { popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : '' }
      });
      return;
    }
  }
  btn.closest('[data-' + type + ']').remove();
  renumberAll();
}

function renumberAll() {
  // renumerar ítems
  const itemsContainer = document.getElementById('items-container');
  itemsContainer.querySelectorAll('[data-item]').forEach(function(row, idx) {
    const span = row.querySelector('span');
    if (span) span.textContent = idx + 1;
    ['descripcion','codigo_unidad_medida','codigo_interno','cantidad'].forEach(function(f) {
      const el = row.querySelector('[name*="[' + f + ']"]');
      if (el) el.name = 'items[' + idx + '][' + f + ']';
    });
  });
  // renumerar vehículos
  document.getElementById('vehiculos-container').querySelectorAll('[data-vehiculo]').forEach(function(row, idx) {
    ['numero_placa','indicador_principal'].forEach(function(f) {
      const el = row.querySelector('[name*="[' + f + ']"]');
      if (el) el.name = 'gre_vehiculos[' + idx + '][' + f + ']';
    });
  });
  // renumerar conductores
  document.getElementById('conductores-container').querySelectorAll('[data-conductor]').forEach(function(row, idx) {
    ['codigo_tipo_documento','numero_documento','nombre','apellido','numero_licencia','indicador_principal'].forEach(function(f) {
      const el = row.querySelector('[name*="[' + f + ']"]');
      if (el) el.name = 'gre_conductores[' + idx + '][' + f + ']';
    });
  });
}

// ── Agregar ítem ─────────────────────────────────────────────────────────────
function addItem() {
  const container = document.getElementById('items-container');
  const idx = container.querySelectorAll('[data-item]').length;
  const row = document.createElement('div');
  row.className = 'item-row';
  row.setAttribute('data-item', '');
  row.innerHTML =
    '<span style="font-size:.8rem;color:#9ca3af;text-align:center;">' + (idx + 1) + '</span>' +
    '<select class="gre-product-picker form-control"><option value="">— Producto —</option>' + productsOptionsHtml() + '</select>' +
    '<input type="text" name="items[' + idx + '][descripcion]" class="form-control" placeholder="Describir mercancía" required>' +
    '<select name="items[' + idx + '][codigo_unidad_medida]" class="form-control" required>' +
    unidadesOptionsHtml('NIU') +
    '</select>' +
    '<input type="text" name="items[' + idx + '][codigo_interno]" class="form-control" placeholder="Código" required>' +
    '<input type="number" name="items[' + idx + '][cantidad]" class="form-control" step="0.0001" min="0.0001" placeholder="1" required>' +
    '<button type="button" class="btn-remove-row" onclick="removeRow(this,\'item\')"><i class=\'bx bx-trash\'></i></button>';
  container.appendChild(row);
}

function clearItems() {
  document.getElementById('items-container').innerHTML = '';
}

function ensureItemRows(count) {
  clearItems();
  for (let i = 0; i < Math.max(1, count); i++) addItem();
}

// ── Agregar vehículo ─────────────────────────────────────────────────────────
function addVehiculo() {
  const container = document.getElementById('vehiculos-container');
  const idx = container.querySelectorAll('[data-vehiculo]').length;
  const row = document.createElement('div');
  row.className = 'vehiculo-row';
  row.setAttribute('data-vehiculo', '');
  row.innerHTML =
    '<div><label class="field-label">Placa *</label><input type="text" name="gre_vehiculos[' + idx + '][numero_placa]" class="form-control" placeholder="Ej: ABC-123" maxlength="10"></div>' +
    '<div style="padding-top:1.4rem;"><label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;"><input type="checkbox" name="gre_vehiculos[' + idx + '][indicador_principal]" value="1"> Principal</label></div>' +
    '<div style="padding-top:1.4rem;"><button type="button" class="btn-remove-row" onclick="removeRow(this,\'vehiculo\')"><i class=\'bx bx-trash\'></i></button></div>';
  container.appendChild(row);
}

function clearVehiculos() {
  document.getElementById('vehiculos-container').innerHTML = '';
}

function ensureVehiculoRows(count) {
  clearVehiculos();
  for (let i = 0; i < Math.max(1, count); i++) addVehiculo();
}

// ── Agregar conductor ────────────────────────────────────────────────────────
function addConductor() {
  const container = document.getElementById('conductores-container');
  const idx = container.querySelectorAll('[data-conductor]').length;
  const row = document.createElement('div');
  row.setAttribute('data-conductor', '');
  row.style.cssText = 'display:grid;grid-template-columns:80px 1fr 1fr 1fr 1fr 120px 40px;gap:.5rem;margin-bottom:.5rem;';
  row.innerHTML =
    '<select name="gre_conductores[' + idx + '][codigo_tipo_documento]" class="form-control"><option value="1">DNI</option><option value="4">C.E.</option><option value="7">Pasaporte</option></select>' +
    '<div style="display:flex;gap:.35rem;"><input type="text" name="gre_conductores[' + idx + '][numero_documento]" class="form-control cond-numero" placeholder="N° Doc"><button type="button" class="btn-secondary btn-lookup-cond" onclick="lookupConductor(this)" style="white-space:nowrap;padding:.3rem .5rem;" title="Consultar DNI"><i class=\'bx bx-search\'></i></button></div>' +
    '<input type="text" name="gre_conductores[' + idx + '][nombre]" class="form-control cond-nombre" placeholder="Nombre">' +
    '<input type="text" name="gre_conductores[' + idx + '][apellido]" class="form-control cond-apellido" placeholder="Apellido">' +
    '<input type="text" name="gre_conductores[' + idx + '][numero_licencia]" class="form-control" placeholder="Licencia">' +
    '<label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer;padding-top:.4rem;"><input type="checkbox" name="gre_conductores[' + idx + '][indicador_principal]" value="1"> Principal</label>' +
    '<button type="button" class="btn-remove-row" style="padding-top:.4rem;" onclick="removeRow(this,\'conductor\')"><i class=\'bx bx-trash\'></i></button>';
  container.appendChild(row);
}

function clearConductores() {
  document.getElementById('conductores-container').innerHTML = '';
}

// ── Opciones de unidades para JS ─────────────────────────────────────────────
function unidadesOptionsHtml(selected) {
  const units = [
    ['NIU','NIU - Unidad'],['ZZ','ZZ - Servicio'],['KGM','KGM - Kilogramos'],
    ['MTR','MTR - Metro'],['LTR','LTR - Litro'],['BX','BX - Caja'],
    ['BG','BG - Bolsa'],['BO','BO - Botella'],['PA','PA - Par'],
    ['PK','PK - Paquete'],['DZN','DZN - Docena'],['SET','SET - Conjunto'],
    ['MIL','MIL - Millar'],['GRM','GRM - Gramo'],['MLT','MLT - Mililitro'],
    ['M2','M2 - Metros cuadrados'],['M3','M3 - Metros cúbicos'],
    ['TNE','TNE - Tonelada'],
  ];
  return units.map(function(u) {
    return '<option value="' + u[0] + '"' + (u[0] === selected ? ' selected' : '') + '>' + u[1] + '</option>';
  }).join('');
}

// ── Datos de productos (para nuevas filas JS) ────────────────────────────────
@php
  $productsJson = $products->map(function ($p) {
    return [
      'id'     => $p->id,
      'desc'   => $p->descripcion,
      'codigo' => $p->codigo_interno       ?? '',
      'unidad' => $p->codigo_unidad_medida ?? 'NIU',
    ];
  })->values();
@endphp
const productsData = @json($productsJson);

function productsOptionsHtml() {
  return productsData.map(function(p) {
    const esc = function(s) { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;'); };
    return '<option value="' + p.id + '" data-desc="' + esc(p.desc) + '" data-codigo="' + esc(p.codigo) + '" data-unidad="' + esc(p.unidad) + '">' + p.desc + '</option>';
  }).join('');
}

// ── Lookup API ────────────────────────────────────────────────────────────────
const lookupUrl = '{{ route('facturador.clients.lookup-doc') }}';

function fetchLookup(type, number, onSuccess) {
  fetch(lookupUrl + '?type=' + encodeURIComponent(type) + '&number=' + encodeURIComponent(number))
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.ok) { onSuccess(data); }
      else { 
        Swal.fire({
          title: 'Documento no encontrado',
          text: data.error || data.message || 'Sin resultados',
          icon: 'info',
          confirmButtonText: 'OK',
          customClass: { popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : '' }
        });
      }
    })
    .catch(function() { 
      Swal.fire({
        title: 'Error de conexión',
        text: 'No se pudo consultar el documento. Intenta nuevamente.',
        icon: 'error',
        confirmButtonText: 'OK',
        customClass: { popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : '' }
      });
    });
}

function lookupDestinatario() {
  const typeEl   = document.querySelector('[name="gre_destinatario[codigo_tipo_documento_destinatario]"]');
  const numberEl = document.getElementById('dest-numero');
  const nombreEl = document.querySelector('[name="gre_destinatario[nombre_razon_social_destinatario]"]');
  if (!numberEl.value.trim()) { 
    Swal.fire({
      title: 'Campo requerido',
      text: 'Ingresa el número de documento.',
      icon: 'warning',
      confirmButtonText: 'OK',
      customClass: { popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : '' }
    });
    return; 
  }
  fetchLookup(typeEl.value, numberEl.value.trim(), function(data) {
    nombreEl.value = data.nombre || '';
  });
}

function lookupTransportista() {
  const typeEl   = document.querySelector('[name="gre_transportista[codigo_tipo_documento_transportista]"]');
  const numberEl = document.getElementById('trans-numero');
  const nombreEl = document.querySelector('[name="gre_transportista[nombre_razon_social_transportista]"]');
  if (!numberEl.value.trim()) { alert('Ingresa el número de documento.'); return; }
  fetchLookup(typeEl.value, numberEl.value.trim(), function(data) {
    nombreEl.value = data.nombre || '';
  });
}

function lookupConductor(btn) {
  const row      = btn.closest('[data-conductor]');
  const typeEl   = row.querySelector('[name*="[codigo_tipo_documento]"]');
  const numberEl = row.querySelector('.cond-numero');
  const nombreEl = row.querySelector('.cond-nombre');
  if (!numberEl || !numberEl.value.trim()) { alert('Ingresa el número de documento.'); return; }
  fetchLookup(typeEl.value, numberEl.value.trim(), function(data) {
    if (nombreEl) nombreEl.value = data.nombre || '';
  });
}

// ── Ubigeo picker ───────────────────────────────────────────────────────────
const UBIGEO_SEARCH_URL = @json(route('facturador.ubigeos.search'));
const ubigeoTimers = new WeakMap();

function closeUbigeoResults(except = null) {
  document.querySelectorAll('.ubigeo-results.is-open').forEach(function(box) {
    if (box !== except) box.classList.remove('is-open');
  });
}

function renderUbigeoResults(input, results) {
  const box = input.closest('.ubigeo-picker')?.querySelector('.ubigeo-results');
  if (!box) return;

  box.innerHTML = '';
  if (!results.length) {
    box.innerHTML = '<div class="ubigeo-option" style="cursor:default;"><strong>Sin resultados</strong><span>Importa el Excel de ubigeos o prueba otra búsqueda.</span></div>';
    box.classList.add('is-open');
    return;
  }

  results.forEach(function(item) {
    const option = document.createElement('button');
    option.type = 'button';
    option.className = 'ubigeo-option';
    option.innerHTML = `<strong>${item.code} - ${item.district}</strong><span>${item.department} / ${item.province}${item.legal_capital ? ' / Capital: ' + item.legal_capital : ''}</span>`;
    option.addEventListener('click', function() {
      input.value = item.code;
      input.dataset.ubigeoLabel = item.label;
      box.classList.remove('is-open');
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
    box.appendChild(option);
  });
  closeUbigeoResults(box);
  box.classList.add('is-open');
}

document.querySelectorAll('.js-ubigeo-search').forEach(function(input) {
  const validateUbigeo = function() {
    const isValid = /^\d{6}$/.test(input.value.trim());
    input.classList.toggle('is-invalid-client', input.value.trim() !== '' && !isValid);
    input.setCustomValidity(isValid ? '' : 'El ubigeo debe tener exactamente 6 dígitos.');
    return isValid;
  };

  input.addEventListener('input', function() {
    const raw = input.value.trim();
    input.value = raw.match(/^\d+$/) ? raw.replace(/\D/g, '').slice(0, 6) : raw;
    const term = input.value.trim();
    validateUbigeo();

    window.clearTimeout(ubigeoTimers.get(input));
    if (term.length < 2) {
      closeUbigeoResults();
      return;
    }

    ubigeoTimers.set(input, window.setTimeout(async function() {
      try {
        const res = await fetch(`${UBIGEO_SEARCH_URL}?q=${encodeURIComponent(term)}`, {
          headers: { 'Accept': 'application/json' },
        });
        const json = await res.json();
        renderUbigeoResults(input, json.results || []);
      } catch (e) {
        renderUbigeoResults(input, []);
      }
    }, 180));
  });

  input.addEventListener('focus', function() {
    if (input.value.trim().length >= 2) {
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }
  });

  input.addEventListener('blur', validateUbigeo);
});

document.addEventListener('click', function(event) {
  if (!event.target.closest('.ubigeo-picker')) {
    closeUbigeoResults();
  }
});

document.getElementById('gre-form')?.addEventListener('submit', function(event) {
  const invalid = Array.from(document.querySelectorAll('.js-ubigeo-search'))
    .find(function(input) {
      const isValid = /^\d{6}$/.test(input.value.trim());
      input.classList.toggle('is-invalid-client', !isValid);
      input.setCustomValidity(isValid ? '' : 'El ubigeo debe tener exactamente 6 dígitos.');
      return !isValid;
    });

  if (invalid) {
    event.preventDefault();
    invalid.reportValidity();
    invalid.focus();
  }
});

// ── Documentos relacionados ────────────────────────────────────────────────
const relatedDocLabels = {
  '01': 'Factura',
  '03': 'Boleta',
  '07': 'Nota de crédito',
  '08': 'Nota de débito',
};

function escapeAttr(value) {
  return String(value ?? '').replace(/[&<>"']/g, function(char) {
    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
  });
}

function relatedDocumentTemplate(index, values = {}) {
  const selected = values.codigo_tipo_documento || '01';
  const label = values.descripcion_tipo_documento || relatedDocLabels[selected] || 'Documento';
  return `
    <div class="related-doc-row" data-related-document style="display:grid; grid-template-columns:140px 90px 130px 90px 150px 40px; gap:.5rem; align-items:end; margin-bottom:.55rem;">
      <div>
        <label class="field-label">Tipo</label>
        <select name="gre_documentos_relacionados[${index}][codigo_tipo_documento]" class="form-control related-doc-type">
          ${Object.entries(relatedDocLabels).map(([code, text]) => `<option value="${code}" ${selected === code ? 'selected' : ''}>${text}</option>`).join('')}
        </select>
        <input type="hidden" name="gre_documentos_relacionados[${index}][descripcion_tipo_documento]" value="${escapeAttr(label)}" class="related-doc-label">
      </div>
      <div>
        <label class="field-label">Serie</label>
        <input type="text" name="gre_documentos_relacionados[${index}][serie_documento]" value="${escapeAttr(values.serie_documento)}" class="form-control" maxlength="10" placeholder="F001">
      </div>
      <div>
        <label class="field-label">Número</label>
        <input type="text" name="gre_documentos_relacionados[${index}][numero_documento]" value="${escapeAttr(values.numero_documento)}" class="form-control" maxlength="20" placeholder="00000001">
      </div>
      <div>
        <label class="field-label">Tipo emisor</label>
        <select name="gre_documentos_relacionados[${index}][codigo_tipo_documento_emisor]" class="form-control">
          <option value="6" ${(values.codigo_tipo_documento_emisor || '6') === '6' ? 'selected' : ''}>RUC</option>
          <option value="1" ${values.codigo_tipo_documento_emisor === '1' ? 'selected' : ''}>DNI</option>
        </select>
      </div>
      <div>
        <label class="field-label">Doc. emisor</label>
        <input type="text" name="gre_documentos_relacionados[${index}][numero_documento_emisor]" value="${escapeAttr(values.numero_documento_emisor)}" class="form-control" maxlength="20" placeholder="RUC emisor">
      </div>
      <button type="button" class="btn-remove-row" onclick="removeRelatedDocument(this)"><i class='bx bx-trash'></i></button>
    </div>
  `;
}

function reindexRelatedDocuments() {
  document.querySelectorAll('#related-documents-container [data-related-document]').forEach(function(row, index) {
    row.querySelectorAll('[name^="gre_documentos_relacionados["]').forEach(function(input) {
      input.name = input.name.replace(/gre_documentos_relacionados\[\d+\]/, `gre_documentos_relacionados[${index}]`);
    });
  });
}

function addRelatedDocument(values = {}) {
  const container = document.getElementById('related-documents-container');
  if (!container) return;
  const index = container.querySelectorAll('[data-related-document]').length;
  container.insertAdjacentHTML('beforeend', relatedDocumentTemplate(index, values));
}

function removeRelatedDocument(button) {
  button.closest('[data-related-document]')?.remove();
  reindexRelatedDocuments();
}

document.addEventListener('change', function(event) {
  if (!event.target.classList.contains('related-doc-type')) return;
  const label = event.target.closest('[data-related-document]')?.querySelector('.related-doc-label');
  if (label) label.value = relatedDocLabels[event.target.value] || 'Documento';
});

// ── Product picker ────────────────────────────────────────────────────────────
document.addEventListener('change', function(e) {
  if (!e.target.classList.contains('gre-product-picker')) return;
  const opt = e.target.selectedOptions[0];
  const row = e.target.closest('[data-item]');
  if (!opt || !opt.value || !row) return;
  const descEl   = row.querySelector('[name*="[descripcion]"]');
  const codigoEl = row.querySelector('[name*="[codigo_interno]"]');
  const unidadEl = row.querySelector('[name*="[codigo_unidad_medida]"]');
  if (descEl   && opt.dataset.desc)   descEl.value   = opt.dataset.desc;
  if (codigoEl && opt.dataset.codigo) codigoEl.value = opt.dataset.codigo;
  if (unidadEl && opt.dataset.unidad) unidadEl.value = opt.dataset.unidad;
});

// ── Extractor IA de GRE desde PDF ────────────────────────────────────────────
function setField(name, value) {
  if (value === null || value === undefined || value === '') return;
  const el = document.querySelector('[name="' + name + '"]');
  if (el) {
    el.value = value;
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }
}

function setModalidad(value) {
  if (!value) return;
  const btn = document.querySelector('.modal-tab[data-modalidad="' + value + '"]');
  if (btn) btn.click();
}

function applyGreExtractedData(data) {
  setField('codigo_motivo_traslado', data.codigo_motivo_traslado);
  setField('descripcion_motivo_traslado', data.descripcion_motivo_traslado);
  setField('fecha_inicio_traslado', data.fecha_inicio_traslado);
  setField('peso_bruto_total', data.peso_bruto_total);
  setField('codigo_unidad_medida_peso_bruto', data.codigo_unidad_medida_peso_bruto);
  setModalidad(data.codigo_modalidad_traslado || '02');

  const dest = data.gre_destinatario || {};
  setField('gre_destinatario[codigo_tipo_documento_destinatario]', dest.codigo_tipo_documento_destinatario || (dest.numero_documento_destinatario?.length === 11 ? '6' : null));
  setField('gre_destinatario[numero_documento_destinatario]', dest.numero_documento_destinatario);
  setField('gre_destinatario[nombre_razon_social_destinatario]', dest.nombre_razon_social_destinatario);

  const partida = data.gre_punto_partida || {};
  setField('gre_punto_partida[ubigeo_punto_partida]', partida.ubigeo_punto_partida);
  setField('gre_punto_partida[direccion_punto_partida]', partida.direccion_punto_partida);

  const llegada = data.gre_punto_llegada || {};
  setField('gre_punto_llegada[ubigeo_punto_llegada]', llegada.ubigeo_punto_llegada);
  setField('gre_punto_llegada[direccion_punto_llegada]', llegada.direccion_punto_llegada);

  const vehiculos = Array.isArray(data.gre_vehiculos) ? data.gre_vehiculos : [];
  if (vehiculos.length) {
    ensureVehiculoRows(vehiculos.length);
    document.querySelectorAll('#vehiculos-container [data-vehiculo]').forEach(function(row, idx) {
      const veh = vehiculos[idx] || {};
      const placa = row.querySelector('[name*="[numero_placa]"]');
      const principal = row.querySelector('[name*="[indicador_principal]"]');
      if (placa && veh.numero_placa) placa.value = veh.numero_placa;
      if (principal) principal.checked = idx === 0 || !!veh.indicador_principal;
    });
  }

  const conductores = Array.isArray(data.gre_conductores) ? data.gre_conductores : [];
  if (conductores.length) {
    clearConductores();
    conductores.forEach(function(conductor, idx) {
      addConductor();
      const row = document.querySelectorAll('#conductores-container [data-conductor]')[idx];
      if (!row) return;
      const setRow = function(field, value) {
        const el = row.querySelector('[name*="[' + field + ']"]');
        if (el && value !== null && value !== undefined) el.value = value;
      };
      setRow('codigo_tipo_documento', conductor.codigo_tipo_documento || '1');
      setRow('numero_documento', conductor.numero_documento);
      setRow('nombre', conductor.nombre);
      setRow('apellido', conductor.apellido);
      setRow('numero_licencia', conductor.numero_licencia);
      const principal = row.querySelector('[name*="[indicador_principal]"]');
      if (principal) principal.checked = idx === 0 || !!conductor.indicador_principal;
    });
  }

  const items = Array.isArray(data.items) ? data.items : [];
  if (items.length) {
    ensureItemRows(items.length);
    document.querySelectorAll('#items-container [data-item]').forEach(function(row, idx) {
      const item = items[idx] || {};
      const setRow = function(field, value) {
        const el = row.querySelector('[name*="[' + field + ']"]');
        if (el && value !== null && value !== undefined) el.value = value;
      };
      setRow('descripcion', item.descripcion);
      setRow('codigo_unidad_medida', item.codigo_unidad_medida || 'NIU');
      setRow('codigo_interno', item.codigo_interno || ('GRE' + String(idx + 1).padStart(3, '0')));
      setRow('cantidad', item.cantidad || 1);
    });
  }

  if (data.documento_relacionado) {
    const obs = document.querySelector('[name="observacion"]');
    if (obs && !obs.value.trim()) obs.value = 'Doc. relacionado: ' + data.documento_relacionado;
  }

  const documentosRelacionados = Array.isArray(data.lista_documentos_relacionados)
    ? data.lista_documentos_relacionados
    : (Array.isArray(data.gre_documentos_relacionados) ? data.gre_documentos_relacionados : []);

  if (documentosRelacionados.length) {
    const container = document.getElementById('related-documents-container');
    if (container) container.innerHTML = '';
    documentosRelacionados.forEach(function(documento) {
      addRelatedDocument(documento);
    });
  }
}

document.getElementById('gre-ai-extract-btn')?.addEventListener('click', async function() {
  const input = document.getElementById('gre-pdf-input');
  const status = document.getElementById('gre-ai-status');
  const file = input?.files?.[0];

  if (!file) {
    Swal.fire({ icon:'warning', title:'Selecciona un PDF', text:'Sube el PDF de la GRE para extraer los datos.' });
    return;
  }

  const fd = new FormData();
  fd.append('pdf', file);
  this.disabled = true;
  const original = this.innerHTML;
  this.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Extrayendo...";
  status.textContent = 'Leyendo PDF y completando datos operativos...';

  try {
    const res = await fetch(@json(route('facturador.gre.extract-pdf')), {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
      body: fd,
    });
    const json = await res.json();
    if (!res.ok || !json.ok) throw new Error(json.error || 'No se pudo extraer el PDF.');
    applyGreExtractedData(json.data || {});
    status.textContent = 'Datos extraídos. Revisa ubigeos, direcciones, placa, conductor e ítems antes de guardar.';
    Swal.fire({ icon:'success', title:'Datos cargados', text:'La GRE fue prellenada. Revisa los campos antes de guardar.' });
  } catch (e) {
    status.textContent = e.message || 'No se pudo procesar el PDF.';
    Swal.fire({ icon:'error', title:'Error al extraer', text:e.message || 'No se pudo procesar el PDF.' });
  } finally {
    this.disabled = false;
    this.innerHTML = original;
  }
});
</script>
@endpush
