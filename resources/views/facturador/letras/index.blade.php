@extends('layouts.app')

@section('title', 'Letras de Cambio — Facturador')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/facturador.css') }}">
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
    <h1 style="display:flex; align-items:center; gap:.5rem;">
      <i class='bx bx-file' style="color:var(--clr-text-main);"></i> Letras de Cambio
    </h1>
    <a href="{{ route('facturador.compras.index') }}" class="btn-secondary" style="font-size:.85rem; padding:.5rem .9rem;">
      <i class='bx bx-arrow-back'></i> Volver a Compras
    </a>
  </div>

  {{-- Alertas --}}
  @if(session('success'))
    <div class="placeholder-content module-alert" data-flash-message style="margin-bottom:1rem;">
      <p>{{ session('success') }}</p>
      <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
    </div>
  @endif

  {{-- Stats --}}
  <div class="stat-cards">
    <div class="stat-card sc-amber">
      <div class="stat-card__icon"><i class='bx bx-time-five'></i></div>
      <div class="stat-card__val">{{ number_format($stats['total_pendiente'], 2) }}</div>
      <div class="stat-card__lbl">Total Pendiente</div>
      <div class="stat-card__sub">{{ $stats['count_pendiente'] }} letra(s)</div>
    </div>
    <div class="stat-card sc-red">
      <div class="stat-card__icon"><i class='bx bx-error-circle'></i></div>
      <div class="stat-card__val">{{ $stats['count_vencidas'] }}</div>
      <div class="stat-card__lbl">Vencidas</div>
      <div class="stat-card__sub">Requieren gestión urgente</div>
    </div>
    <div class="stat-card sc-green">
      <div class="stat-card__icon"><i class='bx bx-check-circle'></i></div>
      <div class="stat-card__val">{{ number_format($stats['total_cobrado_mes'], 2) }}</div>
      <div class="stat-card__lbl">Cobrado este mes</div>
    </div>
    <div class="stat-card sc-blue">
      <div class="stat-card__icon"><i class='bx bxs-file-doc'></i></div>
      <div class="stat-card__val">{{ $letras->total() }}</div>
      <div class="stat-card__lbl">Total letras</div>
    </div>
  </div>

  {{-- Filtros --}}
  <form method="GET" action="{{ route('facturador.letras.index') }}" class="filter-bar">
    <i class='bx bx-filter-alt' style="font-size:1.25rem; color:var(--clr-text-muted);"></i>
    <input type="text" name="search" placeholder="Buscar por número, aceptante o RUC..."
           value="{{ $filters['search'] ?? '' }}" style="min-width:220px;">
    <select name="estado">
      <option value="">Todos los estados</option>
      <option value="pendiente"  @selected(($filters['estado'] ?? '') === 'pendiente')>Pendiente</option>
      <option value="cobrado"    @selected(($filters['estado'] ?? '') === 'cobrado')>Cobrado</option>
      <option value="protestado" @selected(($filters['estado'] ?? '') === 'protestado')>Protestado</option>
    </select>
    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" title="Vencimiento desde">
    <input type="date" name="to"   value="{{ $filters['to'] ?? '' }}"   title="Vencimiento hasta">
    <button type="submit" class="btn-primary" style="font-size:.85rem; padding:.5rem .9rem;">
      <i class='bx bx-search'></i> Filtrar
    </button>
    @if(array_filter($filters))
      <a href="{{ route('facturador.letras.index') }}" class="btn-secondary" style="font-size:.85rem; padding:.5rem .9rem;">
        <i class='bx bx-x'></i> Limpiar
      </a>
    @endif
  </form>

  {{-- Tabla --}}
  <div class="module-table-container" style="overflow-x:auto;">
    <table class="module-table" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th style="padding:.75rem 1rem; text-align:left;">N° Letra</th>
          <th style="padding:.75rem 1rem; text-align:left;">Aceptante</th>
          <th style="padding:.75rem 1rem; text-align:left;">Giro</th>
          <th style="padding:.75rem 1rem; text-align:left;">Vencimiento</th>
          <th style="padding:.75rem 1rem; text-align:right;">Monto</th>
          <th style="padding:.75rem 1rem; text-align:right;">Saldo</th>
          <th style="padding:.75rem 1rem; text-align:left;">Estado</th>
          <th style="padding:.75rem 1rem; text-align:right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @php $prevPurchaseId = null; @endphp
        @forelse($letras as $letra)
          @php $vencida = $letra->esta_vencida; @endphp

          {{-- Cabecera de grupo cuando cambia la compra --}}
          @if($letra->purchase_id !== $prevPurchaseId)
            @php
              $prevPurchaseId = $letra->purchase_id;
              $grupoLetras = $letras->filter(fn($l) => $l->purchase_id === $letra->purchase_id);
              $grupoTotal  = $grupoLetras->sum('monto');
              $grupoSaldo  = $grupoLetras->sum('saldo');
              $grupoCount  = $grupoLetras->count();
            @endphp
            <tr style="background:var(--clr-bg-subtle, #f8fafc); border-top:2px solid var(--clr-border-light,#e5e7eb); border-bottom:1px solid var(--clr-border-light,#e5e7eb);">
              <td colspan="8" style="padding:.6rem 1rem;">
                <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                  <span style="display:inline-flex; align-items:center; gap:.4rem; font-weight:700; font-size:.88rem; color:var(--clr-text-main,#111827);">
                    <i class='bx bx-file' style="color:var(--clr-text-muted,#6b7280);"></i>
                    {{ $letra->purchase?->serie_numero ?? ($letra->referencia ?? 'Compra #'.$letra->purchase_id) }}
                  </span>
                  <span style="font-size:.8rem; color:var(--clr-text-muted,#6b7280);">
                    {{ $letra->purchase?->razon_social_proveedor ?? $letra->aceptante_nombre }}
                  </span>
                  <span style="margin-left:auto; display:flex; gap:.75rem; font-size:.8rem;">
                    <span style="color:var(--clr-text-muted,#6b7280);">{{ $grupoCount }} letra(s)</span>
                    <span style="font-weight:700;">Total: {{ $letra->codigo_moneda }} {{ number_format($grupoTotal, 2) }}</span>
                    @if($grupoSaldo < $grupoTotal)
                      <span style="font-weight:700; color:#059669;">Cobrado: {{ number_format($grupoTotal - $grupoSaldo, 2) }}</span>
                    @endif
                    <a href="{{ route('facturador.compras.show', $letra->purchase_id) }}" style="color:var(--clr-active-bg,#1a6b57); text-decoration:none; font-weight:600;">
                      Ver compra <i class='bx bx-link-external' style="font-size:.85rem;"></i>
                    </a>
                  </span>
                </div>
              </td>
            </tr>
          @endif

          <tr style="border-bottom:1px solid var(--clr-border-light,#f3f4f6); {{ $vencida ? 'background:rgba(245,158,11,.04);' : '' }}">
            <td style="padding:.7rem 1rem .7rem 1.5rem; font-family:monospace; font-weight:700; font-size:.9rem;">
              {{ $letra->numero_letra }}
            </td>
            <td style="padding:.7rem 1rem;">
              <div style="font-weight:600; font-size:.88rem; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                {{ $letra->aceptante_nombre }}
              </div>
              <div style="font-size:.75rem; color:var(--clr-text-muted,#6b7280);">{{ $letra->aceptante_ruc }}</div>
            </td>
            <td style="padding:.7rem 1rem; font-size:.88rem;">{{ $letra->fecha_giro->format('d/m/Y') }}</td>
            <td style="padding:.7rem 1rem;">
              <span style="{{ $vencida ? 'color:#dc2626; font-weight:700;' : '' }}">
                {{ $letra->fecha_vencimiento->format('d/m/Y') }}
              </span>
              @if($vencida)
                <div style="font-size:.75rem; color:#dc2626;">{{ $letra->fecha_vencimiento->diffForHumans() }}</div>
              @endif
            </td>
            <td style="padding:.7rem 1rem; text-align:right; font-weight:700; font-size:.92rem;">
              {{ $letra->codigo_moneda }} {{ number_format($letra->monto, 2) }}
            </td>
            <td style="padding:.7rem 1rem; text-align:right;">
              @if($letra->monto_pagado > 0)
                <span style="font-weight:700; color:{{ $letra->saldo > 0 ? '#d97706' : '#059669' }};">
                  {{ $letra->codigo_moneda }} {{ number_format($letra->saldo, 2) }}
                </span>
                <div class="progress-bar-wrap">
                  <div class="progress-bar-fill"
                       style="width:{{ min(100, round($letra->monto_pagado / $letra->monto * 100)) }}%;
                              background:{{ $letra->saldo == 0 ? '#059669' : '#d97706' }};"></div>
                </div>
              @else
                <span style="color:var(--clr-text-muted,#6b7280);">{{ $letra->codigo_moneda }} {{ number_format($letra->monto, 2) }}</span>
              @endif
            </td>
            <td style="padding:.75rem 1rem;">
              <span class="letra-badge badge-{{ $letra->estado }}">
                {{ $letra->estado_label }}
              </span>
            </td>
            <td style="padding:.75rem 1rem;">
              <div class="action-wrapper">
                <a href="{{ route('facturador.letras.show', $letra) }}"
                   class="btn-action-icon" title="Ver detalle">
                  <i class='bx bx-show'></i>
                </a>
                <a href="{{ route('facturador.letras.imprimir', $letra) }}"
                   class="btn-action-icon" title="Imprimir letra" target="_blank">
                  <i class='bx bx-printer'></i>
                </a>
                @if($letra->estado === 'pendiente')
                  <button type="button" class="btn-action-icon btn-pago"
                          style="color:#059669;"
                          data-id="{{ $letra->id }}"
                          data-monto="{{ $letra->monto }}"
                          data-saldo="{{ $letra->saldo }}"
                          data-moneda="{{ $letra->codigo_moneda }}"
                          title="Registrar pago">
                    <i class='bx bx-dollar'></i>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="padding:2.5rem; text-align:center; color:var(--clr-text-muted,#6b7280);">
              <i class='bx bx-file-blank' style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
              No hay letras de cambio con los filtros seleccionados.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:1rem;">
    {{ $letras->links() }}
  </div>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}

