@extends('layouts.app')

@section('title', 'Facturador | Portal Mendieta')

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
              <h1>Facturador — Selecciona una Empresa</h1>
            </div>
            <p style="color:#6b7280; margin-bottom:1.5rem;">
              Elige la empresa con la que deseas trabajar. Solo se muestran empresas con el facturador habilitado.
            </p>

            @if($companies->isEmpty())
              <p style="color:#9ca3af;">No hay empresas con el facturador habilitado asignadas a tu cuenta.</p>
            @else
              <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap:1rem;">
                @foreach($companies as $company)
                  @php $isActive = session('company_id') == $company->id; @endphp
                  <div style="border:2px solid {{ $isActive ? '#1a6b57' : '#e5e7eb' }}; border-radius:12px; padding:1.25rem; background:#fff;">
                    <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.75rem;">
                      <i class='bx bx-building' style="font-size:1.5rem; color:#1a6b57;"></i>
                      <div>
                        <p style="font-weight:600; margin:0;">{{ $company->name }}</p>
                        <p style="font-size:.85rem; color:#6b7280; margin:0;">RUC: {{ $company->ruc }}</p>
                      </div>
                    </div>
                    @if($isActive)
                      <span style="display:inline-block; background:#d1fae5; color:#065f46; font-size:.78rem; padding:.2rem .7rem; border-radius:20px; margin-bottom:.75rem;">
                        ✓ Empresa activa
                      </span><br>
                      <a href="{{ route('facturador.invoices.index') }}" class="btn-primary" style="text-decoration:none; display:inline-block; font-size:.875rem;">
                        Ir al Facturador →
                      </a>
                    @else
                      <form method="POST" action="{{ route('facturador.active-company') }}">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                        <button type="submit" class="btn-secondary" style="font-size:.875rem;">
                          Seleccionar empresa
                        </button>
                      </form>
                    @endif
                  </div>
                @endforeach
              </div>
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
