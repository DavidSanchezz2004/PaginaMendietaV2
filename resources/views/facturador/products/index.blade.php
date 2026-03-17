@extends('layouts.app')

@section('title', 'Productos — Facturador | Portal Mendieta')

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
              <h1>Catálogo de Productos y Servicios</h1>
              <div style="display:flex; gap:.5rem; align-items:center;">
                <a href="{{ route('facturador.index') }}" class="btn-secondary" style="font-size:.85rem;">
                  <i class='bx bx-building'></i> Cambiar empresa
                </a>
                @can('create', \App\Models\Product::class)
                  <a href="{{ route('facturador.products.create') }}" class="btn-primary">
                    <i class='bx bx-plus'></i> Nuevo Producto
                  </a>
                @endcan
              </div>
            </div>

            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Unidad</th>
                    <th>V. Unitario</th>
                    <th>P. Unitario (IGV)</th>
                    <th>Afecto</th>
                    <th>Estado</th>
                    <th class="cell-action">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($products as $product)
                    <tr>
                      <td><code>{{ $product->codigo_interno }}</code></td>
                      <td>{{ $product->tipo === 'P' ? 'Producto' : 'Servicio' }}</td>
                      <td>{{ $product->descripcion }}</td>
                      <td>{{ $product->codigo_unidad_medida }}</td>
                      <td>{{ number_format($product->valor_unitario, 4) }}</td>
                      <td>{{ number_format($product->precio_unitario, 4) }}</td>
                      <td>
                        @php
                          $afectoLabels = ['10'=>'Gravado','20'=>'Exonerado','30'=>'Inafecto','40'=>'Exportación'];
                          $afecto = (string) $product->codigo_indicador_afecto;
                        @endphp
                        <span title="Código {{ $afecto }}">{{ $afectoLabels[$afecto] ?? $afecto }}</span>
                      </td>
                      <td>
                        <span class="companies-status-pill {{ $product->activo ? 'is-active' : 'is-inactive' }}">
                          {{ $product->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                      <td class="cell-action">
                        <div class="action-wrapper">
                          @can('update', $product)
                            <a href="{{ route('facturador.products.edit', $product) }}" class="btn-action-icon" title="Editar">
                              <i class='bx bx-pencil'></i>
                            </a>
                          @endcan
                          @can('delete', $product)
                            <form method="POST" action="{{ route('facturador.products.destroy', $product) }}" data-confirm-delete>
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
                    <tr><td colspan="9">No hay productos registrados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($products->hasPages())
              <div style="margin-top:1rem;">{{ $products->links() }}</div>
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
      f.addEventListener('submit', e => { if (!confirm('¿Eliminar este producto?')) e.preventDefault(); });
    });
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4000);
    });
  </script>
@endpush
