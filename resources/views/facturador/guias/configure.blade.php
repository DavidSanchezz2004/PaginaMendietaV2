@extends('layouts.app')

@section('title', 'Configurar y Generar Guía')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .step-indicator {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .step {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .step-number {
      width: 32px;
      height: 32px;
      background: #e2e8f0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      color: #6b7280;
    }

    .step.active .step-number {
      background: var(--clr-active-bg, #1a6b57);
      color: white;
    }

    .step-label {
      font-weight: 600;
      font-size: 0.9rem;
    }

    .form-section {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .form-section-title {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      font-size: 0.85rem;
      font-weight: 600;
      display: block;
      margin-bottom: 0.4rem;
      color: var(--clr-text-main, #374151);
    }

    .form-input,
    .form-select,
    .form-textarea {
      width: 100%;
      padding: 0.65rem 0.85rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.9rem;
      outline: none;
      font-family: inherit;
      box-sizing: border-box;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
      border-color: var(--clr-active-bg, #1a6b57);
      box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1);
    }

    .form-textarea {
      resize: vertical;
      min-height: 80px;
    }

    .address-selector {
      display: grid;
      gap: 0.75rem;
    }

    .address-option {
      padding: 1rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: flex-start;
      gap: 1rem;
    }

    .address-option:hover {
      border-color: var(--clr-active-bg, #1a6b57);
      background: rgba(26, 107, 87, 0.02);
    }

    .address-option input {
      margin-top: 0.15rem;
    }

    .address-info {
      flex: 1;
    }

    .address-street {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .address-city {
      font-size: 0.85rem;
      color: var(--clr-text-muted, #6b7280);
    }

    .address-badge {
      display: inline-block;
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
      padding: 0.25rem 0.6rem;
      border-radius: 4px;
      font-size: 0.7rem;
      font-weight: 600;
      margin-top: 0.5rem;
    }

    .summary-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .summary-row:last-child {
      border-bottom: none;
      font-weight: 700;
      font-size: 1.05rem;
      color: var(--clr-active-bg, #1a6b57);
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
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
                <i class='bx bx-file-blank'></i> Generar Guía de Remisión
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Configura los detalles de la guía</small>
            </div>
            <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
              <i class='bx bx-arrow-back'></i> Volver
            </a>
          </div>

          {{-- Indicador de pasos --}}
          <div class="step-indicator">
            <div class="step active">
              <div class="step-number">1</div>
              <div class="step-label">Información Compra</div>
            </div>
            <div class="step active">
              <div class="step-number">2</div>
              <div class="step-label">Dirección Entrega</div>
            </div>
            <div class="step active">
              <div class="step-number">3</div>
              <div class="step-label">Confirmar</div>
            </div>
          </div>

          {{-- Errores de validación --}}
          @if($errors->any())
            <div style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#991b1b; font-size:.9rem;">
              <i class='bx bx-x-circle' style="margin-right:.4rem;"></i>
              <strong>Errores encontrados:</strong>
              <ul style="margin:.5rem 0 0 0; padding-left:1.5rem;">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('facturador.compras.guia.generate', $purchase) }}" id="formGenerateGuia">
            @csrf

            {{-- Sección 1: Información de la Compra --}}
            <div class="form-section">
              <h3 class="form-section-title">
                <i class='bx bx-shopping-bag'></i> Información de la Compra
              </h3>

              <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div>
                  <label class="form-label">Compra</label>
                  <input type="text" value="{{ $purchase->serie_numero }}" disabled
                         style="width:100%; padding:.65rem .85rem; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; cursor:not-allowed;">
                </div>
                <div>
                  <label class="form-label">Proveedor</label>
                  <input type="text" value="{{ $purchase->provider->nombre_razon_social }}" disabled
                         style="width:100%; padding:.65rem .85rem; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; cursor:not-allowed;">
                </div>
                <div>
                  <label class="form-label">Total</label>
                  <input type="text" value="{{ $purchase->codigo_moneda }} {{ number_format($purchase->monto_total, 2) }}" disabled
                         style="width:100%; padding:.65rem .85rem; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; cursor:not-allowed; font-weight:600;">
                </div>
                <div>
                  <label class="form-label">Fecha Emisión</label>
                  <input type="text" value="{{ $purchase->fecha_emision->format('d/m/Y') }}" disabled
                         style="width:100%; padding:.65rem .85rem; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; cursor:not-allowed;">
                </div>
              </div>

              {{-- Items de compra --}}
              <div style="margin-top:1.5rem;">
                <label class="form-label">Items ({{ $purchase->items->count() }})</label>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1rem; max-height:250px; overflow-y:auto;">
                  @foreach($purchase->items as $item)
                    <div style="padding:.5rem 0; border-bottom:1px solid #e2e8f0; font-size:.9rem;">
                      <div style="font-weight:600;">{{ $item->description }}</div>
                      <div style="color:var(--clr-text-muted,#6b7280); font-size:.85rem;">
                        {{ number_format($item->quantity, 2) }} {{ $item->unit }} × {{ number_format($item->unit_price, 2) }}
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>

            {{-- Sección 2: Dirección de Entrega --}}
            <div class="form-section">
              <h3 class="form-section-title">
                <i class='bx bx-map-pin'></i> Dirección de Entrega
              </h3>

              <div class="address-selector">
                @foreach($purchase->client->addresses as $address)
                  <label class="address-option">
                    <input type="radio" name="client_address_id" value="{{ $address->id }}"
                           @if($address->is_default) checked @endif required>
                    <div class="address-info">
                      <div class="address-street">{{ $address->street }}</div>
                      <div class="address-city">
                        {{ $address->city }}@if($address->state), {{ $address->state }}@endif
                      </div>
                      @if($address->postal_code)
                        <div style="font-size:.8rem; color:var(--clr-text-muted,#6b7280);">
                          CP: {{ $address->postal_code }}
                        </div>
                      @endif
                      @if($address->is_default)
                        <div class="address-badge">Predeterminada</div>
                      @endif
                    </div>
                  </label>
                @endforeach
              </div>
            </div>

            {{-- Sección 3: Detalles de la Guía --}}
            <div class="form-section">
              <h3 class="form-section-title">
                <i class='bx bx-file'></i> Detalles de la Guía
              </h3>

              <div class="form-group">
                <label class="form-label">Motivo del Traslado</label>
                <input type="text" name="motivo" class="form-input" placeholder="Ej: Venta, Devolución, Traslado"
                       value="{{ old('motivo', 'Venta') }}" maxlength="100" required>
                <small style="color:var(--clr-text-muted,#6b7280); font-size:.8rem;">Indica el motivo del traslado de la mercancía</small>
              </div>

              <div class="form-group">
                <label class="form-label">Observaciones (opcional)</label>
                <textarea name="observaciones" class="form-textarea" placeholder="Notas adicionales sobre el traslado..."></textarea>
              </div>
            </div>

            {{-- Resumen --}}
            <div class="summary-box">
              <div class="summary-row">
                <span>Cliente:</span>
                <strong>{{ $purchase->client->nombre_razon_social }}</strong>
              </div>
              <div class="summary-row">
                <span>Total Items:</span>
                <strong>{{ $purchase->items->count() }}</strong>
              </div>
              <div class="summary-row">
                <span>Total Compra:</span>
                <strong>{{ $purchase->codigo_moneda }} {{ number_format($purchase->monto_total, 2) }}</strong>
              </div>
            </div>

            {{-- Botones de acción --}}
            <div class="form-actions">
              <button type="submit" class="btn-primary" style="padding:.6rem 2rem;">
                <i class='bx bx-check'></i> Generar Guía
              </button>
              <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary" style="padding:.6rem 1.5rem; text-decoration:none;">
                Cancelar
              </a>
            </div>
          </form>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection

@push('scripts')
<script>
  // Validar que se seleccione una dirección
  document.getElementById('formGenerateGuia').addEventListener('submit', function(e) {
    const selectedAddress = document.querySelector('input[name="client_address_id"]:checked');
    if (!selectedAddress) {
      e.preventDefault();
      alert('Por favor, selecciona una dirección de entrega');
    }
  });
</script>
@endpush
