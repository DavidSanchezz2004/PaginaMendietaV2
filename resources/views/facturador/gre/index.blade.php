@extends('layouts.app')

@section('title', 'Guías de Remisión — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .badge-draft     { background:rgba(107,114,128,.1); color:#374151; border:1px solid rgba(107,114,128,.2); }
    .badge-ready     { background:rgba(30,64,175,.1); color:#3b82f6; border:1px solid rgba(59,130,246,.2); }
    .badge-sent      { background:rgba(6,95,70,.1); color:#065f46; border:1px solid rgba(16,185,129,.2); }
    .badge-error     { background:rgba(153,27,27,.1); color:#ef4444; border:1px solid rgba(239,68,68,.2); }
    .badge-consulted { background:rgba(91,33,182,.1); color:#8b5cf6; border:1px solid rgba(139,92,246,.2); }
    .badge-voided    { background:rgba(107,114,128,.12); color:#6b7280; border:1px solid rgba(107,114,128,.25); text-decoration:line-through; }
    .gre-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .7rem; border-radius:20px; font-size:.75rem; font-weight:700; }
    .filter-bar { display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem; }
    .filter-bar .form-control-sm { border:1px solid var(--clr-border-light,#d1d5db); border-radius:8px; padding:.4rem .7rem; font-size:.85rem; background:var(--clr-bg-input,#fff); color:var(--clr-text-main,#111827); }
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

        @foreach(['success' => null, 'error' => 'module-alert--error', 'warning' => 'module-alert--warning'] as $flashKey => $flashClass)
          @if(session($flashKey))
            <div class="placeholder-content module-alert module-flash {{ $flashClass }}" data-flash-message>
              <p>{{ session($flashKey) }}</p>
              <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
            </div>
          @endif
        @endforeach

        <div class="placeholder-content module-card-wide">
          <div class="module-toolbar">
            <h1><i class='bx bx-map-alt' style="font-size:1.3rem;vertical-align:middle;"></i> Guías de Remisión</h1>
            @can('create', \App\Models\Invoice::class)
              <a href="{{ route('facturador.gre.create') }}" class="btn-primary">
                <i class='bx bx-plus'></i> Nueva GRE
              </a>
            @endcan
          </div>

          {{-- Filtros --}}
          <form method="GET" action="{{ route('facturador.gre.index') }}" class="filter-bar">
            <input type="text" name="serie" value="{{ $filters['serie'] ?? '' }}"
                   class="form-control-sm" placeholder="Serie…" style="width:90px;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   class="form-control-sm" placeholder="Buscar…" style="min-width:160px;">
            <select name="estado" class="form-control-sm">
              <option value="">Todos los estados</option>
              <option value="draft"     {{ ($filters['estado'] ?? '') === 'draft'     ? 'selected' : '' }}>Borrador</option>
              <option value="ready"     {{ ($filters['estado'] ?? '') === 'ready'     ? 'selected' : '' }}>Listo</option>
              <option value="sent"      {{ ($filters['estado'] ?? '') === 'sent'      ? 'selected' : '' }}>Enviado</option>
              <option value="consulted" {{ ($filters['estado'] ?? '') === 'consulted' ? 'selected' : '' }}>Consultado</option>
              <option value="error"     {{ ($filters['estado'] ?? '') === 'error'     ? 'selected' : '' }}>Error</option>
              <option value="voided"    {{ ($filters['estado'] ?? '') === 'voided'    ? 'selected' : '' }}>Anulado</option>
            </select>
            <button type="submit" class="btn-secondary" style="padding:.4rem .8rem; font-size:.85rem;">
              <i class='bx bx-search'></i> Filtrar
            </button>
            @if(array_filter($filters))
              <a href="{{ route('facturador.gre.index') }}" class="btn-secondary" style="padding:.4rem .8rem; font-size:.85rem;">
                <i class='bx bx-x'></i> Limpiar
              </a>
            @endif
          </form>

          <div class="module-table-wrap">
            <table class="module-table">
              <thead>
                <tr>
                  <th>Serie-Número</th>
                  <th>Fecha</th>
                  <th>Modalidad</th>
                  <th>Motivo</th>
                  <th>Destinatario</th>
                  <th>Estado</th>
                  <th style="text-align:center;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                @forelse($gres as $gre)
                  @php
                    $dest      = $gre->gre_destinatario ?? [];
                    $modalidad = $gre->codigo_modalidad_traslado === '01' ? 'Público' : 'Privado';
                  @endphp
                  <tr>
                    <td><strong><code>{{ $gre->serie_numero }}</code></strong></td>
                    <td>{{ $gre->fecha_emision->format('d/m/Y') }}</td>
                    <td>
                      <span style="font-size:.78rem; padding:.2rem .5rem; border-radius:6px; background:rgba(26,107,87,.08); color:var(--clr-active-bg,#1a6b57); font-weight:700;">
                        {{ $gre->codigo_modalidad_traslado }} · {{ $modalidad }}
                      </span>
                    </td>
                    <td style="font-size:.85rem;">{{ $gre->codigo_motivo_traslado }} — {{ Str::limit($gre->descripcion_motivo_traslado, 30) }}</td>
                    <td style="font-size:.85rem;">{{ Str::limit($dest['nombre_razon_social_destinatario'] ?? '—', 30) }}</td>
                    <td>
                      <span class="gre-badge badge-{{ $gre->estado->value }}">{{ $gre->estado->label() }}</span>
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                      <a href="{{ route('facturador.gre.show', $gre) }}" class="btn-secondary" style="padding:.3rem .65rem; font-size:.8rem;">
                        <i class='bx bx-show'></i> Ver
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" style="text-align:center; color:#9ca3af; padding:2rem;">
                      No se encontraron Guías de Remisión.
                      @can('create', \App\Models\Invoice::class)
                        <a href="{{ route('facturador.gre.create') }}">Crear la primera</a>
                      @endcan
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div style="margin-top:1rem;">
            {{ $gres->links() }}
          </div>
        </div>
      </div>
    </main>
  </section>
</div>
@endsection
