@extends('layouts.app')

@section('title', 'Guías de Remisión')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    :root {
      --clr-active-bg: #1a6b57;
      --clr-border-light: #e2e8f0;
      --clr-text-muted: #6b7280;
      --clr-text-main: #374151;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: #f8fafc;
      color: var(--clr-text-main);
    }

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

    .filter-bar {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
      background: white;
      padding: 1rem;
      border-radius: 10px;
      border: 1px solid var(--clr-border-light);
    }

    .guia-row {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1fr 0.8fr auto;
      gap: 1rem;
      align-items: center;
      padding: 1rem;
      border-bottom: 1px solid var(--clr-border-light);
      transition: background 0.2s;
      background: white;
    }

    .guia-row:hover {
      background: rgba(0, 0, 0, 0.01);
    }

    .estado-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.75rem;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .badge-draft {
      background: rgba(100, 116, 139, 0.1);
      color: #64748b;
    }

    .badge-generated {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .badge-invoiced {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }

    .btn-primary {
      background: var(--clr-active-bg, #1a6b57);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 0.6rem 1.25rem;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
      transition: all 0.2s;
    }

    .btn-primary:hover {
      background: #145849;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(26, 107, 87, 0.3);
    }

    .btn-secondary {
      background: #f3f4f6;
      color: var(--clr-text-main);
      border: 1px solid var(--clr-border-light);
      border-radius: 8px;
      padding: 0.6rem 1.25rem;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
      transition: all 0.2s;
    }

    .btn-secondary:hover {
      background: #e5e7eb;
      border-color: #d1d5db;
    }

    .module-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid var(--clr-border-light);
    }

    .module-toolbar h1 {
      margin: 0;
      font-size: 1.75rem;
      font-weight: 700;
    }

    .placeholder-content {
      background: white;
      border-radius: 10px;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .module-card-wide {
      width: 100%;
    }

    input[type="text"],
    input[type="date"],
    select {
      width: 100%;
      padding: 0.55rem 0.85rem;
      border: 1px solid var(--clr-border-light);
      border-radius: 8px;
      font-size: 0.9rem;
      outline: none;
      font-family: inherit;
      transition: border-color 0.2s;
    }

    input[type="text"]:focus,
    input[type="date"]:focus,
    select:focus {
      border-color: var(--clr-active-bg);
      box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1);
    }

    .pagination {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid var(--clr-border-light);
    }

    .pagination a,
    .pagination span {
      padding: 0.5rem 0.75rem;
      border: 1px solid var(--clr-border-light);
      border-radius: 6px;
      text-decoration: none;
      color: var(--clr-text-main);
      transition: all 0.2s;
    }

    .pagination a:hover {
      background: var(--clr-active-bg);
      color: white;
      border-color: var(--clr-active-bg);
    }

    .pagination .active {
      background: var(--clr-active-bg);
      color: white;
      border-color: var(--clr-active-bg);
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
                <i class='bx bx-file-blank'></i> Guías de Remisión
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Listado de guías de remisión generadas</small>
            </div>
          </div>

          {{-- Alertas --}}
          @if(session('success'))
            <div style="background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#166534; font-size:.9rem;">
              <i class='bx bx-check-circle' style="margin-right:.4rem;"></i>
              {{ session('success') }}
            </div>
          @endif

          @if(session('error'))
            <div style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#991b1b; font-size:.9rem;">
              <i class='bx bx-x-circle' style="margin-right:.4rem;"></i>
              {{ session('error') }}
            </div>
          @endif

          {{-- Stats --}}
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
            <div class="stat-card">
              <div>
                <i class='bx bx-file'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['total_guias'] ?? 0 }}</div>
                <div class="stat-label">Total Guías</div>
              </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
              <div>
                <i class='bx bx-hourglass-top'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
                <div class="stat-label">Pendientes</div>
              </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
              <div>
                <i class='bx bx-check-double'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['invoiced'] ?? 0 }}</div>
                <div class="stat-label">Facturadas</div>
              </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
              <div>
                <i class='bx bx-info-circle'></i>
              </div>
              <div>
                <div class="stat-value">{{ $stats['draft'] ?? 0 }}</div>
                <div class="stat-label">Borradores</div>
              </div>
            </div>
          </div>

          {{-- Filtros --}}
          <form method="GET" action="{{ route('facturador.guias.index') }}" class="filter-bar" style="margin-bottom: 1.5rem;">
            <div>
              <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.4rem;">Búsqueda</label>
              <input type="text" name="search" placeholder="Número de guía, compra..."
                     value="{{ request('search') }}"
                     style="width:100%; padding:.55rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
            </div>

            <div>
              <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.4rem;">Estado</label>
              <select name="estado" style="width:100%; padding:.55rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
                <option value="">-- Todos --</option>
                <option value="draft" @if(request('estado') == 'draft') selected @endif>Borrador</option>
                <option value="generated" @if(request('estado') == 'generated') selected @endif>Generada</option>
                <option value="invoiced" @if(request('estado') == 'invoiced') selected @endif>Facturada</option>
              </select>
            </div>

            <div>
              <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.4rem;">Desde</label>
              <input type="date" name="from" value="{{ request('from') }}"
                     style="width:100%; padding:.55rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
            </div>

            <div>
              <label style="font-size:.85rem; font-weight:600; display:block; margin-bottom:.4rem;">Hasta</label>
              <input type="date" name="to" value="{{ request('to') }}"
                     style="width:100%; padding:.55rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.9rem; outline:none; font-family:inherit; box-sizing:border-box;">
            </div>

            <div style="display:flex; gap:.5rem; align-items:flex-end;">
              <button type="submit" class="btn-primary" style="padding:.55rem 1rem;">
                <i class='bx bx-search'></i> Buscar
              </button>
              <a href="{{ route('facturador.guias.index') }}" class="btn-secondary" style="padding:.55rem 1rem; text-decoration:none;">
                <i class='bx bx-reset'></i> Limpiar
              </a>
            </div>
          </form>

          {{-- Tabla de guías --}}
          @if($guias->count())
            <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03);">
              <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem;">
                <i class='bx bx-list-ul'></i> {{ $guias->count() }} guía(s) encontrada(s)
              </div>

              <div style="overflow-x:auto;">
                <div style="min-width:900px;">
                  @foreach($guias as $guia)
                    <div class="guia-row">
                      {{-- Número --}}
                      <div>
                        <div style="font-weight:600; color:var(--clr-active-bg,#1a6b57);">
                          <a href="{{ route('facturador.guias.show', $guia) }}" style="text-decoration:none; color:inherit;">
                            {{ $guia->numero }}
                          </a>
                        </div>
                        <small style="color:var(--clr-text-muted,#6b7280);">Compra: {{ $guia->purchase?->serie_numero ?? '—' }}</small>
                      </div>

                      {{-- Cliente --}}
                      <div>
                        <div style="font-size:.9rem;">{{ $guia->client->nombre_razon_social }}</div>
                        <small style="color:var(--clr-text-muted,#6b7280);">{{ $guia->client->numero_documento }}</small>
                      </div>

                      {{-- Fecha --}}
                      <div style="text-align:center;">
                        <div style="font-size:.9rem;">{{ $guia->fecha_emision->format('d/m/Y') }}</div>
                        <small style="color:var(--clr-text-muted,#6b7280);">{{ $guia->fecha_emision->format('l') }}</small>
                      </div>

                      {{-- Items --}}
                      <div style="text-align:center;">
                        <div style="font-weight:600;">{{ $guia->items->count() }}</div>
                        <small style="color:var(--clr-text-muted,#6b7280);">{{ number_format($guia->getTotalItemsCount(), 2) }} unidades</small>
                      </div>

                      {{-- Estado --}}
                      <div>
                        <span class="estado-badge badge-{{ $guia->estado }}">
                          <i class='bx bx-{{ $guia->estado === 'invoiced' ? 'check-double' : ($guia->estado === 'generated' ? 'check' : 'hourglass-top') }}'></i>
                          {{ $guia->estado_label }}
                        </span>
                      </div>

                      {{-- Acciones --}}
                      <div style="display:flex; gap:.5rem; justify-content:flex-end;">
                        <a href="{{ route('facturador.guias.show', $guia) }}" class="btn-secondary" style="padding:.4rem .7rem; font-size:.8rem;" title="Ver detalle">
                          <i class='bx bx-eye'></i>
                        </a>

                        @if($guia->estado === 'generated' && !$guia->invoice_id)
                          <a href="{{ route('facturador.guias.invoices.create', $guia) }}" class="btn-primary" style="padding:.4rem .7rem; font-size:.8rem;" title="Facturar">
                            <i class='bx bx-plus'></i>
                          </a>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              {{-- Paginación --}}
              @if($guias->hasPages())
                <div style="padding:1rem 1.25rem; border-top:1px solid var(--clr-border-light,rgba(0,0,0,.06)); display:flex; justify-content:center;">
                  {{ $guias->links() }}
                </div>
              @endif
            </div>
          @else
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:2rem; text-align:center;">
              <i class='bx bx-inbox' style="font-size:3rem; color:#cbd5e1; margin-bottom:.5rem;"></i>
              <p style="color:#6b7280; margin:0;">No hay guías de remisión registradas</p>
            </div>
          @endif

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection
