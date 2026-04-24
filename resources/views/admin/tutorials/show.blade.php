@extends('layouts.app')

@section('title', 'Tutorial | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .tutorial-video-wrapper {
          position: relative;
          padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
          height: 0;
          overflow: hidden;
          border-radius: 12px 12px 0 0;
          background: #1f2937;
      }
      .tutorial-video-wrapper iframe {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          border: 0;
      }
      body.dark-mode .module-card {
          box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
      }
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

          <div class="page-header simple-header" style="margin-bottom: 2rem;">
              <div>
                  <h1 class="page-title" style="font-size: 2.5rem; line-height: 1.2; margin-bottom: 0.5rem; max-width: 800px; color: var(--clr-text-main, #111827);">{{ $tutorial->title }}</h1>
                  <p style="color: var(--clr-text-muted, #6b7280); font-size: 0.95rem;">
                      Publicado el {{ $tutorial->published_at ? $tutorial->published_at->format('d \\d\\e M, Y \\a \\l\\a\\s H:i') : 'No publicado' }} 
                  </p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('tutorials.index') }}" class="btn-secondary">
                      <i class='bx bx-arrow-back'></i> Volver
                  </a>
                  @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                  <a href="{{ route('tutorials.edit', $tutorial) }}" class="btn-primary">
                      <i class='bx bx-edit-alt'></i> Editar
                  </a>
                  @endif
              </div>
          </div>

          <div class="module-card" style="padding: 0; overflow: hidden; max-width: 900px; margin: 0 auto; background: var(--clr-bg-card, #fff); border: 1px solid var(--clr-border-light, #e5e7eb); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
              <div class="tutorial-video-wrapper">
                  @if(str_contains($tutorial->video_url, 'youtube.com/embed/'))
                      <iframe src="{{ $tutorial->video_url }}?autoplay=0&rel=0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                  @else
                      <div style="position: absolute; top:0; left:0; width:100%; height:100%; display: flex; align-items: center; justify-content: center; color: white;">
                          <a href="{{ $tutorial->video_url }}" target="_blank" style="color: white;text-decoration: none; display: flex; flex-direction: column; align-items: center; background: rgba(0,0,0,0.5); padding: 2rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.2); transition: background 0.2s;">
                              <i class='bx bx-play-circle' style="font-size: 5rem; margin-bottom: 1rem;"></i>
                              <span style="font-size: 1.25rem; font-weight: 500;">Abrir Video Externo</span>
                              <span style="font-size: 0.85rem; opacity: 0.7; margin-top: 0.5rem;">(No se pudo incrustar directamente)</span>
                          </a>
                      </div>
                  @endif
              </div>
              
              @if($tutorial->description)
              <div style="padding: 2.5rem; background: var(--clr-bg-card, #fff);">
                  <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--clr-text-main, #111827); margin-bottom: 1rem;">Acerca de este tutorial</h3>
                  <div style="font-size: 1.05rem; line-height: 1.7; color: var(--clr-text-main, #4b5563);">
                      {!! nl2br(e($tutorial->description)) !!}
                  </div>
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
