@extends('layouts.app')

@section('title', 'Cronograma de Obligaciones | Portal Mendieta')

@push('styles')
<style>
/* Responsive Cronograma Obligaciones */
.cronograma-filtros {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}
.cronograma-filtros > * {
  min-width: 120px;
}
.cronograma-table-wrap {
  width: 100%;
  overflow-x: auto;
}
.cronograma-table {
  min-width: 800px;
  width: 100%;
}
@media (max-width: 1400px) and (min-width: 901px) {
  .cronograma-filtros {
    gap: 0.5rem;
    padding: 0.5rem 1rem;
  }
  .cronograma-table {
    min-width: 700px;
    font-size: 1rem;
  }
}
@media (max-width: 900px) {
  .cronograma-filtros {
    flex-direction: column;
    gap: 0.7rem;
  }
  .cronograma-filtros > * {
    width: 100%;
    min-width: 0;
  }
  .cronograma-table-wrap {
    overflow-x: auto;
    margin-bottom: 1rem;
  }
  .cronograma-table {
    min-width: 600px;
    font-size: 0.95rem;
  }
}
@media (max-width: 600px) {
  .cronograma-filtros {
    padding: 0.5rem;
  }
  .cronograma-table {
    min-width: 400px;
    font-size: 0.9rem;
  }
}
</style>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    /* ── Filtros ─────────────────────────────────────────────────────── */
    .cron-filter-wrap {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: flex-end;
      margin-bottom: 1.5rem;
      padding: 1.25rem;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
    }
    .cron-filter-group {
      display: flex;
      flex-direction: column;
      gap: .4rem;
      min-width: 150px;
    }
    .cron-filter-group label {
      font-size: .75rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: .06em;
    }
    .cron-input {
      padding: .48rem .75rem;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: .9rem;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
      background: #fff;
    }
    .cron-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .cron-btn {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .5rem 1.1rem;
      border-radius: 6px;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      border: 1px solid transparent;
      transition: all .15s;
      white-space: nowrap;
    }
    .cron-btn-primary { background: #0f172a; color: #fff; border-color: #0f172a; }
    .cron-btn-primary:hover { background: #1e293b; }
    .cron-btn-outline {
      background: #fff;
      color: #475569;
      border-color: #cbd5e1;
      text-decoration: none;
    }
    .cron-btn-outline:hover {
      background: #f1f5f9;
      border-color: #94a3b8;
      color: #1e293b;
    }

    /* ── Toggle vista (Activas / Archivadas) ───────────────────────────── */
    .cron-view-toggle {
      display: inline-flex;
      padding: .18rem;
      border-radius: 999px;
      background: #e5e7eb;
      gap: .15rem;
    }
    .cron-view-toggle .cron-view-btn {
      border-radius: 999px;
      padding: .3rem .85rem;
      font-size: .8rem;
      border: none;
      background: transparent;
      color: #4b5563;
      font-weight: 600;
      cursor: pointer;
      transition: background .15s, color .15s;
    }
    .cron-view-toggle .cron-view-btn.is-active {
      background: #0f172a;
      color: #fff;
    }

    /* ── Botonera dígitos RUC ───────────────────────────────────────────── */
    .cron-digit-filter {
      display: flex;
      flex-direction: column;
      gap: .4rem;
      min-width: 260px;
    }
    .cron-digit-label {
      font-size: .75rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: .06em;
    }
    .cron-digit-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: .4rem;
    }
    .cron-digit-btn {
      padding: .35rem .75rem;
      border-radius: 999px;
      border: 1px solid #cbd5e1;
      background: #fff;
      font-size: .8rem;
      font-weight: 600;
      color: #475569;
      cursor: pointer;
      transition: all .15s;
    }
    .cron-digit-btn:hover {
      background: #e5edff;
      border-color: #94a3b8;
      color: #1e293b;
    }
    .cron-digit-btn.is-active {
      background: #0f172a;
      border-color: #0f172a;
      color: #fff;
    }

    /* ── Stats ──────────────────────────────────────────────────────── */
    .cron-period-title {
      font-size: 1.05rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: .25rem;
    }
    .cron-period-sub {
      font-size: .85rem;
      color: #64748b;
    }

    /* ── Tabla ──────────────────────────────────────────────────────── */
    .cron-table-wrap { overflow-x: auto; margin-top: .75rem; }
    .cron-table {
      width: 100%;
      border-collapse: collapse;
      font-size: .9rem;
    }
    .cron-table th {
      padding: .65rem 1rem;
      text-align: left;
      font-size: .75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #64748b;
      border-bottom: 2px solid #e2e8f0;
      white-space: nowrap;
    }
    .cron-table td {
      padding: .75rem 1rem;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
    }
    .cron-table tr:last-child td { border-bottom: none; }
    .cron-table tr:hover td { background: #f8fafc; }

    /* ── Badges ─────────────────────────────────────────────────────── */
    .cron-badge {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      border-radius: 999px;
      padding: .28rem .75rem;
      font-size: .75rem;
      font-weight: 700;
    }
    .cron-badge.declarado { background: #dcfce7; color: #166534; }
    .cron-badge.pendiente  { background: #fef3c7; color: #92400e; }

    /* ── Acciones ────────────────────────────────────────────────────── */
    .cron-btn-confirm {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #16a34a;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: .45rem .9rem;
      font-size: .82rem;
      font-weight: 700;
      cursor: pointer;
      transition: background .15s;
    }
    .cron-btn-confirm:hover { background: #15803d; }
    .cron-btn-revert {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      background: #fff;
      color: #64748b;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      padding: .45rem .9rem;
      font-size: .82rem;
      font-weight: 600;
      cursor: pointer;
      transition: all .15s;
    }
    .cron-btn-revert:hover { border-color: #ef4444; color: #ef4444; }

    .cron-confirmed-info {
      font-size: .78rem;
      color: #64748b;
      margin-top: .3rem;
    }

    /* ── Dark Mode ──────────────────────────────────────────────────── */
    body.dark-mode .cron-filter-wrap { background: var(--clr-bg-card, #1e293b); border-color: var(--clr-border-light, #334155); }
    body.dark-mode .cron-filter-group label { color: #94a3b8; }
    body.dark-mode .cron-input { background: var(--clr-bg-body, #0f172a); border-color: var(--clr-border-light, #334155); color: var(--clr-text-main, #f8fafc); }
    body.dark-mode .cron-input::placeholder { color: #475569; }
    body.dark-mode .cron-btn-outline { background: transparent; color: #cbd5e1; border-color: #475569; }
    body.dark-mode .cron-btn-outline:hover { background: var(--clr-hover-bg, #1e293b); color: #f8fafc; }
    body.dark-mode .cron-period-title { color: var(--clr-text-main, #f8fafc); }
    body.dark-mode .cron-period-sub { color: #94a3b8; }
    body.dark-mode .cron-table th { color: #94a3b8; border-bottom-color: var(--clr-border-light, #334155); }
    body.dark-mode .cron-table td { border-bottom-color: var(--clr-border-light, #334155); }
    body.dark-mode .cron-table tr:hover td { background: var(--clr-hover-bg, #1e293b); }
    body.dark-mode .cron-badge.declarado { background: rgba(22,163,74,.2); color: #4ade80; }
    body.dark-mode .cron-badge.pendiente { background: rgba(146,64,14,.2); color: #fbbf24; }
    body.dark-mode .cron-btn-revert { background: transparent; color: #94a3b8; border-color: #475569; }
    body.dark-mode .cron-btn-revert:hover { border-color: #ef4444; color: #f87171; }
    body.dark-mode .cron-confirmed-info { color: #94a3b8; }
    
    /* Ajustes específicos para laptops (medio) */
    @media (max-width: 1400px) and (min-width: 901px) {
      /* Reducir ancho del sidebar para ganar espacio al contenido */
      .sidebar-premium { width: 220px; }
      /* Compactar paddings del main para mostrar más contenido */
      .main-content { padding: 1.25rem; }
      .placeholder-content { padding: 2rem; }
      /* Filtros más compactos */
      .cron-filter-wrap { padding: 0.8rem; gap: .6rem; }
      .cron-filter-group { min-width: 130px; }
      /* Tabla más compacta */
      .cron-table th, .cron-table td { padding: .5rem .8rem; }
      .cron-table { font-size: .88rem; }
      .cron-table { min-width: 650px; }
    }
  </style>
@endpush

@section('content')
  @php
    $monthNames = [
      1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
      5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
      9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];
    $vencMes = $month === 12 ? 1 : $month + 1;
    $vencYear = $month === 12 ? $year + 1 : $year;
  @endphp

  <div class="app-layout">
    <aside class="sidebar-premium">
      <div class="sidebar-header">
        <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
        <div class="header-text">
          <h2>Portal Mendieta</h2>
          <p>Panel interno</p>
        </div>
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

          {{-- Flash messages --}}
          @if(session('status'))
            <div class="placeholder-content module-alert module-flash" data-flash-message>
              <p>{{ session('status') }}</p>
              <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close>
                <i class='bx bx-x'></i>
              </button>
            </div>
          @endif

          {{-- Stats --}}
          <div class="module-stats-grid">
            <article class="module-stat-card">
              <span class="module-stat-label">Periodo</span>
              <strong class="module-stat-value">{{ $monthNames[$month] }} {{ $year }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Declaradas</span>
              <strong class="module-stat-value" style="color:#16a34a;">{{ $totalDeclared }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Pendientes</span>
              <strong class="module-stat-value" style="color:#d97706;">{{ $totalPending }}</strong>
            </article>
            <article class="module-stat-card">
              <span class="module-stat-label">Vencimiento en</span>
              <strong class="module-stat-value">{{ $monthNames[$vencMes] }} {{ $vencYear }}</strong>
            </article>
          </div>

          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar" style="margin-bottom: 1.25rem;">
              <div>
                <h1>Cronograma de Obligaciones</h1>
                <p style="margin:.25rem 0 0; color:#6b7280; font-size:.92rem;">
                  Control de declaraciones mensuales según cronograma SUNAT por último dígito del RUC.
                </p>
              </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('obligaciones.cronograma.index') }}" class="cron-filter-wrap">
              <div class="cron-filter-group">
                <label>Mes</label>
                <select name="month" class="cron-input">
                  @foreach($monthNames as $num => $name)
                    <option value="{{ $num }}" {{ $month === $num ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="cron-filter-group">
                <label>Año</label>
                <input type="number" name="year" class="cron-input" value="{{ $year }}" min="2020" max="2030" style="width:90px;">
              </div>
              <div class="cron-filter-group" style="flex:1; min-width:200px;">
                <label>Buscar empresa</label>
                <input type="text" name="q" class="cron-input" value="{{ $filterSearch }}" placeholder="Nombre o RUC…">
              </div>
              <div class="cron-digit-filter">
                <span class="cron-digit-label">Filtrar por último dígito RUC</span>
                <div class="cron-digit-buttons">
                  @php $currentDigit = (string)($filterDigit ?? ''); @endphp
                  <button type="submit" name="digit" value=""
                          class="cron-digit-btn {{ $currentDigit === '' ? 'is-active' : '' }}">
                    Todos
                  </button>
                  @for($d = 0; $d <= 9; $d++)
                    <button type="submit" name="digit" value="{{ $d }}"
                            class="cron-digit-btn {{ $currentDigit === (string)$d ? 'is-active' : '' }}">
                      {{ $d }}
                    </button>
                  @endfor
                </div>
              </div>
              <div class="cron-filter-group">
                <label>Estado</label>
                <select name="status" class="cron-input">
                  <option value="" {{ $filterStatus === '' ? 'selected' : '' }}>Todos</option>
                  <option value="pendiente"  {{ $filterStatus === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                  <option value="declarado"  {{ $filterStatus === 'declarado'  ? 'selected' : '' }}>Declarado</option>
                </select>
              </div>
              <div class="cron-filter-group">
                <label>Asignado a</label>
                <select name="assigned_to" class="cron-input">
                  <option value="" {{ ($filterAssignedTo ?? '') === '' ? 'selected' : '' }}>Todos</option>
                  @foreach($assignedUsers as $user)
                    <option value="{{ $user->id }}" {{ (string)($filterAssignedTo ?? '') === (string)$user->id ? 'selected' : '' }}>
                      {{ $user->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              @php $currentView = $filterView ?? 'active'; @endphp
              <div class="cron-filter-group">
                <label>Vista</label>
                <div class="cron-view-toggle">
                  <button type="submit" name="view" value="active"
                          class="cron-view-btn {{ $currentView === 'active' ? 'is-active' : '' }}">
                    Activas
                  </button>
                  <button type="submit" name="view" value="archived"
                          class="cron-view-btn {{ $currentView === 'archived' ? 'is-active' : '' }}">
                    Archivadas
                  </button>
                </div>
              </div>
              <div class="cron-filter-group" style="justify-content: flex-end;">
                <label style="visibility:hidden;">Buscar</label>
                <div style="display:flex; gap:.5rem;">
                  <button type="submit" class="cron-btn cron-btn-primary">
                    <i class='bx bx-search'></i> Filtrar
                  </button>
                  @if($filterSearch || $filterStatus || $month !== now()->month || $year !== now()->year)
                    <a href="{{ route('obligaciones.cronograma.index') }}" class="cron-btn cron-btn-outline">
                      <i class='bx bx-eraser'></i> Limpiar
                    </a>
                  @endif
                </div>
              </div>
            </form>

            {{-- Tabla --}}
            <div class="cron-table-wrap">
              <table class="cron-table">
                <thead>
                  <tr>
                    <th>Empresa</th>
                    <th>RUC</th>
                    <th style="text-align:center;">Último dígito</th>
                    <th>Fecha de vencimiento</th>
                    <th>Estado declaración</th>
                    <th>Asignado a</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($rows as $row)
                    @php
                      /** @var \App\Models\Company $company */
                      $company = $row['company'];
                      $authUser = auth()->user();
                      $authRole = $authUser?->role?->value ?? '';
                      $hiddenIds = ($hiddenCompanyIds ?? collect());
                      $isHidden  = $hiddenIds->contains($company->id);
                    @endphp
                    <tr>
                      <td style="font-weight:600;">{{ $company->name }}</td>
                      <td style="font-family:monospace; color:#475569; font-size:.88rem;">{{ $company->ruc }}</td>
                      <td style="text-align:center; font-weight:700; font-size:1.05rem;">{{ $row['last_digit'] }}</td>
                      <td>
                        {{ $row['due_date']->format('d') }}
                        {{ $monthNames[$row['due_date']->month] }}
                        {{ $row['due_date']->year }}
                      </td>
                      <td>
                        @if($row['declared'])
                          <span class="cron-badge declarado">
                            <i class='bx bx-check-circle'></i> Declarado
                          </span>
                          @if($row['declaration']?->declaredByUser)
                            <div class="cron-confirmed-info">
                              Por {{ $row['declaration']->declaredByUser->name }}
                              el {{ $row['declaration']->declared_at?->format('d/m/Y H:i') }}
                            </div>
                          @endif
                        @else
                          <span class="cron-badge pendiente">
                            <i class='bx bx-time-five'></i> Pendiente
                          </span>
                        @endif
                      </td>
                      <td>
                        @php
                          $assignedNames = $company->users->pluck('name')->all();
                        @endphp
                        @if(! empty($assignedNames))
                          {{ implode(', ', $assignedNames) }}
                        @else
                          <span style="color:#94a3b8; font-size:.8rem;">Sin asignar</span>
                        @endif
                      </td>
                      <td>
                        <div style="display:flex; flex-direction:column; gap:.35rem;">
                          @if(! $row['declared'])
                            <form method="POST" action="{{ route('obligaciones.cronograma.confirm', $company) }}">
                              @csrf
                              <input type="hidden" name="year"  value="{{ $year }}">
                              <input type="hidden" name="month" value="{{ $month }}">
                              <button type="submit" class="cron-btn-confirm">
                                <i class='bx bx-check'></i> Confirmar declaración
                              </button>
                            </form>
                          @else
                            <form method="POST" action="{{ route('obligaciones.cronograma.revert', $company) }}">
                              @csrf
                              <input type="hidden" name="year"  value="{{ $year }}">
                              <input type="hidden" name="month" value="{{ $month }}">
                              <button type="submit" class="cron-btn-revert">
                                <i class='bx bx-undo'></i> Revertir
                              </button>
                            </form>
                          @endif

                          @if(in_array($authRole, ['admin', 'supervisor'], true))
                            <form method="POST" action="{{ $isHidden
                                  ? route('portal-sunat.unhide', $company)
                                  : route('portal-sunat.hide', $company) }}">
                              @csrf
                              <button type="submit" class="cron-btn-revert" style="border-style:dashed;">
                                <i class='bx {{ $isHidden ? "bx-show" : "bx-low-vision" }}'></i>
                                {{ $isHidden ? 'Mostrar en mi lista' : 'Ocultar para mí' }}
                              </button>
                            </form>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" style="text-align:center; color:#94a3b8; padding:2rem;">
                        <i class='bx bx-calendar-x' style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        No se encontraron empresas con esos filtros.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <p style="margin-top:.75rem; font-size:.8rem; color:#94a3b8;">
              {{ $rows->count() }} empresa(s) mostrada(s).
              &nbsp;·&nbsp;
              Periodo: {{ $monthNames[$month] }} {{ $year }}
              &nbsp;·&nbsp;
              Vencimiento en: {{ $monthNames[$vencMes] }} {{ $vencYear }}
            </p>
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('[data-flash-message]').forEach((flash) => {
    const closeBtn = flash.querySelector('[data-flash-close]');
    if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
    window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4500);
  });
</script>
@endpush