{{-- Modal Registrar Pago --}}
<div class="modal-overlay" id="modalPago" onclick="if(event.target===this) cerrarModalPago()">
  <div class="modal-card" style="max-width:480px;">
    <div class="modal-header">
      <h3 style="font-size:.95rem;margin:0;"><i class='bx bx-dollar' style="margin-right:.35rem;"></i>Registrar Pago</h3>
      <button type="button" onclick="cerrarModalPago()" style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:#64748b;line-height:1;">
        <i class="bx bx-x"></i>
      </button>
    </div>
    <div class="modal-body" style="padding:1.25rem 1.5rem;">
      <div style="margin-bottom:1rem;padding:.75rem 1rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
        <span style="font-size:.82rem;color:#64748b;">Saldo pendiente:</span>
        <span id="pagoSaldoLabel" style="font-weight:700;margin-left:.35rem;color:#111827;"></span>
      </div>
      <div style="margin-bottom:1rem;">
        <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Fecha de pago <span style="color:#ef4444;">*</span></label>
        <input type="date" id="pagoFecha" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;" value="{{ now()->toDateString() }}">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Monto pagado <span style="color:#ef4444;">*</span></label>
        <input type="number" id="pagoMonto" step="0.01" min="0.01" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Medio de pago</label>
        <select id="pagoMedio" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;background:#fff;">
          <option value="transferencia">Transferencia bancaria</option>
          <option value="efectivo">Efectivo</option>
          <option value="cheque">Cheque</option>
          <option value="yape">Yape</option>
          <option value="plin">Plin</option>
        </select>
      </div>
      <div style="margin-bottom:1rem;">
        <label style="font-size:.85rem;font-weight:600;color:#374151;display:block;margin-bottom:.4rem;">Referencia / N° operación</label>
        <input type="text" id="pagoReferencia" placeholder="Ej: 00123456" style="width:100%;padding:.55rem .85rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;">
      </div>
    </div>
    <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:.5rem;padding:1rem 1.5rem;border-top:1px solid #e2e8f0;">
      <button type="button" onclick="cerrarModalPago()" style="padding:.55rem 1.25rem;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#374151;font-size:.85rem;font-weight:600;cursor:pointer;">
        Cancelar
      </button>
      <button type="button" id="btnConfirmarPago" style="padding:.55rem 1.25rem;border:none;border-radius:8px;background:#10b981;color:#fff;font-size:.85rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.4rem;">
        <span id="btnPagoSpinner" style="display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-right-color:#fff;border-radius:50%;animation:spinner-border .75s linear infinite;"></span>
        Confirmar Pago
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  let pagoLetraId = null;

  function abrirModalPago() {
    document.getElementById('modalPago').classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function cerrarModalPago() {
    document.getElementById('modalPago').classList.remove('show');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.btn-pago').forEach(btn => {
    btn.addEventListener('click', function () {
      pagoLetraId = this.dataset.id;
      const saldo  = parseFloat(this.dataset.saldo);
      const moneda = this.dataset.moneda;
      document.getElementById('pagoSaldoLabel').textContent = moneda + ' ' + saldo.toFixed(2);
      document.getElementById('pagoMonto').value = saldo.toFixed(2);
      document.getElementById('pagoMonto').max   = saldo;
      abrirModalPago();
    });
  });

  document.getElementById('btnConfirmarPago').addEventListener('click', async function () {
    const spinner = document.getElementById('btnPagoSpinner');
    spinner.style.display = 'inline-block';
    this.disabled = true;

    const url = `/facturador/letras/${pagoLetraId}/pago`;
    const body = {
      _token:          document.querySelector('meta[name="csrf-token"]').content,
      fecha_pago:      document.getElementById('pagoFecha').value,
      monto_pagado:    document.getElementById('pagoMonto').value,
      medio_pago:      document.getElementById('pagoMedio').value,
      referencia_pago: document.getElementById('pagoReferencia').value,
    };

    try {
      const res  = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': body._token },
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (data.ok) {
        cerrarModalPago();
        location.reload();
      } else {
        Swal.fire({icon:'error', title:'Error', text:'Error: ' + JSON.stringify(data)});
      }
    } catch (e) {
      Swal.fire({icon:'error', title:'Error de red', text:'Intenta nuevamente.'});
    } finally {
      spinner.style.display = 'none';
      this.disabled = false;
    }
  });
</script>
@endpush
