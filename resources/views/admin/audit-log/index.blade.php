@extends('layouts.app')

@section('title', 'Registro de Auditoría | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
  @php
    $eventLabels = [
      'created'  => ['label' => 'Creado',     'color' => '#16a34a'],
      'updated'  => ['label' => 'Modificado',  'color' => '#2563eb'],
      'deleted'  => ['label' => 'Eliminado',   'color' => '#dc2626'],
      'restored' => ['label' => 'Restaurado',  'color' => '#d97706'],
    ];
    $logColors = [
      'empresa'    => '#1a3a6b',
      'usuario'    => '#7c3aed',
      'cliente'    => '#0891b2',
      'factura'    => '#b45309',
      'producto'   => '#059669',
      'credencial' => '#9f1239',
      'default'    => '#64748b',
    ];
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
              <div>
                <h1>Registro de Auditoría</h1>
                <p style="margin-top:.35rem; color:var(--clr-text-muted);">
                  Historial de todas las modificaciones realizadas en el sistema.
                </p>
              </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin.audit-log.index') }}"
                  style="display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.25rem; align-items:flex-end;">
              <div style="flex:1; min-width:200px;">
                <label style="font-size:.8rem; font-weight:600; color:var(--clr-text-muted); display:block; margin-bottom:.3rem;">Buscar usuario / acción</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Nombre, email, descripción..."
                       style="width:100%; padding:.5rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem; font-size:.9rem;">
              </div>
              <div style="min-width:160px;">
                <label style="font-size:.8rem; font-weight:600; color:var(--clr-text-muted); display:block; margin-bottom:.3rem;">Módulo</label>
                <select name="log" style="width:100%; padding:.5rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem; font-size:.9rem;">
                  <option value="">Todos</option>
                  @foreach($logNames as $name)
                    <option value="{{ $name }}" @selected(request('log') === $name)>
                      {{ ucfirst($name) }}
                    </option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="btn-primary" style="padding:.5rem 1.25rem;">
                <i class='bx bx-search'></i> Filtrar
              </button>
              @if(request('q') || request('log'))
                <a href="{{ route('admin.audit-log.index') }}" class="btn-secondary" style="padding:.5rem 1rem;">
                  <i class='bx bx-x'></i> Limpiar
                </a>
              @endif
            </form>

            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>Registro</th>
                    <th>Cambios</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($logs as $log)
                    @php
                      $event      = (string) $log->event;
                      $eventInfo  = $eventLabels[$event] ?? ['label' => ucfirst($event), 'color' => '#64748b'];
                      $logName    = (string) $log->log_name;
                      $logColor   = $logColors[$logName] ?? $logColors['default'];
                      $properties = $log->properties;
                      $old        = $properties->get('old', []);
                      $attrs      = $properties->get('attributes', []);
                    @endphp
                    <tr>
                      <td style="white-space:nowrap; font-size:.83rem; color:var(--clr-text-muted);">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                      </td>
                      <td>
                        @if($log->causer)
                          <strong style="font-size:.88rem;">{{ $log->causer->name }}</strong><br>
                          <small style="color:var(--clr-text-muted);">{{ $log->causer->email }}</small>
                        @else
                          <span style="color:var(--clr-text-muted);">Sistema</span>
                        @endif
                      </td>
                      <td>
                        <span style="
                            background:{{ $logColor }}18;
                            color:{{ $logColor }};
                            border:1px solid {{ $logColor }}44;
                            border-radius:999px;
                            padding:.2rem .65rem;
                            font-size:.78rem;
                            font-weight:700;
                            white-space:nowrap;">
                          {{ ucfirst($logName) }}
                        </span>
                      </td>
                      <td>
                        <span style="
                            background:{{ $eventInfo['color'] }}18;
                            color:{{ $eventInfo['color'] }};
                            border-radius:999px;
                            padding:.2rem .6rem;
                            font-size:.78rem;
                            font-weight:700;
                            white-space:nowrap;">
                          {{ $eventInfo['label'] }}
                        </span>
                      </td>
                      <td style="font-size:.85rem;">
                        @if($log->subject)
                          <code style="font-size:.8rem; color:#64748b;">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</code>
                        @else
                          <span style="color:var(--clr-text-muted);">—</span>
                        @endif
                      </td>
                      <td style="font-size:.82rem; max-width:320px;">
                        @if($event === 'updated' && count($old) > 0)
                          <div style="display:flex; flex-direction:column; gap:.25rem;">
                            @foreach($old as $field => $oldVal)
                              @php $newVal = $attrs[$field] ?? '—'; @endphp
                              <div>
                                <span style="font-weight:600; color:#475569;">{{ $field }}:</span>
                                <span style="color:#dc2626; text-decoration:line-through;">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</span>
                                <span style="color:#64748b;"> → </span>
                                <span style="color:#16a34a;">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                              </div>
                            @endforeach
                          </div>
                        @elseif($event === 'created' && count($attrs) > 0)
                          <span style="color:var(--clr-text-muted); font-size:.8rem;">
                            {{ collect($attrs)->keys()->take(3)->implode(', ') }}
                            @if(count($attrs) > 3), +{{ count($attrs) - 3 }} más @endif
                          </span>
                        @else
                          <span style="color:var(--clr-text-muted);">—</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" style="text-align:center; color:var(--clr-text-muted); padding:2rem;">
                        No hay registros de auditoría aún.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($logs->hasPages())
              <div style="margin-top:1rem;">{{ $logs->links() }}</div>
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
