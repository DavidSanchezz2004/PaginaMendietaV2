@extends('layouts.app')

@section('title', 'Guía ' . $guia->numero)

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .guia-header {
      background: linear-gradient(135deg, var(--clr-active-bg, #1a6b57) 0%, var(--clr-active-bg, #1a6b57) 100%);
      color: white;
      padding: 2rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
    }

    .guia-number {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
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

    .state-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .state-generated {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .state-invoiced {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }

    .state-draft {
      background: rgba(100, 116, 139, 0.1);
      color: #64748b;
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

    .items-table tbody tr:hover {
      background: rgba(0, 0, 0, 0.01);
    }

    .btn-actions {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-end;
      flex-wrap: wrap;
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

          {{-- Header con navegación --}}
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div>
              <h1 style="display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem;">
                <i class='bx bx-file-blank'></i> Guía de Remisión
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Detalle y estado</small>
            </div>
            <div class="btn-actions">
              <a href="{{ route('facturador.guias.index') }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
              @if($guia->estado === 'generated' && !$guia->invoice_id)
                <a href="{{ route('facturador.guias.invoices.create', $guia) }}" class="btn-primary" style="padding:.6rem 1rem; text-decoration:none;">
                  <i class='bx bx-plus'></i> Crear Factura
                </a>
              @endif
            </div>
          </div>

          {{-- Alertas --}}
          @if(session('success'))
            <div style="background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem; color:#166534; font-size:.9rem;">
              <i class='bx bx-check-circle' style="margin-right:.4rem;"></i>
              {{ session('success') }}
            </div>
          @endif

          {{-- Header info --}}
          <div class="guia-header">
            <div class="guia-number">{{ $guia->numero }}</div>
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
              <span class="state-badge state-{{ $guia->estado }}">
                <i class='bx bx-{{ $guia->estado === 'invoiced' ? 'check-double' : ($guia->estado === 'generated' ? 'check' : 'hourglass-top') }}'></i>
                {{ $guia->estado_label }}
              </span>
              @if($guia->invoice_id)
                <span style="font-size:.9rem; opacity:.9;">
                  Factura: <strong>{{ $guia->invoice->serie_numero }}</strong>
                </span>
              @endif
            </div>
            <small style="opacity:.9;">Emitida el {{ $guia->fecha_emision->format('d/m/Y \a \l\a\s H:i') }}</small>
          </div>

          {{-- Información de guía --}}
          <div class="info-grid">
            <div class="info-box">
              <div class="info-label">Número Guía</div>
              <div class="info-value">{{ $guia->numero }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Fecha Emisión</div>
              <div class="info-value">{{ $guia->fecha_emision->format('d/m/Y') }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Motivo Traslado</div>
              <div class="info-value">{{ $guia->motivo }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Estado</div>
              <div class="info-value">
                <span class="state-badge state-{{ $guia->estado }}">
                  {{ $guia->estado_label }}
                </span>
              </div>
            </div>
          </div>

          {{-- Información de compra origen --}}
          <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03); margin-bottom:1.5rem;">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem; background:#f8fafc;">
              <i class='bx bx-shopping-bag'></i> Compra de Origen
            </div>
            <div style="padding:1.5rem;">
              <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Compra</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->purchase->serie_numero }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Proveedor</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->purchase->provider->nombre_razon_social }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Fecha Emisión</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->purchase->fecha_emision->format('d/m/Y') }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Total Compra</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->purchase->codigo_moneda }} {{ number_format($guia->purchase->monto_total, 2) }}</div>
                </div>
              </div>
            </div>
          </div>

          {{-- Información de cliente --}}
          <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03); margin-bottom:1.5rem;">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem; background:#f8fafc;">
              <i class='bx bx-user'></i> Cliente
            </div>
            <div style="padding:1.5rem;">
              <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Razón Social</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->client->nombre_razon_social }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">RUC/DNI</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->client->numero_documento }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Teléfono</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->client->telefono ?? '—' }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Email</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $guia->client->correo ?? '—' }}</div>
                </div>
              </div>

              <hr style="border:none; border-top:1px solid var(--clr-border-light,rgba(0,0,0,.06)); margin:1rem 0;">

              <div>
                <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Dirección de Entrega</div>
                <div style="font-size:1rem; font-weight:600; color:var(--clr-active-bg,#1a6b57);">
                  {{ $guia->clientAddress->full_address }}
                </div>
              </div>
            </div>
          </div>

          {{-- Items --}}
          <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03); margin-bottom:1.5rem;">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem; background:#f8fafc;">
              <i class='bx bx-list-ul'></i> Items ({{ $guia->items->count() }})
            </div>

            <div style="overflow-x:auto;">
              <table class="items-table">
                <thead>
                  <tr>
                    <th>Descripción</th>
                    <th class="text-center">Unidad</th>
                    <th class="text-end">Cantidad</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($guia->items as $item)
                    <tr>
                      <td>{{ $item->description }}</td>
                      <td style="text-align:center;">{{ $item->unit }}</td>
                      <td style="text-align:right; font-weight:600;">{{ number_format($item->quantity, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>

              </table>
            </div>
          </div>

          {{-- Factura vinculada (si existe) --}}
          @if($guia->invoice)
            <div style="background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.3); border-radius:14px; overflow:hidden; padding:1.5rem;">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <h3 style="margin:0 0 .5rem 0; color:#166534; display:flex; align-items:center; gap:.5rem;">
                    <i class='bx bx-check-double'></i> Facturada
                  </h3>
                  <p style="margin:0; color:#166534; font-size:.9rem;">
                    Esta guía fue facturada como <strong>{{ $guia->invoice->serie_numero }}</strong>
                    el {{ $guia->invoice->fecha_emision->format('d/m/Y \a \l\a\s H:i') }}
                  </p>
                </div>
                <a href="{{ route('facturador.invoices.show', $guia->invoice) }}" class="btn-primary" style="padding:.6rem 1rem; text-decoration:none; white-space:nowrap;">
                  <i class='bx bx-arrow-right'></i> Ver Factura
                </a>
              </div>
            </div>
          @else
            @if($guia->estado === 'generated')
              <div style="background:rgba(59,130,246,.08); border:1px solid rgba(59,130,246,.3); border-radius:14px; overflow:hidden; padding:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <div>
                    <h3 style="margin:0 0 .5rem 0; color:#1e40af; display:flex; align-items:center; gap:.5rem;">
                      <i class='bx bx-info-circle'></i> Lista para Facturar
                    </h3>
                    <p style="margin:0; color:#1e40af; font-size:.9rem;">
                      Esta guía está lista para ser facturada. Puedes crear la factura ahora.
                    </p>
                  </div>
                  <a href="{{ route('facturador.guias.invoices.create', $guia) }}" class="btn-primary" style="padding:.6rem 1rem; text-decoration:none; white-space:nowrap;">
                    <i class='bx bx-plus'></i> Crear Factura
                  </a>
                </div>
              </div>
            @endif
          @endif

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection
