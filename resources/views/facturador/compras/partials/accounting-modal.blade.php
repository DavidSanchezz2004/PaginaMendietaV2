{{--
  Modal de Completado Contable — COMPRAS
  Uso: @include('facturador.compras.partials.accounting-modal')
  Requiere: window.ComprasRoutes con accountingGet e accountingSave
--}}

<div id="accounting-modal-overlay"
     style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,.55); backdrop-filter:blur(3px);"
     aria-modal="true" role="dialog" aria-labelledby="am-title">

  <div id="accounting-modal"
       style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:min(680px,96vw); max-height:92vh; overflow-y:auto; border-radius:16px; background:var(--clr-bg-card,#fff); box-shadow:0 20px 60px rgba(0,0,0,.25); display:flex; flex-direction:column;">

    {{-- Header --}}
    <div style="padding:1.25rem 1.5rem 1rem; border-bottom:1px solid var(--clr-border-light,#e5e7eb); display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
      <div>
        <h2 id="am-title" style="margin:0; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:.5rem;">
          <i class='bx bx-clipboard-check' style="color:#1a6b57;"></i> Completar información contable
        </h2>
        <p id="am-missing-summary" style="margin:.3rem 0 0; font-size:.82rem; color:#d97706;">Cargando...</p>
      </div>
      <button type="button" id="am-close" aria-label="Cerrar"
              style="background:none; border:none; cursor:pointer; font-size:1.4rem; color:var(--clr-text-muted,#6b7280); line-height:1; padding:.25rem;">
        <i class='bx bx-x'></i>
      </button>
    </div>

    {{-- Autofill notice --}}
    <div id="am-autofill-notice"
         style="display:none; margin:.75rem 1.5rem 0; padding:.65rem 1rem; background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.25); border-radius:8px; font-size:.82rem; color:#065f46; align-items:center; gap:.5rem;">
      <i class='bx bx-magic-wand' style="font-size:1rem;"></i>
      <span>Auto-rellenado con valores sugeridos. Revisa y ajusta si es necesario.</span>
    </div>

    <form id="am-form" style="padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:1.25rem;">

      {{-- Datos del comprobante (read-only) --}}
      <div style="background:var(--clr-bg-card,#f8fafc); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; padding:1rem;">
        <p style="margin:0 0 .65rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-text-muted,#6b7280);">
          <i class='bx bx-lock-alt' style="vertical-align:middle;"></i> Datos del comprobante
        </p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:.6rem .8rem;">
          <div><span style="font-size:.72rem; color:#9ca3af; display:block;">Tipo</span><strong id="am-view-tipo" style="font-size:.88rem;"></strong></div>
          <div><span style="font-size:.72rem; color:#9ca3af; display:block;">Serie-Número</span><code id="am-view-serie" style="font-size:.88rem;"></code></div>
          <div><span style="font-size:.72rem; color:#9ca3af; display:block;">Proveedor</span><strong id="am-view-proveedor" style="font-size:.85rem; word-break:break-word;"></strong></div>
          <div><span style="font-size:.72rem; color:#9ca3af; display:block;">Total</span><strong id="am-view-total" style="font-size:.95rem; color:#1a6b57;"></strong></div>
          <div><span style="font-size:.72rem; color:#9ca3af; display:block;">Fecha</span><span id="am-view-fecha" style="font-size:.85rem;"></span></div>
        </div>
      </div>

      {{-- Configuración con los campos clave --}}
      <div>
        <p style="margin:0 0 .75rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#1a6b57;">
          <i class='bx bx-zap' style="vertical-align:middle;"></i> Configuración contable
        </p>
        <style>
          .am-field { display:flex; flex-direction:column; gap:.3rem; }
          .am-label { font-size:.78rem; font-weight:600; color:var(--clr-text-muted,#6b7280); }
          .am-req { color:#ef4444; }
          .am-input { padding:.55rem .8rem; border:1px solid #d1d5db; border-radius:8px; font-size:.88rem; background:transparent; color:var(--clr-text-main,#111827); outline:none; transition:all .2s; font-family:inherit; }
          .am-input:focus { border-color:#1a6b57; box-shadow:0 0 0 3px rgba(26,107,87,.1); }
          .am-field--error .am-input { border-color:#ef4444; }
        </style>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">

          <div class="am-field" data-field="tipo_operacion">
            <label class="am-label" for="am-tipo-operacion">Tipo de operación <span class="am-req">*</span></label>
            <select id="am-tipo-operacion" name="tipo_operacion" class="am-input">
              <option value="">— Seleccionar —</option>
              <option value="0401">0401 - Compra interna</option>
              <option value="0402">0402 - Compra a no domiciliados</option>
              <option value="0403">0403 - Importación definitiva</option>
              <option value="0405">0405 - Compra - Anticipos</option>
              <option value="0409">0409 - DUA (importación)</option>
              <option value="0410">0410 - IVAP</option>
              <option value="0412">0412 - Nota de crédito</option>
              <option value="0413">0413 - Nota de débito</option>
            </select>
          </div>

          <div class="am-field" data-field="tipo_compra">
            <label class="am-label" for="am-tipo-compra">Tipo de compra <span class="am-req">*</span></label>
            <select id="am-tipo-compra" name="tipo_compra" class="am-input">
              <option value="">— Seleccionar —</option>
              <option value="NG">NG - Gravadas</option>
              <option value="NI">NI - No Gravadas</option>
              <option value="EX">EX - Exportación</option>
              <option value="GR">GR - Gratuitas</option>
              <option value="MX">MX - Mixtas</option>
            </select>
          </div>

          <div class="am-field" data-field="cuenta_contable">
            <label class="am-label" for="am-cuenta-contable">Cuenta contable <span class="am-req">*</span></label>
            <input id="am-cuenta-contable" name="cuenta_contable" type="text" class="am-input" autocomplete="off"
                   placeholder="Ej: 421, 60, 4011..."
                   list="am-pcge-compras-list">
            <datalist id="am-pcge-compras-list">
              <option value="42">42 - Cuentas por Pagar Comerciales</option>
              <option value="421">421 - Facturas por Pagar</option>
              <option value="4211">4211 - No emitidas</option>
              <option value="4212">4212 - Emitidas</option>
              <option value="60">60 - Compras</option>
              <option value="601">601 - Mercaderías</option>
              <option value="6011">6011 - Mercaderías manufacturadas</option>
              <option value="602">602 - Materias primas</option>
              <option value="603">603 - Materiales auxiliares, suministros y repuestos</option>
              <option value="604">604 - Envases y embalajes</option>
              <option value="607">607 - Mercaderías agropecuarias y piscícolas</option>
              <option value="63">63 - Gastos de Servicios Prestados por Terceros</option>
              <option value="631">631 - Transporte, correos y gastos de viaje</option>
              <option value="632">632 - Asesoría y consultoría</option>
              <option value="636">636 - Servicios básicos</option>
              <option value="637">637 - Publicidad, publicaciones, relaciones públicas</option>
              <option value="638">638 - Servicios contratados</option>
              <option value="40">40 - Tributos por Pagar</option>
              <option value="4011">4011 - IGV</option>
              <option value="40111">40111 - IGV Cuenta propia</option>
              <option value="40112">40112 - IGV cuenta de terceros</option>
              <option value="1682">1682 - IGV por recuperar</option>
            </datalist>
          </div>

          <div class="am-field" data-field="codigo_producto_servicio">
            <label class="am-label" for="am-codigo-ps">Código producto/servicio <span class="am-req">*</span></label>
            <input id="am-codigo-ps" name="codigo_producto_servicio" type="text" class="am-input"
                   placeholder="Ej: 84131500, SRV001...">
          </div>

          <div class="am-field" id="am-field-forma-pago" data-field="forma_pago">
            <label class="am-label" for="am-forma-pago">Forma de pago <span class="am-req">*</span></label>
            <select id="am-forma-pago" name="forma_pago" class="am-input" onchange="toggleCuotasModal(this.value)">
              <option value="">— Seleccionar —</option>
              <option value="01">01 - Contado</option>
              <option value="02">02 - Crédito</option>
              <optgroup label="Medio de Pago">
                <option value="03">03 - Efectivo</option>
                <option value="04">04 - Yape</option>
                <option value="05">05 - Plin</option>
                <option value="06">06 - Banco / Transferencia</option>
                <option value="07">07 - BCP</option>
                <option value="08">08 - BBVA</option>
              </optgroup>
            </select>
          </div>

          <div class="am-field">
            <label class="am-label" for="am-glosa">Glosa</label>
            <input id="am-glosa" name="glosa" type="text" class="am-input" placeholder="Descripción libre...">
          </div>

          <div class="am-field">
            <label class="am-label" for="am-centro-costo">Centro de costo</label>
            <input id="am-centro-costo" name="centro_costo" type="text" class="am-input" placeholder="Área, proyecto...">
          </div>

          <div class="am-field">
            <label class="am-label" for="am-tipo-gasto">Tipo de gasto</label>
            <input id="am-tipo-gasto" name="tipo_gasto" type="text" class="am-input" placeholder="ADM, OPER, VENT...">
          </div>

          <div class="am-field">
            <label class="am-label" for="am-sucursal">Sucursal</label>
            <input id="am-sucursal" name="sucursal" type="text" class="am-input" placeholder="Principal...">
          </div>

          <div class="am-field">
            <label class="am-label" for="am-comprador">Comprador</label>
            <input id="am-comprador" name="comprador" type="text" class="am-input" placeholder="Nombre responsable...">
          </div>
        </div>
      </div>

      {{-- Cuotas --}}
      <div id="am-cuotas-section" style="display:none; background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:1rem;">
        <p style="margin:0 0 .65rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280;">
          <i class='bx bx-calendar' style="vertical-align:middle;"></i> Cuotas de crédito
        </p>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:.6rem;">
          <div class="am-field"><label class="am-label">Cuota 1 — Fecha</label><input id="am-cuota1-fecha" name="cuota_1_fecha" type="date" class="am-input"></div>
          <div class="am-field"><label class="am-label">Cuota 1 — Monto</label><input id="am-cuota1-monto" name="cuota_1_monto" type="number" step="0.01" min="0" class="am-input" placeholder="0.00"></div>
          <div class="am-field"><label class="am-label">Cuota 2 — Fecha</label><input id="am-cuota2-fecha" name="cuota_2_fecha" type="date" class="am-input"></div>
          <div class="am-field"><label class="am-label">Cuota 2 — Monto</label><input id="am-cuota2-monto" name="cuota_2_monto" type="number" step="0.01" min="0" class="am-input" placeholder="0.00"></div>
        </div>
      </div>

      {{-- Flags booleanos --}}
      <div style="display:flex; flex-wrap:wrap; gap:.6rem;">
        @foreach([
          ['am-es-anticipo',    'es_anticipo',               'Anticipo'],
          ['am-es-contingencia','es_documento_contingencia',  'Documento Contingencia'],
          ['am-es-detraccion',  'es_sujeto_detraccion',       'Sujeto Detracción'],
          ['am-es-retencion',   'es_sujeto_retencion',        'Sujeto Retención'],
          ['am-es-percepcion',  'es_sujeto_percepcion',       'Sujeto Percepción'],
        ] as [$id, $name, $label])
          <label style="display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .75rem; background:rgba(0,0,0,.04); border-radius:8px; font-size:.8rem; cursor:pointer;">
            <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" style="accent-color:#1a6b57;">
            {{ $label }}
          </label>
        @endforeach
      </div>

      {{-- Botones --}}
      <div style="display:flex; justify-content:flex-end; gap:.65rem; padding-top:.25rem;">
        <button type="button" id="am-cancel" style="padding:.55rem 1.2rem; background:transparent; border:1px solid #d1d5db; border-radius:8px; cursor:pointer; font-size:.88rem; color:var(--clr-text-muted,#6b7280);">
          Cancelar
        </button>
        <button type="submit" id="am-submit"
                style="padding:.55rem 1.4rem; background:#1a6b57; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.88rem; font-weight:700; display:inline-flex; align-items:center; gap:.4rem;">
          <span id="am-submit-icon"><i class='bx bx-save'></i></span>
          Guardar
        </button>
      </div>

    </form>
  </div>
</div>
