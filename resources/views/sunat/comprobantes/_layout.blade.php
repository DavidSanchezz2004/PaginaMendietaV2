@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .sunat-head { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .sunat-head h1 { margin:0; display:flex; align-items:center; gap:.55rem; color:var(--clr-text-main,#111827); font-size:1.45rem; }
    .sunat-head p { margin:.25rem 0 0; color:var(--clr-text-muted,#6b7280); font-size:.9rem; }
    .sunat-tabs { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.1rem; }
    .sunat-tab { display:inline-flex; align-items:center; gap:.35rem; padding:.5rem .85rem; border:1px solid var(--clr-border-light,#e5e7eb); border-radius:8px; color:var(--clr-text-main,#111827); text-decoration:none; font-size:.85rem; font-weight:800; background:var(--clr-bg-card,#fff); }
    .sunat-tab.active { background:var(--clr-active-bg,#1a6b57); border-color:var(--clr-active-bg,#1a6b57); color:#fff; }
    .sunat-form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; }
    .sunat-form-grid.cols-2 { grid-template-columns:repeat(2,minmax(0,1fr)); }
    @media(max-width:860px){ .sunat-form-grid,.sunat-form-grid.cols-2 { grid-template-columns:1fr; } }
    .sunat-field label { display:block; color:var(--clr-text-muted,#6b7280); font-size:.76rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.35rem; }
    .sunat-field input,.sunat-field select,.sunat-field textarea { width:100%; min-height:40px; border:1px solid var(--clr-border-light,#d1d5db); border-radius:8px; padding:.55rem .7rem; background:transparent; color:var(--clr-text-main,#111827); font:inherit; font-size:.9rem; }
    .sunat-field textarea { min-height:90px; resize:vertical; }
    .sunat-help { color:var(--clr-text-muted,#6b7280); font-size:.8rem; margin-top:.25rem; }
    .sunat-alert { border:1px solid #fde68a; background:#fffbeb; color:#92400e; border-radius:10px; padding:.85rem 1rem; margin-bottom:1rem; font-size:.9rem; }
    .sunat-alert.error { border-color:#fecaca; background:#fef2f2; color:#991b1b; }
    .sunat-alert.success { border-color:#bbf7d0; background:#ecfdf5; color:#047857; }
    .sunat-actions { display:flex; justify-content:flex-end; gap:.65rem; flex-wrap:wrap; margin-top:1rem; }
    .sunat-badge { display:inline-flex; align-items:center; padding:.28rem .7rem; border-radius:999px; font-size:.74rem; font-weight:900; border:1px solid transparent; white-space:nowrap; }
    .sunat-badge.green { background:#ecfdf5; color:#047857; border-color:#bbf7d0; }
    .sunat-badge.red { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
    .sunat-badge.gray { background:#f3f4f6; color:#4b5563; border-color:#e5e7eb; }
    .sunat-badge.blue { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
    .sunat-badge.yellow { background:#fffbeb; color:#b45309; border-color:#fde68a; }
    .sunat-result-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; margin-top:1rem; }
    @media(max-width:860px){ .sunat-result-grid { grid-template-columns:1fr; } }
    .sunat-result-card { border:1px solid var(--clr-border-light,#e5e7eb); border-radius:10px; padding:1rem; background:rgba(15,23,42,.02); }
    .sunat-result-card span { display:block; color:var(--clr-text-muted,#6b7280); font-size:.74rem; font-weight:800; text-transform:uppercase; margin-bottom:.45rem; }
    .sunat-table { width:100%; border-collapse:collapse; font-size:.88rem; }
    .sunat-table th { color:var(--clr-text-muted,#6b7280); text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--clr-border-light,#e5e7eb); padding:.65rem; }
    .sunat-table td { color:var(--clr-text-main,#111827); border-bottom:1px solid var(--clr-border-light,#f1f5f9); padding:.7rem .65rem; vertical-align:middle; }
    .sunat-table-wrap { overflow-x:auto; }
    .invalid-feedback { color:#dc2626; font-size:.78rem; margin-top:.25rem; }
  </style>
@endpush

@php
  $sunatTabs = [
    ['route' => 'sunat.comprobantes.validar.index', 'label' => 'Validar', 'icon' => 'bx bx-search-alt'],
    ['route' => 'sunat.comprobantes.historial', 'label' => 'Historial', 'icon' => 'bx bx-history'],
    ['route' => 'sunat.comprobantes.credenciales', 'label' => 'Credenciales', 'icon' => 'bx bx-key'],
  ];
@endphp

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
        @foreach(['success' => 'success', 'error' => 'error', 'warning' => null, 'status' => 'success'] as $flashKey => $flashClass)
          @if(session($flashKey))
            <div class="sunat-alert {{ $flashClass }}">{{ session($flashKey) }}</div>
          @endif
        @endforeach

        <div class="placeholder-content module-card-wide">
          <div class="sunat-head">
            <div>
              <h1><i class='bx bx-check-shield'></i> Consulta Integrada SUNAT</h1>
              <p>{{ $company->name }} · RUC {{ $company->ruc }}</p>
            </div>
          </div>

          <nav class="sunat-tabs">
            @foreach($sunatTabs as $tab)
              <a href="{{ route($tab['route']) }}" class="sunat-tab {{ request()->routeIs($tab['route']) ? 'active' : '' }}">
                <i class='{{ $tab['icon'] }}'></i> {{ $tab['label'] }}
              </a>
            @endforeach
          </nav>

          {{ $slot }}
        </div>
      </div>
    </main>
  </section>
</div>
