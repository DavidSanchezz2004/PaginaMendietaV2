@extends('layouts.app')

@section('title', 'Panel de Control - Flujo Compras a Facturas')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .stat-card {
      background: linear-gradient(135deg, var(--clr-active-bg, #1a6b57) 0%, var(--clr-active-bg, #1a6b57) 100%);
      color: white;
      border-radius: 10px;
      padding: 1.25rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-card i {
      font-size: 2rem;
      opacity: 0.8;
    }

    .stat-value {
      font-weight: 700;
      font-size: 1.75rem;
    }

    .stat-label {
      font-size: 0.85rem;
      opacity: 0.9;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.75rem;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .badge-registered {
      background: rgba(100, 116, 139, 0.1);
      color: #64748b;
    }

    .badge-assigned {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .badge-guided {
      background: rgba(245, 158, 11, 0.1);
      color: #f59e0b;
    }

    .badge-partially_invoiced {
      background: rgba(168, 85, 247, 0.1);
      color: #a855f7;
    }

    .badge-invoiced {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }

    .card-flow {
      background: #fff;
      border-left: 4px solid var(--clr-active-bg, #1a6b57);
      border-radius: 8px;
      padding: 1.25rem;
      margin-bottom: 1rem;
      transition: all 0.2s;
    }

    .card-flow:hover {
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .flow-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 0.75rem;
    }

    .flow-info {
      flex: 1;
    }

    .flow-title {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .flow-subtitle {
      font-size: 0.85rem;
      color: var(--clr-text-muted, #6b7280);
    }

    .flow-progress {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0.75rem;
      margin: 1rem 0;
    }

    .progress-step {
      text-align: center;
    }

    .progress-step-icon {
      width: 40px;
      height: 40px;
      margin: 0 auto 0.5rem;
      background: #e2e8f0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      color: #6b7280;
    }

    .progress-step.completed .progress-step-icon {
      background: var(--clr-active-bg, #1a6b57);
      color: white;
    }

    .progress-step.active .progress-step-icon {
      background: #3b82f6;
      color: white;
      box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.1);
    }

    .progress-step-label {
      font-size: 0.7rem;
      font-weight: 600;
      color: var(--clr-text-muted, #6b7280);
    }

    .flow-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .filter-tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }

    .filter-tab {
      padding: 0.5rem 1rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #fff;
      cursor: pointer;
      transition: all 0.2s;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .filter-tab.active {
      background: var(--clr-active-bg, #1a6b57);
      color: white;
      border-color: var(--clr-active-bg, #1a6b57);
    }

    .filter-tab:hover {
      border-color: var(--clr-active-bg, #1a6b57);
    }

    .empty-state {
      text-align: center;
      padding: 3rem;
      color: var(--clr-text-muted, #6b7280);
    }

    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
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
                <i class='bx bx-layout'></i> Panel de Control
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Monitorea el flujo de tus compras a facturas</small>
            </div>
          </div>

          {{-- Stats Cards --}}
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
            <div class="stat-card">
              <div>
                <i class='bx bx-shopping-bag'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label">Compras Totales</div>
              </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
              <div>
                <i class='bx bx-hourglass-top'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
                <div class="stat-label">Sin Asignar Cliente</div>
              </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
              <div>
                <i class='bx bx-user'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['assigned'] ?? 0 }}</div>
                <div class="stat-label">Cliente Asignado</div>
              </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
              <div>
                <i class='bx bx-file-blank'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['guided'] ?? 0 }}</div>
                <div class="stat-label">Con Guía</div>
              </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
              <div>
                <i class='bx bx-receipt'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['invoiced'] ?? 0 }}</div>
                <div class="stat-label">Facturadas</div>
              </div>
            </div>
          </div>

          {{-- Filtros --}}
          <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">Todas</button>
            <button class="filter-tab" data-filter="registered">Sin Asignar</button>
            <button class="filter-tab" data-filter="assigned">Cliente Asignado</button>
            <button class="filter-tab" data-filter="guided">Con Guía</button>
            <button class="filter-tab" data-filter="invoiced">Facturadas</button>
          </div>

          {{-- Lista de flujos --}}
          @if($purchases->count())
            <div id="flowsList">
              @foreach($purchases as $purchase)
                <div class="card-flow" data-status="{{ $purchase->status }}">
                  <div class="flow-header">
                    <div class="flow-info">
                      <div class="flow-title">{{ $purchase->serie_numero }}</div>
                      <div class="flow-subtitle">
                        {{ $purchase->provider->nombre_razon_social }} 
                        @if($purchase->client)
                          → <strong>{{ $purchase->client->nombre_razon_social }}</strong>
                        @endif
                      </div>
                    </div>
                    <span class="status-badge badge-{{ $purchase->status }}">
                      {{ match($purchase->status) {
                        'registered' => 'Sin Asignar',
                        'assigned' => 'Cliente Asignado',
                        'guided' => 'Con Guía',
                        'partially_invoiced' => 'Parcial',
                        'invoiced' => 'Facturada',
                        default => $purchase->status
                      } }}
                    </span>
                  </div>

                  {{-- Progress --}}
                  <div class="flow-progress">
                    <div class="progress-step completed">
                      <div class="progress-step-icon"><i class='bx bx-shopping-bag'></i></div>
                      <div class="progress-step-label">Compra</div>
                    </div>

                    <div class="progress-step {{ in_array($purchase->status, ['assigned', 'guided', 'partially_invoiced', 'invoiced']) ? 'completed' : '' }}">
                      <div class="progress-step-icon"><i class='bx bx-user'></i></div>
                      <div class="progress-step-label">Cliente</div>
                    </div>

                    <div class="progress-step {{ in_array($purchase->status, ['guided', 'partially_invoiced', 'invoiced']) ? 'completed' : ($purchase->status === 'assigned' ? 'active' : '') }}">
                      <div class="progress-step-icon"><i class='bx bx-file-blank'></i></div>
                      <div class="progress-step-label">Guía</div>
                    </div>

                    <div class="progress-step {{ in_array($purchase->status, ['partially_invoiced', 'invoiced']) ? 'completed' : '' }}">
                      <div class="progress-step-icon"><i class='bx bx-receipt'></i></div>
                      <div class="progress-step-label">Factura</div>
                    </div>
                  </div>

                  {{-- Info resumen --}}
                  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:1rem; padding:1rem 0; border-top:1px solid #e2e8f0; font-size:.9rem;">
                    <div>
                      <span style="color:var(--clr-text-muted,#6b7280); font-size:.8rem;">Total:</span>
                      <div style="font-weight:600;">{{ $purchase->codigo_moneda }} {{ number_format($purchase->monto_total, 2) }}</div>
                    </div>
                    <div>
                      <span style="color:var(--clr-text-muted,#6b7280); font-size:.8rem;">Items:</span>
                      <div style="font-weight:600;">{{ $purchase->items->count() }}</div>
                    </div>
                    <div>
                      <span style="color:var(--clr-text-muted,#6b7280); font-size:.8rem;">Fecha:</span>
                      <div style="font-weight:600;">{{ $purchase->fecha_emision->format('d/m/Y') }}</div>
                    </div>
                  </div>

                  {{-- Acciones --}}
                  <div class="flow-actions">
                    <a href="{{ route('facturador.purchases.guia-flow', $purchase) }}" class="btn-primary" style="padding:.4rem .8rem; font-size:.85rem; text-decoration:none;">
                      <i class='bx bx-arrow-right'></i> Ver Flujo
                    </a>

                    @if(!$purchase->client)
                      <a href="{{ route('facturador.purchase-client.assign', $purchase) }}" class="btn-secondary" style="padding:.4rem .8rem; font-size:.85rem; text-decoration:none;">
                        <i class='bx bx-user-plus'></i> Asignar
                      </a>
                    @elseif($purchase->status === 'assigned')
                      <a href="{{ route('facturador.compras.guia.preview', $purchase) }}" class="btn-secondary" style="padding:.4rem .8rem; font-size:.85rem; text-decoration:none;">
                        <i class='bx bx-file-blank'></i> Guía
                      </a>
                    @elseif($purchase->status === 'guided')
                      <a href="{{ route('facturador.guias.index', ['from_purchase' => $purchase->id]) }}" class="btn-secondary" style="padding:.4rem .8rem; font-size:.85rem; text-decoration:none;">
                        <i class='bx bx-receipt'></i> Facturar
                      </a>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>

            {{-- Paginación si es necesaria --}}
            @if($purchases->hasPages())
              <div style="margin-top:2rem; display:flex; justify-content:center;">
                {{ $purchases->links() }}
              </div>
            @endif
          @else
            <div class="empty-state">
              <i class='bx bx-inbox'></i>
              <p>No hay compras registradas</p>
            </div>
          @endif

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection

@push('scripts')
<script>
  // Filtrado de estado
  document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
      const filter = this.dataset.filter;

      // Actualizar tab activo
      document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      // Filtrar cards
      document.querySelectorAll('.card-flow').forEach(card => {
        if (filter === 'all' || card.dataset.status === filter) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
</script>
@endpush
