@extends('layouts.app')

@section('title', 'Compras — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .accounting-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .65rem; border-radius:12px; font-size:.72rem; font-weight:700; white-space:nowrap; }
    .accounting-badge--incompleto { background:rgba(239,68,68,.1); color:#dc2626; border:1px solid rgba(239,68,68,.25); }
    .accounting-badge--pendiente  { background:rgba(245,158,11,.1); color:#d97706; border:1px solid rgba(245,158,11,.25); }
    .accounting-badge--listo      { background:rgba(16,185,129,.1); color:#059669; border:1px solid rgba(16,185,129,.25); }
    .btn-completar { display:inline-flex; align-items:center; gap:.3rem; padding:.28rem .7rem; background:rgba(245,158,11,.12); color:#d97706; border:1px solid rgba(245,158,11,.3); border-radius:8px; font-size:.72rem; font-weight:700; cursor:pointer; white-space:nowrap; transition:all .15s; }
    .btn-completar:hover { background:rgba(245,158,11,.2); transform:translateY(-1px); }
    .stat-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
    @media(max-width:900px){ .stat-cards { grid-template-columns:repeat(2,1fr); } }
    @media(max-width:520px){ .stat-cards { grid-template-columns:1fr; } }
    .stat-card { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; padding:1.1rem 1.25rem; display:flex; flex-direction:column; gap:.25rem; box-shadow:0 4px 15px rgba(0,0,0,.03); transition:transform .2s; }
    .stat-card:hover { transform:translateY(-2px); }
    .stat-card__icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.35rem; margin-bottom:.35rem; }
    .stat-card__val  { font-size:1.45rem; font-weight:800; color:var(--clr-text-main,#111827); line-height:1.15; }
    .stat-card__lbl  { font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; letter-spacing:.05em; }
    .stat-card__sub  { font-size:.82rem; color:var(--clr-text-muted,#6b7280); margin-top:.1rem; }
    .sc-red   .stat-card__icon { background:rgba(239,68,68,.12); color:#dc2626; }
    .sc-blue  .stat-card__icon { background:rgba(59,130,246,.12); color:#3b82f6; }
    .sc-amber .stat-card__icon { background:rgba(245,158,11,.12); color:#d97706; }
    .sc-slate .stat-card__icon { background:rgba(107,114,128,.12); color:#6b7280; }
    .filter-bar { display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.5rem; align-items:center; background:var(--clr-bg-card,#fff); padding:1.25rem; border-radius:12px; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); }
    .filter-bar input, .filter-bar select { padding:.55rem .85rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px; font-size:.9rem; color:var(--clr-text-main,#111827); background:transparent; outline:none; transition:all .2s; font-family:inherit; }
    .filter-bar input:focus, .filter-bar select:focus { border-color:var(--clr-active-bg,#1a6b57); box-shadow:0 0 0 3px rgba(26,107,87,.1); }
    .module-table th { color:var(--clr-text-muted,#6b7280); font-weight:700; text-transform:uppercase; font-size:.75rem; letter-spacing:.05em; }
    .btn-action-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; background:rgba(0,0,0,.04); color:var(--clr-text-main,#374151); transition:all .2s; text-decoration:none; font-size:1.15rem; }
    .btn-action-icon:hover { background:rgba(0,0,0,.08); color:var(--clr-active-bg,#1a6b57); transform:translateY(-2px); }
    .action-wrapper { display:flex; gap:.4rem; justify-content:flex-end; }
    /* Badges tipo documento */
    .doc-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .6rem; border-radius:20px; font-size:.72rem; font-weight:800; white-space:nowrap; letter-spacing:.02em; }
    .doc-badge--factura   { background:rgba(59,130,246,.1);  color:#2563eb; border:1px solid rgba(59,130,246,.25); }
    .doc-badge--boleta    { background:rgba(16,185,129,.1);  color:#059669; border:1px solid rgba(16,185,129,.25); }
    .doc-badge--nc        { background:rgba(139,92,246,.1);  color:#7c3aed; border:1px solid rgba(139,92,246,.25); }
    .doc-badge--nd        { background:rgba(245,158,11,.1);  color:#d97706; border:1px solid rgba(245,158,11,.25); }
    .doc-badge--gre       { background:rgba(6,182,212,.1);   color:#0891b2; border:1px solid rgba(6,182,212,.25); }
    .doc-badge--dua       { background:rgba(107,114,128,.1); color:#4b5563; border:1px solid rgba(107,114,128,.25); }
    .doc-badge--otro      { background:rgba(239,68,68,.08);  color:#dc2626; border:1px solid rgba(239,68,68,.2); }
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
            <div class="module-toolbar">
              <h1 style="display:flex; align-items:center; gap:.5rem;">
                <i class='bx bx-cart' style="color:var(--clr-text-main);"></i> Registro de Compras
              </h1>
              <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                <button type="button" id="btn-export-excel"
                        style="display:{{ $stats['listos_count'] > 0 ? 'inline-flex' : 'none' }}; align-items:center; gap:.4rem; padding:.55rem 1.1rem; background:#059669; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.85rem; font-weight:600;"
                        title="{{ $stats['listos_count'] }} compra(s) lista(s)">
                  <i class='bx bx-file-export'></i> Exportar Excel
                  <span style="background:rgba(255,255,255,.25); border-radius:10px; padding:.05rem .45rem; font-size:.75rem;">{{ $stats['listos_count'] }}</span>
                </button>
                @can('create', \App\Models\Purchase::class)
                  <a href="{{ route('facturador.compras.subir') }}" class="btn-secondary" title="Importar comprobante desde PDF">
                    <i class='bx bx-cloud-upload'></i> Subir PDF
                  </a>
                  <a href="{{ route('facturador.compras.create') }}" class="btn-primary">
                    <i class='bx bx-plus'></i> Registrar Compra
                  </a>
                @endcan
              </div>
            </div>

            {{-- Tarjetas de resumen --}}
            <div class="stat-cards">
              <div class="stat-card sc-red">
                <div class="stat-card__icon"><i class='bx bx-cart-alt'></i></div>
                <span class="stat-card__lbl">Total Compras del Mes</span>
                <span class="stat-card__val">PEN {{ number_format($stats['total_mes'], 2) }}</span>
                <span class="stat-card__sub">{{ now()->locale('es')->translatedFormat('F Y') }}</span>
              </div>
              <div class="stat-card sc-blue">
                <div class="stat-card__icon"><i class='bx bx-check-circle'></i></div>
                <span class="stat-card__lbl">Listos para Exportar</span>
                <span class="stat-card__val">{{ $stats['listos_count'] }}</span>
                <span class="stat-card__sub">contablemente completos</span>
              </div>
              <div class="stat-card sc-amber">
                <div class="stat-card__icon"><i class='bx bx-error-circle'></i></div>
                <span class="stat-card__lbl">Pendientes / Incompletos</span>
                <span class="stat-card__val">{{ $stats['pendientes_count'] }}</span>
                <span class="stat-card__sub">requieren completado contable</span>
              </div>
              <div class="stat-card sc-slate">
                <div class="stat-card__icon"><i class='bx bx-list-ul'></i></div>
                <span class="stat-card__lbl">Total Registros</span>
                <span class="stat-card__val">{{ $stats['total_count'] }}</span>
                <span class="stat-card__sub">{{ $company->razon_social ?? '—' }}</span>
              </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="filter-bar">
              <i class='bx bx-filter-alt' style="font-size:1.25rem; color:var(--clr-text-muted);"></i>
              <input type="text" name="search" placeholder="Buscar proveedor, RUC..." value="{{ $filters['search'] ?? '' }}">
              <select name="accounting_status">
                <option value="">— Estado contable —</option>
                <option value="incompleto" {{ ($filters['accounting_status'] ?? '') === 'incompleto' ? 'selected' : '' }}>Incompleto</option>
                <option value="pendiente"  {{ ($filters['accounting_status'] ?? '') === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                <option value="listo"      {{ ($filters['accounting_status'] ?? '') === 'listo'      ? 'selected' : '' }}>Listo</option>
              </select>
              <select name="tipo_documento">
                <option value="">— Tipo doc —</option>
                <option value="01" {{ ($filters['tipo_documento'] ?? '') === '01' ? 'selected' : '' }}>01 - Factura</option>
                <option value="03" {{ ($filters['tipo_documento'] ?? '') === '03' ? 'selected' : '' }}>03 - Boleta</option>
                <option value="07" {{ ($filters['tipo_documento'] ?? '') === '07' ? 'selected' : '' }}>07 - N. Crédito</option>
                <option value="08" {{ ($filters['tipo_documento'] ?? '') === '08' ? 'selected' : '' }}>08 - N. Débito</option>
                <option value="00" {{ ($filters['tipo_documento'] ?? '') === '00' ? 'selected' : '' }}>00 - DUA</option>
              </select>
              <input type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}" title="Desde">
              <input type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}" title="Hasta">
              <button type="submit" class="btn-primary" style="font-size:.85rem; padding:.5rem .9rem;">
                <i class='bx bx-search'></i> Filtrar
              </button>
              @if(array_filter($filters))
                <a href="{{ route('facturador.compras.index') }}" class="btn-secondary" style="font-size:.85rem; padding:.5rem .9rem;">
                  <i class='bx bx-x'></i> Limpiar
                </a>
              @endif
            </form>

            {{-- Tabla --}}
            <div class="module-table-container" style="overflow-x:auto;">
              <table class="module-table" style="width:100%; border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="padding:.75rem 1rem; text-align:left;">Tipo Doc.</th>
                    <th style="padding:.75rem 1rem; text-align:left;">Serie-Número</th>
                    <th style="padding:.75rem 1rem; text-align:left;">Proveedor</th>
                    <th style="padding:.75rem 1rem; text-align:left;">Fecha Emis.</th>
                    <th style="padding:.75rem 1rem; text-align:right;">Total</th>
                    <th style="padding:.75rem 1rem; text-align:center;">Estado Contable</th>
                    <th style="padding:.75rem 1rem; text-align:right;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($purchases as $purchase)
                    @php
                      $tipoMap = [
                        '01' => ['label'=>'Factura',    'css'=>'factura'],
                        '03' => ['label'=>'Boleta',     'css'=>'boleta'],
                        '07' => ['label'=>'N.Crédito',  'css'=>'nc'],
                        '08' => ['label'=>'N.Débito',   'css'=>'nd'],
                        '09' => ['label'=>'GRE',        'css'=>'gre'],
                        '00' => ['label'=>'DUA',        'css'=>'dua'],
                      ];
                      // Si tiene gre_numero forzar badge GRE aunque el código sea otro
                      $esGre   = !empty($purchase->gre_numero);
                      $tipoCod = $esGre ? '09' : ($purchase->codigo_tipo_documento ?? '');
                      $tipoInfo = $tipoMap[$tipoCod] ?? ['label' => $tipoCod ?: '—', 'css' => 'otro'];
                      $tipoIcons = ['factura'=>'bx-receipt','boleta'=>'bx-store','nc'=>'bx-minus-circle','nd'=>'bx-plus-circle','gre'=>'bx-truck','dua'=>'bx-package','otro'=>'bx-file'];
                      $status  = $purchase->accounting_status?->value ?? 'incompleto';
                    @endphp
                    <tr style="border-bottom:1px solid var(--clr-border-light,#f3f4f6);">
                      <td style="padding:.75rem 1rem; font-size:.85rem;">
                        <span class="doc-badge doc-badge--{{ $tipoInfo['css'] }}">
                          <i class='bx {{ $tipoIcons[$tipoInfo["css"]] }}'></i>
                          {{ $tipoInfo['label'] }}
                        </span>
                      </td>
                      <td style="padding:.75rem 1rem; font-family:monospace; font-size:.9rem; font-weight:700;">
                        {{ $purchase->serie_documento ?? '—' }}-{{ $purchase->numero_documento ?? '—' }}
                      </td>
                      <td style="padding:.75rem 1rem;">
                        <div style="font-weight:600; font-size:.88rem;">{{ Str::limit($purchase->razon_social_proveedor, 35) }}</div>
                        <div style="font-size:.75rem; color:var(--clr-text-muted,#6b7280);">{{ $purchase->numero_doc_proveedor }}</div>
                      </td>
                      <td style="padding:.75rem 1rem; font-size:.88rem;">{{ $purchase->fecha_emision?->format('d/m/Y') }}</td>
                      <td style="padding:.75rem 1rem; text-align:right; font-weight:700; font-size:.92rem;">
                        {{ $purchase->codigo_moneda ?? 'PEN' }} {{ number_format($purchase->monto_total, 2) }}
                      </td>
                      <td style="padding:.75rem 1rem; text-align:center;">
                        <span id="status-cell-{{ $purchase->id }}">
                          @if($status === 'listo')
                            <span class="accounting-badge accounting-badge--listo"><i class='bx bx-check-circle'></i> Listo</span>
                          @elseif($status === 'pendiente')
                            <button type="button" class="btn-completar" data-purchase-id="{{ $purchase->id }}"><i class='bx bx-edit'></i> Pendiente</button>
                          @else
                            <button type="button" class="btn-completar" data-purchase-id="{{ $purchase->id }}"><i class='bx bx-plus-circle'></i> Completar</button>
                          @endif
                        </span>
                      </td>
                      <td style="padding:.75rem 1rem;">
                        <div class="action-wrapper">
                          <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-action-icon" title="Ver detalle">
                            <i class='bx bx-show'></i>
                          </a>
                          @can('update', $purchase)
                            <a href="{{ route('facturador.compras.edit', $purchase) }}" class="btn-action-icon" title="Editar">
                              <i class='bx bx-pencil'></i>
                            </a>
                          @endcan
                          @can('delete', $purchase)
                            <form method="POST" action="{{ route('facturador.compras.destroy', $purchase) }}"
                                  data-confirm="¿Eliminar este registro de compra? Esta acción no se puede deshacer.">
                              @csrf @method('DELETE')
                              <button type="submit" class="btn-action-icon" style="color:#ef4444;" title="Eliminar">
                                <i class='bx bx-trash'></i>
                              </button>
                            </form>
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" style="padding:2.5rem; text-align:center; color:var(--clr-text-muted,#6b7280);">
                        <i class='bx bx-cart' style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        No hay compras registradas aún.
                        <br>
                        <a href="{{ route('facturador.compras.create') }}" class="btn-primary" style="margin-top:1rem; display:inline-flex; align-items:center; gap:.4rem;">
                          <i class='bx bx-plus'></i> Registrar primera compra
                        </a>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            {{-- Paginación --}}
            @if($purchases->hasPages())
              <div style="padding:1rem 0;">
                {{ $purchases->withQueryString()->links() }}
              </div>
            @endif
          </div>
        </div>
      </main>
    </section>
  </div>

  @include('facturador.compras.partials.accounting-modal')
  @include('facturador.compras.partials.export-modal')
@endsection

@push('scripts')
<script>
window.ComprasRoutes = {
  accountingGet:  (id) => `/facturador/compras/${id}/accounting`,
  accountingSave: (id) => `/facturador/compras/${id}/accounting`,
  exportCount:    `/facturador/compras/export-count`,
  exportExcel:    `/facturador/compras/export-excel`,
};

// Abrir modal contable
document.querySelectorAll('[data-purchase-id]').forEach(btn => {
  btn.addEventListener('click', () => openAccountingModal(btn.dataset.purchaseId));
});

// Botón exportar
document.getElementById('btn-export-excel')?.addEventListener('click', () => {
  document.getElementById('purchase-export-modal-overlay').style.display = 'block';
});

// Flash close
document.querySelectorAll('[data-flash-close]').forEach(btn => {
  btn.closest('[data-flash-message]')?.style && btn.addEventListener('click', function () {
    this.closest('[data-flash-message]').style.display = 'none';
  });
});
</script>
<script src="{{ asset('js/compras-accounting.js') }}" defer></script>
@endpush
