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
    .news-banner-actions { display:flex; flex-wrap:wrap; gap:.55rem; align-items:center; }
    .news-banner-action { display:inline-flex; align-items:center; gap:.4rem; border:1px solid rgba(255,255,255,.35); background:rgba(255,255,255,.12); color:#fff; padding:.5rem .9rem; border-radius:8px; font-size:.82rem; font-weight:800; text-decoration:none; cursor:pointer; }
    .news-banner-action.primary { background:#fff; color:#1e3a8a; border-color:#fff; }
    .news-banner-dismiss { position:absolute; top:.85rem; right:.85rem; z-index:2; width:34px; height:34px; border-radius:999px; border:1px solid rgba(255,255,255,.35); background:rgba(255,255,255,.12); color:#fff; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
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
    .sales-dashboard-grid { display:grid; grid-template-columns:2fr 1fr; gap:1.25rem; margin-bottom:1.25rem; }
    @media(max-width:980px){ .sales-dashboard-grid { grid-template-columns:1fr; } }
    .sales-bars { display:grid; grid-template-columns:repeat(6,1fr); gap:.85rem; align-items:end; min-height:150px; }
    .sales-bar-item { display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:.42rem; min-width:0; }
    .sales-bar-value { font-size:.74rem; font-weight:800; color:var(--clr-text-main,#111827); text-align:center; }
    .sales-bar-track { width:100%; height:92px; display:flex; align-items:flex-end; justify-content:center; border-bottom:1px solid var(--clr-border-light,#e5e7eb); }
    .sales-bar { width:min(44px,68%); min-height:4px; border-radius:7px 7px 0 0; background:#94a3b8; }
    .sales-bar.current { background:#059669; }
    .sales-bar-label { font-size:.7rem; color:var(--clr-text-muted,#6b7280); text-align:center; white-space:nowrap; }
    .status-mini-grid { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
    .status-mini { border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; padding:.8rem; background:rgba(248,250,252,.65); }
    .status-mini strong { display:block; font-size:1.35rem; color:var(--clr-text-main,#111827); line-height:1; }
    .status-mini span { display:block; margin-top:.3rem; font-size:.73rem; color:var(--clr-text-muted,#6b7280); font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
    .dashboard-page.main-content { justify-content:stretch; padding:1.35rem 1.6rem; }
    .dashboard-page .module-content-stack { width:100%; max-width:none; }
    .dashboard-shell { max-width:none; width:100%; margin:0; display:flex; flex-direction:column; gap:1rem; }
    .dashboard-hero { position:relative; overflow:hidden; background:linear-gradient(135deg,#064e43 0%,#0f766e 48%,#14b8a6 100%); border:1px solid rgba(15,118,110,.28); border-radius:14px; padding:1.45rem 1.55rem; box-shadow:0 18px 42px rgba(15,23,42,.13); display:grid; grid-template-columns:minmax(0,1fr) auto; gap:1.4rem; align-items:start; }
    .dashboard-hero::after { content:''; position:absolute; right:-70px; top:-90px; width:270px; height:270px; border-radius:50%; background:rgba(255,255,255,.12); pointer-events:none; }
    .dashboard-hero h1 { margin:0; color:#fff; font-size:1.75rem; line-height:1.12; display:flex; align-items:center; gap:.65rem; }
    .dashboard-hero h1 i { width:42px; height:42px; border-radius:11px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.16); color:#fff; font-size:1.35rem; }
    .dashboard-hero p { margin:.55rem 0 0; color:rgba(255,255,255,.88); font-size:.94rem; max-width:900px; }
    .dashboard-hero > * { position:relative; z-index:1; }
    .dashboard-hero-metrics { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.65rem; margin-top:1rem; max-width:720px; }
    .hero-metric { padding:.75rem .85rem; border:1px solid rgba(255,255,255,.18); border-radius:11px; background:rgba(255,255,255,.12); color:#fff; }
    .hero-metric span { display:block; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; opacity:.82; }
    .hero-metric strong { display:block; margin-top:.25rem; font-size:1.1rem; line-height:1; }
    .dashboard-actions { display:flex; gap:.5rem; flex-wrap:wrap; justify-content:flex-end; }
    .dashboard-actions a { display:inline-flex; align-items:center; gap:.35rem; border:1px solid rgba(255,255,255,.28); border-radius:9px; padding:.65rem .85rem; text-decoration:none; color:#fff; background:rgba(255,255,255,.12); font-size:.82rem; font-weight:900; white-space:nowrap; backdrop-filter:blur(6px); }
    .dashboard-actions a.primary { background:#fff; color:#064e43; border-color:#fff; }
    .dashboard-context-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.75rem; }
    .context-card { min-height:148px; border:1px solid #e1e8e6; border-radius:13px; background:#fff; padding:1rem; text-decoration:none; color:#0f172a; display:flex; flex-direction:column; gap:.55rem; box-shadow:0 8px 22px rgba(15,23,42,.045); position:relative; overflow:hidden; }
    .context-card::before { content:''; position:absolute; inset:0 0 auto 0; height:3px; background:#0f766e; opacity:.9; }
    .context-card:hover { border-color:#0f766e; box-shadow:0 14px 30px rgba(15,23,42,.09); transform:translateY(-1px); }
    .context-card i { width:34px; height:34px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; background:#e7f3ef; color:#16614f; font-size:1.12rem; }
    .context-card strong { font-size:.93rem; line-height:1.18; }
    .context-card span { color:#64748b; font-size:.78rem; line-height:1.35; }
    .context-card small { margin-top:auto; color:#0f766e; font-weight:900; font-size:.75rem; }
    .dashboard-filter-card { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:12px; padding:.85rem 1rem; display:flex; justify-content:space-between; gap:1rem; align-items:center; }
    .dashboard-filter-card .year-filter select { background:#fff; min-width:92px; }
    .kpi-grid.dashboard-kpis { grid-template-columns:repeat(5,minmax(0,1fr)); gap:.75rem; margin-bottom:0; }
    .dashboard-kpis .kpi-card { border-radius:13px; padding:1.05rem; box-shadow:0 8px 22px rgba(15,23,42,.04); min-height:126px; }
    .dashboard-kpis .kpi-card:hover { transform:none; }
    .dashboard-kpis .kpi-card::before { width:3px; }
    .dashboard-kpis .kpi-value { font-size:1.32rem; letter-spacing:0; }
    .dashboard-kpis .kpi-sub { color:#64748b; }
    .dashboard-main-grid { display:grid; grid-template-columns:minmax(0,1.7fr) minmax(340px,.75fr); gap:1rem; }
    .dashboard-stack { display:flex; flex-direction:column; gap:1rem; min-width:0; }
    .dashboard-side { display:flex; flex-direction:column; gap:1rem; min-width:0; }
    .chart-card { border-radius:13px; box-shadow:0 8px 22px rgba(15,23,42,.04); }
    .dash-section { border-radius:13px; box-shadow:0 8px 22px rgba(15,23,42,.04); }
    .dashboard-quick-grid { display:grid; grid-template-columns:1fr; gap:.6rem; }
    .dashboard-quick-grid a { display:flex; align-items:flex-start; gap:.65rem; min-height:52px; padding:.75rem .8rem; border:1px solid #e5eaf0; border-radius:9px; background:#fff; color:#334155; text-decoration:none; font-size:.82rem; font-weight:800; }
    .dashboard-quick-grid a i { font-size:1.05rem; color:#16614f; }
    .dashboard-quick-grid a span { display:block; color:#64748b; font-size:.73rem; font-weight:600; line-height:1.25; margin-top:.12rem; }
    .admin-dashboard-shell { width:100%; display:flex; flex-direction:column; gap:.85rem; }
    .bi-topbar {
      display:grid;
      grid-template-columns:minmax(0,1fr) auto;
      gap:.85rem;
      align-items:stretch;
      border:1px solid #dbe4ef;
      border-radius:10px;
      background:#fff;
      padding:.85rem;
      box-shadow:0 10px 24px rgba(15,23,42,.045);
    }
    .bi-title h1 { margin:0; font-size:1.45rem; line-height:1.1; color:#0f172a; display:flex; align-items:center; gap:.55rem; }
    .bi-title h1 i { width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:#e7f3ef; color:#0f766e; }
    .bi-title p { margin:.4rem 0 0; color:#64748b; font-size:.86rem; max-width:920px; }
    .bi-actions { display:flex; flex-wrap:wrap; gap:.45rem; justify-content:flex-end; align-content:center; }
    .bi-actions a { display:inline-flex; align-items:center; justify-content:center; gap:.35rem; min-height:36px; padding:.52rem .72rem; border-radius:8px; border:1px solid #dbe4ef; background:#fff; color:#334155; text-decoration:none; font-size:.78rem; font-weight:900; white-space:nowrap; }
    .bi-actions a.primary { background:#0f766e; border-color:#0f766e; color:#fff; }
    .bi-report-grid {
      display:grid;
      grid-template-columns:repeat(12,minmax(0,1fr));
      grid-auto-rows:minmax(92px,auto);
      gap:.75rem;
    }
    .bi-tile {
      border:1px solid #dbe4ef;
      border-radius:10px;
      background:#fff;
      box-shadow:0 8px 20px rgba(15,23,42,.04);
      padding:.9rem;
      min-width:0;
      overflow:hidden;
    }
    .bi-tile-header { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.75rem; }
    .bi-tile-title { color:#334155; font-size:.76rem; font-weight:900; text-transform:uppercase; letter-spacing:.04em; display:flex; align-items:center; gap:.35rem; }
    .bi-tile-link { color:#0f766e; text-decoration:none; font-size:.75rem; font-weight:900; }
    .bi-kpi { grid-column:span 2; min-height:104px; display:flex; flex-direction:column; justify-content:space-between; border-top:3px solid #0f766e; }
    .bi-kpi.blue { border-top-color:#3b82f6; }
    .bi-kpi.warn { border-top-color:#f59e0b; }
    .bi-kpi.danger { border-top-color:#ef4444; }
    .bi-kpi span { display:block; color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.04em; }
    .bi-kpi strong { display:block; margin-top:.25rem; color:#0f172a; font-size:1.85rem; line-height:1; }
    .bi-kpi small { display:block; margin-top:.35rem; color:#64748b; font-size:.72rem; line-height:1.25; }
    .bi-wide { grid-column:span 5; }
    .bi-mid { grid-column:span 4; }
    .bi-side { grid-column:span 3; }
    .bi-full { grid-column:span 12; }
    .bi-bar-list { display:grid; gap:.65rem; }
    .bi-bar-row { display:grid; grid-template-columns:120px minmax(0,1fr) 42px; gap:.65rem; align-items:center; }
    .bi-bar-row span { color:#475569; font-size:.78rem; font-weight:800; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .bi-bar-track { height:12px; border-radius:999px; background:#edf2f7; overflow:hidden; }
    .bi-bar-fill { height:100%; border-radius:999px; background:#0f766e; }
    .bi-bar-fill.blue { background:#3b82f6; }
    .bi-bar-fill.warn { background:#f59e0b; }
    .bi-bar-fill.danger { background:#ef4444; }
    .bi-bar-row strong { color:#0f172a; font-size:.82rem; text-align:right; }
    .bi-status-grid { display:grid; grid-template-columns:1fr 1fr; gap:.55rem; }
    .bi-status { border:1px solid #e5eaf0; border-radius:9px; padding:.75rem; background:#f8fafc; }
    .bi-status span { color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; letter-spacing:.04em; }
    .bi-status strong { display:block; margin-top:.25rem; color:#0f172a; font-size:1.35rem; line-height:1; }
    .bi-action-list { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.6rem; }
    .bi-action-list a { min-height:76px; border:1px solid #dbe4ef; border-radius:9px; text-decoration:none; color:#0f172a; background:#fff; padding:.75rem; display:flex; flex-direction:column; gap:.35rem; }
    .bi-action-list a:hover { border-color:#0f766e; background:#f8fffd; }
    .bi-action-list i { color:#0f766e; font-size:1.15rem; }
    .bi-action-list strong { font-size:.82rem; line-height:1.1; }
    .bi-action-list span { color:#64748b; font-size:.72rem; line-height:1.25; }
    .bi-list { display:flex; flex-direction:column; gap:.45rem; }
    .bi-list .activity-item { padding:.55rem; border:1px solid #eef2f7; border-radius:9px; }
    .sales-dashboard-grid { grid-template-columns:1fr; gap:1rem; margin-bottom:0; }
    @media(max-width:1180px){
      .kpi-grid.dashboard-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); }
      .dashboard-context-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
      .dashboard-main-grid { grid-template-columns:1fr; }
      .dashboard-side { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); }
      .bi-kpi { grid-column:span 4; }
      .bi-wide, .bi-mid, .bi-side { grid-column:span 6; }
      .bi-action-list { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media(max-width:760px){
      .dashboard-hero, .dashboard-filter-card { flex-direction:column; align-items:stretch; }
      .dashboard-hero { display:flex; }
      .dashboard-hero-metrics { grid-template-columns:1fr; }
      .dashboard-actions { justify-content:flex-start; }
      .bi-topbar { grid-template-columns:1fr; }
      .bi-actions { justify-content:flex-start; }
      .bi-kpi, .bi-wide, .bi-mid, .bi-side, .bi-full { grid-column:span 12; }
      .bi-action-list { grid-template-columns:1fr; }
      .kpi-grid.dashboard-kpis,
      .dashboard-side,
      .dashboard-context-grid,
      .dashboard-quick-grid { grid-template-columns:1fr; }
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

    <main class="main-content dashboard-page">
      <div class="module-content-stack">

        {{-- Ultima Novedad --}}
        @if($latestNews)
        <div class="news-banner" data-news-banner data-news-id="{{ $latestNews->id }}">
          <button type="button" class="news-banner-dismiss" data-news-read title="Marcar como leído">
            <i class='bx bx-x'></i>
          </button>
          <div class="news-banner-content">
            <span class="news-banner-label"><i class='bx bx-news' style="margin-right:.2rem;"></i> Ultima Novedad</span>
            <h2>{{ $latestNews->title }}</h2>
            <p>{{ Str::limit($latestNews->excerpt ?? strip_tags($latestNews->content), 110) }}</p>
            <div class="news-banner-actions">
              <a href="{{ route('news.show', $latestNews) }}" class="news-banner-action primary" data-news-open>
                Leer anuncio <i class='bx bx-right-arrow-alt'></i>
              </a>
              <button type="button" class="news-banner-action" data-news-read>
                <i class='bx bx-check'></i> Marcar como leído
              </button>
            </div>
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

        <div class="dashboard-shell">
          <div class="dashboard-hero">
            <div>
              <h1><i class='bx bx-home-smile'></i> Tu panel de trabajo</h1>
              <p>Desde aquí puedes emitir ventas, subir compras, revisar qué aceptó SUNAT, descargar documentos y escribirnos cuando necesites ayuda. La meta es que no tengas que buscar en el menú para hacer lo habitual.</p>
              <div class="dashboard-hero-metrics">
                <div class="hero-metric">
                  <span>Ingresos {{ $selectedYear }}</span>
                  <strong>S/ {{ number_format($an['total_ingresos'], 0) }}</strong>
                </div>
                <div class="hero-metric">
                  <span>Mes actual</span>
                  <strong>S/ {{ number_format($financial['ingresos'] ?? 0, 0) }}</strong>
                </div>
                <div class="hero-metric">
                  <span>Estado SUNAT</span>
                  <strong>{{ $an['estado_mes_actual']['errores'] }} errores</strong>
                </div>
              </div>
            </div>
            <div class="dashboard-actions">
              <a href="{{ route('facturador.invoices.create') }}" class="primary"><i class='bx bx-plus'></i> Nueva factura</a>
              <a href="{{ route('facturador.compras.subir') }}"><i class='bx bx-upload'></i> Subir compra</a>
              @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}"><i class='bx bx-message-square-add'></i> Consulta</a>
              @endcan
            </div>
          </div>

          <div class="dashboard-context-grid">
            <a href="{{ route('facturador.invoices.create') }}" class="context-card">
              <i class='bx bx-receipt'></i>
              <strong>Emitir comprobante</strong>
              <span>Crea facturas o boletas y revisa el estado SUNAT desde el mismo flujo.</span>
              <small>Emitir ahora</small>
            </a>
            <a href="{{ route('facturador.compras.subir') }}" class="context-card">
              <i class='bx bx-cloud-upload'></i>
              <strong>Enviar compras</strong>
              <span>Sube XML/PDF de proveedores para ordenar gastos, IGV y control contable.</span>
              <small>Subir archivo</small>
            </a>
            <a href="{{ route('facturador.invoices.index') }}" class="context-card">
              <i class='bx bx-shield-quarter'></i>
              <strong>Revisar SUNAT</strong>
              <span>Encuentra comprobantes con error, pendientes, aceptados o anulados.</span>
              <small>Ver estados</small>
            </a>
            @can('viewAny', App\Models\FinalDocument::class)
              <a href="{{ route('final-documents.index') }}" class="context-card">
                <i class='bx bx-folder-open'></i>
                <strong>Documentos finales</strong>
                <span>Accede a reportes, constancias y archivos compartidos por el estudio.</span>
                <small>Abrir documentos</small>
              </a>
            @else
              <a href="{{ route('reports.index') }}" class="context-card">
                <i class='bx bx-folder-open'></i>
                <strong>Reportes</strong>
                <span>Consulta reportes publicados y archivos preparados para tu empresa.</span>
                <small>Ver reportes</small>
              </a>
            @endcan
            @can('create', App\Models\Ticket::class)
              <a href="{{ route('tickets.create') }}" class="context-card">
                <i class='bx bx-message-square-detail'></i>
                <strong>Hablar con contabilidad</strong>
                <span>Envía una consulta con el contexto de tu empresa activa.</span>
                <small>Nueva consulta</small>
              </a>
            @endcan
          </div>

          <div class="dashboard-filter-card">
            <div>
              <div style="font-weight:900;color:var(--clr-text-main,#111827);font-size:.95rem;">Periodo de analisis</div>
              <div style="font-size:.78rem;color:var(--clr-text-muted,#64748b);margin-top:.1rem;">Los importes anuales usan el periodo seleccionado. El estado SUNAT usa el mes actual.</div>
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
        <div class="kpi-grid dashboard-kpis">
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

          <div class="dashboard-main-grid">
            <div class="dashboard-stack">
              {{-- Facturacion ultimos meses --}}
              <div class="sales-dashboard-grid">
                <div class="chart-card">
                  <div class="chart-card-header">
                    <div>
                      <div class="chart-card-title"><i class='bx bx-receipt' style="color:#059669;"></i> Facturacion de los ultimos 6 meses</div>
                      <div class="chart-card-sub">Monto total de comprobantes no anulados</div>
                    </div>
                    <a href="{{ route('facturador.invoices.index') }}" style="font-size:.78rem; font-weight:700; color:#059669; text-decoration:none;">Ver comprobantes</a>
                  </div>
                  <div class="sales-bars">
                    @foreach($an['ventas_ultimos_6_meses'] as $point)
                      @php
                        $height = max(4, round(($point['total'] / $an['ventas_ultimos_6_meses_max']) * 92));
                        $isCurrent = $point['month'] === now()->format('Y-m');
                      @endphp
                      <div class="sales-bar-item">
                        <div class="sales-bar-value">{{ number_format($point['total'], 0) }}</div>
                        <div class="sales-bar-track">
                          <div class="sales-bar {{ $isCurrent ? 'current' : '' }}" style="height:{{ $height }}px;"></div>
                        </div>
                        <div class="sales-bar-label">{{ $point['label'] }}</div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>

        {{-- Grafico principal: Ingresos vs Gastos --}}
        <div class="chart-row">
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
        </div>
      </div>

            <div class="dashboard-side">
          <div class="chart-card">
            <div class="chart-card-header">
              <div>
                <div class="chart-card-title"><i class='bx bx-pulse' style="color:#f59e0b;"></i> Estado del mes</div>
                <div class="chart-card-sub">Comprobantes de {{ now()->locale('es')->translatedFormat('F Y') }}</div>
              </div>
            </div>
            <div class="status-mini-grid">
              <div class="status-mini"><strong style="color:#059669;">{{ $an['estado_mes_actual']['aceptados'] }}</strong><span>Aceptados</span></div>
              <div class="status-mini"><strong style="color:#d97706;">{{ $an['estado_mes_actual']['pendientes'] }}</strong><span>Pendientes</span></div>
              <div class="status-mini"><strong style="color:#dc2626;">{{ $an['estado_mes_actual']['errores'] }}</strong><span>Errores</span></div>
              <div class="status-mini"><strong style="color:#6b7280;">{{ $an['estado_mes_actual']['anulados'] }}</strong><span>Anulados</span></div>
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-card-header">
              <div>
                <div class="chart-card-title"><i class='bx bx-bolt-circle' style="color:#16614f;"></i> Atajos con contexto</div>
                <div class="chart-card-sub">Acciones directas para evitar vueltas por el menú</div>
              </div>
            </div>
            <div class="dashboard-quick-grid">
              <a href="{{ route('facturador.invoices.create') }}"><i class='bx bx-plus'></i><div>Nueva factura<span>Registrar venta y dejarla lista para SUNAT.</span></div></a>
              <a href="{{ route('facturador.compras.subir') }}"><i class='bx bx-upload'></i><div>Subir compra<span>Registrar XML/PDF de proveedor.</span></div></a>
              <a href="{{ route('facturador.letras.index') }}"><i class='bx bx-file'></i><div>Letras<span>Revisar canjes, vencimientos y pagos.</span></div></a>
              @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}"><i class='bx bx-message-square-add'></i><div>Consulta<span>Crear ticket para soporte contable.</span></div></a>
              @endcan
            </div>
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

          <div class="chart-card" style="flex:1; min-width:280px;">
            <div class="chart-card-header">
              <div>
                <div class="chart-card-title"><i class='bx bx-map-alt' style="color:#f59e0b;"></i> ¿Qué hacemos por ti?</div>
                <div class="chart-card-sub">Resumen simple del flujo de trabajo mensual</div>
              </div>
            </div>
            <div style="display:grid; gap:.7rem;">
              <div class="status-mini"><strong style="font-size:1rem;color:#0f766e;">1. Ordenamos ventas y compras</strong><span>Emites comprobantes y subes compras desde este portal.</span></div>
              <div class="status-mini"><strong style="font-size:1rem;color:#0f766e;">2. Revisamos estados</strong><span>Los errores SUNAT, pendientes y anulaciones quedan visibles para actuar rápido.</span></div>
              <div class="status-mini"><strong style="font-size:1rem;color:#0f766e;">3. Dejamos evidencia</strong><span>Reportes, documentos finales y consultas quedan centralizados.</span></div>
            </div>
          </div>
        </div>
        </div>{{-- dashboard-shell --}}

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
        <div class="admin-dashboard-shell">
          <section class="bi-topbar">
            <div class="bi-title">
              <h1><i class='bx bx-bar-chart-square'></i> Panel ejecutivo</h1>
              <p>
                Vista rápida de operación: empresas, facturador, SUNAT, soporte y publicaciones. Entra directo a lo pendiente.
              </p>
            </div>
            <div class="bi-actions">
              @if($isGlobalPanel)
                <a href="{{ route('companies.create') }}" class="primary"><i class='bx bx-plus'></i> Empresa</a>
                <a href="{{ route('users.create') }}"><i class='bx bx-user-plus'></i> Usuario</a>
              @endif
              <a href="{{ route('reports.create') }}"><i class='bx bx-file-plus'></i> Reporte</a>
              <a href="{{ route('tickets.index') }}"><i class='bx bx-support'></i> Soporte</a>
            </div>
          </section>

          <div class="bi-report-grid">
            @if($isGlobalPanel)
              <article class="bi-tile bi-kpi">
                <span>Empresas activas</span>
                <strong>{{ $metrics['active_companies'] ?? $metrics['total_companies'] }}</strong>
                <small>{{ $metrics['total_companies'] }} empresas registradas</small>
              </article>
              <article class="bi-tile bi-kpi blue">
                <span>Facturador ON</span>
                <strong>{{ $metrics['facturador_enabled'] ?? 0 }}</strong>
                <small>Empresas listas para emitir</small>
              </article>
              <article class="bi-tile bi-kpi">
                <span>Claves SOL</span>
                <strong>{{ $metrics['sunat_credentials'] ?? 0 }}</strong>
                <small>Empresas con credenciales guardadas</small>
              </article>
              <article class="bi-tile bi-kpi blue">
                <span>Usuarios activos</span>
                <strong>{{ $metrics['active_users'] ?? 0 }}</strong>
                <small>Personas con acceso vigente</small>
              </article>
            @else
              <article class="bi-tile bi-kpi">
                <span>Empresas asignadas</span>
                <strong>{{ $metrics['total_companies'] }}</strong>
                <small>Cartera activa del auxiliar</small>
              </article>
            @endif
            <article class="bi-tile bi-kpi warn">
              <span>Consultas abiertas</span>
              <strong>{{ $metrics['open_tickets'] }}</strong>
              <small>Tickets abiertos o en revisión</small>
            </article>
            <article class="bi-tile bi-kpi">
              <span>Reportes emitidos</span>
              <strong>{{ $metrics['total_reports'] }}</strong>
              <small>Documentos publicados o registrados</small>
            </article>

            @if($isGlobalPanel)
              @php
                $totalMonth = max(1, (int) ($metrics['month_invoices'] ?? 0));
                $acceptedPct = round((($metrics['month_accepted'] ?? 0) / $totalMonth) * 100);
                $errorPct = round((($metrics['month_errors'] ?? 0) / $totalMonth) * 100);
                $facturadorPct = round((($metrics['facturador_enabled'] ?? 0) / max(1, $metrics['total_companies'])) * 100);
                $solPct = round((($metrics['sunat_credentials'] ?? 0) / max(1, $metrics['total_companies'])) * 100);
              @endphp
              <section class="bi-tile bi-wide">
                <div class="bi-tile-header">
                  <div class="bi-tile-title"><i class='bx bx-pulse'></i> Estado operativo del mes</div>
                </div>
                <div class="bi-bar-list">
                  <div class="bi-bar-row">
                    <span>Comprobantes</span>
                    <div class="bi-bar-track"><div class="bi-bar-fill" style="width:100%;"></div></div>
                    <strong>{{ $metrics['month_invoices'] ?? 0 }}</strong>
                  </div>
                  <div class="bi-bar-row">
                    <span>Aceptados</span>
                    <div class="bi-bar-track"><div class="bi-bar-fill blue" style="width:{{ $acceptedPct }}%;"></div></div>
                    <strong>{{ $metrics['month_accepted'] ?? 0 }}</strong>
                  </div>
                  <div class="bi-bar-row">
                    <span>Errores</span>
                    <div class="bi-bar-track"><div class="bi-bar-fill danger" style="width:{{ $errorPct }}%;"></div></div>
                    <strong>{{ $metrics['month_errors'] ?? 0 }}</strong>
                  </div>
                </div>
              </section>

              <section class="bi-tile bi-mid">
                <div class="bi-tile-header">
                  <div class="bi-tile-title"><i class='bx bx-shield-quarter'></i> Preparación SUNAT</div>
                </div>
                <div class="bi-bar-list">
                  <div class="bi-bar-row">
                    <span>Facturador</span>
                    <div class="bi-bar-track"><div class="bi-bar-fill" style="width:{{ $facturadorPct }}%;"></div></div>
                    <strong>{{ $facturadorPct }}%</strong>
                  </div>
                  <div class="bi-bar-row">
                    <span>Claves SOL</span>
                    <div class="bi-bar-track"><div class="bi-bar-fill warn" style="width:{{ $solPct }}%;"></div></div>
                    <strong>{{ $solPct }}%</strong>
                  </div>
                </div>
              </section>
            @endif

            <section class="bi-tile bi-side">
              <div class="bi-tile-header">
                <div class="bi-tile-title"><i class='bx bx-list-check'></i> Resumen</div>
              </div>
              <div class="bi-status-grid">
                <div class="bi-status"><span>Empresas</span><strong>{{ $metrics['total_companies'] }}</strong></div>
                <div class="bi-status"><span>Soporte</span><strong>{{ $metrics['open_tickets'] }}</strong></div>
                <div class="bi-status"><span>Mes</span><strong>{{ $metrics['month_invoices'] ?? 0 }}</strong></div>
                <div class="bi-status"><span>Errores</span><strong>{{ $metrics['month_errors'] ?? 0 }}</strong></div>
              </div>
            </section>

            <section class="bi-tile bi-full">
              <div class="bi-tile-header">
                <div class="bi-tile-title"><i class='bx bx-bolt-circle'></i> Acciones rápidas</div>
              </div>
              <div class="bi-action-list">
                <a href="{{ route('companies.index') }}"><i class='bx bx-buildings'></i><strong>Empresas</strong><span>Estado, facturador y claves SOL.</span></a>
                <a href="{{ route('users.index') }}"><i class='bx bx-user-check'></i><strong>Usuarios</strong><span>Accesos y asignaciones.</span></a>
                <a href="{{ route('reports.index') }}"><i class='bx bx-folder-open'></i><strong>Reportes</strong><span>Publicaciones y archivos.</span></a>
                <a href="{{ route('tickets.index') }}"><i class='bx bx-message-square-detail'></i><strong>Soporte</strong><span>Tickets y seguimiento.</span></a>
              </div>
            </section>

            <section class="bi-tile bi-wide">
              <div class="bi-tile-header">
                <div class="bi-tile-title"><i class='bx bx-folder-open'></i> Reportes recientes</div>
                <a href="{{ route('reports.index') }}" class="bi-tile-link">Ver todos</a>
              </div>
              <div class="bi-list activity-list">
                  @forelse($recentReports as $report)
                  <a href="{{ route('reports.index') }}" class="activity-item">
                    <div class="activity-icon" style="background:rgba(15,118,110,.1); color:#0f766e;"><i class='bx bx-file'></i></div>
                    <div class="activity-content">
                      <div class="activity-title">{{ $report->title }}</div>
                      <div class="activity-meta">{{ $report->company->name ?? 'Sin empresa' }} &middot; {{ $report->created_at->format('d/m/Y') }}</div>
                    </div>
                  </a>
                  @empty
                  <p style="font-size:.83rem; color:#9ca3af; text-align:center; padding:1rem 0;">Sin reportes recientes.</p>
                  @endforelse
              </div>
            </section>

            <section class="bi-tile bi-mid">
              <div class="bi-tile-header">
                <div class="bi-tile-title"><i class='bx bx-message-square-detail'></i> Soporte reciente</div>
                <a href="{{ route('tickets.index') }}" class="bi-tile-link">Ver tickets</a>
              </div>
              <div class="bi-list activity-list">
                    @forelse($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" class="activity-item">
                      <div class="activity-icon" style="background:rgba(239,68,68,.1); color:#dc2626;"><i class='bx bx-envelope'></i></div>
                      <div class="activity-content">
                        <div class="activity-title">{{ $ticket->subject }}</div>
                        <div class="activity-meta">{{ $ticket->company->name ?? 'Sin empresa' }} &middot; {{ $ticket->updated_at->diffForHumans() }}</div>
                      </div>
                    </a>
                    @empty
                    <p style="font-size:.83rem; color:#9ca3af; text-align:center; padding:1rem 0;">Sin actividad reciente.</p>
                    @endforelse
              </div>
            </section>

            <section class="bi-tile bi-side">
              <div class="bi-tile-header">
                <div class="bi-tile-title"><i class='bx bx-map-alt'></i> Flujo</div>
              </div>
              <div class="bi-bar-list">
                <div class="bi-bar-row"><span>Alta</span><div class="bi-bar-track"><div class="bi-bar-fill" style="width:100%;"></div></div><strong>1</strong></div>
                <div class="bi-bar-row"><span>Operación</span><div class="bi-bar-track"><div class="bi-bar-fill blue" style="width:100%;"></div></div><strong>2</strong></div>
                <div class="bi-bar-row"><span>Cierre</span><div class="bi-bar-track"><div class="bi-bar-fill warn" style="width:100%;"></div></div><strong>3</strong></div>
              </div>
            </section>
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
  document.querySelectorAll('[data-news-banner]').forEach(banner => {
    const newsId = banner.dataset.newsId;
    const key = newsId ? `portal_mendieta_news_read_${newsId}` : null;
    if (!key) return;

    if (localStorage.getItem(key) === '1') {
      banner.remove();
      return;
    }

    const markRead = () => {
      localStorage.setItem(key, '1');
      banner.remove();
    };

    banner.querySelectorAll('[data-news-read]').forEach(btn => {
      btn.addEventListener('click', markRead);
    });

    banner.querySelector('[data-news-open]')?.addEventListener('click', () => {
      localStorage.setItem(key, '1');
    });
  });

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
