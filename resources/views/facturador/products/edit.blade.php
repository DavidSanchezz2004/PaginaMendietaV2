@extends('layouts.app')

@section('title', 'Editar Producto — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
  @php $isEditing = true; @endphp

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

          @if ($errors->any())
            <div class="placeholder-content module-alert">
              @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
          @endif

          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <h1>Editar Producto — <code>{{ $product->codigo_interno }}</code></h1>
              <a href="{{ route('facturador.products.index') }}" class="btn-secondary">
                <i class='bx bx-arrow-back'></i> Volver
              </a>
            </div>

            <form method="POST" action="{{ route('facturador.products.update', $product) }}" class="module-form companies-form-grid">
              @csrf
              @method('PUT')
              @include('facturador.products._form')

              <div class="form-group full-width profile-actions module-actions">
                <a href="{{ route('facturador.products.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">
                  <i class='bx bx-save'></i> Actualizar Producto
                </button>
              </div>
            </form>
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
<script>
  document.getElementById('valor_unitario').addEventListener('input', function () {
    const val = parseFloat(this.value);
    const precio = document.getElementById('precio_unitario');
    if (!isNaN(val) && val >= 0) {
      precio.value = (val * 1.18).toFixed(2);
    } else {
      precio.value = '';
    }
  });
</script>
@endpush
