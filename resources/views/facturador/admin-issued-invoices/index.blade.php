@extends('layouts.app')

@section('title', 'Comprobantes emitidos global | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .global-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .global-head h1 { margin:0; display:flex; align-items:center; gap:.55rem; font-size:1.55rem; color:var(--clr-text-main,#111827); }
    .global-head p { margin:.25rem 0 0; color:var(--clr-text-muted,#6b7280); font-size:.9rem; }
    .quota-card { border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; padding:1rem; min-width:min(360px,100%); background:rgba(15,23,42,.02); }
    .quota-line { display:flex; justify-content:space-between; gap:1rem; font-size:.9rem; color:var(--clr-text-main,#111827); font-weight:700; }
    .quota-bar { height:10px; border-radius:999px; background:#e5e7eb; overflow:hidden; margin:.7rem 0 .35rem; }
    .quota-fill { height:100%; background:#059669; border-radius:999px; }
    .quota-fill.is-warning { background:#d97706; }
    .quota-fill.is-danger { background:#dc2626; }
    .stat-cards { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; margin-bottom:1.4rem; }
    .stat-card { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; padding:1rem; }
    .stat-card span { display:block; color:var(--clr-text-muted,#6b7280); font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
    .stat-card strong { display:block; color:var(--clr-text-main,#111827); font-size:1.25rem; margin-top:.25rem; }
    .filter-bar { display:flex; gap:.65rem; flex-wrap:wrap; align-items:center; margin-bottom:1.25rem; padding:1rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; background:var(--clr-bg-card,#fff); }
    .filter-bar input, .filter-bar select { min-height:38px; border:1px solid var(--clr-border-light,#d1d5db); border-radius:8px; padding:.5rem .7rem; background:transparent; color:var(--clr-text-main,#111827); font:inherit; font-size:.88rem; }
    .module-table th { color:var(--clr-text-muted,#6b7280); font-weight:800; text-transform:uppercase; font-size:.72rem; letter-spacing:.04em; }
    .module-table td { color:var(--clr-text-main,#111827); font-size:.88rem; font-weight:500; vertical-align:middle; }
    .invoice-badge { display:inline-flex; align-items:center; padding:.28rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; white-space:nowrap; border:1px solid transparent; }
    .badge-draft { background:#f3f4f6; color:#4b5563; border-color:#e5e7eb; }
    .badge-ready { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
    .badge-sent, .badge-consulted { background:#ecfdf5; color:#047857; border-color:#bbf7d0; }
    .badge-error, .badge-voided, .badge-pending { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
    .doc-badge { display:inline-flex; align-items:center; padding:.25rem .6rem; border-radius:8px; background:#f8fafc; border:1px solid #e2e8f0; color:#334155; font-size:.74rem; font-weight:800; }
    .company-cell strong, .client-cell strong { display:block; color:var(--clr-text-main,#111827); font-size:.88rem; }
    .company-cell small, .client-cell small { display:block; color:var(--clr-text-muted,#6b7280); font-size:.75rem; margin-top:.15rem; }
    .amount-cell { text-align:right; font-weight:800; color:var(--clr-active-bg,#1a6b57); white-space:nowrap; }
    @media(max-width:980px){ .stat-cards { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:560px){ .stat-cards { grid-template-columns:1fr; } .quota-card { min-width:100%; } }
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
        'userName' => auth()->user()?->name,
        'userEmail' => auth()->user()?->email,
      ])

      <main class="main-content">
        <div class="module-content-stack">
          <div class="placeholder-content module-card-wide">
            <div class="global-head">
              <div>
                <h1><i class='bx bx-spreadsheet'></i> Comprobantes Emitidos Global</h1>
                <p>Vista interna para admin y supervisor, sin filtro por empresa activa.</p>
              </div>
              @php
                $quotaClass = $stats['quota_percent'] >= 95 ? 'is-danger' : ($stats['quota_percent'] >= 80 ? 'is-warning' : '');
              @endphp
              <div class="quota-card">
                <div class="quota-line">
                  <span>Plan mensual</span>
                  <span>{{ $stats['quota_used'] }} / {{ $stats['quota_limit'] }}</span>
                </div>
                <div class="quota-bar">
                  <div class="quota-fill {{ $quotaClass }}" style="width:{{ $stats['quota_percent'] }}%;"></div>
                </div>
                <small style="color:var(--clr-text-muted,#6b7280); font-weight:700;">
                  Restan {{ $stats['quota_remaining'] }} comprobantes para {{ $stats['period_label'] }}.
                </small>
              </div>
            </div>

            <div class="stat-cards">
              <div class="stat-card">
                <span>Registros del periodo</span>
                <strong>{{ $stats['total_period'] }}</strong>
              </div>
              <div class="stat-card">
                <span>Emitidos / consultados</span>
                <strong>{{ $stats['issued_period'] }}</strong>
              </div>
              <div class="stat-card">
                <span>Anulados</span>
                <strong>{{ $stats['voided_period'] }}</strong>
              </div>
              <div class="stat-card">
                <span>Importe no anulado</span>
                <strong style="font-size:1rem; line-height:1.55;">
                  @forelse($stats['amounts_by_currency'] as $currency => $amount)
                    <span style="display:block; color:var(--clr-text-main,#111827); font-size:1rem; text-transform:none; letter-spacing:0;">{{ $currency }} {{ number_format($amount, 2) }}</span>
                  @empty
                    <span style="color:var(--clr-text-muted,#6b7280); font-size:1rem; text-transform:none; letter-spacing:0;">Sin importes</span>
                  @endforelse
                </strong>
              </div>
            </div>

            <form method="GET" class="filter-bar">
              <input type="month" name="month" value="{{ $filters['month'] }}">
              <select name="company_id">
                <option value="">Todas las empresas</option>
                @foreach($companies as $company)
                  <option value="{{ $company->id }}" @selected((string) $filters['company_id'] === (string) $company->id)>
                    {{ $company->name }} - {{ $company->ruc }}
                  </option>
                @endforeach
              </select>
              <select name="tipo">
                <option value="">Todos los tipos</option>
                @foreach(['01' => 'Factura', '03' => 'Boleta', '07' => 'Nota de crédito', '08' => 'Nota de débito', '09' => 'GRE'] as $code => $label)
                  <option value="{{ $code }}" @selected($filters['tipo'] === $code)>{{ $label }}</option>
                @endforeach
              </select>
              <select name="estado">
                <option value="">Todos los estados</option>
                @foreach(\App\Enums\InvoiceStatusEnum::cases() as $status)
                  <option value="{{ $status->value }}" @selected($filters['estado'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
              </select>
              <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Serie, cliente, empresa, RUC...">
              <button type="submit" class="btn-primary" style="min-height:38px;"><i class='bx bx-search'></i> Filtrar</button>
              <a href="{{ route('facturador.admin-issued-invoices.index') }}" class="btn-secondary" style="min-height:38px; display:inline-flex; align-items:center;">
                <i class='bx bx-eraser'></i> Limpiar
              </a>
            </form>

            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>Empresa</th>
                    <th>Comprobante</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th style="text-align:right;">Total</th>
                    <th>Estado</th>
                    <th>SUNAT</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($invoices as $invoice)
                    @php
                      $tipoMap = ['01' => 'Factura', '03' => 'Boleta', '07' => 'N. Crédito', '08' => 'N. Débito', '09' => 'GRE'];
                    @endphp
                    <tr>
                      <td class="company-cell">
                        <strong>{{ $invoice->company->name ?? $invoice->company->razon_social ?? '—' }}</strong>
                        <small>RUC {{ $invoice->company->ruc ?? '—' }}</small>
                      </td>
                      <td><code>{{ $invoice->serie_numero }}</code></td>
                      <td><span class="doc-badge">{{ $tipoMap[$invoice->codigo_tipo_documento] ?? $invoice->codigo_tipo_documento }}</span></td>
                      <td>{{ $invoice->fecha_emision?->format('d/m/Y') ?? '—' }}</td>
                      <td class="client-cell">
                        <strong>{{ $invoice->client->nombre_razon_social ?? '—' }}</strong>
                        <small>{{ $invoice->client->numero_documento ?? '' }}</small>
                      </td>
                      <td class="amount-cell">{{ $invoice->codigo_moneda }} {{ number_format($invoice->monto_total, 2) }}</td>
                      <td>
                        <span class="invoice-badge badge-{{ $invoice->estado->value }}">{{ $invoice->estado->label() }}</span>
                      </td>
                      <td>
                        <span class="invoice-badge {{ $invoice->estado_feasy->isAccepted() ? 'badge-sent' : 'badge-pending' }}">
                          {{ $invoice->estado_feasy->label() }}
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="8">No hay comprobantes para los filtros seleccionados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div style="margin-top:1rem;">
              {{ $invoices->links() }}
            </div>
          </div>
        </div>
      </main>
    </section>
  </div>
@endsection
