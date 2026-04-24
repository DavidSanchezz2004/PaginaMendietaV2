{{--
  Modal de Completado Contable
  Uso: @include('facturador.invoices.partials.accounting-modal')
  Requiere: rutas JS en window.AccountingRoutes (ver index.blade.php)
--}}

{{-- Overlay + Modal --}}
<div id="accounting-modal-overlay"
     style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,.55); backdrop-filter:blur(3px);"
     aria-modal="true" role="dialog" aria-labelledby="am-title">

  <div id="accounting-modal"
       style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:min(680px,96vw); max-height:92vh; overflow-y:auto; border-radius:16px; background:var(--clr-bg-card,#fff); box-shadow:0 20px 60px rgba(0,0,0,.25); display:flex; flex-direction:column;">

    {{-- Header --}}
    <div style="padding:1.25rem 1.5rem 1rem; border-bottom:1px solid var(--clr-border-light,#e5e7eb); display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
      <div>
        <h2 id="am-title" style="margin:0; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:.5rem;">
          <i class='bx bx-clipboard-check' style="color:var(--clr-active-bg,#1a6b57);"></i>
          Completar información contable
        </h2>
        <p id="am-missing-summary" style="margin:.3rem 0 0; font-size:.82rem; color:#d97706;">
          Cargando...
        </p>
      </div>
      <button type="button" id="am-close" aria-label="Cerrar"
              style="background:none; border:none; cursor:pointer; font-size:1.4rem; color:var(--clr-text-muted,#6b7280); line-height:1; padding:.25rem;">
        <i class='bx bx-x'></i>
      </button>
    </div>

    {{-- Autofill notice --}}
    <div id="am-autofill-notice"
         style="display:none; margin:0.75rem 1.5rem 0; padding:.65rem 1rem; background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.25); border-radius:8px; font-size:.82rem; color:#065f46; align-items:center; gap:.5rem;">
      <i class='bx bx-magic-wand' style="font-size:1rem;"></i>
      <span>Auto-rellenado con valores sugeridos. Revisa y ajusta si es necesario.</span>
    </div>

    <form id="am-form" style="padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:1.25rem;">

      {{-- ── SECCIÓN 1: Datos del comprobante (read-only) ── --}}
      <div style="background:var(--clr-bg-card,#f8fafc); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; padding:1rem;">
        <p style="margin:0 0 .65rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-text-muted,#6b7280);">
          <i class='bx bx-lock-alt' style="vertical-align:middle;"></i> Datos del comprobante
        </p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:.6rem .8rem;">
          <div>
            <span style="font-size:.72rem; color:var(--clr-text-muted,#9ca3af); display:block;">Tipo</span>
            <strong id="am-view-tipo" style="font-size:.88rem;"></strong>
          </div>
          <div>
            <span style="font-size:.72rem; color:var(--clr-text-muted,#9ca3af); display:block;">Serie-Número</span>
            <code id="am-view-serie" style="font-size:.88rem;"></code>
          </div>
          <div>
            <span style="font-size:.72rem; color:var(--clr-text-muted,#9ca3af); display:block;">Cliente</span>
            <strong id="am-view-cliente" style="font-size:.85rem; word-break:break-word;"></strong>
          </div>
          <div>
            <span style="font-size:.72rem; color:var(--clr-text-muted,#9ca3af); display:block;">Total</span>
            <strong id="am-view-total" style="font-size:.95rem; color:var(--clr-active-bg,#1a6b57);"></strong>
          </div>
          <div>
            <span style="font-size:.72rem; color:var(--clr-text-muted,#9ca3af); display:block;">Fecha</span>
            <span id="am-view-fecha" style="font-size:.85rem;"></span>
          </div>
        </div>
      </div>

      {{-- ── SECCIÓN 2: Configuración rápida ── --}}
      <div>
        <p style="margin:0 0 .75rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-active-bg,#1a6b57);">
          <i class='bx bx-zap' style="vertical-align:middle;"></i> Configuración rápida
        </p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">

          <div class="am-field" data-field="tipo_operacion">
            <label class="am-label" for="am-tipo-operacion">Tipo de operación <span class="am-req">*</span></label>
            <select id="am-tipo-operacion" name="tipo_operacion" class="am-input">
              <option value="">— Seleccionar —</option>
              <option value="0101">0101 - Venta interna</option>
              <option value="0102">0102 - Exportación definitiva</option>
              <option value="0103">0103 - No domiciliados</option>
              <option value="0104">0104 - Venta interna - Anticipos</option>
              <option value="0105">0105 - Venta itinerante</option>
              <option value="0106">0106 - Factura guía</option>
              <option value="0109">0109 - Venta - IVAP</option>
              <option value="0112">0112 - Nota de crédito</option>
              <option value="0113">0113 - Nota de débito</option>
              <option value="0116">0116 - Guía de Remisión</option>
              <option value="1001">1001 - Operación sujeta a SPOT</option>
              <option value="2001">2001 - Operación sujeta a Retención</option>
            </select>
          </div>

          <div class="am-field" data-field="tipo_venta">
            <label class="am-label" for="am-tipo-venta">Tipo de venta <span class="am-req">*</span></label>
            <select id="am-tipo-venta" name="tipo_venta" class="am-input">
              <option value="">— Seleccionar —</option>
              <option value="IN">IN - Venta interna</option>
              <option value="EX">EX - Exportación</option>
              <option value="NC">NC - Nota de crédito</option>
              <option value="ND">ND - Nota de débito</option>
              <option value="CA">CA - Consignación</option>
            </select>
          </div>

          <div class="am-field" data-field="cuenta_contable" style="position:relative;">
            <label class="am-label" for="am-cuenta-contable">Cuenta contable <span class="am-req">*</span></label>
            <input id="am-cuenta-contable" name="cuenta_contable" type="text"
                   class="am-input" autocomplete="off"
                   placeholder="Ej: 121, 7011..."
                   list="am-pcge-list">
            <datalist id="am-pcge-list">
              <option value="12">12 - Cuentas por Cobrar Comerciales</option>
              <option value="121">121 - Facturas por Cobrar - Emitidas en cartera</option>
              <option value="1211">1211 - No emitidas</option>
              <option value="1212">1212 - En cobranza</option>
              <option value="122">122 - Anticipos</option>
              <option value="70">70 - Ventas</option>
              <option value="701">701 - Mercaderías</option>
              <option value="7011">7011 - Mercaderías manufacturadas - Nacionales</option>
              <option value="7012">7012 - Mercaderías manufacturadas - Extranjeras</option>
              <option value="702">702 - Productos terminados</option>
              <option value="703">703 - Subproductos, desechos y desperdicios</option>
              <option value="704">704 - Productos en proceso</option>
              <option value="705">705 - Materiales auxiliares, suministros y repuestos</option>
              <option value="706">706 - Envases y embalajes</option>
              <option value="707">707 - Mercaderías agropecuarias y piscícolas</option>
              <option value="709">709 - Devoluciones sobre ventas</option>
              <option value="71">71 - Variación de la producción almacenada</option>
              <option value="72">72 - Producción de activo inmovilizado</option>
              <option value="73">73 - Descuentos, rebajas y bonificaciones obtenidos</option>
              <option value="74">74 - Descuentos, rebajas y bonificaciones concedidos</option>
              <option value="75">75 - Otros ingresos de gestión</option>
              <option value="40">40 - Tributos por Pagar</option>
              <option value="401">401 - Gobierno Central</option>
              <option value="4011">4011 - IGV</option>
              <option value="40111">40111 - IGV - Cuenta propia</option>
              <option value="40112">40112 - IGV - Cuenta de terceros</option>
              <option value="4013">4013 - Impuesto a la Renta</option>
              <option value="4017">4017 - Retenciones y anticipos del IR</option>
              <option value="4031">4031 - ESSALUD</option>
              <option value="4032">4032 - SNP</option>
              <option value="10">10 - Efectivo y Equivalentes de Efectivo</option>
              <option value="104">104 - Cuentas corrientes en instituciones financieras</option>
              <option value="1041">1041 - CCI nacionales</option>
              <option value="1042">1042 - CCI del exterior</option>
              <option value="14">14 - Cuentas por Cobrar al Personal</option>
              <option value="16">16 - Otras Cuentas por Cobrar</option>
              <option value="20">20 - Mercaderías</option>
              <option value="60">60 - Compras</option>
              <option value="63">63 - Gastos de Servicios Prestados por Terceros</option>
              <option value="64">64 - Gastos por Tributos</option>
              <option value="65">65 - Otros Gastos de Gestión</option>
              <option value="67">67 - Gastos Financieros</option>
            </datalist>
          </div>

          <div class="am-field" data-field="forma_pago">
            <label class="am-label" for="am-forma-pago">Forma de pago <span class="am-req">*</span></label>
            <select id="am-forma-pago" name="forma_pago" class="am-input">
              <option value="">— Seleccionar —</option>
              <option value="1">01 - Contado</option>
              <option value="2">02 - Crédito</option>
            </select>
          </div>

        </div>

        {{-- Detracción toggle --}}
        <div style="margin-top:.75rem; display:flex; align-items:center; gap:.75rem; padding:.65rem .85rem; background:rgba(245,158,11,.06); border:1px solid rgba(245,158,11,.2); border-radius:8px;">
          <label class="am-label" for="am-detraccion" style="margin:0; font-size:.85rem; cursor:pointer; display:flex; align-items:center; gap:.5rem; flex:1;">
            <i class='bx bx-transfer' style="color:#d97706;"></i>
            ¿Sujeto a detracción?
          </label>
          <label style="position:relative; display:inline-block; width:44px; height:24px; cursor:pointer;">
            <input type="checkbox" id="am-detraccion" name="indicador_detraccion" style="opacity:0; width:0; height:0; position:absolute;">
            <span class="am-toggle-slider"></span>
          </label>
        </div>
      </div>

      {{-- ── SECCIÓN 3: Campos adicionales (colapsable) ── --}}
      <div>
        <button type="button" id="am-toggle-advanced"
                style="background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:.5rem; padding:.4rem 0; font-size:.82rem; font-weight:600; color:var(--clr-text-muted,#6b7280); width:100%;">
          <i class='bx bx-chevron-right' id="am-advanced-chevron" style="transition:transform .2s; font-size:1rem;"></i>
          Campos adicionales (avanzado)
        </button>
        <div id="am-advanced-section" style="display:none; padding-top:.75rem;">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">

            <div class="am-field" data-field="codigo_producto_servicio">
              <label class="am-label" for="am-codigo-producto">Código producto/servicio <span class="am-req">*</span></label>
              <input id="am-codigo-producto" name="codigo_producto_servicio" type="text"
                     class="am-input" maxlength="50" placeholder="Ej: P001, SRV-CONT...">
            </div>

            <div class="am-field" data-field="glosa">
              <label class="am-label" for="am-glosa">Glosa</label>
              <input id="am-glosa" name="glosa" type="text" class="am-input" maxlength="500"
                     placeholder="Descripción contable...">
            </div>

            <div class="am-field" data-field="centro_costo">
              <label class="am-label" for="am-centro-costo">Centro de costo</label>
              <input id="am-centro-costo" name="centro_costo" type="text" class="am-input" maxlength="50">
            </div>

            <div class="am-field" data-field="tipo_gasto">
              <label class="am-label" for="am-tipo-gasto">Tipo de gasto</label>
              <select id="am-tipo-gasto" name="tipo_gasto" class="am-input">
                <option value="">— Opcional —</option>
                <option value="GG">GG - Gasto General</option>
                <option value="GV">GV - Gasto de Ventas</option>
                <option value="GA">GA - Gasto Administrativo</option>
                <option value="GF">GF - Gasto Financiero</option>
                <option value="CP">CP - Costo de Producción</option>
              </select>
            </div>

            <div class="am-field" data-field="sucursal">
              <label class="am-label" for="am-sucursal">Sucursal</label>
              <input id="am-sucursal" name="sucursal" type="text" class="am-input" maxlength="50">
            </div>

            <div class="am-field" data-field="vendedor">
              <label class="am-label" for="am-vendedor">Vendedor</label>
              <input id="am-vendedor" name="vendedor" type="text" class="am-input" maxlength="100">
            </div>

          </div>
          {{-- Flags booleanos --}}
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:.5rem; margin-top:.75rem;">
            <label style="display:flex; align-items:center; gap:.55rem; font-size:.83rem; cursor:pointer; padding:.5rem .75rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px;">
              <input type="checkbox" id="am-anticipo" name="es_anticipo" style="width:15px; height:15px; cursor:pointer;">
              Es pago de anticipo
            </label>
            <label style="display:flex; align-items:center; gap:.55rem; font-size:.83rem; cursor:pointer; padding:.5rem .75rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px;">
              <input type="checkbox" id="am-contingencia" name="es_documento_contingencia" style="width:15px; height:15px; cursor:pointer;">
              Doc. de contingencia
            </label>
            <label style="display:flex; align-items:center; gap:.55rem; font-size:.83rem; cursor:pointer; padding:.5rem .75rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px;">
              <input type="checkbox" id="am-retencion" name="es_sujeto_retencion" style="width:15px; height:15px; cursor:pointer;">
              Sujeto a retención
            </label>
            <label style="display:flex; align-items:center; gap:.55rem; font-size:.83rem; cursor:pointer; padding:.5rem .75rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px;">
              <input type="checkbox" id="am-percepcion" name="es_sujeto_percepcion" style="width:15px; height:15px; cursor:pointer;">
              Sujeto a percepción
            </label>
          </div>
        </div>
      </div>

      {{-- ── SECCIÓN 4: Cuotas (visible solo si crédito) ── --}}
      <div id="am-cuotas-section" style="display:none; border:1px solid rgba(59,130,246,.25); border-radius:10px; padding:1rem; background:rgba(59,130,246,.04);">
        <p style="margin:0 0 .75rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#3b82f6;">
          <i class='bx bx-credit-card' style="vertical-align:middle;"></i> Detalle de cuotas (crédito)
        </p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
          <div>
            <label class="am-label" for="am-cuota1-fecha">Fecha cuota 1</label>
            <input id="am-cuota1-fecha" name="cuota_1_fecha" type="date" class="am-input">
          </div>
          <div>
            <label class="am-label" for="am-cuota1-monto">Monto cuota 1</label>
            <input id="am-cuota1-monto" name="cuota_1_monto" type="number" step="0.01" min="0" class="am-input" placeholder="0.00">
          </div>
          <div>
            <label class="am-label" for="am-cuota2-fecha">Fecha cuota 2</label>
            <input id="am-cuota2-fecha" name="cuota_2_fecha" type="date" class="am-input">
          </div>
          <div>
            <label class="am-label" for="am-cuota2-monto">Monto cuota 2</label>
            <input id="am-cuota2-monto" name="cuota_2_monto" type="number" step="0.01" min="0" class="am-input" placeholder="0.00">
          </div>
        </div>
      </div>

      {{-- Error de validación --}}
      <div id="am-error-box" style="display:none; padding:.65rem 1rem; background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.3); border-radius:8px; font-size:.83rem; color:#dc2626;">
        <i class='bx bx-error-circle' style="vertical-align:middle;"></i>
        <span id="am-error-text"></span>
      </div>

      {{-- Footer --}}
      <div style="display:flex; justify-content:flex-end; gap:.75rem; padding-top:.25rem; border-top:1px solid var(--clr-border-light,#e5e7eb);">
        <button type="button" id="am-cancel"
                style="padding:.6rem 1.4rem; border:1px solid var(--clr-border-light,#e5e7eb); background:transparent; border-radius:8px; cursor:pointer; font-size:.88rem; color:var(--clr-text-main,#374151); font-weight:500;">
          Cancelar
        </button>
        <button type="submit" id="am-submit"
                style="padding:.6rem 1.6rem; background:var(--clr-active-bg,#1a6b57); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.88rem; font-weight:600; display:flex; align-items:center; gap:.4rem;">
          <i class='bx bx-save'></i>
          <span id="am-submit-text">Guardar</span>
        </button>
      </div>

    </form>
  </div>
</div>

<style>
  .am-label  { display:block; font-size:.78rem; font-weight:600; color:var(--clr-text-muted,#6b7280); margin-bottom:.3rem; }
  .am-req    { color:#ef4444; }
  .am-input  { width:100%; padding:.5rem .75rem; border:1px solid var(--clr-border-light,#d1d5db); border-radius:8px; font-size:.88rem; font-family:inherit; background:transparent; color:var(--clr-text-main,#111827); outline:none; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; }
  .am-input:focus { border-color:var(--clr-active-bg,#1a6b57); box-shadow:0 0 0 3px rgba(26,107,87,.1); }
  .am-field.has-error .am-input { border-color:#ef4444; }
  .am-field.has-error .am-label { color:#ef4444; }
  body.dark-mode .am-input { border-color:rgba(255,255,255,.15); color:var(--clr-text-main); }
  body.dark-mode .am-input:focus { border-color:var(--clr-text-accent,#a3ccaa); }

  /* Toggle switch */
  .am-toggle-slider { position:absolute; inset:0; background:#e5e7eb; border-radius:24px; transition:.2s; }
  .am-toggle-slider:before { content:''; position:absolute; width:18px; height:18px; border-radius:50%; background:#fff; top:3px; left:3px; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
  #am-detraccion:checked + .am-toggle-slider { background:var(--clr-active-bg,#1a6b57); }
  #am-detraccion:checked + .am-toggle-slider:before { transform:translateX(20px); }
</style>

<script>
(function () {
  'use strict';

  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  // ROUTES se lee lazily en cada función para evitar problemas de orden de scripts
  function getRoutes() { return window.AccountingRoutes ?? {}; }

  const overlay    = document.getElementById('accounting-modal-overlay');
  const modal      = document.getElementById('accounting-modal');
  const form       = document.getElementById('am-form');
  const closeBtn   = document.getElementById('am-close');
  const cancelBtn  = document.getElementById('am-cancel');
  const submitBtn  = document.getElementById('am-submit');
  const submitText = document.getElementById('am-submit-text');
  const errorBox   = document.getElementById('am-error-box');
  const errorText  = document.getElementById('am-error-text');
  const autofillNotice = document.getElementById('am-autofill-notice');

  // Cuotas toggle
  const formaPagoSel  = document.getElementById('am-forma-pago');
  const cuotasSection = document.getElementById('am-cuotas-section');

  formaPagoSel?.addEventListener('change', function() {
    cuotasSection.style.display = this.value === '2' ? 'block' : 'none';
  });

  // Advanced toggle
  const btnAdvanced  = document.getElementById('am-toggle-advanced');
  const advSection   = document.getElementById('am-advanced-section');
  const chevron      = document.getElementById('am-advanced-chevron');

  btnAdvanced?.addEventListener('click', function () {
    const open = advSection.style.display === 'block';
    advSection.style.display = open ? 'none' : 'block';
    chevron.style.transform = open ? 'rotate(0deg)' : 'rotate(90deg)';
  });

  // Close handlers
  function closeModal() {
    overlay.style.display = 'none';
    form.reset();
    cuotasSection.style.display = 'none';
    advSection.style.display = 'none';
    chevron.style.transform = 'rotate(0deg)';
    errorBox.style.display = 'none';
    autofillNotice.style.display = 'none';
    document.querySelectorAll('.am-field.has-error').forEach(f => f.classList.remove('has-error'));
    currentInvoiceId = null;
  }

  closeBtn?.addEventListener('click', closeModal);
  cancelBtn?.addEventListener('click', closeModal);
  overlay?.addEventListener('click', function(e) {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && overlay.style.display !== 'none') closeModal();
  });

  // ── Open modal ────────────────────────────────────────────────────────
  let currentInvoiceId = null;

  window.openAccountingModal = function(invoiceId) {
    currentInvoiceId = invoiceId;
    const ROUTES = getRoutes();
    const url = ROUTES.get.replace(':id', invoiceId);

    document.getElementById('am-missing-summary').textContent = 'Cargando datos...';
    overlay.style.display = 'block';
    modal.scrollTop = 0;

    fetch(url, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => populateModal(data))
    .catch(() => {
      document.getElementById('am-missing-summary').textContent = 'Error al cargar datos.';
    });
  };

  function populateModal(data) {
    const inv      = data.invoice;
    const complete = data.completeness;
    const suggest  = data.suggestions;

    // Read-only section
    document.getElementById('am-view-tipo').textContent    = inv.tipo_documento_label;
    document.getElementById('am-view-serie').textContent   = inv.serie_numero;
    document.getElementById('am-view-cliente').textContent = inv.cliente;
    document.getElementById('am-view-total').textContent   = inv.codigo_moneda + ' ' + inv.monto_total;
    document.getElementById('am-view-fecha').textContent   = inv.fecha_emision;

    // Missing summary
    const missingCount = Object.keys(complete.missing_required).length;
    if (missingCount === 0) {
      document.getElementById('am-missing-summary').textContent = '✓ Todos los campos obligatorios están completos.';
      document.getElementById('am-missing-summary').style.color = '#059669';
    } else {
      document.getElementById('am-missing-summary').textContent = '⚠ Faltan ' + missingCount + ' campo' + (missingCount > 1 ? 's' : '') + ' obligatorio' + (missingCount > 1 ? 's' : '') + ' por completar.';
      document.getElementById('am-missing-summary').style.color = '#d97706';
    }

    // Prioridad: valor guardado > sugerencia
    function val(field) {
      return inv[field] || suggest[field] || '';
    }
    const hasSuggestions = Object.keys(suggest).length > 0 && Object.values(inv).some(v => !v) ;

    setSelectValue('am-tipo-operacion',  val('tipo_operacion'));
    setSelectValue('am-tipo-venta',      val('tipo_venta'));
    document.getElementById('am-cuenta-contable').value       = val('cuenta_contable');
    setSelectValue('am-forma-pago',      val('forma_pago'));
    document.getElementById('am-codigo-producto').value       = inv.codigo_producto_servicio;
    document.getElementById('am-glosa').value                 = inv.glosa;
    document.getElementById('am-centro-costo').value          = inv.centro_costo;
    setSelectValue('am-tipo-gasto',      inv.tipo_gasto);
    document.getElementById('am-sucursal').value              = inv.sucursal;
    document.getElementById('am-vendedor').value              = inv.vendedor;
    document.getElementById('am-anticipo').checked            = inv.es_anticipo;
    document.getElementById('am-contingencia').checked        = inv.es_documento_contingencia;
    document.getElementById('am-retencion').checked           = inv.es_sujeto_retencion;
    document.getElementById('am-percepcion').checked          = inv.es_sujeto_percepcion;
    document.getElementById('am-detraccion').checked          = inv.indicador_detraccion;

    // Cuotas
    if (val('forma_pago') === '2') {
      cuotasSection.style.display = 'block';
    }
    const cuotas = inv.lista_cuotas || [];
    if (cuotas[0]) {
      document.getElementById('am-cuota1-fecha').value = cuotas[0].fecha_pago || '';
      document.getElementById('am-cuota1-monto').value = cuotas[0].monto ?? cuotas[0].importe ?? '';
    }
    if (cuotas[1]) {
      document.getElementById('am-cuota2-fecha').value = cuotas[1].fecha_pago || '';
      document.getElementById('am-cuota2-monto').value = cuotas[1].monto ?? cuotas[1].importe ?? '';
    }

    // Highlight missing required fields
    document.querySelectorAll('.am-field').forEach(f => f.classList.remove('has-error'));
    Object.keys(complete.missing_required).forEach(field => {
      const el = document.querySelector('[data-field="' + field + '"]');
      if (el) el.classList.add('has-error');
    });

    // Autofill notice — mostrar solo si hay sugerencias aplicadas y había campos vacíos
    if (Object.keys(suggest).length > 0 && missingCount < Object.keys(complete.missing_required).length + Object.keys(suggest).length) {
      autofillNotice.style.display = 'flex';
    } else if (Object.keys(suggest).length > 0 && !inv.tipo_operacion && suggest.tipo_operacion) {
      autofillNotice.style.display = 'flex';
    }

    // Restaurar última cuenta contable desde localStorage
    const lastCuenta = localStorage.getItem('lastCuentaContable');
    if (lastCuenta && !inv.cuenta_contable) {
      document.getElementById('am-cuenta-contable').value = lastCuenta;
    }
  }

  function setSelectValue(id, value) {
    const el = document.getElementById(id);
    if (el && value) el.value = value;
  }

  // ── Submit ────────────────────────────────────────────────────────────
  form?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!currentInvoiceId) return;

    errorBox.style.display = 'none';
    document.querySelectorAll('.am-field').forEach(f => f.classList.remove('has-error'));
    submitBtn.disabled = true;
    submitText.textContent = 'Guardando...';

    const ROUTES = getRoutes();
    const url = ROUTES.save.replace(':id', currentInvoiceId);
    const payload = new FormData(form);

    // Guardar última cuenta contable en localStorage
    const cuenta = document.getElementById('am-cuenta-contable').value.trim();
    if (cuenta) localStorage.setItem('lastCuentaContable', cuenta);

    // Checkboxes: enviar explícitamente 0/1 porque FormData no incluye unchecked
    payload.set('es_anticipo',               document.getElementById('am-anticipo').checked ? '1' : '0');
    payload.set('es_documento_contingencia', document.getElementById('am-contingencia').checked ? '1' : '0');
    payload.set('es_sujeto_retencion',       document.getElementById('am-retencion').checked ? '1' : '0');
    payload.set('es_sujeto_percepcion',      document.getElementById('am-percepcion').checked ? '1' : '0');
    payload.append('_method', 'PATCH');

    fetch(url, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: payload,
    })
    .then(async r => {
      const body = await r.json();
      if (!r.ok) throw body;
      return body;
    })
    .then(data => {
      const savedId = currentInvoiceId; // capturar antes de que closeModal() lo ponga a null
      closeModal();
      // Actualizar badge en la fila sin recargar
      updateRowBadge(savedId, data.accounting_status, data.completeness);
    })
    .catch(err => {
      if (err && err.errors) {
        const firstMsg = Object.values(err.errors)[0]?.[0] ?? 'Error de validación.';
        showError(firstMsg);
        // Highlight field with error
        Object.keys(err.errors).forEach(field => {
          const el = document.querySelector('[data-field="' + field + '"]');
          if (el) el.classList.add('has-error');
        });
      } else {
        showError(err.message ?? 'Error al guardar.');
      }
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitText.textContent = 'Guardar';
    });
  });

  function showError(msg) {
    errorText.textContent = msg;
    errorBox.style.display = 'block';
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function updateRowBadge(invoiceId, status, completeness) {
    document.querySelectorAll('[data-accounting-badge="' + invoiceId + '"]').forEach(el => {
      el.className = 'accounting-badge accounting-badge--' + status;
      const icons  = { incompleto: '✗', pendiente: '⚠', listo: '✓' };
      const labels = { incompleto: 'Incompleto', pendiente: 'Pendiente', listo: 'Listo' };
      el.innerHTML = icons[status] + ' ' + labels[status];
    });
    // Mostrar/ocultar botón "Completar"
    document.querySelectorAll('[data-completar-btn="' + invoiceId + '"]').forEach(el => {
      el.style.display = status === 'listo' ? 'none' : 'inline-flex';
    });
    // Actualizar contador de listos para el botón exportar
    updateExportButtonVisibility();
  }

  function updateExportButtonVisibility() {
    const listos = document.querySelectorAll('[data-accounting-badge]');
    let count = 0;
    listos.forEach(el => { if (el.classList.contains('accounting-badge--listo')) count++; });
    const exportBtn = document.getElementById('btn-export-excel');
    if (exportBtn) exportBtn.style.display = count > 0 ? 'inline-flex' : 'none';
    const exportCount = document.getElementById('export-ready-count');
    if (exportCount) exportCount.textContent = count;
  }
})();
</script>
