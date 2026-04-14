@extends('layouts.app')

@section('title', 'Cotizador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .cot-grid { display:grid; grid-template-columns:1fr 300px; gap:1.5rem; align-items:start; }
    @media (max-width:900px) { .cot-grid { grid-template-columns:1fr; } }
    .form-section-title { font-weight:600; font-size:.95rem; color:#374151; margin:1.25rem 0 .75rem; padding-bottom:.4rem; border-bottom:1px solid #e5e7eb; }
    .cot-items-table { width:100%; border-collapse:collapse; font-size:.85rem; margin-top:.5rem; }
    .cot-items-table th { background:#f9fafb; padding:.5rem .6rem; text-align:left; font-weight:600; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
    .cot-items-table td { padding:.4rem .5rem; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
    .cot-items-table input { padding:.3rem .5rem; border:1px solid #e5e7eb; border-radius:6px; font-size:.83rem; width:100%; box-sizing:border-box; }
    .totals-box { background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:1.25rem; }
    .totals-row { display:flex; justify-content:space-between; margin-bottom:.5rem; font-size:.9rem; }
    .totals-row.grand { font-size:1.1rem; font-weight:700; color:#013b33; border-top:1px solid #e5e7eb; padding-top:.75rem; margin-top:.5rem; }
    .btn-add-row { display:inline-flex; align-items:center; gap:.4rem; background:#eef7f5; color:#013b33; border:1px solid #013b33; border-radius:8px; padding:.4rem .9rem; font-size:.85rem; cursor:pointer; margin-top:.5rem; }
    .btn-add-row:hover { background:#d1ede7; }
    .btn-del-row { background:none; border:none; cursor:pointer; color:#ef4444; padding:.2rem; display:flex; }
    .plantillas-bar { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; margin-bottom:.75rem; padding:.6rem .8rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; font-size:.83rem; color:#374151; }
    .plantillas-bar strong { color:#013b33; margin-right:.25rem; }
    .btn-plantilla { display:inline-flex; align-items:center; gap:.35rem; background:#013b33; color:#fff; border:none; border-radius:7px; padding:.35rem .85rem; font-size:.83rem; cursor:pointer; transition:.15s; }
    .btn-plantilla:hover { background:#025c47; }
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
            <h1>Cotizador</h1>
          </div>

          <form method="POST" action="{{ route('facturador.quotations.preview') }}" id="cot-form" target="_blank">
            @csrf

            <div class="cot-grid">

              {{-- ===== IZQUIERDA ===== --}}
              <div>

                {{-- CABECERA --}}
                <p class="form-section-title">Datos de la Cotización</p>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.8rem;">
                  <div class="form-group">
                    <label>N° Cotización *</label>
                    <input type="text" name="cot_number" class="form-input"
                      value="{{ old('cot_number', 'COT-' . date('Y') . '-001') }}" required maxlength="50">
                    @error('cot_number')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group">
                    <label>Fecha Emisión *</label>
                    <input type="date" name="fecha_emision" class="form-input"
                      value="{{ old('fecha_emision', date('Y-m-d')) }}" required>
                    @error('fecha_emision')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group">
                    <label>Válido hasta *</label>
                    <input type="date" name="fecha_vencimiento" class="form-input"
                      value="{{ old('fecha_vencimiento', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    @error('fecha_vencimiento')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                </div>

                {{-- CLIENTE --}}
                <p class="form-section-title">Cliente</p>
                <div style="display:grid; grid-template-columns:auto 1fr auto; gap:.8rem; align-items:end;">
                  <div class="form-group" style="margin:0;">
                    <label>Tipo Doc.</label>
                    <select name="cliente_tipo_doc" id="cliente_tipo_doc" class="form-input" style="width:120px;">
                      <option value="6" {{ old('cliente_tipo_doc','6')=='6' ? 'selected' : '' }}>RUC</option>
                      <option value="1" {{ old('cliente_tipo_doc')=='1' ? 'selected' : '' }}>DNI</option>
                    </select>
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label>Número *</label>
                    <input type="text" name="cliente_numero_doc" id="cliente_numero_doc" class="form-input"
                      value="{{ old('cliente_numero_doc') }}" maxlength="20" required>
                    @error('cliente_numero_doc')<p class="form-error">{{ $message }}</p>@enderror
                  </div>
                  <div style="margin:0;">
                    <button type="button" id="btn-lookup" class="btn-primary" style="margin-bottom:0;">
                      <i class='bx bx-search'></i> Buscar
                    </button>
                  </div>
                </div>
                <div class="form-group" style="margin-top:.8rem;">
                  <label>Nombre / Razón Social *</label>
                  <input type="text" name="cliente_nombre" id="cliente_nombre" class="form-input"
                    value="{{ old('cliente_nombre') }}" required maxlength="200">
                  @error('cliente_nombre')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- DESCRIPCIÓN --}}
                <p class="form-section-title">Descripción del Servicio</p>
                <div class="form-group">
                  <textarea name="descripcion" class="form-input" rows="3"
                    placeholder="Descripción general del servicio ofrecido...">{{ old('descripcion') }}</textarea>
                </div>

                {{-- ÍTEMS --}}
                <p class="form-section-title">Servicios / Ítems</p>
                <div class="plantillas-bar">
                  <strong><i class='bx bx-layer'></i> Plantillas rápidas:</strong>
                  <button type="button" class="btn-plantilla" id="btn-tpl-rer">
                    <i class='bx bx-file'></i> RER – Régimen Especial
                  </button>
                  <button type="button" class="btn-plantilla" id="btn-tpl-rus" style="background:#2563eb;">
                    <i class='bx bx-file'></i> NRUS – Nuevo RUS
                  </button>
                  <button type="button" class="btn-plantilla" id="btn-tpl-mype" style="background:#7c3aed;">
                    <i class='bx bx-file'></i> MYPE – Régimen General
                  </button>
                </div>
                <div style="overflow-x:auto;">
                  <table class="cot-items-table">
                    <thead>
                      <tr>
                        <th style="width:50%">Servicio / Descripción</th>
                        <th style="width:12%">Cantidad</th>
                        <th style="width:18%">Precio Unit. (S/)</th>
                        <th style="width:15%">Total (S/)</th>
                        <th style="width:5%"></th>
                      </tr>
                    </thead>
                    <tbody id="items-tbody">
                      <tr>
                        <td><input type="text" name="items[0][servicio]" placeholder="Servicio..." required></td>
                        <td><input type="number" name="items[0][cantidad]" value="1" min="0" step="0.01" class="item-qty"></td>
                        <td><input type="number" name="items[0][precio]"  value="0" min="0" step="0.01" class="item-price"></td>
                        <td><input type="number" name="items[0][total]"   value="0" readonly style="background:#f3f4f6;" class="item-total"></td>
                        <td><button type="button" class="btn-del-row" title="Eliminar fila"><i class='bx bx-trash'></i></button></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <button type="button" id="btn-add-row" class="btn-add-row">
                  <i class='bx bx-plus'></i> Agregar ítem
                </button>

              </div>

              {{-- ===== DERECHA: TOTALES ===== --}}
              <div>
                <p class="form-section-title">Totales</p>
                <div class="totals-box">
                  <div class="totals-row">
                    <span>Subtotal</span>
                    <span>S/ <b id="disp-subtotal">0.00</b></span>
                  </div>

                  <div class="totals-row" style="align-items:center;">
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;">
                      <input type="checkbox" name="aplica_igv" id="aplica_igv" value="1"
                        {{ old('aplica_igv') ? 'checked' : '' }}>
                      IGV (18%)
                    </label>
                    <span>S/ <b id="disp-igv">0.00</b></span>
                  </div>

                  <div class="totals-row grand">
                    <span>TOTAL</span>
                    <span>S/ <b id="disp-total">0.00</b></span>
                  </div>
                </div>

                <div style="margin-top:1.5rem; display:flex; flex-direction:column; gap:.75rem;">
                  <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">
                    <i class='bx bx-printer'></i> Generar Cotización
                  </button>
                  <hr style="border:none; border-top:1px solid #e5e7eb; margin:.25rem 0;">
                  <p style="font-size:.75rem; color:#6b7280; text-align:center; margin:0;">Fichas de servicios (sin precios)</p>
                  <a href="{{ route('facturador.quotations.service-proposal', 'rer') }}"  target="_blank" style="display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.45rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;color:#166534;font-size:.82rem;text-decoration:none;font-weight:600;">
                    <i class='bx bx-file'></i> Propuesta RER
                  </a>
                  <a href="{{ route('facturador.quotations.service-proposal', 'rus') }}"  target="_blank" style="display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.45rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;color:#1d4ed8;font-size:.82rem;text-decoration:none;font-weight:600;">
                    <i class='bx bx-file'></i> Propuesta NRUS
                  </a>
                  <a href="{{ route('facturador.quotations.service-proposal', 'mype') }}" target="_blank" style="display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.45rem;background:#faf5ff;border:1px solid #ddd6fe;border-radius:8px;color:#6d28d9;font-size:.82rem;text-decoration:none;font-weight:600;">
                    <i class='bx bx-file'></i> Propuesta MYPE
                  </a>
                </div>
              </div>

            </div><!-- /.cot-grid -->
          </form>
        </div>
      </div>
    </main>
  </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
  /* ── utilidades ──────────────────────────────────────── */
  const fmt = v => parseFloat(v || 0).toFixed(2);

  function recalcRow(tr) {
    const qty   = parseFloat(tr.querySelector('.item-qty')?.value   || 0);
    const price = parseFloat(tr.querySelector('.item-price')?.value || 0);
    const tot   = tr.querySelector('.item-total');
    if (tot) tot.value = fmt(qty * price);
  }

  function recalcTotals() {
    const rows     = document.querySelectorAll('#items-tbody tr');
    const subtotal = Array.from(rows).reduce((sum, tr) => {
      return sum + parseFloat(tr.querySelector('.item-total')?.value || 0);
    }, 0);
    const igv   = document.getElementById('aplica_igv').checked ? subtotal * 0.18 : 0;
    const total = subtotal + igv;
    document.getElementById('disp-subtotal').textContent = fmt(subtotal);
    document.getElementById('disp-igv').textContent      = fmt(igv);
    document.getElementById('disp-total').textContent    = fmt(total);
  }

  /* ── re-index names after add/remove ────────────────── */
  function reindex() {
    document.querySelectorAll('#items-tbody tr').forEach((tr, i) => {
      tr.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/items\[\d+\]/, `items[${i}]`);
      });
    });
  }

  /* ── escucha cambios en la tabla ─────────────────────── */
  document.getElementById('items-tbody').addEventListener('input', function (e) {
    const tr = e.target.closest('tr');
    if (!tr) return;
    recalcRow(tr);
    recalcTotals();
  });

  document.getElementById('items-tbody').addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-del-row');
    if (!btn) return;
    const rows = document.querySelectorAll('#items-tbody tr');
    if (rows.length <= 1) return;
    btn.closest('tr').remove();
    reindex();
    recalcTotals();
  });

  /* ── agregar fila ────────────────────────────────────── */
  document.getElementById('btn-add-row').addEventListener('click', function () {
    const tbody = document.getElementById('items-tbody');
    const idx   = tbody.querySelectorAll('tr').length;
    const tr    = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="text"   name="items[${idx}][servicio]" placeholder="Servicio..." required></td>
      <td><input type="number" name="items[${idx}][cantidad]" value="1" min="0" step="0.01" class="item-qty"></td>
      <td><input type="number" name="items[${idx}][precio]"   value="0" min="0" step="0.01" class="item-price"></td>
      <td><input type="number" name="items[${idx}][total]"    value="0" readonly style="background:#f3f4f6;" class="item-total"></td>
      <td><button type="button" class="btn-del-row" title="Eliminar"><i class='bx bx-trash'></i></button></td>
    `;
    tbody.appendChild(tr);
  });

  /* ── IGV toggle ──────────────────────────────────────── */
  document.getElementById('aplica_igv').addEventListener('change', recalcTotals);

  /* ── Lookup RUC / DNI ────────────────────────────────── */
  document.getElementById('btn-lookup').addEventListener('click', function () {
    const tipo   = document.getElementById('cliente_tipo_doc').value;
    const numero = document.getElementById('cliente_numero_doc').value.trim();
    if (!numero) return;

    fetch(`{{ route('facturador.clients.lookup-doc') }}?type=${tipo}&number=${numero}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        document.getElementById('cliente_nombre').value = data.nombre || '';
      } else {
        Swal.fire({icon:'warning', title:'No encontrado', text:'No se encontró el documento. Ingrese el nombre manualmente.'});
      }
    })
    .catch(() => Swal.fire({icon:'error', title:'Error de conexión', text:'No se pudo consultar. Verifique su conexión.'}));
  });

  /* ── Recalc inicial ──────────────────────────────────── */
  document.querySelectorAll('#items-tbody tr').forEach(tr => recalcRow(tr));
  recalcTotals();

  /* ── Plantillas ─────────────────────────────────────── */
  const PLANTILLAS = {
    rer: {
      descripcion: 'Propuesta de Servicio Contable – Régimen Especial (RER)\n\nPaquete mensual que incluye todo el soporte contable y tributario para empresas bajo el Régimen Especial de Renta.',
      items: [
        { servicio: 'Registro contable mensual (compras, ventas y documentación)',       cantidad: 1 },
        { servicio: 'Declaraciones tributarias mensuales – IGV/Renta RER (PDT 621)',      cantidad: 1 },
        { servicio: 'Libros contables electrónicos (Registro de Compras y Ventas)',       cantidad: 1 },
        { servicio: 'Planilla electrónica PLAME (Essalud, AFP/ONP, T-Registro)',          cantidad: 1 },
        { servicio: 'Asesoría contable y tributaria permanente (SUNAT)',                  cantidad: 1 },
        { servicio: 'Reportes básicos mensuales (impuestos e ingresos/gastos)',           cantidad: 1 },
      ]
    },
    rus: {
      descripcion: 'Propuesta de Servicio Contable – Nuevo RUS\n\nPaquete mensual para personas naturales o empresas bajo el Nuevo Régimen Único Simplificado.',
      items: [
        { servicio: 'Determinación y pago de cuota mensual NRUS',                         cantidad: 1 },
        { servicio: 'Asesoría contable y tributaria permanente (SUNAT)',                  cantidad: 1 },
        { servicio: 'Control de límites de ingresos y compras',                           cantidad: 1 },
        { servicio: 'Reporte mensual de cumplimiento tributario',                         cantidad: 1 },
      ]
    },
    mype: {
      descripcion: 'Propuesta de Servicio Contable – MYPE Régimen General\n\nPaquete mensual completo para empresas bajo el Régimen MYPE Tributario o Régimen General.',
      items: [
        { servicio: 'Registro contable mensual completo',                                 cantidad: 1 },
        { servicio: 'Declaraciones mensuales PDT 621 (IGV/Renta)',                        cantidad: 1 },
        { servicio: 'Declaración anual del Impuesto a la Renta',                          cantidad: 1 },
        { servicio: 'Libros contables electrónicos (PLE SUNAT)',                          cantidad: 1 },
        { servicio: 'Planilla electrónica PLAME',                                         cantidad: 1 },
        { servicio: 'Balances mensuales y estados financieros',                           cantidad: 1 },
        { servicio: 'Asesoría contable y tributaria permanente',                          cantidad: 1 },
      ]
    },
  };

  async function cargarPlantilla(key) {
    const p = PLANTILLAS[key];
    if (!p) return;
    if (document.querySelector('#items-tbody tr')) {
      const confirmed = await Swal.fire({
        title: '¿Reemplazar ítems?',
        text: 'Se reemplazarán los ítems actuales con la plantilla "' + key.toUpperCase() + '".',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, reemplazar'
      });
      if (!confirmed.isConfirmed) return;
    }

    // descripción
    const desc = document.querySelector('[name="descripcion"]');
    if (desc) desc.value = p.descripcion;

    // limpiar tabla
    const tbody = document.getElementById('items-tbody');
    tbody.innerHTML = '';

    // cargar ítems
    p.items.forEach((item, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="text"   name="items[${i}][servicio]" value="${item.servicio.replace(/"/g,'&quot;')}" required></td>
        <td><input type="number" name="items[${i}][cantidad]" value="${item.cantidad}" min="0" step="0.01" class="item-qty"></td>
        <td><input type="number" name="items[${i}][precio]"   value="0"      min="0" step="0.01" class="item-price"></td>
        <td><input type="number" name="items[${i}][total]"    value="0"      readonly style="background:#f3f4f6;" class="item-total"></td>
        <td><button type="button" class="btn-del-row" title="Eliminar"><i class='bx bx-trash'></i></button></td>
      `;
      tbody.appendChild(tr);
    });
    recalcTotals();
  }

  document.getElementById('btn-tpl-rer').addEventListener('click',  () => cargarPlantilla('rer'));
  document.getElementById('btn-tpl-rus').addEventListener('click',  () => cargarPlantilla('rus'));
  document.getElementById('btn-tpl-mype').addEventListener('click', () => cargarPlantilla('mype'));
})();
</script>
@endpush
