@extends('layouts.app')

@section('title', 'Nueva Nota de Crédito/Débito')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .module-form input, .module-form select, .module-form textarea { padding:.55rem .85rem; border:1px solid var(--clr-border-light, #e5e7eb); border-radius:8px; font-size:.9rem; font-family: inherit; color: var(--clr-text-main, #111827); background: transparent; outline: none; transition: all 0.2s ease; width: 100%; }
    .btn-action-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: rgba(0,0,0,0.04); color: var(--clr-text-main, #374151); transition: all 0.2s; text-decoration: none; font-size: 1.15rem; }
    .btn-action-icon:hover { background: rgba(0,0,0,0.08); color: var(--clr-active-bg, #1a6b57); transform: translateY(-2px); }
    .item-row { background: var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:8px; padding:1rem; margin-bottom:1rem; }
    .totals-row { background: rgba(16,185,129,.08); padding:1.25rem; border-radius:8px; }
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
          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <h1 style="display:flex; align-items:center; gap:0.5rem;"><i class='bx bx-plus-circle' style="color:var(--clr-text-main);"></i> Nueva Nota de Crédito/Débito</h1>
            </div>

            @if($errors->any())
              <div class="alert alert-danger mb-4">
                <ul style="margin:0; padding-left:1.5rem;">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div style="display:grid; grid-template-columns:1fr 350px; gap:1.5rem;">
              <!-- Formulario -->
              <form action="{{ route('facturador.credit-debit-notes.store') }}" method="POST" class="module-form">
                @csrf

                <!-- Factura Original -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Comprobante Original *</h6>
                  <div style="display:grid; gap:1rem;">
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Factura o Boleta</label>
                      <select name="invoice_id" id="invoiceSelect" class="module-form" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($invoices as $id => $display)
                          <option value="{{ $id }}" {{ old('invoice_id') == $id ? 'selected' : '' }}>{{ $display }}</option>
                        @endforeach
                      </select>
                      @error('invoice_id')
                        <small style="color:#ef4444;">{{ $message }}</small>
                      @enderror
                    </div>
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Tipo de Nota</label>
                      <select name="codigo_tipo_nota" class="module-form" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($notaTypes as $code => $label)
                          <option value="{{ $code }}" {{ old('codigo_tipo_nota') == $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                      </select>
                      @error('codigo_tipo_nota')
                        <small style="color:#ef4444;">{{ $message }}</small>
                      @enderror
                    </div>
                  </div>
                </div>

                <!-- Datos de la Nota -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Datos de la Nota</h6>
                  <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; margin-bottom:1rem;">
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Serie</label>
                      <input type="text" name="serie_documento" class="module-form" value="{{ old('serie_documento', $suggestions['serie']) }}" required>
                    </div>
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Número</label>
                      <input type="text" name="numero_documento" class="module-form" value="{{ old('numero_documento', $suggestions['numero']) }}" required>
                    </div>
                  </div>
                  <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:1rem;">
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Código Interno</label>
                      <input type="text" class="module-form" value="{{ $suggestions['codigo_interno'] }}" readonly>
                    </div>
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Fecha Emisión</label>
                      <input type="date" name="fecha_emision" class="module-form" value="{{ old('fecha_emision', now()->format('Y-m-d')) }}" required>
                    </div>
                  </div>
                </div>

                <!-- Observación y Correo -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <div style="display:grid; gap:1rem;">
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Observación</label>
                      <textarea name="observacion" class="module-form" rows="3" placeholder="(Opcional)">{{ old('observacion') }}</textarea>
                    </div>
                    <div>
                      <label style="display:block; font-size:.9rem; font-weight:600; margin-bottom:.4rem; color:var(--clr-text-muted);">Correo</label>
                      <input type="email" name="correo" class="module-form" value="{{ old('correo') }}" placeholder="(Opcional)">
                    </div>
                  </div>
                </div>

                <!-- Items -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Items de la Nota *</h6>
                  <div id="itemsContainer" style="margin-bottom:1rem;"></div>
                  <button type="button" class="btn-secondary" onclick="addItem()" style="font-size:.85rem;">
                    <i class='bx bx-plus'></i> Agregar Item
                  </button>
                </div>

                <!-- Botones -->
                <div style="display:flex; gap:1rem;">
                  <button type="submit" class="btn-primary" style="flex:1;">
                    <i class='bx bx-save'></i> Guardar Nota
                  </button>
                  <a href="{{ route('facturador.credit-debit-notes.index') }}" class="btn-secondary" style="flex:1; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                    <i class='bx bx-x'></i> Cancelar
                  </a>
                </div>
              </form>

              <!-- Sidebar -->
              <div>
                <!-- Resumen de Factura -->
                <div id="invoiceResume" style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; display:none;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Factura Original</h6>
                  <div id="invoiceResumeContent" style="font-size:.9rem; line-height:1.6;">
                    <!-- Se llena con AJAX -->
                  </div>
                </div>

                <!-- Totales -->
                <div class="totals-row">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Totales</h6>
                  <div style="font-size:.85rem; line-height:1.8;">
                    <div style="display:flex; justify-content:space-between;">
                      <span>Gravado:</span>
                      <strong>S/. <span id="totalGravado">0.00</span></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                      <span>IGV (18%):</span>
                      <strong>S/. <span id="totalIGV">0.00</span></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                      <span>Inafecto:</span>
                      <strong>S/. <span id="totalInafecto">0.00</span></strong>
                    </div>
                    <hr style="margin:0.75rem 0; border:0; border-top:1px solid rgba(0,0,0,.1);">
                    <div style="display:flex; justify-content:space-between; font-size:1.1rem;">
                      <span style="font-weight:700;">TOTAL:</span>
                      <strong style="color:var(--clr-active-bg, #1a6b57);">S/. <span id="totalNota">0.00</span></strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    let itemCount = 0;

    document.getElementById('invoiceSelect').addEventListener('change', async function() {
      if (!this.value) {
        document.getElementById('invoiceResume').style.display = 'none';
        return;
      }
      try {
        const response = await fetch(`/api/invoices/${this.value}`);
        if (response.ok) {
          const invoice = await response.json();
          document.getElementById('invoiceResume').style.display = 'block';
          document.getElementById('invoiceResumeContent').innerHTML = `
            <div><strong>${invoice.serie_documento}-${invoice.numero_documento}</strong></div>
            <div style="color:var(--clr-text-muted); font-size:.8rem;">${invoice.nombre_razon_social_adquiriente}</div>
            <hr style="margin:0.5rem 0; border:0; border-top:1px solid rgba(0,0,0,.1);">
            <div>Total: <strong>S/. ${parseFloat(invoice.monto_total).toFixed(2)}</strong></div>
          `;
        }
      } catch (error) {
        console.log('API no disponible aún');
      }
    });

    function addItem() {
      const container = document.getElementById('itemsContainer');
      const index = itemCount++;
      const itemHTML = `
        <div class="item-row" id="item-${index}">
          <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:1rem;">
            <div>
              <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.3rem;">Descripción</label>
              <input type="text" name="items[${index}][descripcion]" class="module-form" required>
            </div>
            <div>
              <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.3rem;">Cantidad</label>
              <input type="number" step="0.01" name="items[${index}][cantidad]" class="module-form" value="1" onchange="calcularTotales()" required>
            </div>
            <div>
              <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.3rem;">Precio Unit.</label>
              <input type="number" step="0.01" name="items[${index}][monto_precio_unitario]" class="module-form" onchange="calcularTotales()" required>
            </div>
          </div>
          <div style="margin-top:1rem;">
            <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.3rem;">Tipo (Gravado/Inafecto)</label>
            <select name="items[${index}][codigo_indicador_afecto]" class="module-form" onchange="calcularTotales()" required>
              <option value="01">Gravado (IGV 18%)</option>
              <option value="02">Inafecto</option>
            </select>
          </div>
          <button type="button" class="btn-action-icon" style="margin-top:1rem; color:#ef4444;" onclick="removeItem(${index})">
            <i class='bx bx-trash'></i> Quitar
          </button>
        </div>
      `;
      container.insertAdjacentHTML('beforeend', itemHTML);
    }

    function removeItem(index) {
      const item = document.getElementById(`item-${index}`);
      if (item) {
        item.remove();
        calcularTotales();
      }
    }

    function calcularTotales() {
      let gravado = 0, inafecto = 0;
      document.querySelectorAll('.item-row').forEach(row => {
        const cantidad = parseFloat(row.querySelector('input[name*="cantidad"]').value) || 0;
        const precio = parseFloat(row.querySelector('input[name*="monto_precio_unitario"]').value) || 0;
        const tipo = row.querySelector('select[name*="codigo_indicador_afecto"]').value;
        const total = cantidad * precio;
        
        if (tipo === '01') gravado += total;
        else inafecto += total;
      });
      
      const igv = gravado * 0.18;
      const total = gravado + igv + inafecto;
      
      document.getElementById('totalGravado').textContent = gravado.toFixed(2);
      document.getElementById('totalIGV').textContent = igv.toFixed(2);
      document.getElementById('totalInafecto').textContent = inafecto.toFixed(2);
      document.getElementById('totalNota').textContent = total.toFixed(2);
    }

    window.addEventListener('load', () => {
      addItem();
      calcularTotales();
    });
  </script>
@endpush
