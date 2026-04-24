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

<script>
  console.log('Preview page loaded');
  console.log('Purchase:', {!! json_encode($purchase->only(['id', 'status', 'client_id', 'serie_numero'])) !!});
  console.log('Addresses count:', {{ count($addresses) }});
  console.log('Preview data:', {!! json_encode($preview) !!});

  // Recalculo en tiempo real al editar precio
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
</script>

@endsection
