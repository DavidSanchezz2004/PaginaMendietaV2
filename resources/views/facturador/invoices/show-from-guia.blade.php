@extends('layouts.app')

@section('title', 'Factura ' . $invoice->serie_numero)

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .invoice-header {
      background: linear-gradient(135deg, var(--clr-active-bg, #1a6b57) 0%, var(--clr-active-bg, #1a6b57) 100%);
      color: white;
      padding: 2rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
    }

    .invoice-number {
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

    .state-draft {
      background: rgba(100, 116, 139, 0.1);
      color: #64748b;
    }

    .state-sent {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .state-accepted {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }

    .state-rejected {
      background: rgba(239, 68, 68, 0.1);
      color: #dc2626;
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

    .totals-section {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .totals-box {
      background: #fff;
      border: 1px solid var(--clr-border-light, #e2e8f0);
      border-radius: 10px;
      padding: 1.5rem;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .total-row:last-child {
      border-bottom: none;
      font-weight: 700;
      font-size: 1.1rem;
      padding: 0.75rem 0;
      margin-top: 0.5rem;
      border-top: 2px solid var(--clr-active-bg, #1a6b57);
    }

    .btn-actions {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-end;
      flex-wrap: wrap;
    }

    .letter-link {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background: rgba(26, 107, 87, 0.1);
      color: var(--clr-active-bg, #1a6b57);
      border-radius: 6px;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .letter-link:hover {
      background: rgba(26, 107, 87, 0.2);
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
                <i class='bx bx-receipt'></i> Factura
              </h1>
              <small style="color:var(--clr-text-muted,#6b7280);">Detalle y acciones</small>
            </div>
            <div class="btn-actions">
              <a href="{{ route('facturador.invoices.index') }}" class="btn-secondary" style="padding:.6rem 1rem; text-decoration:none;">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
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
          <div class="invoice-header">
            <div class="invoice-number">{{ $invoice->serie_numero }}</div>
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
              <span class="state-badge state-{{ strtolower($invoice->estado ?? 'draft') }}">
                <i class='bx bx-{{ $invoice->estado === 'accepted' ? 'check' : 'document' }}'></i>
                {{ $invoice->estado_label ?? 'Borrador' }}
              </span>
            </div>
            <small style="opacity:.9;">Emitida el {{ $invoice->fecha_emision->format('d/m/Y \a \l\a\s H:i') }}</small>
          </div>

          {{-- Información de factura --}}
          <div class="info-grid">
            <div class="info-box">
              <div class="info-label">Serie / Número</div>
              <div class="info-value">{{ $invoice->serie_numero }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Fecha Emisión</div>
              <div class="info-value">{{ $invoice->fecha_emision->format('d/m/Y') }}</div>
            </div>

            <div class="info-box">
              <div class="info-label">Guía Origen</div>
              <div class="info-value">
                @if($invoice->guia)
                  <a href="{{ route('facturador.guias.show', $invoice->guia) }}" style="color:var(--clr-active-bg,#1a6b57); text-decoration:none;">
                    {{ $invoice->guia->numero }}
                  </a>
                @else
                  —
                @endif
              </div>
            </div>

            <div class="info-box">
              <div class="info-label">Moneda</div>
              <div class="info-value">{{ $invoice->codigo_moneda }}</div>
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
                  <div style="font-size:1rem; font-weight:600;">{{ $invoice->client->nombre_razon_social }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">RUC/DNI</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $invoice->client->numero_documento }}</div>
                </div>

                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Correo</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $invoice->client->correo ?? '—' }}</div>
                </div>
              </div>

              @if($invoice->clientAddress)
                <hr style="border:none; border-top:1px solid var(--clr-border-light,rgba(0,0,0,.06)); margin:1rem 0;">
                <div>
                  <div style="font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; margin-bottom:.5rem;">Dirección</div>
                  <div style="font-size:1rem; font-weight:600;">{{ $invoice->clientAddress->full_address }}</div>
                </div>
              @endif
            </div>
          </div>

          {{-- Items --}}
          <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03); margin-bottom:1.5rem;">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem; background:#f8fafc;">
              <i class='bx bx-list-ul'></i> Items ({{ $invoice->items->count() }})
            </div>

            <div style="overflow-x:auto;">
              <table class="items-table">
                <thead>
                  <tr>
                    <th>Descripción</th>
                    <th class="text-center">Unidad</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio Unit.</th>
                    <th class="text-end">Descuento</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($invoice->items as $item)
                    <tr>
                      <td>{{ $item->description }}</td>
                      <td style="text-align:center;">{{ $item->unit }}</td>
                      <td style="text-align:right;">{{ number_format($item->quantity, 2) }}</td>
                      <td style="text-align:right;">{{ number_format($item->unit_price, 2) }}</td>
                      <td style="text-align:right;">{{ number_format($item->monto_descuento ?? 0, 2) }}</td>
                      <td style="text-align:right; font-weight:600;">{{ number_format($item->total, 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          {{-- Totales --}}
          <div class="totals-section">
            <div></div>
            <div class="totals-box">
              <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ number_format($invoice->monto_total ?? 0, 2) }}</span>
              </div>

              @if($invoice->has_retention)
                <div class="total-row" style="color:#dc2626;">
                  <span>Retención ({{ $invoice->retention_percentage ?? 3 }}%):</span>
                  <span>- {{ number_format($invoice->retention_amount ?? 0, 2) }}</span>
                </div>
              @endif

              <div class="total-row" style="background:rgba(26,107,87,.05); color:var(--clr-active-bg,#1a6b57);">
                <span>{{ $invoice->has_retention ? 'Total Neto' : 'Total' }}:</span>
                <span>{{ number_format($invoice->has_retention ? ($invoice->total_after_retention ?? $invoice->monto_total) : $invoice->monto_total ?? 0, 2) }}</span>
              </div>
            </div>
          </div>

          {{-- Letras si existen --}}
          @if($invoice->letras->count())
            <div style="background:#fff; border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,.03); margin-bottom:1.5rem;">
              <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,rgba(0,0,0,.06)); font-weight:600; font-size:.92rem; display:flex; align-items:center; gap:.5rem; background:#f8fafc;">
                <i class='bx bx-transfer'></i> Letras de Cambio ({{ $invoice->letras->count() }})
              </div>

              <div style="padding:1.5rem;">
                <div style="display:flex; flex-wrap:wrap; gap:1rem;">
                  @foreach($invoice->letras as $letra)
                    <a href="{{ route('facturador.letras.show', $letra) }}" class="letter-link">
                      <i class='bx bx-file'></i>
                      {{ $letra->numero_letra }} · Venc. {{ $letra->fecha_vencimiento->format('d/m/Y') }}
                    </a>
                  @endforeach
                </div>
              </div>
            </div>
          @endif

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}
@endsection
