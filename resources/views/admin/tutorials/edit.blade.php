@extends('layouts.app')

@section('title', 'Editar Tutorial | Portal Mendieta')

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
                  <h1 class="page-title">Editar Tutorial</h1>
                  <p class="page-description">Actualizando el video tutorial seleccionado.</p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('tutorials.index') }}" class="btn-secondary">
                      <i class='bx bx-arrow-back'></i> Volver
                  </a>
              </div>
          </div>

          <div class="module-card">
              <form class="module-form form-grid is-editing" method="POST" action="{{ route('tutorials.update', $tutorial) }}">
                  @csrf
                  @method('PATCH')

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="title" class="form-label" style="color: var(--clr-text-main, #374151);">Título del Tutorial <span style="color:red">*</span></label>
                      <input type="text" class="form-input @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $tutorial->title) }}" required autofocus style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                      @error('title')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="description" class="form-label" style="color: var(--clr-text-main, #374151);">Descripción Detallada</label>
                      <textarea class="form-input @error('description') is-invalid @enderror" id="description" name="description" rows="4" style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">{{ old('description', $tutorial->description) }}</textarea>
                      @error('description')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="video_url" class="form-label" style="color: var(--clr-text-main, #374151);">URL del Video <span style="color:red">*</span></label>
                      <input type="url" class="form-input @error('video_url') is-invalid @enderror" id="video_url" name="video_url" value="{{ old('video_url', $tutorial->video_url) }}" required style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                      <small style="color: var(--clr-text-muted, #6b7280); font-size: 0.75rem;">Si es de YouTube, se adaptará para incrustarse correctamente en las tarjetas.</small>
                      @error('video_url')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group">
                      <label for="status" class="form-label" style="color: var(--clr-text-main, #374151);">Estado <span style="color:red">*</span></label>
                      <select class="form-input @error('status') is-invalid @enderror" id="status" name="status" required style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                          <option value="draft" {{ old('status', $tutorial->published_at ? 'published' : 'draft') == 'draft' ? 'selected' : '' }}>Borrador (No visible)</option>
                          <option value="published" {{ old('status', $tutorial->published_at ? 'published' : 'draft') == 'published' ? 'selected' : '' }}>Publicado (Visible)</option>
                      </select>
                      @error('status')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="module-actions" style="grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center;">
                      <div>
                          <button type="button" form="delete-form" class="btn-secondary" style="color: #dc2626; border-color: #fca5a5; background-color: #fef2f2;" onclick="Swal.fire({title:'¿Estás seguro?',text:'¿Deseas eliminar este tutorial?',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',cancelButtonText:'Cancelar',confirmButtonText:'Sí, eliminar'}).then(r=>{if(r.isConfirmed)document.getElementById('delete-form').submit()})">
                              <i class='bx bx-trash'></i> Eliminar
                          </button>
                      </div>
                      <div style="display: flex; gap: 1rem;">
                          <button type="button" class="btn-secondary" onclick="window.location='{{ route('tutorials.index') }}'">Cancelar</button>
                          <button type="submit" class="btn-primary">
                              <i class='bx bx-save'></i> Guardar Cambios
                          </button>
                      </div>
                  </div>
              </form>

              <form id="delete-form" action="{{ route('tutorials.destroy', $tutorial) }}" method="POST" style="display: none;">
                  @csrf
                  @method('DELETE')
              </form>
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
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
      if (closeBtn) {
        closeBtn.addEventListener('click', () => hideFlashMessage(flash));
      }

      window.setTimeout(() => {
        if (document.body.contains(flash)) {
          hideFlashMessage(flash);
        }
      }, 4000);
    });
  </script>
@endpush
