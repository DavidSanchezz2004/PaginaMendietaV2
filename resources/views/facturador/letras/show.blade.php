@extends('layouts.app')

@section('title', 'Letra ' . $letra->numero_letra . ' — Detalle')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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
        <div class="placeholder-content module-card-wide" style="padding:1.5rem;">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-0">
        <i class='bx bx-file me-2'></i>Letra {{ $letra->numero_letra }}
      </h4>
      <small class="text-muted">
        Ref: {{ $letra->referencia ?? '—' }}
        @if($letra->purchase)
          · Compra #{{ $letra->purchase_id }}
        @endif
      </small>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('facturador.letras.imprimir', $letra) }}"
         class="btn btn-outline-primary btn-sm" target="_blank">
        <i class='bx bx-printer me-1'></i>Imprimir
      </a>
      <a href="{{ route('facturador.letras.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class='bx bx-arrow-back me-1'></i>Volver
      </a>
    </div>
  </div>

  <div class="row g-4">
    {{-- Panel principal: datos --}}
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom fw-semibold py-3">
          <i class='bx bx-detail me-2'></i>Datos de la Letra
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-sm-4">
              <div class="text-muted small">Número</div>
              <div class="fw-bold font-monospace">{{ $letra->numero_letra }}</div>
            </div>
            <div class="col-sm-4">
              <div class="text-muted small">Lugar de Giro</div>
              <div class="fw-semibold">{{ $letra->lugar_giro }}</div>
            </div>
            <div class="col-sm-4">
              <div class="text-muted small">Fecha de Giro</div>
              <div class="fw-semibold">{{ $letra->fecha_giro->format('d/m/Y') }}</div>
            </div>
            <div class="col-sm-4">
              <div class="text-muted small">Vencimiento</div>
              <div class="fw-bold {{ $letra->esta_vencida ? 'text-danger' : '' }}">
                {{ $letra->fecha_vencimiento->format('d/m/Y') }}
                @if($letra->esta_vencida)
                  <span class="badge bg-danger ms-1">VENCIDA</span>
                @endif
              </div>
            </div>
            <div class="col-sm-4">
              <div class="text-muted small">Moneda / Importe</div>
              <div class="fw-bold fs-5">{{ $letra->codigo_moneda }} {{ number_format($letra->monto, 2) }}</div>
            </div>
            <div class="col-sm-4">
              <div class="text-muted small">Estado</div>
              <span class="badge {{ $letra->estado_badge_class }} fs-6">{{ $letra->estado_label }}</span>
            </div>
            <div class="col-12">
              <div class="text-muted small">Importe en letras</div>
              <div class="fw-semibold">{{ $letra->monto_letras }}</div>
            </div>
          </div>

          <hr>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="text-muted small fw-semibold mb-1">TENEDOR (quien cobra)</div>
              <div class="fw-bold">{{ $letra->tenedor_nombre }}</div>
              @if($letra->tenedor_ruc)
                <div class="small text-muted">RUC: {{ $letra->tenedor_ruc }}</div>
              @endif
              @if($letra->tenedor_domicilio)
                <div class="small text-muted">{{ $letra->tenedor_domicilio }}</div>
              @endif
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold mb-1">ACEPTANTE (quien paga)</div>
              <div class="fw-bold">{{ $letra->aceptante_nombre }}</div>
              @if($letra->aceptante_ruc)
                <div class="small text-muted">RUC: {{ $letra->aceptante_ruc }}</div>
              @endif
              @if($letra->aceptante_domicilio)
                <div class="small text-muted">{{ $letra->aceptante_domicilio }}</div>
              @endif
              @if($letra->aceptante_telefono)
                <div class="small text-muted">Tel: {{ $letra->aceptante_telefono }}</div>
              @endif
            </div>
          </div>

          @if($letra->banco)
            <hr>
            <div class="row g-3">
              <div class="col-12"><div class="text-muted small fw-semibold mb-1">DATOS BANCARIOS</div></div>
              <div class="col-sm-3"><div class="text-muted small">Banco</div><div>{{ $letra->banco }}</div></div>
              <div class="col-sm-3"><div class="text-muted small">Oficina</div><div>{{ $letra->banco_oficina ?? '—' }}</div></div>
              <div class="col-sm-4"><div class="text-muted small">Cuenta</div><div class="font-monospace">{{ $letra->banco_cuenta ?? '—' }}</div></div>
              <div class="col-sm-2"><div class="text-muted small">D.C.</div><div>{{ $letra->banco_dc ?? '—' }}</div></div>
            </div>
          @endif

          <hr>
          <div class="row g-3">
            <div class="col-sm-4">
              <div class="text-muted small">Cuenta contable</div>
              <div class="fw-semibold font-monospace">{{ $letra->cuenta_contable }}</div>
            </div>
            <div class="col-sm-8">
              <div class="text-muted small">Observaciones</div>
              <div>{{ $letra->observaciones ?? '—' }}</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Historial de pagos --}}
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
          <span class="fw-semibold"><i class='bx bx-history me-2'></i>Historial de Pagos</span>
          @if($letra->estado === 'pendiente')
            <button type="button" class="btn btn-success btn-sm" id="btnAbrirPago">
              <i class='bx bx-plus me-1'></i>Registrar Pago
            </button>
          @endif
        </div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Fecha</th>
                <th class="text-end">Monto Pagado</th>
                <th>Medio</th>
                <th>Referencia</th>
                <th>Usuario</th>
              </tr>
            </thead>
            <tbody>
              @forelse($letra->pagos as $pago)
                <tr>
                  <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                  <td class="text-end fw-bold text-success">{{ $letra->codigo_moneda }} {{ number_format($pago->monto_pagado, 2) }}</td>
                  <td>{{ $pago->medio_pago_label }}</td>
                  <td class="text-muted small">{{ $pago->referencia_pago ?? '—' }}</td>
                  <td class="text-muted small">{{ $pago->user?->name ?? '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-3">Sin pagos registrados.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Panel lateral: saldo + compra origen --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-4">
          <div class="text-muted small mb-1">Saldo pendiente</div>
          <div class="display-6 fw-bold {{ $letra->saldo == 0 ? 'text-success' : 'text-dark' }}">
            {{ $letra->codigo_moneda }} {{ number_format($letra->saldo, 2) }}
          </div>
          <div class="mt-2 text-muted small">
            Pagado: {{ $letra->codigo_moneda }} {{ number_format($letra->monto_pagado, 2) }}
            de {{ number_format($letra->monto, 2) }}
          </div>
          @if($letra->monto > 0)
            <div class="progress mt-3" style="height:8px;">
              <div class="progress-bar bg-success"
                   style="width:{{ min(100, round($letra->monto_pagado / $letra->monto * 100)) }}%"></div>
            </div>
          @endif
        </div>
      </div>

      @if($letra->purchase)
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-bottom py-3 fw-semibold">
            <i class='bx bx-link me-2'></i>Compra Origen
          </div>
          <div class="card-body">
            <div class="text-muted small">Documento</div>
            <div class="fw-bold">{{ $letra->purchase->serie_numero }}</div>
            <div class="text-muted small mt-2">Proveedor</div>
            <div>{{ $letra->purchase->razon_social_proveedor }}</div>
            <div class="text-muted small">RUC: {{ $letra->purchase->numero_doc_proveedor }}</div>
            <div class="text-muted small mt-2">Fecha emisión</div>
            <div>{{ $letra->purchase->fecha_emision?->format('d/m/Y') }}</div>
            <div class="text-muted small mt-2">Total compra</div>
            <div class="fw-bold">{{ $letra->purchase->codigo_moneda }} {{ number_format($letra->purchase->monto_total, 2) }}</div>
            <a href="{{ route('facturador.compras.show', $letra->purchase_id) }}"
               class="btn btn-outline-primary btn-sm mt-3 w-100">
              Ver compra completa
            </a>
          </div>
        </div>
      @endif
    </div>
  </div>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}

{{-- Modal Pago --}}
<div class="modal fade" id="modalPago" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Pago — Letra {{ $letra->numero_letra }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Fecha de pago</label>
          <input type="date" id="pagoFecha" class="form-control" value="{{ now()->toDateString() }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Monto pagado <span class="text-muted small">(saldo: {{ $letra->codigo_moneda }} {{ number_format($letra->saldo, 2) }})</span></label>
          <input type="number" id="pagoMonto" class="form-control" step="0.01"
                 value="{{ $letra->saldo }}" min="0.01" max="{{ $letra->saldo }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Medio de pago</label>
          <select id="pagoMedio" class="form-select">
            <option value="transferencia">Transferencia</option>
            <option value="efectivo">Efectivo</option>
            <option value="cheque">Cheque</option>
            <option value="yape">Yape</option>
            <option value="plin">Plin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Referencia / N° operación</label>
          <input type="text" id="pagoReferencia" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarPago">Confirmar Pago</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('btnAbrirPago')?.addEventListener('click', () => {
    new bootstrap.Modal(document.getElementById('modalPago')).show();
  });

  document.getElementById('btnConfirmarPago')?.addEventListener('click', async function () {
    this.disabled = true;
    const res = await fetch('{{ route("facturador.letras.pago", $letra) }}', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({
        fecha_pago:      document.getElementById('pagoFecha').value,
        monto_pagado:    document.getElementById('pagoMonto').value,
        medio_pago:      document.getElementById('pagoMedio').value,
        referencia_pago: document.getElementById('pagoReferencia').value,
      }),
    });
    const data = await res.json();
    if (data.ok) { location.reload(); }
    else { Swal.fire({icon:'error', title:'Error', text: JSON.stringify(data)}); this.disabled = false; }
  });
</script>
@endpush
