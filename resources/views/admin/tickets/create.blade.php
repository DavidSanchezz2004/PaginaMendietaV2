@extends('layouts.app')

@section('title', 'Nueva Consulta | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .file-upload-wrapper {
          position: relative;
          display: flex; align-items: center; justify-content: center;
          padding: 2rem; border: 2px dashed #d1d5db; border-radius: 0.5rem;
          background: #f9fafb; cursor: pointer; transition: all 0.2s;
          text-align: center; color: #6b7280;
      }
      .file-upload-wrapper:hover { border-color: #3b82f6; background: #eff6ff; color: #3b82f6; }
      .file-upload-wrapper input[type="file"] {
          position: absolute; width: 100%; height: 100%; top: 0; left: 0;
          opacity: 0; cursor: pointer;
      }
      .file-upload-text { pointer-events: none; }
      .file-upload-name { display: block; margin-top: 0.5rem; font-weight: 500; color: #111827; }
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
          
          @if($errors->has('general'))
            <div class="placeholder-content module-alert" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
              <p>{{ $errors->first('general') }}</p>
            </div>
          @endif

          <div class="page-header simple-header">
              <div>
                  <h1 class="page-title">Nueva Consulta</h1>
                  <p class="page-description">Envía una consulta directamente a tu asociado contable. Responderemos a la brevedad.</p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('tickets.index') }}" class="btn-secondary">
                      <i class='bx bx-arrow-back'></i> Volver
                  </a>
              </div>
          </div>

          <div class="module-card" style="max-width: 800px; margin: 0 auto; margin-top: 2rem;">
              <form class="module-form form-grid" method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                  @csrf

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="subject" class="form-label">Asunto / Tema de Consulta <span style="color:red">*</span></label>
                      <input type="text" class="form-input @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required autofocus placeholder="Ej: Duda sobre declaración del mes">
                      @error('subject')
                          <div class="invalid-feedback" style="color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="message" class="form-label">Mensaje detallado <span style="color:red">*</span></label>
                      <textarea class="form-input @error('message') is-invalid @enderror" id="message" name="message" rows="6" required placeholder="Describe tu consulta con el mayor detalle posible...">{{ old('message') }}</textarea>
                      @error('message')
                          <div class="invalid-feedback" style="color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label class="form-label">Archivo adjunto (Opcional)</label>
                      <div class="file-upload-wrapper">
                          <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                          <div class="file-upload-text">
                              <i class='bx bx-cloud-upload' style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                              <p>Arrastra un archivo aquí o haz clic para subir</p>
                              <span class="file-upload-name" id="file-name-display">Ningún archivo seleccionado (Max 5MB)</span>
                          </div>
                      </div>
                      @error('attachment')
                          <div class="invalid-feedback" style="color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="module-actions" style="grid-column: 1 / -1;">
                      <button type="button" class="btn-secondary" onclick="window.location='{{ route('tickets.index') }}'">Cancelar</button>
                      <button type="submit" class="btn-primary">
                          <i class='bx bx-send'></i> Enviar Consulta
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
    document.body.classList.add('mendieta-admin');

    document.querySelectorAll('.toggle-submenu').forEach((btn) => {
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        btn.closest('.nav-item')?.classList.toggle('open');
      });
    });

    // Tema
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

    // Profile menu
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
    const fileInput = document.getElementById('attachment');
    const fileNameDisplay = document.getElementById('file-name-display');
    if (fileInput && fileNameDisplay) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = 'Seleccionado: ' + e.target.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Ningún archivo seleccionado (Max 5MB)';
            }
        });
    }

    // Flash message
    const hideFlashMessage = (flash) => {
      flash.classList.add('is-hiding');
      window.setTimeout(() => flash.remove(), 220);
    };
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => hideFlashMessage(flash));
      window.setTimeout(() => { if (document.body.contains(flash)) hideFlashMessage(flash); }, 4000);
    });
  </script>
@endpush
