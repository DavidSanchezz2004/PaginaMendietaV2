@extends('layouts.app')

@section('title', 'Subir Documento Final | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .file-upload-wrapper {
          position: relative;
          display: flex; align-items: center; justify-content: center;
          padding: 3rem 2rem; border: 2px dashed var(--clr-border-light, #d1d5db); border-radius: 0.5rem;
          background: var(--clr-bg-body, #f9fafb); cursor: pointer; transition: all 0.2s;
          text-align: center; color: var(--clr-text-muted, #6b7280);
      }
      .file-upload-wrapper:hover { border-color: #3b82f6; background: var(--clr-active-bg, #eff6ff); color: #3b82f6; }
      .file-upload-wrapper input[type="file"] {
          position: absolute; width: 100%; height: 100%; top: 0; left: 0;
          opacity: 0; cursor: pointer; z-index: 10;
      }
      .file-upload-text { pointer-events: none; z-index: 1; }
      .file-upload-name { display: block; margin-top: 0.5rem; font-weight: 500; color: var(--clr-text-main, #111827); }
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
                  <h1 class="page-title">Subir Documento Final</h1>
                  <p class="page-description">Sube constancias, Fichas RUC o Declaraciones para proveerlas al cliente.</p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('final-documents.index') }}" class="btn-secondary">
                      <i class='bx bx-arrow-back'></i> Volver
                  </a>
              </div>
          </div>

          <div class="module-card" style="max-width: 800px; margin: 0 auto; margin-top: 2rem;">
              <form class="module-form form-grid" method="POST" action="{{ route('final-documents.store') }}" enctype="multipart/form-data">
                  @csrf

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="company_id" class="form-label" style="color: var(--clr-text-main, #374151);">Empresa Destino <span style="color:red">*</span></label>
                      <select name="company_id" id="company_id" class="form-input @error('company_id') is-invalid @enderror" required style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                          <option value="">-- Selecciona una empresa --</option>
                          @foreach($companies as $company)
                              <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                  {{ $company->name }} (RUC: {{ $company->ruc }})
                              </option>
                          @endforeach
                      </select>
                      @error('company_id')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: span 1;">
                      <label for="title" class="form-label" style="color: var(--clr-text-main, #374151);">Título del Documento <span style="color:red">*</span></label>
                      <input type="text" class="form-input @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Ej: Ficha RUC Actualizada" style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                      @error('title')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: span 1;">
                      <label for="document_type" class="form-label" style="color: var(--clr-text-main, #374151);">Tipo de Documento <span style="color:red">*</span></label>
                      <select name="document_type" id="document_type" class="form-input @error('document_type') is-invalid @enderror" required style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                          <option value="pdj" {{ old('document_type') === 'pdj' ? 'selected' : '' }}>PDJ Anual / Mensual</option>
                          <option value="ficha_ruc" {{ old('document_type') === 'ficha_ruc' ? 'selected' : '' }}>Ficha RUC</option>
                          <option value="other" {{ old('document_type') === 'other' ? 'selected' : '' }}>Otro Documento General</option>
                      </select>
                      @error('document_type')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label class="form-label" style="color: var(--clr-text-main, #374151);">Archivo <span style="color:red">*</span></label>
                      <div class="file-upload-wrapper">
                          <input type="file" id="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                          <div class="file-upload-text">
                              <i class='bx bx-cloud-upload' style="font-size: 3rem; margin-bottom: 0.5rem;"></i>
                              <p>Arrastra un archivo aquí o haz clic para subir</p>
                              <span class="file-upload-name" id="file-name-display">Ningún archivo seleccionado (Max 10MB)</span>
                          </div>
                      </div>
                      @error('file')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="module-actions" style="grid-column: 1 / -1;">
                      <button type="button" class="btn-secondary" onclick="window.location='{{ route('final-documents.index') }}'">Cancelar</button>
                      <button type="submit" class="btn-primary">
                          <i class='bx bx-cloud-upload'></i> Subir
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

    // File upload
        const fileInput = document.getElementById('file');
        const fileNameDisplay = document.getElementById('file-name-display');
        if (fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files.length > 0) {
                    fileNameDisplay.textContent = 'Seleccionado: ' + this.files[0].name;
                } else {
                    fileNameDisplay.textContent = 'Ningún archivo seleccionado (Max 10MB)';
                }
            });
        }
    }); // End DOMContentLoaded
  </script>
@endpush
