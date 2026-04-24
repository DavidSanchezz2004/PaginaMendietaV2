@extends('layouts.app')

@section('title', 'Compra ' . $purchase->serie_numero . ' - Flujo Guías')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .timeline {
      display: flex;
      gap: 0;
      margin-bottom: 2rem;
    }

    .timeline-step {
      flex: 1;
      position: relative;
      text-align: center;
    }

    .timeline-step::after {
      content: '';
      position: absolute;
      top: 35px;
      left: 50%;
      right: -50%;
      height: 2px;
      background: #e2e8f0;
    }

    .timeline-step.completed::after {
      background: var(--clr-active-bg, #1a6b57);
    }

    .timeline-step:last-child::after {
      display: none;
    }

    .timeline-circle {
      width: 70px;
      height: 70px;
      margin: 0 auto 1rem;
      background: #e2e8f0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #6b7280;
      position: relative;
      z-index: 1;
    }

    .timeline-step.completed .timeline-circle {
      background: var(--clr-active-bg, #1a6b57);
      color: white;
    }

    .timeline-step.active .timeline-circle {
      background: #3b82f6;
      color: white;
      box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.1);
    }

    .timeline-label {
      font-weight: 600;
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
    }

    .timeline-sublabel {
      font-size: 0.8rem;
      color: var(--clr-text-muted, #6b7280);
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .info-box {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.25rem;
    }

    .info-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--clr-text-muted, #6b7280);
      text-transform: uppercase;
      margin-bottom: 0.5rem;
    }

    .info-value {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--clr-text-main, #374151);
    }

    .action-card {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .action-card.disabled {
      opacity: 0.6;
      pointer-events: none;
    }

    .action-description {
      flex: 1;
    }

    .action-description h4 {
      margin: 0 0 0.5rem 0;
      font-size: 1rem;
    }

    .action-description p {
      margin: 0;
      font-size: 0.85rem;
      color: var(--clr-text-muted, #6b7280);
    }

    .items-table {
      width: 100%;
      border-collapse: collapse;
    }

    .items-table thead {
      background: #f8fafc;
      border-bottom: 1px solid var(--clr-border-light, #e2e8f0);
    }

    .items-table th {
      padding: 0.75rem 1rem;
      text-align: left;
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--clr-text-muted, #6b7280);
      text-transform: uppercase;
    }

    .items-table td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid var(--clr-border-light, #e2e8f0);
      font-size: 0.9rem;
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.75rem;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .badge-pending {
      background: rgba(100, 116, 139, 0.1);
      color: #64748b;
    }

    .badge-guided {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .badge-invoiced {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
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
                <i class='bx bx-shopping-bag'></i> Flujo de Compra a Factura
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">{{ $purchase->serie_numero }}</small>
            </div>
            <a href="{{ route('facturador.compras.show', $purchase) }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
              <i class='bx bx-arrow-back'></i> Volver
            </a>
          </div>

          {{-- Alertas de Error/Validación --}}
          @if($errors->any())
            <div style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#991b1b; font-size:.9rem;">
              <div style="font-weight:600; margin-bottom:.5rem;"><i class='bx bx-x-circle' style="margin-right:.4rem;"></i>Errores encontrados:</div>
              <ul style="margin:0; padding-left:1.5rem;">
                @foreach($errors->all() as $error)
                  <li style="margin-bottom:.3rem;">{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Validación de Compra --}}
          @if($purchase->status === 'registered' && !$purchase->client_id)
            <div style="background:rgba(249,115,22,.08); border:1px solid rgba(249,115,22,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#92400e; font-size:.9rem;">
              <div style="font-weight:600; margin-bottom:.5rem;"><i class='bx bx-info-circle' style="margin-right:.4rem;"></i>Paso 1: Asignar Cliente</div>
              <p style="margin:0 0 .5rem 0;">Esta compra aún no tiene cliente asignado. Debes asignar un cliente antes de generar la guía.</p>
            </div>
          @endif

          @if($purchase->client_id && $purchase->status === 'assigned')
            <div style="background:rgba(59,130,246,.08); border:1px solid rgba(59,130,246,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#1e40af; font-size:.9rem;">
              <div style="font-weight:600; margin-bottom:.5rem;"><i class='bx bx-info-circle' style="margin-right:.4rem;"></i>Paso 2: Generar Guía</div>
              <p style="margin:0;">Cliente asignado correctamente. Procede a generar la guía de remisión.</p>
            </div>
          @endif

          {{-- Timeline del proceso --}}
          <div class="timeline" style="margin-bottom:2rem;">
            <div class="timeline-step {{ $purchase->status === 'registered' || $purchase->status ? 'completed' : '' }}">
              <div class="timeline-circle"><i class='bx bx-shopping-bag'></i></div>
              <div class="timeline-label">Compra Registrada</div>
              <div class="timeline-sublabel">Paso 1</div>
            </div>

            <div class="timeline-step {{ in_array($purchase->status, ['assigned', 'guided', 'partially_invoiced', 'invoiced']) ? 'completed' : ($purchase->status === 'registered' ? 'active' : '') }}">
              <div class="timeline-circle"><i class='bx bx-user'></i></div>
              <div class="timeline-label">Cliente Asignado</div>
              <div class="timeline-sublabel">Paso 2</div>
            </div>

            <div class="timeline-step {{ in_array($purchase->status, ['guided', 'partially_invoiced', 'invoiced']) ? 'completed' : ($purchase->status === 'assigned' ? 'active' : '') }}">
              <div class="timeline-circle"><i class='bx bx-file-blank'></i></div>
              <div class="timeline-label">Guía Generada</div>
              <div class="timeline-sublabel">Paso 3</div>
            </div>

            <div class="timeline-step {{ in_array($purchase->status, ['partially_invoiced', 'invoiced']) ? 'completed' : ($purchase->status === 'guided' ? 'active' : '') }}">
              <div class="timeline-circle"><i class='bx bx-receipt'></i></div>
              <div class="timeline-label">Facturada</div>
              <div class="timeline-sublabel">Paso 4</div>
            </div>
          </div>

          {{-- Información de la compra --}}
          <div class="info-grid">
            <div class="info-box">
              <div class="info-label">Número Compra</div>
              <div class="info-value">{{ $purchase->serie_numero }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Proveedor</div>
              <div class="info-value">{{ $purchase->provider->nombre_razon_social }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Total</div>
              <div class="info-value">{{ $purchase->codigo_moneda }} {{ number_format($purchase->monto_total, 2) }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Estado Actual</div>
              <div class="info-value">
                <span class="badge-status badge-{{ $purchase->status }}">
                  {{ match($purchase->status) {
                    'registered' => 'Registrada',
                    'assigned' => 'Cliente Asignado',
                    'guided' => 'Guía Generada',
                    'partially_invoiced' => 'Parcialmente Facturada',
                    'invoiced' => 'Facturada',
                    default => $purchase->status
                  } }}
                </span>
              </div>
            </div>
          </div>

          {{-- Cliente (si está asignado) --}}
          @if($purchase->client)
            <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; padding:1.5rem; box-shadow:0 4px 15px rgba(0,0,0,.03); margin-bottom:1.5rem;">
              <h3 style="margin-top:0; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; justify-content:space-between;">
                <span style="display:flex; align-items:center; gap:.5rem;">
                  <i class='bx bx-user-check'></i> Cliente Asignado
                </span>
                @if(!in_array($purchase->status, ['guided', 'partially_invoiced', 'invoiced']))
                  <a href="{{ route('facturador.purchase-client.assign.form', $purchase) }}"
                     style="display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .75rem; background:rgba(245,158,11,.08); color:#d97706; border:1px solid rgba(245,158,11,.3); border-radius:8px; font-size:.78rem; font-weight:600; text-decoration:none;">
                    <i class='bx bx-edit'></i> Cambiar cliente
                  </a>
                @endif
              </h3>
              <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Razón Social</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $purchase->client->nombre_razon_social }}</div>
                </div>
                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">RUC/DNI</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $purchase->client->numero_documento }}</div>
                </div>
              </div>
            </div>
          @endif

          {{-- Acciones disponibles --}}
          <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03); margin-bottom:1.5rem;">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem; background:#f8fafc;">
              <i class='bx bx-checklist'></i> Acciones Disponibles
            </div>

            <div style="padding:1.5rem;">
              {{-- Asignar cliente --}}
              @if(!$purchase->client)
                <div class="action-card">
                  <div class="action-description">
                    <h4><i class='bx bx-user-plus'></i> Asignar Cliente</h4>
                    <p>Selecciona el cliente que recibirá esta compra</p>
                  </div>
                  <a href="{{ route('facturador.purchase-client.assign.form', $purchase) }}" class="btn-primary" style="padding:.6rem 1.25rem; text-decoration:none; white-space:nowrap;">
                    <i class='bx bx-arrow-right'></i> Asignar
                  </a>
                </div>
              @else
                {{-- Generar guía --}}
                @if(in_array($purchase->status, ['assigned', 'guided', 'partially_invoiced', 'invoiced']))
                  @php $ultimaGuia = $purchase->guias->sortByDesc('id')->first(); @endphp
                  <div class="action-card">
                    <div class="action-description">
                      @if($ultimaGuia)
                        <h4><i class='bx bx-file-blank'></i> Ver Guía de Remisión</h4>
                        <p>Guía <strong>GRE-{{ $ultimaGuia->numero }}</strong> ya generada</p>
                      @else
                        <h4><i class='bx bx-file-blank'></i> Generar Guía de Remisión</h4>
                        <p>Crea una guía de remisión para comenzar el trámite de facturación</p>
                      @endif
                    </div>
                    @if($ultimaGuia)
                      <a href="{{ route('facturador.guias.show', $ultimaGuia) }}" class="btn-primary" style="padding:.6rem 1.25rem; text-decoration:none; white-space:nowrap;">
                        <i class='bx bx-show'></i> Ver Guía
                      </a>
                    @else
                      <a href="{{ route('facturador.compras.guia.preview', $purchase) }}" class="btn-primary" style="padding:.6rem 1.25rem; text-decoration:none; white-space:nowrap;">
                        <i class='bx bx-arrow-right'></i> Generar
                      </a>
                    @endif
                  </div>
                @endif

                {{-- Ver/crear facturas desde guías --}}
                @if(in_array($purchase->status, ['guided', 'partially_invoiced', 'invoiced']))
                  <div class="action-card">
                    <div class="action-description">
                      <h4><i class='bx bx-receipt'></i> Crear Factura desde Guía</h4>
                      <p>Genera una factura a partir de la guía de remisión</p>
                    </div>
                    <a href="{{ route('facturador.guias.index', ['from_purchase' => $purchase->id]) }}" class="btn-primary" style="padding:.6rem 1.25rem; text-decoration:none; white-space:nowrap;">
                      <i class='bx bx-arrow-right'></i> Ver Guías
                    </a>
                  </div>
                @endif
              @endif
            </div>
          </div>

          {{-- Items de la compra --}}
          @if($purchase->items->count())
            <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03);">
              <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem; background:#f8fafc;">
                <i class='bx bx-list-ul'></i> Items ({{ $purchase->items->count() }}) - DEBUG: status={{ $purchase->status }}, client_id={{ $purchase->client_id }}
              </div>

              <div style="overflow-x:auto;">
                <table class="items-table">
                  <thead>
                    <tr>
                      <th>Descripción</th>
                      <th class="text-center">Unidad</th>
                      <th class="text-end">Cantidad</th>
                      <th class="text-end">Precio Unit.</th>
                      <th class="text-end">Total</th>
                      <th class="text-center">Facturado</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($purchase->items as $item)
                      <tr>
                        <td>{{ $item->descripcion }}</td>
                        <td style="text-align:center;">{{ $item->unidad_medida }}</td>
                        <td style="text-align:right;">{{ number_format($item->cantidad, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($item->valor_unitario, 2) }}</td>
                        <td style="text-align:right; font-weight:600;">{{ number_format($item->cantidad * $item->valor_unitario, 2) }}</td>
                        <td style="text-align:center;">
                          <span class="badge-status {{ ($item->cantidad - ($item->invoiced_quantity ?? 0)) > 0 ? 'badge-pending' : 'badge-invoiced' }}">
                            {{ ($item->invoiced_quantity ?? 0) . '/' . $item->cantidad }}
                          </span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection
