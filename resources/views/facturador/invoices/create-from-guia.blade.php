@extends('layouts.app')

@section('title', 'Crear Factura desde Guía — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .invoice-card {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .invoice-header {
      border-bottom: 1px solid var(--clr-border-light, #e2e8f0);
      padding-bottom: 1rem;
      margin-bottom: 1rem;
    }

    .invoice-header h3 {
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

    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
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

    .total-row {
      background: #f8fafc;
      font-weight: 600;
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      justify-content: flex-end;
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
                <i class='bx bx-receipt'></i> Crear Factura desde Guía
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Guía GRE-{{ $guia->numero }}</small>
            </div>
            <a href="{{ route('facturador.guias.show', $guia) }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
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

          {{-- Card: Información de Guía --}}
          <div class="invoice-card">
            <div class="invoice-header">
              <h3><i class='bx bx-file-blank'></i> Información de Guía</h3>
            </div>
            <div class="info-grid">
              <div class="info-item">
                <div class="info-label">Número Guía</div>
                <div class="info-value">GRE-{{ $guia->numero }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">Compra</div>
                <div class="info-value">{{ $guia->purchase->serie_numero }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">Cliente</div>
                <div class="info-value">{{ $guia->client->nombre_razon_social }}</div>
              </div>
              <div class="info-item">
                <div class="info-label">Fecha Guía</div>
                <div class="info-value">{{ $guia->created_at->format('d/m/Y') }}</div>
              </div>
            </div>
          </div>

          {{-- Card: Datos de Factura --}}
          @php
            $invoiceSubtotal = 0;
            foreach ($guia->items as $calcItem) {
              $calcPurchaseItem = $calcItem->purchaseItem;
              $calcUnitPrice = $calcItem->unit_price ?? $calcPurchaseItem->valor_unitario ?? 0;
              $invoiceSubtotal += $calcItem->quantity * $calcUnitPrice;
            }
            $invoiceIgv = round($invoiceSubtotal * 0.18, 2);
            $invoiceTotal = round($invoiceSubtotal + $invoiceIgv, 2);
          @endphp
          <form method="POST" action="{{ route('facturador.guias.invoices.store', $guia) }}" id="invoiceForm">
            @csrf

            <div class="invoice-card">
              <div class="invoice-header">
                <h3><i class='bx bx-receipt'></i> Datos de Factura</h3>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Tipo de Documento <span style="color:#dc2626;">*</span></label>
                  <select name="codigo_tipo_documento" class="form-select @error('codigo_tipo_documento') is-invalid @enderror" required>
                    <option value="">-- Seleccionar --</option>
                    <option value="01" @if(old('codigo_tipo_documento', '01') === '01') selected @endif>Factura (01)</option>
                    <option value="03" @if(old('codigo_tipo_documento') === '03') selected @endif>Boleta (03)</option>
                  </select>
                  @error('codigo_tipo_documento')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="form-group">
                  <label class="form-label">Serie <span style="color:#dc2626;">*</span></label>
                  <input type="text" name="serie_documento" class="form-input @error('serie_documento') is-invalid @enderror"
                    value="{{ old('serie_documento', $suggestions['serie'] ?? 'F001') }}" required maxlength="4">
                  @error('serie_documento')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="form-group">
                  <label class="form-label">Número <span style="color:#dc2626;">*</span></label>
                  <div class="form-input" style="background: #f3f4f6; color: #6b7280; display: flex; align-items: center; padding: 0.6rem 0.85rem; border: 1px solid #e5e7eb;">
                    <i class='bx bx-check' style="color: #10b981; margin-right: 0.5rem;"></i>
                    <span>Se generará automáticamente</span>
                  </div>
                  {{-- Campo oculto para envío (se generará en backend) --}}
                  <input type="hidden" name="numero_documento" value="">
                </div>

                <div class="form-group">
                  <label class="form-label">Moneda <span style="color:#dc2626;">*</span></label>
                  <select name="codigo_moneda" class="form-select @error('codigo_moneda') is-invalid @enderror" required>
                    <option value="">-- Seleccionar --</option>
                    <option value="PEN" @if(old('codigo_moneda', $guia->purchase->codigo_moneda) === 'PEN') selected @endif>PEN</option>
                    <option value="USD" @if(old('codigo_moneda', $guia->purchase->codigo_moneda) === 'USD') selected @endif>USD</option>
                    <option value="EUR" @if(old('codigo_moneda', $guia->purchase->codigo_moneda) === 'EUR') selected @endif>EUR</option>
                  </select>
                  @error('codigo_moneda')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="form-group">
                  <label class="form-label">Forma de Pago <span style="color:#dc2626;">*</span></label>
                  <select name="forma_pago" id="forma-pago-select" class="form-select @error('forma_pago') is-invalid @enderror" required>
                    <option value="">-- Seleccionar --</option>
                    <option value="1" @if(old('forma_pago', '1') === '1') selected @endif>Contado</option>
                    <option value="2" @if(old('forma_pago') === '2') selected @endif>Crédito</option>
                  </select>
                  @error('forma_pago')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div id="cuotas-section" style="display:none; margin-top:1rem; border:1px solid #bbf7d0; background:#f0fdf4; border-radius:8px; padding:1rem;">
                <div style="display:flex; justify-content:space-between; gap:1rem; align-items:center; margin-bottom:.75rem;">
                  <strong style="color:#166534; font-size:.95rem;"><i class='bx bx-calendar-check'></i> Plan de Cuotas</strong>
                  <div style="font-size:.86rem;">
                    <span style="color:#64748b;">Suma cuotas:</span>
                    <strong id="cuotas-suma" style="color:#166534;">{{ $guia->purchase->codigo_moneda ?? 'PEN' }} 0.00</strong>
                  </div>
                </div>

                <div id="cuotas-body" style="display:flex; flex-direction:column; gap:.45rem;">
                  @foreach(old('lista_cuotas', []) as $ci => $cuota)
                    <div class="cuota-row" style="display:grid; grid-template-columns:32px minmax(140px,1fr) minmax(140px,1fr) 38px; gap:.5rem; align-items:center;">
                      <span class="cuota-num" style="text-align:center; color:#64748b; font-weight:700;">{{ $ci + 1 }}</span>
                      <input type="date" name="lista_cuotas[{{ $ci }}][fecha_pago]" value="{{ $cuota['fecha_pago'] ?? '' }}" class="form-input cuota-fecha">
                      <input type="number" name="lista_cuotas[{{ $ci }}][monto]" value="{{ $cuota['monto'] ?? '' }}" class="form-input cuota-monto" step="0.01" min="0.01" placeholder="Monto">
                      <button type="button" class="cuota-remove" style="height:38px; border:none; border-radius:8px; background:#dcfce7; color:#166534; cursor:pointer;"><i class='bx bx-trash'></i></button>
                    </div>
                  @endforeach
                </div>

                <div style="display:flex; justify-content:space-between; gap:1rem; align-items:center; margin-top:.75rem; flex-wrap:wrap;">
                  <button type="button" id="cuota-add" class="btn-secondary" style="padding:.45rem .85rem; cursor:pointer;">
                    <i class='bx bx-plus'></i> Agregar cuota
                  </button>
                  <span style="font-size:.78rem; color:#94a3b8;">Max. 12 cuotas. La suma debe igualar el total del comprobante.</span>
                </div>
              </div>
            </div>

            {{-- Card: Items de Guía --}}
            @if($guia->items->count())
              <div class="invoice-card">
                <div class="invoice-header">
                  <h3><i class='bx bx-list-ul'></i> Items de la Guía ({{ $guia->items->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                  <table class="items-table">
                    <thead>
                      <tr>
                        <th>Descripción</th>
                        <th style="text-align: right;">Cantidad</th>
                        <th>Unidad</th>
                        <th style="text-align: right;">Precio (s/IGV)</th>
                        <th style="text-align: right;">Total (s/IGV)</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php $total = 0; @endphp
                      @foreach($guia->items as $item)
                        @php 
                          $purchaseItem = $item->purchaseItem;
                          $unitPrice = $item->unit_price ?? $purchaseItem->valor_unitario ?? 0;
                          $subtotal = $item->quantity * $unitPrice;
                          $total += $subtotal; 
                        @endphp
                        <tr>
                          <td>{{ $item->description }}</td>
                          <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                          <td>{{ $item->unit }}</td>
                          <td style="text-align: right;">{{ number_format($unitPrice, 2) }}</td>
                          <td style="text-align: right; font-weight: 600;">{{ number_format($subtotal, 2) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                      @php $igv = round($total * 0.18, 2); $totalConIgv = round($total + $igv, 2); @endphp
                      <tr>
                        <td colspan="4" style="text-align:right; padding:.5rem .75rem; color:var(--clr-text-muted,#6b7280);">OP. Gravadas (s/IGV):</td>
                        <td style="text-align:right; padding:.5rem .75rem;">{{ number_format($total, 2) }}</td>
                      </tr>
                      <tr>
                        <td colspan="4" style="text-align:right; padding:.5rem .75rem; color:var(--clr-text-muted,#6b7280);">IGV (18%):</td>
                        <td style="text-align:right; padding:.5rem .75rem;">{{ number_format($igv, 2) }}</td>
                      </tr>
                      <tr style="font-size:1.05rem; font-weight:700; border-top:2px solid var(--clr-border-light,#e2e8f0);">
                        <td colspan="4" style="text-align:right; padding:.75rem; color:var(--clr-active-bg,#1a6b57);">TOTAL (c/IGV):</td>
                        <td style="text-align:right; padding:.75rem; color:var(--clr-active-bg,#1a6b57);">{{ number_format($totalConIgv, 2) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            @endif

            {{-- Items Ocultos para Envío (Requeridos por validación) --}}
            @foreach($guia->items as $index => $item)
              @php 
                $purchaseItem = $item->purchaseItem;
                $unitPrice = $item->unit_price ?? $purchaseItem->valor_unitario ?? 0;
              @endphp
              <input type="hidden" name="items[{{ $index }}][purchase_item_id]" value="{{ $purchaseItem->id }}">
              <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}">
              <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $unitPrice }}">
              <input type="hidden" name="items[{{ $index }}][correlativo]" value="{{ $index + 1 }}">
            @endforeach

            {{-- Botones de Acción --}}
            <div class="form-actions">
              <a href="{{ route('facturador.guias.show', $guia) }}" class="btn-secondary" style="text-decoration: none; padding: 0.6rem 1.5rem;">
                <i class='bx bx-x'></i> Cancelar
              </a>
              <button type="submit" class="btn-primary" style="padding: 0.6rem 1.5rem; cursor: pointer;">
                <i class='bx bx-check'></i> Crear Factura
              </button>
            </div>
          </form>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}

<script>
  const invoiceTotal = {{ json_encode($invoiceTotal) }};
  const invoiceCurrency = @json($guia->purchase->codigo_moneda ?? 'PEN');
  const form = document.getElementById('invoiceForm');
  const formaPago = document.getElementById('forma-pago-select');
  const cuotasSection = document.getElementById('cuotas-section');
  const cuotasBody = document.getElementById('cuotas-body');
  const cuotasSuma = document.getElementById('cuotas-suma');
  const cuotaAdd = document.getElementById('cuota-add');
  const maxCuotas = 12;

  function toIsoDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function addMonths(date, months) {
    const next = new Date(date.getTime());
    const day = next.getDate();
    next.setMonth(next.getMonth() + months);
    if (next.getDate() !== day) next.setDate(0);
    return next;
  }

  function cuotaRows() {
    return Array.from(cuotasBody.querySelectorAll('.cuota-row'));
  }

  function updateCuotasSuma() {
    const suma = cuotaRows().reduce((acc, row) => {
      return acc + (parseFloat(row.querySelector('.cuota-monto')?.value || '0') || 0);
    }, 0);
    cuotasSuma.textContent = `${invoiceCurrency} ${suma.toFixed(2)}`;
  }

  function renumberCuotas() {
    cuotaRows().forEach((row, idx) => {
      row.querySelector('.cuota-num').textContent = idx + 1;
      const fecha = row.querySelector('.cuota-fecha');
      const monto = row.querySelector('.cuota-monto');
      if (fecha) fecha.name = `lista_cuotas[${idx}][fecha_pago]`;
      if (monto) monto.name = `lista_cuotas[${idx}][monto]`;
    });
  }

  function distributeCuotas() {
    const rows = cuotaRows();
    if (!rows.length) {
      updateCuotasSuma();
      return;
    }
    const base = Math.floor((invoiceTotal / rows.length) * 100) / 100;
    let assigned = 0;
    rows.forEach((row, idx) => {
      const amount = idx === rows.length - 1
        ? Math.round((invoiceTotal - assigned) * 100) / 100
        : base;
      assigned += amount;
      const monto = row.querySelector('.cuota-monto');
      const fecha = row.querySelector('.cuota-fecha');
      if (monto) monto.value = amount.toFixed(2);
      if (fecha && !fecha.value) fecha.value = toIsoDate(addMonths(new Date(), idx + 1));
    });
    updateCuotasSuma();
  }

  function addCuotaRow() {
    if (cuotaRows().length >= maxCuotas) return;
    const idx = cuotaRows().length;
    const row = document.createElement('div');
    row.className = 'cuota-row';
    row.style.cssText = 'display:grid; grid-template-columns:32px minmax(140px,1fr) minmax(140px,1fr) 38px; gap:.5rem; align-items:center;';
    row.innerHTML = `
      <span class="cuota-num" style="text-align:center; color:#64748b; font-weight:700;">${idx + 1}</span>
      <input type="date" name="lista_cuotas[${idx}][fecha_pago]" class="form-input cuota-fecha">
      <input type="number" name="lista_cuotas[${idx}][monto]" class="form-input cuota-monto" step="0.01" min="0.01" placeholder="Monto">
      <button type="button" class="cuota-remove" style="height:38px; border:none; border-radius:8px; background:#dcfce7; color:#166534; cursor:pointer;"><i class='bx bx-trash'></i></button>
    `;
    cuotasBody.appendChild(row);
    row.querySelector('.cuota-remove').addEventListener('click', () => {
      row.remove();
      renumberCuotas();
      distributeCuotas();
    });
    row.querySelector('.cuota-monto').addEventListener('input', updateCuotasSuma);
    distributeCuotas();
  }

  function toggleCuotas() {
    if (formaPago.value === '2') {
      cuotasSection.style.display = 'block';
      if (!cuotaRows().length) addCuotaRow();
      distributeCuotas();
    } else {
      cuotasSection.style.display = 'none';
    }
  }

  cuotaRows().forEach(row => {
    row.querySelector('.cuota-remove')?.addEventListener('click', () => {
      row.remove();
      renumberCuotas();
      distributeCuotas();
    });
    row.querySelector('.cuota-monto')?.addEventListener('input', updateCuotasSuma);
  });

  formaPago?.addEventListener('change', toggleCuotas);
  cuotaAdd?.addEventListener('click', addCuotaRow);
  toggleCuotas();

  if (form) {
    form.addEventListener('submit', function(e) {
      if (formaPago?.value !== '2') return;
      const suma = cuotaRows().reduce((acc, row) => acc + (parseFloat(row.querySelector('.cuota-monto')?.value || '0') || 0), 0);
      if (Math.abs(suma - invoiceTotal) > 0.01) {
        e.preventDefault();
        alert(`La suma de cuotas debe ser ${invoiceCurrency} ${invoiceTotal.toFixed(2)}.`);
      }
    });
  } else {
    console.error('Formulario no encontrado.');
  }
</script>

@endsection
