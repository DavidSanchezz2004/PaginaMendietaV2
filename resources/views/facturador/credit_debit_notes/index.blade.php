@extends('layouts.app')

@section('title', 'Notas de Crédito y Débito')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .badge-draft     { background:rgba(107, 114, 128, 0.1); color:var(--clr-text-muted, #374151); border:1px solid rgba(107, 114, 128, 0.2); }
    .badge-sent      { background:rgba(6, 95, 70, 0.1); color:var(--clr-active-bg, #065f46); border:1px solid rgba(16, 185, 129, 0.2); }
    .badge-error     { background:rgba(153, 27, 27, 0.1); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.2); }
    .badge-consulted { background:rgba(91, 33, 182, 0.1); color:#8b5cf6; border:1px solid rgba(139, 92, 246, 0.2); }
    
    .note-badge      { display:inline-flex; align-items:center; gap:0.35rem; padding:.35rem .85rem; border-radius:20px; font-size:.78rem; font-weight:700; white-space:nowrap; }
    
    .filter-bar      { display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.5rem; align-items:center; background: var(--clr-bg-card, #ffffff); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--clr-border-light, rgba(0,0,0,0.06)); box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: all 0.3s ease; }
    body.dark-mode .filter-bar { background: var(--clr-bg-card); border-color: var(--clr-border-light); }
    
    .filter-bar input, .filter-bar select { padding:.55rem .85rem; border:1px solid var(--clr-border-light, #e5e7eb); border-radius:8px; font-size:.9rem; font-family: inherit; color: var(--clr-text-main, #111827); background: transparent; outline: none; transition: all 0.2s ease; }
    body.dark-mode .filter-bar input, body.dark-mode .filter-bar select { border-color: rgba(255,255,255,0.1); }
    .filter-bar input:focus, .filter-bar select:focus { border-color: var(--clr-active-bg, #1a6b57); box-shadow: 0 0 0 3px rgba(26, 107, 87, 0.1); }
    
    .module-table th { color: var(--clr-text-muted, #6b7280); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .module-table td { color: var(--clr-text-main, #111827); font-weight: 500; font-size: 0.9rem; }
    
    .btn-action-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: rgba(0,0,0,0.04); color: var(--clr-text-main, #374151); transition: all 0.2s; text-decoration: none; font-size: 1.15rem; }
    .btn-action-icon:hover { background: rgba(0,0,0,0.08); color: var(--clr-active-bg, #1a6b57); transform: translateY(-2px); }
    .action-wrapper { display: flex; gap: 0.4rem; justify-content: flex-end; }
    
    .stat-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
    @media(max-width:900px){ .stat-cards { grid-template-columns:repeat(2,1fr); } }
    .stat-card { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,rgba(0,0,0,.06)); border-radius:14px; padding:1.1rem 1.25rem; display:flex; flex-direction:column; gap:.25rem; box-shadow:0 4px 15px rgba(0,0,0,.03); transition:transform .2s; }
    .stat-card:hover { transform:translateY(-2px); }
    .stat-card__icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.35rem; margin-bottom:.35rem; }
    .stat-card__val  { font-size:1.45rem; font-weight:800; color:var(--clr-text-main,#111827); line-height:1.15; }
    .stat-card__lbl  { font-size:.75rem; font-weight:700; color:var(--clr-text-muted,#6b7280); text-transform:uppercase; letter-spacing:.05em; }
    .sc-green .stat-card__icon { background:rgba(16,185,129,.12); color:#059669; }
    .sc-blue  .stat-card__icon { background:rgba(59,130,246,.12); color:#3b82f6; }
    .sc-amber .stat-card__icon { background:rgba(245,158,11,.12); color:#d97706; }
    .sc-slate .stat-card__icon { background:rgba(107,114,128,.12); color:#6b7280; }
  </style>
@endpush

@section('content')
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

          @foreach(['status' => null, 'success' => null, 'error' => 'module-alert--error'] as $flashKey => $flashClass)
            @if(session($flashKey))
              <div class="placeholder-content module-alert module-flash {{ $flashClass }}" data-flash-message>
                <p>{{ session($flashKey) }}</p>
                <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
              </div>
            @endif
          @endforeach

          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <h1 style="display:flex; align-items:center; gap:0.5rem;"><i class='bx bx-file-blank' style="color:var(--clr-text-main);"></i> Notas de Crédito y Débito</h1>
              <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                <a href="{{ route('facturador.index') }}" class="btn-secondary" style="font-size:.85rem;">
                  <i class='bx bx-building'></i> Cambiar empresa
                </a>
                @can('create', \App\Models\CreditDebitNote::class)
                  <a href="{{ route('facturador.credit-debit-notes.create') }}" class="btn-primary">
                    <i class='bx bx-plus'></i> Nueva Nota
                  </a>
                @endcan
              </div>
            </div>

            {{-- Tarjetas de resumen --}}
            <div class="stat-cards">
              <div class="stat-card sc-green">
                <div class="stat-card__icon"><i class='bx bx-check-circle'></i></div>
                <span class="stat-card__lbl">Total Crédito</span>
                <span class="stat-card__val">PEN {{ number_format($stats['total_credito'], 2) }}</span>
              </div>

              <div class="stat-card sc-blue">
                <div class="stat-card__icon"><i class='bx bx-minus-circle'></i></div>
                <span class="stat-card__lbl">Total Débito</span>
                <span class="stat-card__val">PEN {{ number_format($stats['total_debito'], 2) }}</span>
              </div>

              <div class="stat-card sc-amber">
                <div class="stat-card__icon"><i class='bx bx-hourglass'></i></div>
                <span class="stat-card__lbl">Pendientes</span>
                <span class="stat-card__val">{{ $stats['pendientes'] }}</span>
              </div>

              <div class="stat-card sc-slate">
                <div class="stat-card__icon"><i class='bx bx-error-circle'></i></div>
                <span class="stat-card__lbl">Errores</span>
                <span class="stat-card__val">{{ $stats['errores'] }}</span>
              </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="filter-bar">
              <i class='bx bx-filter-alt' style="font-size: 1.25rem; color: var(--clr-text-muted);"></i>
              <select name="tipo" class="form-select" style="max-width:150px;">
                <option value="">Todos los tipos</option>
                <option value="07" {{ request('tipo') === '07' ? 'selected' : '' }}>Notas de Crédito</option>
                <option value="08" {{ request('tipo') === '08' ? 'selected' : '' }}>Notas de Débito</option>
              </select>
              <select name="estado" class="form-select" style="max-width:150px;">
                <option value="">Todos los estados</option>
                <option value="draft" {{ request('estado') === 'draft' ? 'selected' : '' }}>Borrador</option>
                <option value="sent" {{ request('estado') === 'sent' ? 'selected' : '' }}>Enviada</option>
                <option value="error" {{ request('estado') === 'error' ? 'selected' : '' }}>Error</option>
                <option value="consulted" {{ request('estado') === 'consulted' ? 'selected' : '' }}>Consultada</option>
              </select>
              <input type="text" name="search" placeholder="Buscar serie, número..." value="{{ request('search') }}" style="flex:1;">
              <button type="submit" class="btn-primary" style="font-size:.85rem; padding: 0.55rem 1.2rem;"><i class='bx bx-search'></i> Filtrar</button>
              <a href="{{ route('facturador.credit-debit-notes.index') }}" class="btn-secondary" style="font-size:.85rem; padding: 0.55rem 1.2rem;"><i class='bx bx-eraser'></i></a>
            </form>

            {{-- Tabla --}}
            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>Tipo</th>
                    <th>Serie-Número</th>
                    <th>Cliente</th>
                    <th style="text-align:right;">Monto</th>
                    <th>Estado</th>
                    <th>SUNAT</th>
                    <th>Fecha Emisión</th>
                    <th class="cell-action">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($notes as $note)
                    <tr>
                      <td>
                        <span class="note-badge badge-{{ $note->isCreditNote() ? 'sent' : 'error' }}">
                          {{ $note->getTypeLabel() }}
                        </span>
                      </td>
                      <td>
                        <code style="font-weight:600;">{{ $note->serie_documento }}-{{ $note->numero_documento }}</code>
                      </td>
                      <td>
                        <div style="font-weight:600;">{{ $note->invoice?->client->nombre_razon_social ?? '—' }}</div>
                        <small style="color:var(--clr-text-muted);">{{ $note->codigo_interno }}</small>
                      </td>
                      <td style="text-align:right; font-weight:700; color:var(--clr-active-bg, #1a6b57);">
                        S/. {{ number_format($note->monto_total, 2) }}
                      </td>
                      <td>
                        <span class="note-badge badge-{{ $note->estado }}">
                          {{ $note->getEstadoLabel() }}
                        </span>
                      </td>
                      <td>
                        @if($note->estado === 'sent')
                          <span class="note-badge badge-sent" style="font-size:.68rem;">Aceptada</span>
                        @elseif($note->estado === 'error')
                          <span class="note-badge badge-error" style="font-size:.68rem;">Rechazada</span>
                        @else
                          <span style="color:var(--clr-text-muted); font-size:.8rem;">—</span>
                        @endif
                      </td>
                      <td>{{ $note->fecha_emision->format('d/m/Y H:i') }}</td>
                      <td class="cell-action">
                        <div class="action-wrapper">
                          <a href="{{ route('facturador.credit-debit-notes.show', $note) }}" class="btn-action-icon" title="Ver detalle">
                            <i class='bx bx-show'></i>
                          </a>
                          @if($note->estado === 'draft')
                            <form method="POST" action="{{ route('facturador.credit-debit-notes.emit', $note) }}" style="display:inline;">
                              @csrf
                              <button type="submit" class="btn-action-icon" title="Enviar a SUNAT">
                                <i class='bx bx-send' style="color:#059669;"></i>
                              </button>
                            </form>
                          @elseif($note->estado === 'sent')
                            <form method="POST" action="{{ route('facturador.credit-debit-notes.consult', $note) }}" style="display:inline;">
                              @csrf
                              <button type="submit" class="btn-action-icon" title="Consultar estado">
                                <i class='bx bx-search' style="color:#3b82f6;"></i>
                              </button>
                            </form>
                          @elseif($note->estado === 'error')
                            <form method="POST" action="{{ route('facturador.credit-debit-notes.emit', $note) }}" style="display:inline;">
                              @csrf
                              <button type="submit" class="btn-action-icon" title="Reintentar">
                                <i class='bx bx-arrow-back' style="color:#d97706;"></i>
                              </button>
                            </form>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="8" style="text-align:center; color:var(--clr-text-muted); padding:2rem;">No hay notas registradas</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($notes->hasPages())
              <div style="margin-top:1rem;">{{ $notes->links() }}</div>
            @endif
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
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4000);
    });
  </script>
@endpush
