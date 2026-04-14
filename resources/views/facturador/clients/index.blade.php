@extends('layouts.app')

@section('title', 'Clientes — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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
              <h1>Catálogo de Clientes</h1>
              <div style="display:flex; gap:.5rem; align-items:center;">
                <a href="{{ route('facturador.index') }}" class="btn-secondary" style="font-size:.85rem;">
                  <i class='bx bx-building'></i> Cambiar empresa
                </a>
                @can('create', \App\Models\Client::class)
                  <a href="{{ route('facturador.clients.create') }}" class="btn-primary">
                    <i class='bx bx-plus'></i> Nuevo Cliente
                  </a>
                @endcan
              </div>
            </div>

            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>Tipo Doc.</th>
                    <th>N° Documento</th>
                    <th>Razón Social</th>
                    <th>País</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th class="cell-action">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($clients as $client)
                    <tr>
                      <td>{{ $client->codigo_tipo_documento }}</td>
                      <td>{{ $client->numero_documento }}</td>
                      <td>{{ $client->nombre_razon_social }}</td>
                      <td>{{ $client->pais }}</td>
                      <td>{{ $client->correo_electronico ?? '—' }}</td>
                      <td>
                        <span class="companies-status-pill {{ $client->activo ? 'is-active' : 'is-inactive' }}">
                          {{ $client->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                      <td class="cell-action">
                        <div class="action-wrapper">
                          @can('update', $client)
                            <a href="{{ route('facturador.clients.edit', $client) }}" class="btn-action-icon" title="Editar">
                              <i class='bx bx-pencil'></i>
                            </a>
                          @endcan
                          @can('delete', $client)
                            <form method="POST" action="{{ route('facturador.clients.destroy', $client) }}" data-confirm-delete>
                              @csrf @method('DELETE')
                              <button type="submit" class="btn-action-icon" title="Eliminar">
                                <i class='bx bx-trash'></i>
                              </button>
                            </form>
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="7">No hay clientes registrados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($clients->hasPages())
              <div style="margin-top:1rem;">{{ $clients->links() }}</div>
            @endif
          </div>

        </div>
      </main>
    </section>
  </div>



@endsection

@push('scripts')
  <script>
    document.querySelectorAll('[data-confirm-delete]').forEach(f => {
      f.addEventListener('submit', async e => {
        e.preventDefault();
        const result = await Swal.fire({
          title: 'Eliminar cliente',
          text: 'Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          reverseButtons: true,
          customClass: {
            popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : ''
          }
        });

        if (result.isConfirmed) {
          HTMLFormElement.prototype.submit.call(f);
        }
      });
    });
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4000);
    });


  </script>
@endpush
