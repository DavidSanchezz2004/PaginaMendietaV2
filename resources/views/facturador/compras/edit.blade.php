@extends('layouts.app')

@section('title', 'Editar Compra — Facturador | Portal Mendieta')

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
                <i class='bx bx-pencil' style="color:var(--clr-text-main);"></i> Editar Compra #{{ $purchase->id }}
              </h1>
              <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary">
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

            <form method="POST" action="{{ route('facturador.compras.update', $purchase) }}">
              @csrf @method('PUT')

              {{-- SECCIÓN 1: Comprobante --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-file'></i> Datos del Comprobante</p>
                <div class="form-grid-3">
                  <div class="form-group">
                    <label for="codigo_tipo_documento">Tipo de Documento *</label>
                    <select name="codigo_tipo_documento" id="codigo_tipo_documento" required onchange="onTipoDocChange(this.value)">
                      <option value="01" {{ old('codigo_tipo_documento', $purchase->codigo_tipo_documento) === '01' ? 'selected' : '' }}>01 - Factura</option>
                      <option value="03" {{ old('codigo_tipo_documento', $purchase->codigo_tipo_documento) === '03' ? 'selected' : '' }}>03 - Boleta</option>
                      <option value="07" {{ old('codigo_tipo_documento', $purchase->codigo_tipo_documento) === '07' ? 'selected' : '' }}>07 - Nota de Crédito</option>
                      <option value="08" {{ old('codigo_tipo_documento', $purchase->codigo_tipo_documento) === '08' ? 'selected' : '' }}>08 - Nota de Débito</option>
                      <option value="00" {{ old('codigo_tipo_documento', $purchase->codigo_tipo_documento) === '00' ? 'selected' : '' }}>00 - DUA</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="serie_documento">Serie</label>
                    <input type="text" name="serie_documento" id="serie_documento" value="{{ old('serie_documento', $purchase->serie_documento) }}" maxlength="10">
                  </div>
                  <div class="form-group">
                    <label for="numero_documento">Número *</label>
                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $purchase->numero_documento) }}" required maxlength="20">
                  </div>
                  <div class="form-group">
                    <label for="fecha_emision">Fecha Emisión *</label>
                    <input type="date" name="fecha_emision" id="fecha_emision" value="{{ old('fecha_emision', $purchase->fecha_emision?->format('Y-m-d')) }}" required>
                  </div>
                  <div class="form-group">
                    <label for="fecha_vencimiento">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="{{ old('fecha_vencimiento', $purchase->fecha_vencimiento?->format('Y-m-d')) }}">
                  </div>
                  <div class="form-group" id="anio-dua-group" style="{{ $purchase->codigo_tipo_documento === '00' ? '' : 'display:none;' }}">
                    <label for="anio_emision_dua">Año Emisión DUA</label>
                    <input type="text" name="anio_emision_dua" id="anio_emision_dua" value="{{ old('anio_emision_dua', $purchase->anio_emision_dua) }}" maxlength="4">
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 2: Proveedor --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-user-pin'></i> Datos del Proveedor</p>
                <div class="form-grid-3">
                  <div class="form-group">
                    <label for="tipo_doc_proveedor">Tipo Documento *</label>
                    <select name="tipo_doc_proveedor" id="tipo_doc_proveedor" required>
                      <option value="6" {{ old('tipo_doc_proveedor', $purchase->tipo_doc_proveedor) === '6' ? 'selected' : '' }}>06 - RUC</option>
                      <option value="1" {{ old('tipo_doc_proveedor', $purchase->tipo_doc_proveedor) === '1' ? 'selected' : '' }}>01 - DNI</option>
                      <option value="4" {{ old('tipo_doc_proveedor', $purchase->tipo_doc_proveedor) === '4' ? 'selected' : '' }}>04 - Carnet de Extranjería</option>
                      <option value="7" {{ old('tipo_doc_proveedor', $purchase->tipo_doc_proveedor) === '7' ? 'selected' : '' }}>07 - Pasaporte</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="numero_doc_proveedor">Número Documento *</label>
                    <input type="text" name="numero_doc_proveedor" id="numero_doc_proveedor" value="{{ old('numero_doc_proveedor', $purchase->numero_doc_proveedor) }}" required maxlength="20">
                  </div>
                  <div class="form-group">
                    <label for="razon_social_proveedor">Razón Social *</label>
                    <input type="text" name="razon_social_proveedor" id="razon_social_proveedor" value="{{ old('razon_social_proveedor', $purchase->razon_social_proveedor) }}" required maxlength="200">
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 3: Montos --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-dollar'></i> Importes</p>
                <div class="form-grid-3" style="margin-bottom:1rem;">
                  <div class="form-group">
                    <label for="codigo_moneda">Moneda *</label>
                    <select name="codigo_moneda" id="codigo_moneda" required onchange="toggleTipoCambio(this.value)">
                      <option value="PEN" {{ old('codigo_moneda', $purchase->codigo_moneda) === 'PEN' ? 'selected' : '' }}>PEN - Soles</option>
                      <option value="USD" {{ old('codigo_moneda', $purchase->codigo_moneda) === 'USD' ? 'selected' : '' }}>USD - Dólares</option>
                      <option value="EUR" {{ old('codigo_moneda', $purchase->codigo_moneda) === 'EUR' ? 'selected' : '' }}>EUR - Euros</option>
                    </select>
                  </div>
                  <div class="form-group" id="tipo-cambio-group" style="{{ old('codigo_moneda', $purchase->codigo_moneda) !== 'PEN' ? '' : 'display:none;' }}">
                    <label for="monto_tipo_cambio">Tipo de Cambio</label>
                    <input type="number" name="monto_tipo_cambio" id="monto_tipo_cambio" value="{{ old('monto_tipo_cambio', $purchase->monto_tipo_cambio) }}" step="0.001" min="0">
                  </div>
                  <div class="form-group">
                    <label for="porcentaje_igv">% IGV *</label>
                    <select name="porcentaje_igv" id="porcentaje_igv" required>
                      <option value="18" {{ old('porcentaje_igv', $purchase->porcentaje_igv) == '18' ? 'selected' : '' }}>18%</option>
                      <option value="10" {{ old('porcentaje_igv', $purchase->porcentaje_igv) == '10' ? 'selected' : '' }}>10%</option>
                      <option value="8"  {{ old('porcentaje_igv', $purchase->porcentaje_igv) == '8'  ? 'selected' : '' }}>8%</option>
                      <option value="0"  {{ old('porcentaje_igv', $purchase->porcentaje_igv) == '0'  ? 'selected' : '' }}>0% (Exonerado)</option>
                    </select>
                  </div>
                </div>
                <div class="monto-row">
                  <div class="form-group">
                    <label for="base_imponible_gravadas">Base Imponible *</label>
                    <input type="number" name="base_imponible_gravadas" id="base_imponible_gravadas" value="{{ old('base_imponible_gravadas', $purchase->base_imponible_gravadas) }}" step="0.01" min="0" required>
                  </div>
                  <div class="form-group">
                    <label for="igv_gravadas">IGV *</label>
                    <input type="number" name="igv_gravadas" id="igv_gravadas" value="{{ old('igv_gravadas', $purchase->igv_gravadas) }}" step="0.01" min="0" required>
                  </div>
                  <div class="form-group">
                    <label for="monto_no_gravado">No Gravado</label>
                    <input type="number" name="monto_no_gravado" id="monto_no_gravado" value="{{ old('monto_no_gravado', $purchase->monto_no_gravado) }}" step="0.01" min="0">
                  </div>
                  <div class="form-group">
                    <label for="monto_exonerado">Exonerado</label>
                    <input type="number" name="monto_exonerado" id="monto_exonerado" value="{{ old('monto_exonerado', $purchase->monto_exonerado) }}" step="0.01" min="0">
                  </div>
                  <div class="form-group">
                    <label for="otros_tributos">Otros Tributos</label>
                    <input type="number" name="otros_tributos" id="otros_tributos" value="{{ old('otros_tributos', $purchase->otros_tributos) }}" step="0.01" min="0">
                  </div>
                  <div class="form-group">
                    <label for="monto_descuento">Descuentos</label>
                    <input type="number" name="monto_descuento" id="monto_descuento" value="{{ old('monto_descuento', $purchase->monto_descuento) }}" step="0.01" min="0">
                  </div>
                  <div class="form-group" style="grid-column:span 2;">
                    <label for="monto_total">TOTAL *</label>
                    <input type="number" name="monto_total" id="monto_total" value="{{ old('monto_total', $purchase->monto_total) }}" step="0.01" min="0" required style="font-weight:700;">
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 4: Forma de Pago --}}
              <div class="form-section">
                <p class="form-section__title"><i class='bx bx-credit-card'></i> Forma de Pago</p>
                <div class="form-grid-2">
                  <div class="form-group">
                    <label for="forma_pago">Forma de Pago</label>
                    @php $fpCurrent = old('forma_pago', str_pad($purchase->forma_pago ?? '', 2, '0', STR_PAD_LEFT)); @endphp
                    <select name="forma_pago" id="forma_pago">
                      <option value="">— Sin especificar —</option>
                      <optgroup label="Modalidad SUNAT">
                        <option value="01" {{ $fpCurrent === '01' ? 'selected' : '' }}>Contado</option>
                        <option value="02" {{ $fpCurrent === '02' ? 'selected' : '' }}>Crédito</option>
                      </optgroup>
                      <optgroup label="Medio de Pago">
                        <option value="03" {{ $fpCurrent === '03' ? 'selected' : '' }}>Efectivo</option>
                        <option value="04" {{ $fpCurrent === '04' ? 'selected' : '' }}>Yape</option>
                        <option value="05" {{ $fpCurrent === '05' ? 'selected' : '' }}>Plin</option>
                        <option value="06" {{ $fpCurrent === '06' ? 'selected' : '' }}>Banco / Transferencia</option>
                        <option value="07" {{ $fpCurrent === '07' ? 'selected' : '' }}>BCP</option>
                        <option value="08" {{ $fpCurrent === '08' ? 'selected' : '' }}>BBVA</option>
                      </optgroup>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="observacion">Observación</label>
                    <textarea name="observacion" id="observacion" rows="2" maxlength="500">{{ old('observacion', $purchase->observacion) }}</textarea>
                  </div>
                </div>
              </div>

              {{-- SECCIÓN 5: SPOT / Detracción --}}
              <div class="form-section">
                <p class="form-section__title" style="color:#b45309;"><i class='bx bx-receipt'></i> SPOT - Detracción (Opcional)</p>
                <div class="form-grid-2">
                  <div class="form-group">
                    <label for="es_sujeto_detraccion">¿Sujeto a Detracción?</label>
                    <select name="es_sujeto_detraccion" id="es_sujeto_detraccion" onchange="toggleDetraccionFields()">
                      <option value="0" {{ old('es_sujeto_detraccion', $purchase->es_sujeto_detraccion ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
                      <option value="1" {{ old('es_sujeto_detraccion', $purchase->es_sujeto_detraccion ? '1' : '0') === '1' ? 'selected' : '' }}>Sí</option>
                    </select>
                  </div>
                  <div class="form-group" id="monto-detrac-group" style="{{ $purchase->es_sujeto_detraccion ? '' : 'display:none;' }}">
                    <label for="monto_detraccion">Monto Detracción</label>
                    <input type="number" name="monto_detraccion" id="monto_detraccion" value="{{ old('monto_detraccion', $purchase->monto_detraccion) }}" step="0.01" min="0" onchange="calcularMontoNetoEdit()">
                  </div>
                </div>
                <div id="detraccion-fields" style="{{ $purchase->es_sujeto_detraccion ? '' : 'display:none;' }}; border-top:1px solid #e5e7eb; padding-top:1rem; margin-top:1rem;">
                  <div class="form-grid-2">
                    <div class="form-group" style="grid-column:1/-1;">
                      <label for="detraccion_leyenda">Leyenda / Descripción SPOT</label>
                      <textarea name="detraccion_leyenda" id="detraccion_leyenda" rows="3" maxlength="500">{{ old('detraccion_leyenda', $purchase->informacion_detraccion['leyenda'] ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                      <label for="detraccion_bien_codigo">Código Bien/Servicio</label>
                      <input type="text" name="detraccion_bien_codigo" id="detraccion_bien_codigo" value="{{ old('detraccion_bien_codigo', $purchase->informacion_detraccion['bien_codigo'] ?? '') }}" maxlength="50">
                    </div>
                    <div class="form-group">
                      <label for="detraccion_bien_descripcion">Bien o Servicio</label>
                      <input type="text" name="detraccion_bien_descripcion" id="detraccion_bien_descripcion" value="{{ old('detraccion_bien_descripcion', $purchase->informacion_detraccion['bien_descripcion'] ?? '') }}" maxlength="200">
                    </div>
                    <div class="form-group">
                      <label for="detraccion_medio_pago">Medio de Pago</label>
                      <input type="text" name="detraccion_medio_pago" id="detraccion_medio_pago" value="{{ old('detraccion_medio_pago', $purchase->informacion_detraccion['medio_pago'] ?? '') }}" maxlength="100">
                    </div>
                    <div class="form-group">
                      <label for="detraccion_numero_cuenta">Nro. Cta. B.N.</label>
                      <input type="text" name="detraccion_numero_cuenta" id="detraccion_numero_cuenta" value="{{ old('detraccion_numero_cuenta', $purchase->informacion_detraccion['numero_cuenta'] ?? '') }}" maxlength="50">
                    </div>
                    <div class="form-group">
                      <label for="detraccion_porcentaje">Porcentaje (%)</label>
                      <input type="number" name="detraccion_porcentaje" id="detraccion_porcentaje" value="{{ old('detraccion_porcentaje', $purchase->informacion_detraccion['porcentaje'] ?? '') }}" step="0.01" min="0" onchange="calcularMontoNetoEdit()">
                    </div>
                  </div>
                  <div class="form-group" style="margin-top:.75rem;">
                    <label style="color:#059669; font-weight:700;">Deuda Neta (Total - Detracción)</label>
                    <input type="number" name="monto_neto_detraccion" id="monto_neto_detraccion" value="{{ old('monto_neto_detraccion', $purchase->monto_neto_detraccion) }}" step="0.01" min="0" readonly style="background:#f0fdf4; color:#059669; font-weight:700;">
                  </div>
                </div>
              </div>

              {{-- Botones --}}
              <div style="display:flex; gap:.75rem; justify-content:flex-end; padding-top:.5rem;">
                <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Guardar Cambios
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
function onTipoDocChange(val) {
  document.getElementById('anio-dua-group').style.display = val === '00' ? '' : 'none';
}
function toggleTipoCambio(val) {
  document.getElementById('tipo-cambio-group').style.display = val !== 'PEN' ? '' : 'none';
}
function toggleDetraccionFields() {
  const esDetrac = document.getElementById('es_sujeto_detraccion').value === '1';
  document.getElementById('monto-detrac-group').style.display = esDetrac ? '' : 'none';
  document.getElementById('detraccion-fields').style.display = esDetrac ? '' : 'none';
  if (esDetrac) {
    calcularMontoNetoEdit();
  }
}
function calcularMontoNetoEdit() {
  const total = parseFloat(document.getElementById('monto_total').value) || 0;
  const detrac = parseFloat(document.getElementById('monto_detraccion').value) || 0;
  const neto = total - detrac;
  document.getElementById('monto_neto_detraccion').value = neto.toFixed(2);
}
</script>
@endpush
