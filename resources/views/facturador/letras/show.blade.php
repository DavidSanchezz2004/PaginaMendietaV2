@extends('layouts.app')

@section('title', 'Letra ' . $letra->numero_letra . ' | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .letter-page { display:grid; gap:1rem; }
    .letter-head { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
    .letter-title { display:flex; align-items:center; gap:.65rem; margin:0; color:var(--clr-text-main,#111827); font-size:1.45rem; }
    .letter-title i { width:38px; height:38px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; background:#e7f3ef; color:#16614f; }
    .letter-subtitle { margin:.3rem 0 0; color:var(--clr-text-muted,#64748b); font-size:.86rem; }
    .letter-actions { display:flex; gap:.55rem; flex-wrap:wrap; justify-content:flex-end; }
    .letter-grid { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:1rem; align-items:start; }
    .letter-card { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; overflow:hidden; }
    .letter-card__head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; padding:.85rem 1rem; border-bottom:1px solid var(--clr-border-light,#e5e7eb); background:rgba(15,23,42,.02); }
    .letter-card__head h2 { margin:0; display:flex; align-items:center; gap:.45rem; color:var(--clr-text-main,#111827); font-size:.98rem; }
    .letter-card__body { padding:1rem; }
    .info-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.85rem; }
    .info-item span { display:block; color:var(--clr-text-muted,#64748b); font-size:.73rem; text-transform:uppercase; font-weight:800; letter-spacing:.03em; }
    .info-item strong { display:block; margin-top:.18rem; color:var(--clr-text-main,#111827); font-size:.95rem; overflow-wrap:anywhere; }
    .party-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--clr-border-light,#e5e7eb); }
    .party-box { border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px; padding:.85rem; background:rgba(15,23,42,.015); }
    .party-box h3 { margin:0 0 .55rem; color:var(--clr-text-muted,#64748b); font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
    .party-box strong { display:block; color:var(--clr-text-main,#111827); margin-bottom:.25rem; }
    .muted-line { color:var(--clr-text-muted,#64748b); font-size:.84rem; line-height:1.45; }
    .status-pill { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.32rem .7rem; font-size:.76rem; font-weight:800; text-transform:uppercase; }
    .status-pending { background:#fef3c7; color:#92400e; }
    .status-paid { background:#dcfce7; color:#166534; }
    .status-danger { background:#fee2e2; color:#991b1b; }
    .saldo-card { text-align:center; padding:1.25rem; }
    .saldo-label { color:var(--clr-text-muted,#64748b); font-size:.78rem; font-weight:800; text-transform:uppercase; }
    .saldo-value { margin:.35rem 0; color:var(--clr-text-main,#111827); font-size:2rem; font-weight:900; }
    .saldo-value.is-clear { color:#059669; }
    .progress-track { height:8px; background:#e5e7eb; border-radius:999px; overflow:hidden; margin-top:.85rem; }
    .progress-fill { height:100%; background:#059669; border-radius:999px; }
    .letter-table-wrap { overflow:auto; }
    .letter-table { width:100%; border-collapse:collapse; font-size:.86rem; }
    .letter-table th { text-align:left; padding:.65rem .75rem; color:var(--clr-text-muted,#64748b); background:rgba(15,23,42,.03); text-transform:uppercase; font-size:.72rem; letter-spacing:.04em; border-bottom:1px solid var(--clr-border-light,#e5e7eb); }
    .letter-table td { padding:.7rem .75rem; border-bottom:1px solid var(--clr-border-light,#f1f5f9); color:var(--clr-text-main,#111827); vertical-align:middle; }
    .letter-table .num { text-align:right; font-weight:800; }
    .modal-backdrop-letter { position:fixed; inset:0; z-index:90; display:none; align-items:center; justify-content:center; padding:1rem; background:rgba(15,23,42,.55); }
    .modal-backdrop-letter.is-open { display:flex; }
    .pay-modal { width:min(520px,100%); background:var(--clr-bg-card,#fff); border-radius:10px; overflow:hidden; box-shadow:0 24px 80px rgba(15,23,42,.28); }
    .pay-modal__head { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.1rem; border-bottom:1px solid var(--clr-border-light,#e5e7eb); }
    .pay-modal__head h2 { margin:0; font-size:1rem; color:var(--clr-text-main,#111827); }
    .pay-modal__body { padding:1.1rem; display:grid; gap:.75rem; }
    .pay-modal__footer { display:flex; justify-content:flex-end; gap:.6rem; padding:1rem 1.1rem; border-top:1px solid var(--clr-border-light,#e5e7eb); background:rgba(15,23,42,.03); }
    .letter-field { display:flex; flex-direction:column; gap:.32rem; }
    .letter-field label { color:var(--clr-text-muted,#64748b); font-size:.76rem; font-weight:800; text-transform:uppercase; }
    .letter-input { min-height:40px; border:1px solid var(--clr-border-light,#d1d5db); border-radius:7px; padding:.55rem .7rem; background:transparent; color:var(--clr-text-main,#111827); }
    @media(max-width:960px){ .letter-grid { grid-template-columns:1fr; } }
    @media(max-width:720px){ .info-grid, .party-grid { grid-template-columns:1fr; } .letter-actions { justify-content:flex-start; } }
  </style>
@endpush

@section('content')
@php
  $statusClass = match ($letra->estado) {
    'cobrado', 'compensada' => 'status-paid',
    'protestado' => 'status-danger',
    default => $letra->esta_vencida ? 'status-danger' : 'status-pending',
  };
  $paidPercent = $letra->monto > 0 ? min(100, round($letra->monto_pagado / $letra->monto * 100)) : 0;
@endphp

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
      'userName' => auth()->user()?->name,
      'userEmail' => auth()->user()?->email,
    ])

    <main class="main-content">
      <div class="module-content-stack">
        <div class="placeholder-content module-card-wide letter-page">
          <div class="letter-head">
            <div>
              <h1 class="letter-title"><i class='bx bx-file'></i> Letra {{ $letra->numero_letra }}</h1>
              <p class="letter-subtitle">
                Referencia {{ $letra->referencia ?? '—' }}
                @if($letra->invoice)
                  · Factura {{ $letra->invoice->serie_numero }}
                @endif
              </p>
            </div>
            <div class="letter-actions">
              <a href="{{ route('facturador.letras.imprimir', $letra) }}" class="btn-secondary" target="_blank">
                <i class='bx bx-printer'></i> Imprimir
              </a>
              <a href="{{ route('facturador.letras.index', ['search' => $letra->referencia]) }}" class="btn-secondary">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
            </div>
          </div>

          <div class="letter-grid">
            <div class="letter-card">
              <div class="letter-card__head">
                <h2><i class='bx bx-detail'></i> Datos de la letra</h2>
                <span class="status-pill {{ $statusClass }}">{{ $letra->estado_label }}</span>
              </div>
              <div class="letter-card__body">
                <div class="info-grid">
                  <div class="info-item"><span>Número</span><strong>{{ $letra->numero_letra }}</strong></div>
                  <div class="info-item"><span>Lugar de giro</span><strong>{{ $letra->lugar_giro }}</strong></div>
                  <div class="info-item"><span>Fecha de giro</span><strong>{{ $letra->fecha_giro?->format('d/m/Y') }}</strong></div>
                  <div class="info-item"><span>Vencimiento</span><strong>{{ $letra->fecha_vencimiento?->format('d/m/Y') }}</strong></div>
                  <div class="info-item"><span>Moneda / importe</span><strong>{{ $letra->codigo_moneda }} {{ number_format($letra->monto, 2) }}</strong></div>
                  <div class="info-item"><span>Cuenta contable</span><strong>{{ $letra->cuenta_contable }}</strong></div>
                  <div class="info-item" style="grid-column:1/-1;"><span>Importe en letras</span><strong>{{ $letra->monto_letras }}</strong></div>
                </div>

                <div class="party-grid">
                  <div class="party-box">
                    <h3>Tenedor (quien cobra)</h3>
                    <strong>{{ $letra->tenedor_nombre }}</strong>
                    @if($letra->tenedor_ruc)<div class="muted-line">RUC: {{ $letra->tenedor_ruc }}</div>@endif
                    @if($letra->tenedor_domicilio)<div class="muted-line">{{ $letra->tenedor_domicilio }}</div>@endif
                  </div>
                  <div class="party-box">
                    <h3>Aceptante (quien paga)</h3>
                    <strong>{{ $letra->aceptante_nombre }}</strong>
                    @if($letra->aceptante_ruc)<div class="muted-line">RUC: {{ $letra->aceptante_ruc }}</div>@endif
                    @if($letra->aceptante_domicilio)<div class="muted-line">{{ $letra->aceptante_domicilio }}</div>@endif
                    @if($letra->aceptante_telefono)<div class="muted-line">Tel: {{ $letra->aceptante_telefono }}</div>@endif
                  </div>
                </div>

                <div class="party-box" style="margin-top:1rem;">
                  <h3>Observaciones</h3>
                  <div class="muted-line">{{ $letra->observaciones ?? '—' }}</div>
                </div>
              </div>
            </div>

            <aside>
              <div class="letter-card saldo-card">
                <div class="saldo-label">Saldo pendiente</div>
                <div class="saldo-value {{ $letra->saldo == 0 ? 'is-clear' : '' }}">
                  {{ $letra->codigo_moneda }} {{ number_format($letra->saldo, 2) }}
                </div>
                <div class="muted-line">
                  Pagado: {{ $letra->codigo_moneda }} {{ number_format($letra->monto_pagado, 2) }}
                  de {{ number_format($letra->monto, 2) }}
                </div>
                <div class="progress-track"><div class="progress-fill" style="width:{{ $paidPercent }}%;"></div></div>
                @if(in_array($letra->estado, ['pendiente', 'compensada_parcial'], true))
                  <button type="button" class="btn-primary" id="btnAbrirPago" style="margin-top:1rem;">
                    <i class='bx bx-plus'></i> Registrar pago
                  </button>
                @endif
              </div>

              @if($letra->invoice)
                <div class="letter-card" style="margin-top:1rem;">
                  <div class="letter-card__head"><h2><i class='bx bx-link'></i> Factura origen</h2></div>
                  <div class="letter-card__body">
                    <div class="info-item"><span>Documento</span><strong>{{ $letra->invoice->serie_numero }}</strong></div>
                    <div class="info-item" style="margin-top:.65rem;"><span>Cliente</span><strong>{{ $letra->invoice->client?->nombre_razon_social ?? $letra->aceptante_nombre }}</strong></div>
                    <a href="{{ route('facturador.invoices.show', $letra->invoice) }}" class="btn-secondary" style="width:100%; justify-content:center; margin-top:1rem;">
                      Ver factura
                    </a>
                  </div>
                </div>
              @endif
            </aside>
          </div>

          <div class="letter-card">
            <div class="letter-card__head">
              <h2><i class='bx bx-history'></i> Historial de pagos</h2>
            </div>
            <div class="letter-table-wrap">
              <table class="letter-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th class="num">Monto pagado</th>
                    <th>Medio</th>
                    <th>Referencia</th>
                    <th>Usuario</th>
                    <th>Observación</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($letra->pagos as $pago)
                    <tr>
                      <td>{{ $pago->fecha_pago?->format('d/m/Y') }}</td>
                      <td class="num">{{ $letra->codigo_moneda }} {{ number_format($pago->monto_pagado, 2) }}</td>
                      <td>{{ $pago->medio_pago_label }}</td>
                      <td>{{ $pago->referencia_pago ?? '—' }}</td>
                      <td>{{ $pago->user?->name ?? '—' }}</td>
                      <td>{{ $pago->observaciones ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="6" style="text-align:center; color:var(--clr-text-muted,#64748b); padding:1.5rem;">Sin pagos registrados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          @if($letra->compensations->isNotEmpty())
            <div class="letter-card">
              <div class="letter-card__head"><h2><i class='bx bx-transfer'></i> Endosos / compensaciones</h2></div>
              <div class="letter-table-wrap">
                <table class="letter-table">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Proveedor</th>
                      <th>Factura compensada</th>
                      <th class="num">Monto</th>
                      <th>Observación</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($letra->compensations as $compensation)
                      @foreach($compensation->details as $detail)
                        <tr>
                          <td>{{ $compensation->compensation_date?->format('d/m/Y') }}</td>
                          <td>{{ $compensation->supplier?->nombre_display ?? '—' }}</td>
                          <td>{{ $detail->purchaseInvoice?->serie_numero ?? '—' }}</td>
                          <td class="num">{{ $compensation->currency }} {{ number_format($detail->amount, 2) }}</td>
                          <td>{{ $compensation->observation ?? '—' }}</td>
                        </tr>
                      @endforeach
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif
        </div>
      </div>
    </main>
  </section>
</div>

<div class="modal-backdrop-letter" id="modalPago">
  <div class="pay-modal">
    <div class="pay-modal__head">
      <h2>Registrar pago - Letra {{ $letra->numero_letra }}</h2>
      <button type="button" class="btn-action-icon" id="btnCerrarPago" aria-label="Cerrar"><i class='bx bx-x'></i></button>
    </div>
    <div class="pay-modal__body">
      <div class="letter-field">
        <label>Fecha de pago</label>
        <input type="date" id="pagoFecha" class="letter-input" value="{{ now()->toDateString() }}">
      </div>
      <div class="letter-field">
        <label>Monto pagado (saldo: {{ $letra->codigo_moneda }} {{ number_format($letra->saldo, 2) }})</label>
        <input type="number" id="pagoMonto" class="letter-input" step="0.01" value="{{ $letra->saldo }}" min="0.01" max="{{ $letra->saldo }}">
      </div>
      <div class="letter-field">
        <label>Medio de pago</label>
        <select id="pagoMedio" class="letter-input">
          <option value="transferencia">Transferencia</option>
          <option value="efectivo">Efectivo</option>
          <option value="cheque">Cheque</option>
          <option value="yape">Yape</option>
          <option value="plin">Plin</option>
        </select>
      </div>
      <div class="letter-field">
        <label>Referencia / Nro. operación</label>
        <input type="text" id="pagoReferencia" class="letter-input" maxlength="100">
      </div>
      <div class="letter-field">
        <label>Observación</label>
        <input type="text" id="pagoObservacion" class="letter-input" maxlength="500">
      </div>
    </div>
    <div class="pay-modal__footer">
      <button type="button" class="btn-secondary" id="btnCancelarPago">Cancelar</button>
      <button type="button" class="btn-primary" id="btnConfirmarPago">Confirmar pago</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const modalPago = document.getElementById('modalPago');
  const closePago = () => modalPago?.classList.remove('is-open');

  document.getElementById('btnAbrirPago')?.addEventListener('click', () => {
    modalPago?.classList.add('is-open');
  });
  document.getElementById('btnCerrarPago')?.addEventListener('click', closePago);
  document.getElementById('btnCancelarPago')?.addEventListener('click', closePago);
  modalPago?.addEventListener('click', (event) => {
    if (event.target === modalPago) closePago();
  });

  document.getElementById('btnConfirmarPago')?.addEventListener('click', async function () {
    this.disabled = true;
    const res = await fetch('{{ route("facturador.letras.pago", $letra) }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        fecha_pago: document.getElementById('pagoFecha').value,
        monto_pagado: document.getElementById('pagoMonto').value,
        medio_pago: document.getElementById('pagoMedio').value,
        referencia_pago: document.getElementById('pagoReferencia').value,
        observaciones: document.getElementById('pagoObservacion').value,
      }),
    });
    const data = await res.json();
    if (data.ok) {
      location.reload();
      return;
    }
    Swal.fire({icon:'error', title:'Error', text:data.message || 'No se pudo registrar el pago.'});
    this.disabled = false;
  });
</script>
@endpush
