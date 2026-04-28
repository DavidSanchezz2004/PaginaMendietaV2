@extends('layouts.app')

@section('title', 'Detalle Compra — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    @media(max-width:640px){ .detail-grid { grid-template-columns:1fr; } }
    .detail-section { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:12px; padding:1.25rem; }
    .detail-section__title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-active-bg,#1a6b57); margin-bottom:.85rem; display:flex; align-items:center; gap:.4rem; }
    .dl-row { display:grid; grid-template-columns:45% 55%; gap:.25rem; font-size:.88rem; padding:.35rem 0; border-bottom:1px solid var(--clr-border-light,#f3f4f6); }
    .dl-row:last-child { border-bottom:none; }
    .dl-label { color:var(--clr-text-muted,#6b7280); font-weight:600; font-size:.8rem; }
    .dl-value { color:var(--clr-text-main,#111827); font-weight:500; word-break:break-word; }
    .accounting-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .7rem; border-radius:12px; font-size:.78rem; font-weight:700; }
    .accounting-badge--incompleto { background:rgba(239,68,68,.1); color:#dc2626; border:1px solid rgba(239,68,68,.25); }
    .accounting-badge--pendiente  { background:rgba(245,158,11,.1); color:#d97706; border:1px solid rgba(245,158,11,.25); }
    .accounting-badge--listo      { background:rgba(16,185,129,.1); color:#059669; border:1px solid rgba(16,185,129,.25); }
    .monto-card { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:12px; padding:1.25rem; }
    .monto-row  { display:flex; justify-content:space-between; font-size:.9rem; padding:.3rem 0; border-bottom:1px solid var(--clr-border-light,#f3f4f6); }
    .monto-row:last-child { border-bottom:none; font-weight:800; font-size:1.05rem; }
    .monto-lbl  { color:var(--clr-text-muted,#6b7280); }
    .monto-val  { font-weight:600; color:var(--clr-text-main,#111827); }
    .items-tbl  { width:100%; border-collapse:collapse; font-size:.84rem; }
    .items-tbl th { background:#f1f5f9; padding:.45rem .65rem; text-align:left; font-weight:700;
                    font-size:.78rem; color:#374151; white-space:nowrap; }
    .items-tbl td { padding:.4rem .65rem; border-bottom:1px solid #f1f5f9; vertical-align:top; }
    .items-tbl tbody tr:last-child td { border-bottom:none; }
    .items-tbl tbody tr:hover { background:#f8fafc; }
    .items-tbl .num { text-align:right; }
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
          @foreach(['success','warning','error'] as $f)
            @if(session($f))
              <div class="placeholder-content module-alert {{ $f === 'error' ? 'module-alert--error' : '' }}" data-flash-message>
                <p>{{ session($f) }}</p>
                <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
              </div>
            @endif
          @endforeach

          <div class="placeholder-content module-card-wide">
            {{-- Toolbar --}}
            <div class="module-toolbar" style="margin-bottom:1.5rem;">
              <h1 style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;">
                <i class='bx bx-cart' style="color:var(--clr-text-main);"></i>
                Compra #{{ $purchase->id }}
                <code style="font-size:.88rem; color:var(--clr-text-muted,#6b7280); font-weight:400;">
                  {{ $purchase->serie_documento }}-{{ $purchase->numero_documento }}
                </code>
                @php $status = $purchase->accounting_status?->value ?? 'incompleto'; @endphp
                <span class="accounting-badge accounting-badge--{{ $status }}">
                  @if($status === 'listo')<i class='bx bx-check-circle'></i> Listo
                  @elseif($status === 'pendiente')<i class='bx bx-time'></i> Pendiente
                  @else<i class='bx bx-error-circle'></i> Incompleto
                  @endif
                </span>
              </h1>
              <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
                @if($status !== 'listo')
                  <button type="button" id="btn-completar-contable"
                          style="display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; background:#d97706; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.85rem; font-weight:600;"
                          data-purchase-id="{{ $purchase->id }}">
                    <i class='bx bx-clipboard-check'></i> Completar Contable
                  </button>
                @else
                  <button type="button" id="btn-completar-contable"
                          style="display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; background:rgba(5,150,105,.1); color:#059669; border:1px solid rgba(5,150,105,.3); border-radius:8px; cursor:pointer; font-size:.85rem; font-weight:600;"
                          data-purchase-id="{{ $purchase->id }}">
                    <i class='bx bx-edit'></i> Editar Contable
                  </button>
                @endif
                @can('update', $purchase)
                  <a href="{{ route('facturador.compras.edit', $purchase) }}" class="btn-secondary" style="font-size:.85rem;">
                    <i class='bx bx-pencil'></i> Editar
                  </a>
                @endcan
                @can('delete', $purchase)
                  <form method="POST" action="{{ route('facturador.compras.destroy', $purchase) }}"
                        data-confirm="¿Eliminar este registro de compra? Esta acción no se puede deshacer.">
                    @csrf @method('DELETE')
                    <button type="submit" style="display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1rem; background:rgba(239,68,68,.1); color:#dc2626; border:1px solid rgba(239,68,68,.3); border-radius:8px; cursor:pointer; font-size:.85rem; font-weight:600;">
                      <i class='bx bx-trash'></i> Eliminar
                    </button>
                  </form>
                @endcan
                {{-- Asignar Cliente (si no tiene) --}}
                @if(!$purchase->client_id && $purchase->status !== 'invoiced' && $purchase->status !== 'partially_invoiced')
                  <a href="{{ route('facturador.purchase-client.assign.form', $purchase) }}"
                     style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:#3b82f6;color:#fff;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;">
                    <i class='bx bx-user-plus'></i> Asignar Cliente
                  </a>
                @elseif($purchase->client_id)
                  <a href="{{ route('facturador.purchases.guia-flow', $purchase) }}"
                     style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:rgba(26,107,87,.1);color:#1a6b57;border:1px solid rgba(26,107,87,.3);border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;">
                    <i class='bx bx-trending-up'></i> Ver Flujo
                  </a>
                @endif
                @if(!$purchase->letras()->exists())
                  <a href="{{ route('facturador.compras.canjear.form', $purchase) }}"
                     style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:#7c3aed;color:#fff;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;">
                    <i class='bx bx-transfer'></i> Canjear a Letras
                  </a>
                @else
                  <a href="{{ route('facturador.letras.index') }}"
                     style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:rgba(124,58,237,.1);color:#7c3aed;border:1px solid rgba(124,58,237,.3);border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;">
                    <i class='bx bx-transfer'></i> Ver Letras
                  </a>
                @endif
                <a href="{{ route('facturador.compras.index') }}" class="btn-secondary" style="font-size:.85rem;">
                  <i class='bx bx-arrow-back'></i> Volver
                </a>
              </div>
            </div>

            {{-- Contenido de dos columnas --}}
            <div class="detail-grid" style="margin-bottom:1.25rem;">

              {{-- Datos del comprobante --}}
              <div class="detail-section">
                <p class="detail-section__title"><i class='bx bx-file'></i> Comprobante</p>
                @php
                  $tipoMap = ['01'=>'Factura','03'=>'Boleta','07'=>'Nota de Crédito','08'=>'Nota de Débito','00'=>'DUA'];
                @endphp
                <div class="dl-row"><span class="dl-label">Tipo</span><span class="dl-value">{{ $tipoMap[$purchase->codigo_tipo_documento] ?? $purchase->codigo_tipo_documento }}</span></div>
                <div class="dl-row"><span class="dl-label">Serie-Número</span><span class="dl-value"><code>{{ $purchase->serie_documento ?? '—' }}-{{ $purchase->numero_documento ?? '—' }}</code></span></div>
                <div class="dl-row"><span class="dl-label">Fecha Emisión</span><span class="dl-value">{{ $purchase->fecha_emision?->format('d/m/Y') }}</span></div>
                <div class="dl-row"><span class="dl-label">Fecha Vencimiento</span><span class="dl-value">{{ $purchase->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</span></div>
                <div class="dl-row"><span class="dl-label">Moneda</span><span class="dl-value">{{ $purchase->codigo_moneda ?? 'PEN' }}</span></div>
                @if($purchase->monto_tipo_cambio)
                <div class="dl-row"><span class="dl-label">Tipo de Cambio</span><span class="dl-value">{{ $purchase->monto_tipo_cambio }}</span></div>
                @endif
                @if($purchase->forma_pago)
                <div class="dl-row"><span class="dl-label">Forma de Pago</span><span class="dl-value">{{ ['01'=>'Contado','02'=>'Crédito','03'=>'Efectivo','04'=>'Yape','05'=>'Plin','06'=>'Banco / Transferencia','07'=>'BCP','08'=>'BBVA','1'=>'Contado','2'=>'Crédito','3'=>'Efectivo','4'=>'Yape','5'=>'Plin','6'=>'Banco / Transferencia','7'=>'BCP','8'=>'BBVA'][$purchase->forma_pago] ?? $purchase->forma_pago }}</span></div>
                @endif
                @if($purchase->observacion)
                <div class="dl-row"><span class="dl-label">Observación</span><span class="dl-value">{{ $purchase->observacion }}</span></div>
                @endif
              </div>

              {{-- Datos del proveedor --}}
              <div class="detail-section">
                <p class="detail-section__title"><i class='bx bx-buildings'></i> Proveedor</p>
                <div class="dl-row"><span class="dl-label">Razón Social</span><span class="dl-value" style="font-weight:700;">{{ $purchase->razon_social_proveedor }}</span></div>
                <div class="dl-row"><span class="dl-label">Número Documento</span><span class="dl-value"><code>{{ $purchase->numero_doc_proveedor }}</code></span></div>
                @if($purchase->provider)
                <div class="dl-row"><span class="dl-label">En catálogo</span><span class="dl-value" style="color:#059669;"><i class='bx bx-check-circle'></i> Sí</span></div>
                @endif
              </div>
            </div>

            {{-- Importes --}}
            <div class="monto-card" style="margin-bottom:1.25rem;">
              <p class="detail-section__title" style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-active-bg,#1a6b57); margin-bottom:.85rem;">
                <i class='bx bx-dollar-circle'></i> Importes
              </p>
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:0 2rem; max-width:480px;">
                <div class="monto-row"><span class="monto-lbl">Base Imponible Gravadas</span><span class="monto-val">{{ number_format($purchase->base_imponible_gravadas, 2) }}</span></div>
                <div class="monto-row"><span class="monto-lbl">IGV ({{ $purchase->porcentaje_igv }}%)</span><span class="monto-val">{{ number_format($purchase->igv_gravadas, 2) }}</span></div>
                @if($purchase->monto_no_gravado)
                <div class="monto-row"><span class="monto-lbl">No Gravado / Inafecto</span><span class="monto-val">{{ number_format($purchase->monto_no_gravado, 2) }}</span></div>
                @endif
                @if($purchase->monto_exonerado)
                <div class="monto-row"><span class="monto-lbl">Exonerado</span><span class="monto-val">{{ number_format($purchase->monto_exonerado, 2) }}</span></div>
                @endif
                @if($purchase->monto_descuento)
                <div class="monto-row"><span class="monto-lbl">Descuento</span><span class="monto-val">- {{ number_format($purchase->monto_descuento, 2) }}</span></div>
                @endif
                <div class="monto-row" style="grid-column:1/-1; border-top:2px solid var(--clr-border-light,#e5e7eb); padding-top:.5rem; margin-top:.25rem;">
                  <span class="monto-lbl" style="font-weight:700; color:var(--clr-text-main);">TOTAL {{ $purchase->codigo_moneda ?? 'PEN' }}</span>
                  <span class="monto-val" style="font-size:1.2rem; color:var(--clr-active-bg,#1a6b57);">{{ number_format($purchase->monto_total, 2) }}</span>
                </div>
              </div>
            </div>

            {{-- SPOT / Detracción --}}
            @if($purchase->es_sujeto_detraccion)
            <div class="detail-section" style="margin-bottom:1.25rem; border-left:4px solid #b45309;">
              <p class="detail-section__title" style="color:#b45309;"><i class='bx bx-receipt'></i> SPOT - Información de Detracción</p>
              @php $det = $purchase->informacion_detraccion ?? []; @endphp
              <div class="dl-row"><span class="dl-label">Sujeto a Detracción</span><span class="dl-value" style="color:#b45309; font-weight:700;"><i class='bx bx-check-circle'></i> Sí</span></div>
              @if($det['leyenda'] ?? null)
              <div class="dl-row" style="grid-template-columns:100%;"><span class="dl-label">Leyenda SUNAT</span><span class="dl-value" style="white-space:normal;">{{ $det['leyenda'] }}</span></div>
              @endif
              @if($det['bien_codigo'] ?? null)
              <div class="dl-row"><span class="dl-label">Código Bien/Servicio</span><span class="dl-value">{{ $det['bien_codigo'] }}</span></div>
              @endif
              @if($det['bien_descripcion'] ?? null)
              <div class="dl-row"><span class="dl-label">Bien o Servicio</span><span class="dl-value">{{ $det['bien_descripcion'] }}</span></div>
              @endif
              @if($det['medio_pago'] ?? null)
              <div class="dl-row"><span class="dl-label">Medio de Pago</span><span class="dl-value">{{ $det['medio_pago'] }}</span></div>
              @endif
              @if($det['numero_cuenta'] ?? null)
              <div class="dl-row"><span class="dl-label">Nro. Cta. B.N.</span><span class="dl-value"><code>{{ $det['numero_cuenta'] }}</code></span></div>
              @endif
              @if($det['porcentaje'] ?? null)
              <div class="dl-row"><span class="dl-label">Porcentaje Detracción</span><span class="dl-value">{{ $det['porcentaje'] }}%</span></div>
              @endif
              @if($purchase->monto_detraccion)
              <div class="dl-row"><span class="dl-label">Monto Detracción</span><span class="dl-value" style="font-weight:700; color:#b45309;">- {{ number_format($purchase->monto_detraccion, 2) }} {{ $purchase->codigo_moneda }}</span></div>
              @endif
              @if($purchase->monto_neto_detraccion)
              <div class="dl-row" style="border-top:2px solid #fcd34d; padding-top:.5rem; margin-top:.25rem;"><span class="dl-label" style="font-weight:700;">Deuda Neta (a pagar)</span><span class="dl-value" style="font-weight:700; color:#059669; font-size:1.05rem;">{{ number_format($purchase->monto_neto_detraccion, 2) }} {{ $purchase->codigo_moneda }}</span></div>
              @endif
            </div>
            @endif

            {{-- Ítems / productos --}}
            @if($purchase->items->isNotEmpty())
            <div class="detail-section" style="margin-bottom:1.25rem; overflow-x:auto;">
              <p class="detail-section__title"><i class='bx bx-list-ul'></i> Productos / Líneas de detalle</p>
              <table class="items-tbl">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th>Unidad</th>
                    <th class="num">Cantidad</th>
                    <th class="num">Valor Unit.</th>
                    <th class="num">Descuento</th>
                    <th class="num">Importe Venta</th>
                    <th class="num">ICBPER</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($purchase->items as $item)
                  <tr>
                    <td style="color:#9ca3af; font-size:.78rem;">{{ $item->correlativo }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td style="color:#6b7280;">{{ $item->unidad_medida ?? '—' }}</td>
                    <td class="num">{{ number_format($item->cantidad, 4) }}</td>
                    <td class="num">{{ number_format($item->valor_unitario, 6) }}</td>
                    <td class="num">{{ number_format($item->descuento, 2) }}</td>
                    <td class="num" style="font-weight:600;">{{ number_format($item->cantidad * $item->valor_unitario, 4) }}</td>
                    <td class="num">{{ number_format($item->icbper, 2) }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif

            @if($purchase->letterCompensationDetails->isNotEmpty())
            <div class="detail-section" style="margin-bottom:1.25rem; overflow-x:auto;">
              <p class="detail-section__title"><i class='bx bx-transfer'></i> Historial de pagos por compensación</p>
              <table class="items-tbl">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Letra utilizada</th>
                    <th>Cliente / aceptante</th>
                    <th class="num">Monto compensado</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($purchase->letterCompensationDetails as $detail)
                    @php
                      $compensation = $detail->compensation;
                      $letraCompensada = $compensation?->letraCambio;
                    @endphp
                    <tr>
                      <td>{{ $compensation?->compensation_date?->format('d/m/Y') ?? '—' }}</td>
                      <td>Compensación con letra</td>
                      <td>{{ $letraCompensada?->numero_letra ?? '—' }}</td>
                      <td>{{ $letraCompensada?->aceptante_nombre ?? '—' }}</td>
                      <td class="num" style="font-weight:700;">{{ $purchase->codigo_moneda }} {{ number_format($detail->amount, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif

            {{-- Campos contables --}}
            @if($status !== 'incompleto')
            <div class="detail-section">
              <p class="detail-section__title"><i class='bx bx-clipboard-check'></i> Información Contable</p>
              <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:.5rem .75rem;">
                @if($purchase->tipo_operacion)
                <div class="dl-row"><span class="dl-label">Tipo Operación</span><span class="dl-value">{{ $purchase->tipo_operacion }}</span></div>
                @endif
                @if($purchase->tipo_compra)
                <div class="dl-row"><span class="dl-label">Tipo Compra</span><span class="dl-value">{{ $purchase->tipo_compra?->value }} — {{ $purchase->tipo_compra?->label() }}</span></div>
                @endif
                @if($purchase->cuenta_contable)
                <div class="dl-row"><span class="dl-label">Cuenta Contable</span><span class="dl-value">{{ $purchase->cuenta_contable }}</span></div>
                @endif
                @if($purchase->codigo_producto_servicio)
                <div class="dl-row"><span class="dl-label">Cód. Producto/Servicio</span><span class="dl-value">{{ $purchase->codigo_producto_servicio }}</span></div>
                @endif
                @if($purchase->glosa)
                <div class="dl-row"><span class="dl-label">Glosa</span><span class="dl-value">{{ $purchase->glosa }}</span></div>
                @endif
                @if($purchase->centro_costo)
                <div class="dl-row"><span class="dl-label">Centro de Costo</span><span class="dl-value">{{ $purchase->centro_costo }}</span></div>
                @endif
                @if($purchase->tipo_gasto)
                <div class="dl-row"><span class="dl-label">Tipo de Gasto</span><span class="dl-value">{{ $purchase->tipo_gasto }}</span></div>
                @endif
              </div>
            </div>
            @endif

            {{-- Componente: Datos de GRE si existen --}}
            @include('facturador.compras.components.gre-info')

          </div>
        </div>
      </main>
    </section>
  </div>

  @include('facturador.compras.partials.accounting-modal')
@endsection

@push('scripts')
<script>
window.ComprasRoutes = {
  accountingGet:  (id) => `/facturador/compras/${id}/accounting`,
  accountingSave: (id) => `/facturador/compras/${id}/accounting`,
};

document.getElementById('btn-completar-contable')?.addEventListener('click', function () {
  openAccountingModal(this.dataset.purchaseId);
});

document.querySelectorAll('[data-flash-close]').forEach(btn => {
  btn.addEventListener('click', function () {
    this.closest('[data-flash-message]').style.display = 'none';
  });
});
</script>
<script src="{{ asset('js/compras-accounting.js') }}" defer></script>
@endpush
