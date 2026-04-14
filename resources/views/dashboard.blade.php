@extends('layouts.app')

@section('title', 'Dashboard Analítico | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    /* KPI cards */
    .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:1rem; margin-bottom:1.5rem; }
    .kpi-card { background:var(--clr-bg-card,#fff); border-radius:14px; padding:1.25rem 1.4rem; border:1px solid var(--clr-border-light,#f0f0f0); box-shadow:0 2px 10px rgba(0,0,0,.04); display:flex; flex-direction:column; gap:.35rem; position:relative; overflow:hidden; transition:transform .2s; }
    .kpi-card:hover { transform:translateY(-3px); }
    .kpi-card::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; border-radius:14px 0 0 14px; }
    .kpi-card.green::before  { background:#10b981; }
    .kpi-card.red::before    { background:#ef4444; }
    .kpi-card.blue::before   { background:#3b82f6; }
    .kpi-card.purple::before { background:#8b5cf6; }
    .kpi-card.amber::before  { background:#f59e0b; }
    .kpi-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-text-muted,#6b7280); }
    .kpi-value { font-size:1.65rem; font-weight:800; color:var(--clr-text-main,#111827); line-height:1.1; letter-spacing:-.02em; }
    .kpi-sub   { font-size:.75rem; color:var(--clr-text-muted,#9ca3af); margin-top:.1rem; }
    .kpi-badge { display:inline-flex; align-items:center; gap:.2rem; font-size:.72rem; font-weight:700; padding:.15rem .5rem; border-radius:6px; }
    .kpi-badge.up   { background:rgba(16,185,129,.1);  color:#059669; }
    .kpi-badge.down { background:rgba(239,68,68,.1);    color:#dc2626; }
    /* Chart cards */
    .chart-row  { display:grid; gap:1.25rem; margin-bottom:1.25rem; }
    .chart-row-2 { grid-template-columns:2fr 1fr; }
    @media(max-width:1100px){ .chart-row-2 { grid-template-columns:1fr; } }
    .chart-card { background:var(--clr-bg-card,#fff); border-radius:14px; padding:1.25rem 1.5rem; border:1px solid var(--clr-border-light,#f0f0f0); box-shadow:0 2px 10px rgba(0,0,0,.04); }
    .chart-card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; flex-wrap:wrap; gap:.5rem; }
    .chart-card-title  { font-size:.88rem; font-weight:700; color:var(--clr-text-main,#111827); display:flex; align-items:center; gap:.4rem; }
    .chart-card-sub    { font-size:.75rem; color:var(--clr-text-muted,#9ca3af); margin-top:.1rem; }
    /* Top proveedores */
    .prov-bar { margin-bottom:.7rem; }
    .prov-bar-head  { display:flex; justify-content:space-between; font-size:.8rem; margin-bottom:.25rem; }
    .prov-bar-name  { font-weight:600; color:var(--clr-text-main,#374151); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:65%; }
    .prov-bar-val   { font-weight:700; color:#059669; white-space:nowrap; }
    .prov-bar-track { height:7px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
    .prov-bar-fill  { height:100%; border-radius:999px; background:linear-gradient(90deg,#1a6b57,#10b981); }
    /* News banner */
    .news-banner { background:linear-gradient(135deg,#1e3a8a,#3b82f6); color:#fff; border-radius:14px; padding:1.5rem 2rem; position:relative; overflow:hidden; margin-bottom:1.5rem; display:flex; align-items:center; gap:2rem; }
    .news-banner::after { content:''; position:absolute; right:-60px; top:-60px; width:220px; height:220px; background:rgba(255,255,255,.08); border-radius:50%; pointer-events:none; }
    .news-banner-content { position:relative; z-index:1; flex-grow:1; }
    .news-banner-label { display:inline-block; padding:.2rem .7rem; background:rgba(255,255,255,.2); border-radius:999px; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.75rem; }
    .news-banner h2 { font-size:1.3rem; font-weight:700; margin-bottom:.4rem; line-height:1.3; }
    .news-banner p  { font-size:.88rem; opacity:.9; margin-bottom:1.25rem; max-width:550px; }
    /* Activity sections */
    .dash-section { background:var(--clr-bg-card,#fff); border-radius:14px; border:1px solid var(--clr-border-light,#f0f0f0); box-shadow:0 2px 10px rgba(0,0,0,.04); overflow:hidden; }
    .dash-section-header { padding:1rem 1.25rem; border-bottom:1px solid var(--clr-border-light,#f3f4f6); display:flex; justify-content:space-between; align-items:center; }
    .dash-section-header h2 { font-size:.9rem; font-weight:700; color:var(--clr-text-main,#111827); display:flex; align-items:center; gap:.4rem; }
    .dash-section-body { padding:1rem 1.25rem; }
    .activity-list { display:flex; flex-direction:column; }
    .activity-item { display:flex; gap:.75rem; align-items:center; padding:.6rem .5rem; border-bottom:1px solid var(--clr-border-light,#f3f4f6); text-decoration:none; border-radius:8px; transition:background .15s; }
    .activity-item:last-child { border-bottom:none; }
    .activity-item:hover { background:var(--clr-hover-bg,#f9fafb); }
    .activity-icon { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
    .activity-content { flex-grow:1; min-width:0; }
    .activity-title { font-size:.85rem; font-weight:600; color:var(--clr-text-main,#111827); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .activity-meta  { font-size:.73rem; color:var(--clr-text-muted,#9ca3af); margin-top:.1rem; }
    /* Year selector */
    .year-filter { display:flex; align-items:center; gap:.4rem; }
    .year-filter select { padding:.35rem .65rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px; font-size:.82rem; color:var(--clr-text-main,#374151); background:transparent; cursor:pointer; outline:none; }
    /* Admin metric cards */
    .metric-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1.25rem; margin-bottom:1.75rem; }
    .metric-card { background:var(--clr-bg-card,#fff); border-radius:14px; padding:1.4rem; display:flex; align-items:center; gap:1rem; border:1px solid var(--clr-border-light,#f0f0f0); box-shadow:0 2px 10px rgba(0,0,0,.04); transition:transform .2s; }
    .metric-card:hover { transform:translateY(-3px); }
    .metric-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; flex-shrink:0; }
    .metric-icon.blue   { background:rgba(59,130,246,.1);  color:#3b82f6; }
    .metric-icon.red    { background:rgba(239,68,68,.1);   color:#ef4444; }
    .metric-icon.green  { background:rgba(16,185,129,.1);  color:#10b981; }
    .metric-icon.purple { background:rgba(139,92,246,.1);  color:#8b5cf6; }
    .metric-info h3 { font-size:.8rem; color:var(--clr-text-muted,#6b7280); font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.2rem; }
    .metric-info p  { font-size:1.65rem; color:var(--clr-text-main,#111827); font-weight:800; line-height:1; }
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
      <span class="menu-label">MENU PRINCIPAL</span>
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

        {{-- Ultima Novedad --}}
        @if($latestNews)
        <div class="news-banner">
          <div class="news-banner-content">
            <span class="news-banner-label"><i class='bx bx-news' style="margin-right:.2rem;"></i> Ultima Novedad</span>
            <h2>{{ $latestNews->title }}</h2>
            <p>{{ Str::limit($latestNews->excerpt ?? strip_tags($latestNews->content), 110) }}</p>
            <a href="{{ route('news.show', $latestNews) }}" style="display:inline-flex; align-items:center; gap:.4rem; background:#fff; color:#1e3a8a; padding:.5rem 1.25rem; border-radius:8px; font-size:.85rem; font-weight:600; text-decoration:none;">
              Leer Anuncio <i class='bx bx-right-arrow-alt'></i>
            </a>
          </div>
        </div>
        @endif

        @php
          $userRole = auth()->user()->role instanceof \App\Enums\RoleEnum ? auth()->user()->role->value : auth()->user()->role;
          $isGlobalPanel = in_array($userRole, ['admin', 'supervisor']);
        @endphp

        {{-- DASHBOARD ANALITICO solo para clientes con empresa activa --}}
        @if(!$isGlobalPanel && !empty($analytics))
        @php $an = $analytics; @endphp

        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; margin-bottom:1.25rem;">
          <div>
            <h1 style="font-size:1.25rem; font-weight:800; color:var(--clr-text-main,#111827); margin:0; display:flex; align-items:center; gap:.5rem;">
              <i class='bx bx-bar-chart-alt-2' style="color:#3b82f6;"></i> Analitica Financiera
            </h1>
            <p style="margin:0; font-size:.8rem; color:var(--clr-text-muted,#6b7280);">Resumen inteligente de ingresos y gastos</p>
          </div>
          <form method="GET" class="year-filter">
            <i class='bx bx-calendar' style="color:var(--clr-text-muted,#9ca3af);"></i>
            <select name="year" onchange="this.form.submit()">
              @foreach($an['anios'] as $anio)
                <option value="{{ $anio }}" {{ $anio == $selectedYear ? 'selected' : '' }}>{{ $anio }}</option>
              @endforeach
            </select>
          </form>
        </div>

        {{-- KPIs del anio --}}
        <div class="kpi-grid">
          <div class="kpi-card green">
            <span class="kpi-label">Ingresos {{ $selectedYear }}</span>
            <span class="kpi-value">S/ {{ number_format($an['total_ingresos'], 0) }}</span>
            <span class="kpi-sub">{{ $an['total_facturas'] }} comprobantes emitidos</span>
          </div>
          <div class="kpi-card red">
            <span class="kpi-label">Gastos {{ $selectedYear }}</span>
            <span class="kpi-value">S/ {{ number_format($an['total_gastos'], 0) }}</span>
            <span class="kpi-sub">{{ $an['total_compras'] }} compras registradas</span>
          </div>
          <div class="kpi-card {{ ($an['total_ingresos'] - $an['total_gastos']) >= 0 ? 'blue' : 'red' }}">
            <span class="kpi-label">Resultado Neto</span>
            <span class="kpi-value" style="color:{{ ($an['total_ingresos'] - $an['total_gastos']) >= 0 ? '#059669' : '#dc2626' }}">
              S/ {{ number_format(abs($an['total_ingresos'] - $an['total_gastos']), 0) }}
            </span>
            <span class="kpi-sub">{{ ($an['total_ingresos'] - $an['total_gastos']) >= 0 ? 'Utilidad' : 'Perdida' }} acumulada del anio</span>
          </div>
          <div class="kpi-card purple">
            <span class="kpi-label">Margen Bruto</span>
            <span class="kpi-value">{{ $an['margen'] }}%</span>
            <span class="kpi-sub">Sobre ingresos totales</span>
          </div>
          @if(!empty($financial))
          <div class="kpi-card amber">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
              <span class="kpi-label">Mes Actual</span>
              @if($financial['variacion'] !== null)
                <span class="kpi-badge {{ $financial['variacion'] >= 0 ? 'up' : 'down' }}">
                  <i class='bx {{ $financial['variacion'] >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}'></i>
                  {{ abs($financial['variacion']) }}%
                </span>
              @endif
            </div>
            <span class="kpi-value">S/ {{ number_format($financial['ingresos'], 0) }}</span>
            <span class="kpi-sub">Ingresos {{ $financial['mes'] }}</span>
          </div>
          @endif
        </div>

        {{-- Grafico principal: Ingresos vs Gastos --}}
        <div class="chart-row chart-row-2">
          <div class="chart-card">
            <div class="chart-card-header">
              <div>
                <div class="chart-card-title"><i class='bx bx-bar-chart-alt' style="color:#3b82f6;"></i> Ingresos vs Gastos Mensuales</div>
                <div class="chart-card-sub">Evolucion mensual {{ $selectedYear }}</div>
              </div>
              <div style="display:flex; gap:.5rem;">
                <span style="font-size:.73rem; display:flex; align-items:center; gap:.3rem; color:#10b981;"><span style="width:10px; height:10px; background:#10b981; border-radius:2px; display:inline-block;"></span> Ingresos</span>
                <span style="font-size:.73rem; display:flex; align-items:center; gap:.3rem; color:#ef4444;"><span style="width:10px; height:10px; background:#ef4444; border-radius:2px; display:inline-block;"></span> Gastos</span>
              </div>
            </div>
            <div style="position:relative; height:260px;"><canvas id="chartIngresosGastos"></canvas></div>
          </div>

          <div class="chart-card">
            <div class="chart-card-header">
              <div>
                <div class="chart-card-title"><i class='bx bx-pie-chart-alt-2' style="color:#8b5cf6;"></i> Gastos por Tipo</div>
                <div class="chart-card-sub">Distribucion de compras</div>
              </div>
            </div>
            <div style="position:relative; height:200px;"><canvas id="chartTipoGasto"></canvas></div>
            <div id="legendTipo" style="display:flex; flex-wrap:wrap; gap:.4rem .8rem; margin-top:.75rem; justify-content:center;"></div>
          </div>
        </div>

        {{-- IGV y Top Proveedores --}}
        <div class="chart-row chart-row-2">
          <div class="chart-card">
            <div class="chart-card-header">
              <div>
                <div class="chart-card-title"><i class='bx bx-receipt' style="color:#f59e0b;"></i> IGV Cobrado vs Pagado</div>
                <div class="chart-card-sub">Posicion de IGV mensual {{ $selectedYear }}</div>
              </div>
              <div style="display:flex; gap:.5rem;">
                <span style="font-size:.73rem; display:flex; align-items:center; gap:.3rem; color:#3b82f6;"><span style="width:10px; height:10px; background:#3b82f6; border-radius:2px; display:inline-block;"></span> Cobrado</span>
                <span style="font-size:.73rem; display:flex; align-items:center; gap:.3rem; color:#f59e0b;"><span style="width:10px; height:10px; background:#f59e0b; border-radius:2px; display:inline-block;"></span> Pagado</span>
              </div>
            </div>
            <div style="position:relative; height:220px;"><canvas id="chartIgv"></canvas></div>
          </div>

          <div class="chart-card">
            <div class="chart-card-header">
              <div>
                <div class="chart-card-title"><i class='bx bx-buildings' style="color:#10b981;"></i> Top Proveedores</div>
                <div class="chart-card-sub">Por monto total {{ $selectedYear }}</div>
              </div>
            </div>
            <div style="margin-top:.25rem;">
              @php $maxProv = $an['top_proveedores']->max('total') ?: 1; @endphp
              @forelse($an['top_proveedores'] as $prov)
              <div class="prov-bar">
                <div class="prov-bar-head">
                  <span class="prov-bar-name">{{ $prov['nombre'] }}</span>
                  <span class="prov-bar-val">S/ {{ number_format($prov['total'], 0) }}</span>
                </div>
                <div class="prov-bar-track">
                  <div class="prov-bar-fill" style="width:{{ round($prov['total'] / $maxProv * 100) }}%;"></div>
                </div>
              </div>
              @empty
              <p style="font-size:.83rem; color:var(--clr-text-muted,#9ca3af); text-align:center; padding:1.5rem 0;">Sin datos de compras para este anio.</p>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Resultado neto acumulado --}}
        <div class="chart-card" style="margin-bottom:1.25rem;">
          <div class="chart-card-header">
            <div>
              <div class="chart-card-title"><i class='bx bx-trending-up' style="color:#059669;"></i> Resultado Neto Acumulado</div>
              <div class="chart-card-sub">Ingresos menos Gastos por mes (acumulado) {{ $selectedYear }}</div>
            </div>
          </div>
          <div style="position:relative; height:200px;"><canvas id="chartResultado"></canvas></div>
        </div>

        {{-- Actividad --}}
        <div style="display:flex; gap:1.25rem; flex-wrap:wrap; margin-bottom:1.25rem;">
          <div class="dash-section" style="flex:1; min-width:260px;">
            <div class="dash-section-header">
              <h2><i class='bx bx-folder-open' style="color:#6b7280;"></i> Reportes Recientes</h2>
              <a href="{{ route('reports.index') }}" style="font-size:.8rem; color:#3b82f6; text-decoration:none;">Ver todos</a>
            </div>
            <div class="dash-section-body">
              <div class="activity-list">
                @forelse($recentReports as $report)
                <a href="javascript:void(0)" class="activity-item">
                  <div class="activity-icon" style="background:rgba(107,114,128,.1); color:#6b7280;">
                    @if($report->format === 'pdf')<i class='bx bxs-file-pdf' style="color:#ef4444;"></i>
                    @elseif(in_array($report->format, ['excel','csv']))<i class='bx bxs-file-blank' style="color:#10b981;"></i>
                    @else<i class='bx bx-bar-chart-alt-2' style="color:#f59e0b;"></i>
                    @endif
                  </div>
                  <div class="activity-content">
                    <div class="activity-title">{{ $report->title }}</div>
                    <div class="activity-meta">{{ $report->created_at->format('d/m/Y') }}</div>
                  </div>
                </a>
                @empty
                <p style="font-size:.83rem; color:var(--clr-text-muted,#9ca3af); padding:.75rem 0; text-align:center;">Sin reportes recientes.</p>
                @endforelse
              </div>
            </div>
          </div>

          <div class="dash-section" style="flex:1; min-width:260px;">
            <div class="dash-section-header">
              <h2><i class='bx bx-message-square-detail' style="color:#6b7280;"></i> Soporte Reciente</h2>
            </div>
            <div class="dash-section-body">
              <div class="activity-list">
                @forelse($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="activity-item">
                  <div class="activity-icon" style="background:{{ $ticket->status->value === 'open' ? 'rgba(239,68,68,.1)' : ($ticket->status->value === 'in_progress' ? 'rgba(59,130,246,.1)' : 'rgba(107,114,128,.1)') }}; color:{{ $ticket->status->value === 'open' ? '#dc2626' : ($ticket->status->value === 'in_progress' ? '#3b82f6' : '#6b7280') }};">
                    <i class='bx {{ $ticket->status->value === 'open' ? 'bx-envelope' : ($ticket->status->value === 'in_progress' ? 'bx-envelope-open' : 'bx-check-double') }}'></i>
                  </div>
                  <div class="activity-content">
                    <div class="activity-title">{{ $ticket->subject }}</div>
                    <div class="activity-meta">{{ $ticket->updated_at->diffForHumans() }}</div>
                  </div>
                </a>
                @empty
                <p style="font-size:.83rem; color:var(--clr-text-muted,#9ca3af); padding:.75rem 0; text-align:center;">Sin actividad reciente.</p>
                @endforelse
              </div>
            </div>
          </div>

          <div class="chart-card" style="min-width:200px; max-width:240px; display:flex; flex-direction:column; gap:.6rem; justify-content:center;">
            <p style="font-size:.8rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; letter-spacing:.05em; margin:0 0 .3rem;"><i class='bx bx-bolt-circle' style="color:#f59e0b;"></i> Accesos Rapidos</p>
            @can('create', App\Models\Ticket::class)
            <a href="{{ route('tickets.create') }}" class="btn-primary" style="text-align:center; padding:.6rem; font-size:.83rem;">
              <i class='bx bx-pencil'></i> Nueva Consulta
            </a>
            @endcan
            <a href="{{ route('facturador.compras.index') }}" class="btn-secondary" style="text-align:center; padding:.6rem; font-size:.83rem;">
              <i class='bx bx-cart'></i> Ver Compras
            </a>
            <a href="{{ route('facturador.invoices.index') }}" class="btn-secondary" style="text-align:center; padding:.6rem; font-size:.83rem;">
              <i class='bx bx-receipt'></i> Ver Facturas
            </a>
            @can('viewAny', App\Models\FinalDocument::class)
            <a href="{{ route('final-documents.index') }}" class="btn-secondary" style="text-align:center; padding:.6rem; font-size:.83rem;">
              <i class='bx bx-folder'></i> Docs. Finales
            </a>
            @endcan
          </div>
        </div>

        <script>
          window.__DASH__ = {
            labels:     @json($an['labels']),
            ingresos:   @json($an['ingresos_mes']),
            gastos:     @json($an['gastos_mes']),
            igvCobrado: @json($an['igv_cobrado']),
            igvPagado:  @json($an['igv_pagado']),
            tipoLabels: @json($an['por_tipo']->keys()),
            tipoValues: @json($an['por_tipo']->values()),
          };
        </script>

        {{-- Admin / Auxiliar panel basico --}}
        @elseif($isGlobalPanel || isset($userRole) && $userRole === 'auxiliar')
        <div class="page-header simple-header" style="margin-bottom:1.25rem; padding-bottom:0; border:none;">
          <h1 class="page-title">Vista General</h1>
          <p class="page-description" style="color:var(--clr-text-muted,#6b7280);">Resumen de actividad y metricas clave.</p>
        </div>
        <div class="metric-cards">
          @if($isGlobalPanel)
          <div class="metric-card">
            <div class="metric-icon blue"><i class='bx bx-buildings'></i></div>
            <div class="metric-info"><h3>Total Empresas</h3><p>{{ $metrics['total_companies'] }}</p></div>
          </div>
          <div class="metric-card">
            <div class="metric-icon purple"><i class='bx bx-file'></i></div>
            <div class="metric-info"><h3>Reportes Emitidos</h3><p>{{ $metrics['total_reports'] }}</p></div>
          </div>
          <div class="metric-card">
            <div class="metric-icon red"><i class='bx bx-message-square-error'></i></div>
            <div class="metric-info"><h3>Consultas Abiertas</h3><p>{{ $metrics['open_tickets'] }}</p></div>
          </div>
          @else
          <div class="metric-card">
            <div class="metric-icon blue"><i class='bx bx-buildings'></i></div>
            <div class="metric-info"><h3>Empresas Asignadas</h3><p>{{ $metrics['total_companies'] }}</p></div>
          </div>
          <div class="metric-card">
            <div class="metric-icon purple"><i class='bx bx-file'></i></div>
            <div class="metric-info"><h3>Total Reportes</h3><p>{{ $metrics['total_reports'] }}</p></div>
          </div>
          <div class="metric-card">
            <div class="metric-icon red"><i class='bx bx-message-square-error'></i></div>
            <div class="metric-info"><h3>Consultas Activas</h3><p>{{ $metrics['open_tickets'] }}</p></div>
          </div>
          @endif
        </div>
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; flex-wrap:wrap;">
          <div class="dash-section">
            <div class="dash-section-header">
              <h2><i class='bx bx-folder-open'></i> Reportes Recientes</h2>
              <a href="{{ route('reports.index') }}" style="font-size:.8rem; color:#3b82f6; text-decoration:none;">Ver todos</a>
            </div>
            <div class="dash-section-body">
              <div class="activity-list">
                @forelse($recentReports as $report)
                <a href="javascript:void(0)" class="activity-item">
                  <div class="activity-icon" style="background:rgba(107,114,128,.1); color:#6b7280;"><i class='bx bx-file'></i></div>
                  <div class="activity-content">
                    <div class="activity-title">{{ $report->title }}</div>
                    <div class="activity-meta">{{ $report->company->name ?? '' }} &middot; {{ $report->created_at->format('d/m/Y') }}</div>
                  </div>
                </a>
                @empty
                <p style="font-size:.83rem; color:#9ca3af; text-align:center; padding:1rem 0;">Sin reportes recientes.</p>
                @endforelse
              </div>
            </div>
          </div>
          <div class="dash-section">
            <div class="dash-section-header"><h2><i class='bx bx-message-square-detail'></i> Soporte Reciente</h2></div>
            <div class="dash-section-body">
              <div class="activity-list">
                @forelse($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="activity-item">
                  <div class="activity-icon" style="background:rgba(239,68,68,.1); color:#dc2626;"><i class='bx bx-envelope'></i></div>
                  <div class="activity-content">
                    <div class="activity-title">{{ $ticket->subject }}</div>
                    <div class="activity-meta">{{ $ticket->company->name ?? '' }} &middot; {{ $ticket->updated_at->diffForHumans() }}</div>
                  </div>
                </a>
                @empty
                <p style="font-size:.83rem; color:#9ca3af; text-align:center; padding:1rem 0;">Sin actividad reciente.</p>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        @else
        <div style="text-align:center; padding:3rem 0;">
          <i class='bx bx-bar-chart-alt-2' style="font-size:3rem; color:#d1d5db;"></i>
          <p style="color:var(--clr-text-muted,#6b7280); margin-top:.75rem;">Selecciona una empresa activa para ver la analitica financiera.</p>
          <a href="{{ route('companies.index') }}" class="btn-primary" style="margin-top:1rem; display:inline-flex; gap:.4rem;">
            <i class='bx bx-buildings'></i> Ir a Empresas
          </a>
        </div>
        @endif

      </div>
    </main>
  </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
  const d = window.__DASH__;
  if (!d) return;

  const isDark    = document.body.classList.contains('dark-mode');
  const gridColor = isDark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)';
  const textColor = isDark ? '#9ca3af' : '#6b7280';

  const scalesXY = {
    x: { grid:{color:gridColor}, ticks:{color:textColor, font:{size:11}} },
    y: { grid:{color:gridColor}, ticks:{color:textColor, font:{size:11}, callback: v => 'S/ '+v.toLocaleString('es-PE')} },
  };
  const moneyTip = ctx => ' S/ '+Number(ctx.raw).toLocaleString('es-PE',{minimumFractionDigits:2});

  // 1. Ingresos vs Gastos
  new Chart(document.getElementById('chartIngresosGastos'), {
    type: 'bar',
    data: {
      labels: d.labels,
      datasets: [
        { label:'Ingresos', data:d.ingresos, backgroundColor:'rgba(16,185,129,.75)', borderColor:'#10b981', borderWidth:1.5, borderRadius:5, borderSkipped:false },
        { label:'Gastos',   data:d.gastos,   backgroundColor:'rgba(239,68,68,.65)',  borderColor:'#ef4444', borderWidth:1.5, borderRadius:5, borderSkipped:false },
      ],
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{callbacks:{label:moneyTip}}}, scales:scalesXY },
  });

  // 2. Donut por tipo
  const palette = ['#3b82f6','#ef4444','#f59e0b','#8b5cf6','#10b981'];
  new Chart(document.getElementById('chartTipoGasto'), {
    type: 'doughnut',
    data: { labels:d.tipoLabels, datasets:[{data:d.tipoValues, backgroundColor:palette, borderWidth:2, hoverOffset:8}] },
    options: { responsive:true, maintainAspectRatio:false, cutout:'65%', plugins:{legend:{display:false}, tooltip:{callbacks:{label:moneyTip}}} },
  });
  // Leyenda manual
  const leg = document.getElementById('legendTipo');
  if (leg) {
    const total = d.tipoValues.reduce((a,b)=>a+b,0);
    d.tipoLabels.forEach((lbl,i) => {
      const pct = total > 0 ? Math.round(d.tipoValues[i]/total*100) : 0;
      leg.innerHTML += `<span style="font-size:.72rem;display:flex;align-items:center;gap:.25rem;"><span style="width:9px;height:9px;background:${palette[i]};border-radius:2px;display:inline-block;"></span>${lbl} (${pct}%)</span>`;
    });
  }

  // 3. IGV
  new Chart(document.getElementById('chartIgv'), {
    type: 'line',
    data: {
      labels: d.labels,
      datasets: [
        { label:'IGV Cobrado', data:d.igvCobrado, borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.12)', fill:true, tension:.4, pointRadius:3 },
        { label:'IGV Pagado',  data:d.igvPagado,  borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,.12)', fill:true, tension:.4, pointRadius:3 },
      ],
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{callbacks:{label:moneyTip}}}, scales:scalesXY },
  });

  // 4. Resultado neto acumulado (línea)
  let acum = 0;
  const resultAcum = d.ingresos.map((v,i) => { acum += (v - d.gastos[i]); return Math.round(acum*100)/100; });
  new Chart(document.getElementById('chartResultado'), {
    type: 'line',
    data: {
      labels: d.labels,
      datasets: [{ 
        label:'Resultado Acumulado', 
        data:resultAcum, 
        borderColor:'#059669', 
        backgroundColor:'rgba(16,185,129,.12)', 
        fill:true, 
        tension:.4, 
        pointRadius:5,
        pointBackgroundColor:'#059669',
        pointBorderColor:'#fff',
        pointBorderWidth:2,
        borderWidth:2.5 
      }],
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, tooltip:{callbacks:{label:moneyTip}}}, scales:scalesXY },
  });
})();
</script>
@endpush