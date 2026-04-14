@extends('layouts.app')

@section('title', 'Documentos Finales | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .doc-type-badge {
          display: inline-flex; align-items: center; justify-content: center;
          padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
      }
      .type-pdj { background-color: #ede9fe; color: #7c3aed; border: 1px solid #ddd6fe; }
      .type-ficha-ruc { background-color: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
      .type-other { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
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
        'userName' => auth()->user()?->name,
        'userEmail' => auth()->user()?->email,
      ])

      <main class="main-content">
        <div class="module-content-stack">
          @if (session('status'))
            <div class="placeholder-content module-alert module-flash" data-flash-message>
              <p>{{ session('status') }}</p>
              <button type="button" class="module-flash-close" aria-label="Cerrar mensaje" data-flash-close>
                <i class='bx bx-x'></i>
              </button>
            </div>
          @endif

          <div class="page-header simple-header">
              <div>
                  <h1 class="page-title">Documentos Finales</h1>
                  <p class="page-description">Archivo maestro de entregables, fichas RUC y declaraciones declaradas por la empresa.</p>
              </div>
              <div class="header-actions">
                  @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                  <a href="{{ route('final-documents.create') }}" class="btn-primary">
                      <i class='bx bx-cloud-upload'></i> Subir Documento
                  </a>
                  @endif
              </div>
          </div>

          <div class="module-card" style="padding: 0; overflow-x: auto;">
              <table class="module-table" style="min-width: 800px;">
                  <thead>
                      <tr>
                          <th>Documento</th>
                          <th>Tipo</th>
                          @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                          <th>Empresa</th>
                          @endif
                          <th>Fecha Subida</th>
                          <th style="text-align: center;">Acciones</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($documents as $doc)
                          <tr>
                              <td>
                                  <div style="display: flex; align-items: center; gap: 0.75rem;">
                                      <div style="width: 40px; height: 40px; border-radius: 8px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                                          <i class='bx bx-file' style="font-size: 1.25rem;"></i>
                                      </div>
                                      <div>
                                          <span style="display: block; font-weight: 500; color: var(--clr-text-main, #111827);">{{ $doc->title }}</span>
                                          <span style="font-size: 0.75rem; color: var(--clr-text-muted, #6b7280);" title="{{ $doc->original_name }}">{{ Str::limit($doc->original_name, 25) }}</span>
                                      </div>
                                  </div>
                              </td>
                              <td>
                                  <span class="doc-type-badge type-{{ str_replace('_', '-', strtolower($doc->document_type)) }}">
                                      {{ $doc->document_type === 'pdj' ? 'PDJ Anual' : ($doc->document_type === 'ficha_ruc' ? 'Ficha RUC' : 'Generales') }}
                                  </span>
                              </td>
                              @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                              <td>{{ $doc->company->name }}</td>
                              @endif
                              <td>{{ $doc->created_at->format('d/m/Y') }}</td>
                              <td>
                                  <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                      <a href="{{ route('final-documents.download', $doc) }}" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;" target="_blank">
                                          <i class='bx bx-download'></i> Descargar
                                      </a>
                                      
                                      @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                      <form action="{{ route('final-documents.destroy', $doc) }}" method="POST" data-confirm="¿Eliminar este documento permanentemente de los registros de la empresa?" style="margin: 0;">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit" class="btn-secondary" style="padding: 0.35rem 0.5rem; color: #dc2626; border-color: #fca5a5; background-color: #fef2f2;" title="Eliminar">
                                              <i class='bx bx-trash'></i>
                                          </button>
                                      </form>
                                      @endif
                                  </div>
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="{{ (auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR) ? 5 : 4 }}" style="text-align: center; padding: 4rem 2rem;">
                                  <i class='bx bx-folder-open' style="font-size: 3rem; color: var(--clr-text-muted, #e5e7eb); margin-bottom: 1rem;"></i>
                                  <h3 style="font-weight: 500; font-size: 1.125rem; color: var(--clr-text-main, #374151);">Aún no hay documentos finales</h3>
                                  <p style="color: var(--clr-text-muted, #6b7280); margin-top: 0.5rem;">Las constancias, fichas y declaraciones definitivas aparecerán aquí.</p>
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>

          @if($documents->hasPages())
              <div class="pagination-wrapper" style="margin-top: 1.5rem;">
                  {{ $documents->links() }}
              </div>
          @endif
        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('mendieta-admin');

    document.querySelectorAll('.toggle-submenu').forEach((btn) => {
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        btn.closest('.nav-item')?.classList.toggle('open');
      });
    });

    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const applyTheme = (theme) => {
      const dark = theme === 'dark';
      document.body.classList.toggle('dark-mode', dark);
      if (themeIcon) {
        themeIcon.classList.toggle('bx-moon', !dark);
        themeIcon.classList.toggle('bx-sun', dark);
      }
    };
    const savedTheme = localStorage.getItem('mendieta-theme') || 'light';
    applyTheme(savedTheme);
    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', () => {
        const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
        localStorage.setItem('mendieta-theme', nextTheme);
        applyTheme(nextTheme);
      });
    }

    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    if (profileBtn && profileDropdown) {
      profileBtn.addEventListener('click', () => profileDropdown.classList.toggle('show'));
      document.addEventListener('click', (event) => {
        const container = document.getElementById('profile-container');
        if (container && !container.contains(event.target)) {
          profileDropdown.classList.remove('show');
        }
      });
    }

    const hideFlashMessage = (flash) => {
      flash.classList.add('is-hiding');
      window.setTimeout(() => flash.remove(), 220);
    };

    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => hideFlashMessage(flash));
      window.setTimeout(() => { if (document.body.contains(flash)) hideFlashMessage(flash); }, 4000);
    });
    }); // End DOMContentLoaded
  </script>
@endpush
