@extends('layouts.app')

@section('title', 'Registrar Compra — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; }
    @media(max-width:700px){ .form-grid-2, .form-grid-3 { grid-template-columns:1fr; } }
    .form-section { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:12px; padding:1.5rem; margin-bottom:1.25rem; }
    .form-section__title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-active-bg,#1a6b57); margin-bottom:1rem; }
    .form-group label { display:block; font-size:.82rem; font-weight:600; color:var(--clr-text-muted,#6b7280); margin-bottom:.4rem; }
    .form-group input, .form-group select, .form-group textarea {
      width:100%; padding:.6rem .85rem; border:1px solid var(--clr-border-light,#e5e7eb);
      border-radius:8px; font-size:.92rem; background:transparent; color:var(--clr-text-main,#111827);
      outline:none; transition:all .2s; font-family:inherit; box-sizing:border-box;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
      border-color:var(--clr-active-bg,#1a6b57); box-shadow:0 0 0 3px rgba(26,107,87,.1);
    }
    .is-error input, .is-error select { border-color:#ef4444 !important; }
    .error-msg { font-size:.78rem; color:#ef4444; margin-top:.25rem; }
    .monto-row { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:.75rem; }
    @media(max-width:800px){ .monto-row { grid-template-columns:1fr 1fr; } }
    @media(max-width:480px){ .monto-row { grid-template-columns:1fr; } }
    #proveedor-suggestions { position:absolute; z-index:100; background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.12); max-height:200px; overflow-y:auto; width:100%; }
    .suggestion-item { padding:.6rem 1rem; cursor:pointer; font-size:.88rem; border-bottom:1px solid rgba(0,0,0,.04); }
    .suggestion-item:hover { background:rgba(26,107,87,.06); }
    .items-tbl { width:100%; border-collapse:collapse; font-size:.84rem; }
    .items-tbl th { background:#f1f5f9; padding:.4rem .55rem; text-align:left; font-weight:700; font-size:.78rem; color:#374151; white-space:nowrap; }
    .items-tbl td { padding:.3rem .45rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .items-tbl tbody tr:last-child td { border-bottom:none; }
    .items-tbl input { width:100%; padding:.3rem .45rem; border:1px solid #d1d5db; border-radius:6px; font-size:.82rem; box-sizing:border-box; background:transparent; color:var(--clr-text-main,#111827); }
    .items-tbl input:focus { border-color:var(--clr-active-bg,#1a6b57); outline:none; }
    .items-tbl .td-del { width:36px; text-align:center; }
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

            <div class="module-toolbar" style="margin-bottom:1.5rem;">
              <h1 style="display:flex; align-items:center; gap:.5rem;">
                <i class='bx bx-cart' style="color:var(--clr-text-main);"></i> Registrar Compra
              </h1>
              <a href="{{ route('facturador.compras.index') }}" class="btn-secondary">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
            </div>

            @if($errors->any())
              <div class="placeholder-content module-alert module-alert--error" style="margin-bottom:1rem;">
                <ul style="margin:0; padding-left:1.2rem;">
                  @foreach($errors->all() as $e)<li style="font-size:.88rem;">{{ $e }}</li>@endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('facturador.compras.store') }}" id="purchase-form">
              @csrf

              {{-- SECCIÓN 1: Comprobante --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-file'></i> Datos del Comprobante</p>
                <div class="form-grid-3">
                  <div class="form-group {{ $errors->has('codigo_tipo_documento') ? 'is-error' : '' }}">
                    <label for="codigo_tipo_documento">Tipo de Documento <span style="color:#ef4444;">*</span></label>
                    <select name="codigo_tipo_documento" id="codigo_tipo_documento" required onchange="onTipoDocChange(this.value)">
                      <option value="01" {{ old('codigo_tipo_documento','01') === '01' ? 'selected' : '' }}>01 - Factura</option>
                      <option value="03" {{ old('codigo_tipo_documento') === '03' ? 'selected' : '' }}>03 - Boleta</option>
                      <option value="07" {{ old('codigo_tipo_documento') === '07' ? 'selected' : '' }}>07 - Nota de Crédito</option>
                      <option value="08" {{ old('codigo_tipo_documento') === '08' ? 'selected' : '' }}>08 - Nota de Débito</option>
                      <option value="00" {{ old('codigo_tipo_documento') === '00' ? 'selected' : '' }}>00 - DUA</option>
                    </select>
                    @error('codigo_tipo_documento')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group {{ $errors->has('serie_documento') ? 'is-error' : '' }}">
                    <label for="serie_documento">Serie</label>
                    <input type="text" name="serie_documento" id="serie_documento" value="{{ old('serie_documento') }}" maxlength="10" placeholder="Ej: F001">
                    @error('serie_documento')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group {{ $errors->has('numero_documento') ? 'is-error' : '' }}">
                    <label for="numero_documento">Número <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento') }}" maxlength="20" required placeholder="Ej: 00000001">
                    @error('numero_documento')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group {{ $errors->has('fecha_emision') ? 'is-error' : '' }}">
                    <label for="fecha_emision">Fecha de Emisión <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="fecha_emision" id="fecha_emision" value="{{ old('fecha_emision', now()->format('Y-m-d')) }}" required>
                    @error('fecha_emision')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group {{ $errors->has('fecha_vencimiento') ? 'is-error' : '' }}">
                    <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}">
                    @error('fecha_vencimiento')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group" id="anio-dua-group" style="display:none;">
                    <label for="anio_emision_dua">Año Emisión DUA</label>
                    <input type="text" name="anio_emision_dua" id="anio_emision_dua" value="{{ old('anio_emision_dua') }}" maxlength="4" placeholder="2024">
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 2: Proveedor --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-user-pin'></i> Datos del Proveedor</p>
                <div class="form-grid-3">
                  <div class="form-group {{ $errors->has('tipo_doc_proveedor') ? 'is-error' : '' }}">
                    <label for="tipo_doc_proveedor">Tipo Documento <span style="color:#ef4444;">*</span></label>
                    <select name="tipo_doc_proveedor" id="tipo_doc_proveedor" required>
                      <option value="6" {{ old('tipo_doc_proveedor','6') === '6' ? 'selected' : '' }}>06 - RUC</option>
                      <option value="1" {{ old('tipo_doc_proveedor') === '1' ? 'selected' : '' }}>01 - DNI</option>
                      <option value="4" {{ old('tipo_doc_proveedor') === '4' ? 'selected' : '' }}>04 - Carnet de Extranjería</option>
                      <option value="7" {{ old('tipo_doc_proveedor') === '7' ? 'selected' : '' }}>07 - Pasaporte</option>
                      <option value="A" {{ old('tipo_doc_proveedor') === 'A' ? 'selected' : '' }}>A - Cédula Diplomática</option>
                    </select>
                    @error('tipo_doc_proveedor')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group {{ $errors->has('numero_doc_proveedor') ? 'is-error' : '' }}" style="position:relative;">
                    <label for="numero_doc_proveedor">Número Documento <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="numero_doc_proveedor" id="numero_doc_proveedor" value="{{ old('numero_doc_proveedor') }}" required maxlength="20" placeholder="20123456789" autocomplete="off">
                    <div id="proveedor-suggestions" style="display:none;"></div>
                    @error('numero_doc_proveedor')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group {{ $errors->has('razon_social_proveedor') ? 'is-error' : '' }}">
                    <label for="razon_social_proveedor">Razón Social <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="razon_social_proveedor" id="razon_social_proveedor" value="{{ old('razon_social_proveedor') }}" required maxlength="200" placeholder="EMPRESA SAC">
                    @error('razon_social_proveedor')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 3: Montos --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-dollar'></i> Importes</p>

                {{-- Fila 1: Moneda + % IGV --}}
                <div class="form-grid-2" style="margin-bottom:1rem;">
                  <div class="form-group {{ $errors->has('codigo_moneda') ? 'is-error' : '' }}">
                    <label for="codigo_moneda">Moneda <span style="color:#ef4444;">*</span></label>
                    <select name="codigo_moneda" id="codigo_moneda" required onchange="toggleTipoCambio(this.value)">
                      <option value="PEN" {{ old('codigo_moneda','PEN') === 'PEN' ? 'selected' : '' }}>PEN - Soles</option>
                      <option value="USD" {{ old('codigo_moneda') === 'USD' ? 'selected' : '' }}>USD - Dólares</option>
                      <option value="EUR" {{ old('codigo_moneda') === 'EUR' ? 'selected' : '' }}>EUR - Euros</option>
                    </select>
                    @error('codigo_moneda')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group {{ $errors->has('porcentaje_igv') ? 'is-error' : '' }}">
                    <label for="porcentaje_igv">% IGV <span style="color:#ef4444;">*</span></label>
                    <select name="porcentaje_igv" id="porcentaje_igv" required onchange="calcularDesdeBase()">
                      <option value="18" {{ old('porcentaje_igv','18') == '18' ? 'selected' : '' }}>18%</option>
                      <option value="10" {{ old('porcentaje_igv') == '10' ? 'selected' : '' }}>10%</option>
                      <option value="8"  {{ old('porcentaje_igv') == '8'  ? 'selected' : '' }}>8%</option>
                      <option value="0"  {{ old('porcentaje_igv') == '0'  ? 'selected' : '' }}>0% (Exonerado / Inafecto)</option>
                    </select>
                    @error('porcentaje_igv')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                </div>

                {{-- Fila 2: Base + IGV (auto) + Total (campo principal) --}}
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.75rem; margin-bottom:.85rem;">
                  <div class="form-group {{ $errors->has('base_imponible_gravadas') ? 'is-error' : '' }}">
                    <label for="base_imponible_gravadas" style="display:flex; align-items:center; gap:.35rem;">
                      Base Imponible <span style="color:#ef4444;">*</span>
                      <span style="font-size:.7rem; font-weight:400; color:#9ca3af;">(sin IGV)</span>
                    </label>
                    <input type="number" name="base_imponible_gravadas" id="base_imponible_gravadas"
                           value="{{ old('base_imponible_gravadas','0.00') }}"
                           step="0.01" min="0" required oninput="calcularDesdeBase()">
                    @error('base_imponible_gravadas')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>

                  <div class="form-group {{ $errors->has('igv_gravadas') ? 'is-error' : '' }}">
                    <label for="igv_gravadas" style="display:flex; align-items:center; gap:.35rem;">
                      IGV <span style="color:#ef4444;">*</span>
                      <span style="font-size:.7rem; font-weight:400; color:#059669; background:rgba(5,150,105,.08); padding:.1rem .4rem; border-radius:4px;">auto</span>
                    </label>
                    <input type="number" name="igv_gravadas" id="igv_gravadas"
                           value="{{ old('igv_gravadas','0.00') }}"
                           step="0.01" min="0" required
                           style="background:rgba(5,150,105,.04); color:var(--clr-text-main);"
                           oninput="recalcTotal()">
                    @error('igv_gravadas')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>

                  <div class="form-group {{ $errors->has('monto_total') ? 'is-error' : '' }}">
                    <label for="monto_total" style="display:flex; align-items:center; gap:.35rem;">
                      TOTAL <span style="color:#ef4444;">*</span>
                      <span style="font-size:.7rem; font-weight:400; color:#9ca3af;">(con IGV)</span>
                    </label>
                    <input type="number" name="monto_total" id="monto_total"
                           value="{{ old('monto_total','0.00') }}"
                           step="0.01" min="0" required
                           style="font-weight:700; font-size:1.05rem; border-color:rgba(26,107,87,.4);"
                           oninput="calcularDesdeTotal()">
                    @error('monto_total')<p class="error-msg">{{ $message }}</p>@enderror
                  </div>
                </div>

                {{-- Ayuda rápida --}}
                <p style="font-size:.75rem; color:var(--clr-text-muted,#9ca3af); margin:0 0 .85rem; display:flex; align-items:center; gap:.35rem;">
                  <i class='bx bx-info-circle'></i>
                  Escribe la <strong>Base Imponible</strong> o el <strong>Total</strong>: el otro campo se calcula solo.
                </p>

                {{-- Toggle campos adicionales --}}
                <button type="button" id="btn-avanzado" onclick="toggleAvanzado()"
                        style="display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .85rem; background:transparent; border:1px dashed var(--clr-border-light,#d1d5db); border-radius:8px; cursor:pointer; font-size:.8rem; color:var(--clr-text-muted,#6b7280); margin-bottom:.75rem; transition:all .2s;">
                  <i class='bx bx-plus-circle' id="btn-avanzado-icon"></i>
                  <span id="btn-avanzado-txt">Campos adicionales (No gravado, Exonerado, Descuento…)</span>
                </button>

                {{-- Campos avanzados --}}
                <div id="campos-avanzados" style="{{ ($hasCamposAvanzados ?? false) ? '' : 'display:none;' }}">
                  <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:.75rem; margin-bottom:.75rem;">
                    <div class="form-group {{ $errors->has('monto_no_gravado') ? 'is-error' : '' }}">
                      <label for="monto_no_gravado">No Gravado / Inafecto</label>
                      <input type="number" name="monto_no_gravado" id="monto_no_gravado"
                             value="{{ old('monto_no_gravado','0.00') }}" step="0.01" min="0" oninput="recalcTotal()">
                      @error('monto_no_gravado')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group {{ $errors->has('monto_exonerado') ? 'is-error' : '' }}">
                      <label for="monto_exonerado">Exonerado</label>
                      <input type="number" name="monto_exonerado" id="monto_exonerado"
                             value="{{ old('monto_exonerado','0.00') }}" step="0.01" min="0" oninput="recalcTotal()">
                      @error('monto_exonerado')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group {{ $errors->has('otros_tributos') ? 'is-error' : '' }}">
                      <label for="otros_tributos">Otros Tributos</label>
                      <input type="number" name="otros_tributos" id="otros_tributos"
                             value="{{ old('otros_tributos','0.00') }}" step="0.01" min="0" oninput="recalcTotal()">
                      @error('otros_tributos')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group {{ $errors->has('monto_descuento') ? 'is-error' : '' }}">
                      <label for="monto_descuento">Descuentos</label>
                      <input type="number" name="monto_descuento" id="monto_descuento"
                             value="{{ old('monto_descuento','0.00') }}" step="0.01" min="0" oninput="recalcTotal()">
                      @error('monto_descuento')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group" id="tipo-cambio-group" style="{{ old('codigo_moneda','PEN') !== 'PEN' ? '' : 'display:none;' }}">
                      <label for="monto_tipo_cambio">Tipo de Cambio</label>
                      <input type="number" name="monto_tipo_cambio" id="monto_tipo_cambio"
                             value="{{ old('monto_tipo_cambio') }}" step="0.001" min="0" placeholder="3.750">
                      @error('monto_tipo_cambio')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 4: Forma de Pago --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-credit-card'></i> Forma de Pago</p>
                <div class="form-grid-2">
                  <div class="form-group">
                    <label for="forma_pago">Forma de Pago</label>
                    <select name="forma_pago" id="forma_pago">
                      <option value="">— Sin especificar —</option>
                      <optgroup label="Modalidad SUNAT">
                        <option value="01" {{ old('forma_pago') === '01' ? 'selected' : '' }}>Contado</option>
                        <option value="02" {{ old('forma_pago') === '02' ? 'selected' : '' }}>Crédito</option>
                      </optgroup>
                      <optgroup label="Medio de Pago">
                        <option value="03" {{ old('forma_pago') === '03' ? 'selected' : '' }}>Efectivo</option>
                        <option value="04" {{ old('forma_pago') === '04' ? 'selected' : '' }}>Yape</option>
                        <option value="05" {{ old('forma_pago') === '05' ? 'selected' : '' }}>Plin</option>
                        <option value="06" {{ old('forma_pago') === '06' ? 'selected' : '' }}>Banco / Transferencia</option>
                        <option value="07" {{ old('forma_pago') === '07' ? 'selected' : '' }}>BCP</option>
                        <option value="08" {{ old('forma_pago') === '08' ? 'selected' : '' }}>BBVA</option>
                      </optgroup>
                      <optgroup label="Documentos Comerciales">
                        <option value="09" {{ old('forma_pago') === '09' ? 'selected' : '' }}>Letra de Cambio</option>
                      </optgroup>
                    </select>
                    <small id="letra-hint" style="display:none;color:#d97706;font-size:.78rem;margin-top:.3rem;">
                      <i class='bx bx-info-circle'></i> Después de registrar la compra, usa el botón <strong>"Canjear a Letras"</strong> para configurar los vencimientos.
                    </small>
                  </div>
                  <div class="form-group">
                    <label for="observacion">Observación</label>
                    <textarea name="observacion" id="observacion" rows="2" maxlength="500" placeholder="Notas internas...">{{ old('observacion') }}</textarea>
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 5: Productos / ítems (opcional) --}}
              <div class="form-section">
                <p class="form-section__title" style="margin-bottom:.6rem;">
                  <i class='bx bx-list-ul'></i> Productos / Ítems
                  <span style="font-size:.72rem; font-weight:400; color:#9ca3af; text-transform:none; letter-spacing:0; margin-left:.4rem;">(opcional)</span>
                </p>
                <p style="font-size:.78rem; color:#9ca3af; margin:0 0 .85rem;">
                  <i class='bx bx-info-circle'></i> Agrega las líneas de detalle del comprobante si las necesitas consultar en el futuro.
                </p>

                <div style="overflow-x:auto;">
                  <table class="items-tbl" id="itemsCreateTable">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Valor Unit.</th>
                        <th>Descuento</th>
                        <th>Importe Venta</th>
                        <th>ICBPER</th>
                        <th class="td-del"></th>
                      </tr>
                    </thead>
                    <tbody id="itemsCreateTbody"></tbody>
                  </table>
                </div>

                <input type="hidden" name="items_json" id="items_json_create" value="[]">

                <button type="button" onclick="addItemRow()"
                        style="margin-top:.75rem; display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .9rem; background:transparent; border:1px dashed #6b7280; border-radius:8px; cursor:pointer; font-size:.82rem; color:#6b7280;">
                  <i class='bx bx-plus'></i> Agregar línea
                </button>
              </div>

              {{-- Botones --}}
              <div style="display:flex; gap:.75rem; justify-content:flex-end; padding-top:.5rem;">
                <a href="{{ route('facturador.compras.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Registrar Compra
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
/* ── Tipo documento ── */
function onTipoDocChange(val) {
  document.getElementById('anio-dua-group').style.display = val === '00' ? '' : 'none';
}

/* ── Moneda: muestra tipo de cambio en campos avanzados ── */
function toggleTipoCambio(val) {
  var el = document.getElementById('tipo-cambio-group');
  if (el) el.style.display = val !== 'PEN' ? '' : 'none';
}

/* ── Toggle campos avanzados ── */
var _avanzadoOpen = false;
function toggleAvanzado() {
  _avanzadoOpen = !_avanzadoOpen;
  document.getElementById('campos-avanzados').style.display = _avanzadoOpen ? '' : 'none';
  document.getElementById('btn-avanzado-icon').className = _avanzadoOpen ? 'bx bx-minus-circle' : 'bx bx-plus-circle';
  document.getElementById('btn-avanzado-txt').textContent = _avanzadoOpen
    ? 'Ocultar campos adicionales'
    : 'Campos adicionales (No gravado, Exonerado, Descuento…)';
}

/* ── Helpers ── */
function pct()     { return parseInt(document.getElementById('porcentaje_igv').value) || 0; }
function getNum(id){ return parseFloat(document.getElementById(id).value) || 0; }
function setNum(id, val){ document.getElementById(id).value = val.toFixed(2); }

function extras() {
  return getNum('monto_no_gravado')
       + getNum('monto_exonerado')
       + getNum('otros_tributos')
       - getNum('monto_descuento');
}

/* ── Escribe en Base → calcula IGV y Total ── */
function calcularDesdeBase() {
  var base = getNum('base_imponible_gravadas');
  var igv  = Math.round(base * pct() / 100 * 100) / 100;
  setNum('igv_gravadas', igv);
  setNum('monto_total', base + igv + extras());
}

/* ── Escribe en Total → calcula Base e IGV (inversa) ── */
function calcularDesdeTotal() {
  var total = getNum('monto_total');
  var factor = 1 + pct() / 100;
  var base = factor > 0 ? Math.round(total / factor * 100) / 100 : total;
  var igv  = Math.round((total - base) * 100) / 100;
  document.getElementById('base_imponible_gravadas').value = base.toFixed(2);
  document.getElementById('igv_gravadas').value = igv.toFixed(2);
}

/* ── Recalcula solo el Total (cuando cambian los campos avanzados o IGV manual) ── */
function recalcTotal() {
  var base = getNum('base_imponible_gravadas');
  var igv  = getNum('igv_gravadas');
  setNum('monto_total', base + igv + extras());
}

/* ── Lookup de proveedor en catálogo ── */
var lookupTimeout;
document.getElementById('numero_doc_proveedor').addEventListener('input', function () {
  clearTimeout(lookupTimeout);
  var doc  = this.value.trim();
  var sugg = document.getElementById('proveedor-suggestions');
  if (doc.length < 8) { sugg.style.display = 'none'; return; }
  lookupTimeout = setTimeout(function () {
    fetch('{{ route('facturador.compras.lookup-provider') }}?doc=' + encodeURIComponent(doc), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.found) {
        sugg.innerHTML = '<div class="suggestion-item" data-razon="' + data.razon_social + '" data-tipo="' + data.tipo_documento + '">'
          + '<strong>' + doc + '</strong> — ' + data.razon_social + '</div>';
        sugg.style.display = 'block';
        sugg.querySelectorAll('.suggestion-item').forEach(function(item){
          item.addEventListener('click', function(){
            document.getElementById('razon_social_proveedor').value = this.dataset.razon;
            document.getElementById('tipo_doc_proveedor').value     = this.dataset.tipo;
            sugg.style.display = 'none';
          });
        });
      } else {
        sugg.style.display = 'none';
      }
    })
    .catch(function(){ sugg.style.display = 'none'; });
  }, 500);
});

document.addEventListener('click', function(e){
  if (!e.target.closest('#numero_doc_proveedor') && !e.target.closest('#proveedor-suggestions')) {
    document.getElementById('proveedor-suggestions').style.display = 'none';
  }
});

/* ── Forma de pago: mostrar hint si es Letra de Cambio ── */
document.getElementById('forma_pago').addEventListener('change', function () {
  document.getElementById('letra-hint').style.display = this.value === '09' ? '' : 'none';
});

/* ── Inicializar ── */
onTipoDocChange(document.getElementById('codigo_tipo_documento').value);
toggleTipoCambio(document.getElementById('codigo_moneda').value);
if (document.getElementById('forma_pago').value === '09') {
  document.getElementById('letra-hint').style.display = '';
}

// Si hay errores de validación y había campos avanzados con valores, abrir sección
(function(){
  var avIds = ['monto_no_gravado','monto_exonerado','otros_tributos','monto_descuento'];
  var hasVal = avIds.some(function(id){
    var el = document.getElementById(id);
    return el && parseFloat(el.value) !== 0;
  });
  if (hasVal) toggleAvanzado();
})();

/* ── Tabla de ítems ── */
var _itemCount = 0;

function addItemRow(data) {
  data = data || {};
  _itemCount++;
  var i = _itemCount;
  var tr = document.createElement('tr');
  tr.dataset.row = i;
  tr.innerHTML =
    '<td style="color:#9ca3af;font-size:.78rem;">' + i + '</td>' +
    '<td><input type="text"   data-field="descripcion"    placeholder="Descripción del producto/servicio" value="' + escHtml(data.descripcion || '') + '"></td>' +
    '<td><input type="text"   data-field="unidad_medida"  placeholder="UND" style="width:70px" value="' + escHtml(data.unidad_medida || '') + '"></td>' +
    '<td><input type="number" data-field="cantidad"       step="0.0001" min="0" style="width:82px" value="' + (data.cantidad || 1) + '" oninput="calcImporte(this)"></td>' +
    '<td><input type="number" data-field="valor_unitario" step="0.000001" min="0" style="width:95px" value="' + (data.valor_unitario || 0) + '" oninput="calcImporte(this)"></td>' +
    '<td><input type="number" data-field="descuento"      step="0.01" min="0" style="width:80px" value="' + (data.descuento || 0) + '" oninput="calcImporte(this)"></td>' +
    '<td><input type="number" data-field="importe_venta"  step="0.0001" min="0" style="width:95px" value="' + (data.importe_venta || 0) + '"></td>' +
    '<td><input type="number" data-field="icbper"         step="0.01" min="0" style="width:72px" value="' + (data.icbper || 0) + '"></td>' +
    '<td class="td-del"><button type="button" onclick="removeItemRow(this)" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:1.1rem;padding:0 .25rem;" title="Eliminar"><i class=\'bx bx-trash\'></i></button></td>';

  tr.querySelectorAll('input').forEach(function(inp){
    inp.addEventListener('input', syncItemsJsonCreate);
  });

  document.getElementById('itemsCreateTbody').appendChild(tr);
  syncItemsJsonCreate();
}

function calcImporte(inp) {
  var tr  = inp.closest('tr');
  var qty = parseFloat(tr.querySelector('[data-field="cantidad"]').value)       || 0;
  var val = parseFloat(tr.querySelector('[data-field="valor_unitario"]').value) || 0;
  var des = parseFloat(tr.querySelector('[data-field="descuento"]').value)      || 0;
  tr.querySelector('[data-field="importe_venta"]').value = Math.max(0, qty * val - des).toFixed(4);
  syncItemsJsonCreate();
}

function removeItemRow(btn) {
  btn.closest('tr').remove();
  // renumerar
  document.querySelectorAll('#itemsCreateTbody tr').forEach(function(tr, idx){
    tr.querySelector('td:first-child').textContent = idx + 1;
    tr.dataset.row = idx + 1;
  });
  syncItemsJsonCreate();
}

function syncItemsJsonCreate() {
  var rows   = document.querySelectorAll('#itemsCreateTbody tr');
  var result = [];
  rows.forEach(function(tr){
    var get = function(f){ return tr.querySelector('[data-field="' + f + '"]')?.value ?? ''; };
    if (!get('descripcion').trim()) return;
    result.push({
      descripcion:    get('descripcion').trim(),
      unidad_medida:  get('unidad_medida').trim(),
      cantidad:       parseFloat(get('cantidad'))       || 0,
      valor_unitario: parseFloat(get('valor_unitario')) || 0,
      descuento:      parseFloat(get('descuento'))      || 0,
      importe_venta:  parseFloat(get('importe_venta'))  || 0,
      icbper:         parseFloat(get('icbper'))          || 0,
    });
  });
  document.getElementById('items_json_create').value = JSON.stringify(result);
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
