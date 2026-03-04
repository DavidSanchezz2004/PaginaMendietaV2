@extends('layouts.app')

@section('title', 'Tutoriales | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .tutorial-card {
          background: var(--clr-bg-card, #fff); border-radius: 12px; overflow: hidden; 
          box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid var(--clr-border-light, #e5e7eb); 
          display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;
      }
      .tutorial-card:hover {
          transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
      }
      .tutorial-video-container {
          position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;
          background: #111827; display: flex; align-items: center; justify-content: center;
      }
      .tutorial-video-container iframe {
          position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;
      }
      .tutorial-fallback {
          position: absolute; top: 0; left: 0; width: 100%; height: 100%;
          display: flex; align-items: center; justify-content: center; color: white;
          background: linear-gradient(to bottom right, #374151, #111827);
      }
      body.dark-mode .tutorial-card {
          box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2) !important;
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

          <div class="page-header simple-header">
              <div>
                  <h1 class="page-title">Tutoriales</h1>
                  <p class="page-description">Guías y recursos para utilizar la plataforma al máximo.</p>
              </div>
              <div class="header-actions">
                  @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                      <a href="{{ route('tutorials.create') }}" class="btn-primary">
                          <i class='bx bx-plus-circle'></i> Nuevo Tutorial
                      </a>
                  @endif
              </div>
          </div>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
              @forelse($tutorials as $tutorial)
                  <article class="tutorial-card fade-in">
                      <div class="tutorial-video-container">
                          @if(str_contains($tutorial->video_url, 'youtube.com/embed/'))
                              <iframe src="{{ $tutorial->video_url }}?autoplay=0&rel=0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                          @else
                              <div class="tutorial-fallback">
                                  <a href="{{ $tutorial->video_url }}" target="_blank" style="color: white;text-decoration: none; display: flex; flex-direction: column; align-items: center;">
                                      <i class='bx bx-play-circle' style="font-size: 3rem; margin-bottom: 0.5rem;"></i>
                                      <span style="font-size: 0.95rem; font-weight: 500;">Ver Video Externo</span>
                                  </a>
                              </div>
                          @endif
                      </div>
                      
                      <div style="padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column;">
                          <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--clr-text-main, #111827); margin-bottom: 0.5rem; line-height: 1.4;">
                              {{ $tutorial->title }}
                          </h3>
                          <p style="font-size: 0.95rem; color: var(--clr-text-muted, #4b5563); line-height: 1.5; flex-grow: 1; margin-bottom: 1rem;">
                              {{ Str::limit($tutorial->description, 100) }}
                          </p>
                          <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: space-between; margin-top: auto; border-top: 1px solid var(--clr-border-light, #f3f4f6); padding-top: 1rem;">
                              <span style="font-size: 0.85rem; color: var(--clr-text-muted, #6b7280);">
                                  <i class='bx bx-time'></i> {{ $tutorial->published_at ? $tutorial->published_at->diffForHumans() : 'Borrador' }}
                              </span>
                              
                              <div style="display: flex; gap: 0.5rem;">
                                  <a href="{{ route('tutorials.show', $tutorial) }}" class="btn-secondary" style="padding: 0.4rem 0.6rem; font-size: 0.85rem;" title="Ver detalles">
                                      <i class='bx bx-window-open'></i>
                                  </a>
                                  @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                      <a href="{{ route('tutorials.edit', $tutorial) }}" class="btn-primary" style="padding: 0.4rem 0.6rem; font-size: 0.85rem;" title="Editar">
                                          <i class='bx bx-edit-alt'></i>
                                      </a>
                                  @endif
                              </div>
                          </div>
                      </div>
                  </article>
              @empty
                  <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: var(--clr-bg-card, white); border-radius: 12px; border: 1px dashed var(--clr-border-light, #d1d5db);">
                      <i class='bx bxs-video' style="font-size: 3rem; color: var(--clr-text-muted, #9ca3af); margin-bottom: 1rem;"></i>
                      <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--clr-text-main, #374151);">No hay tutoriales disponibles</h3>
                      <p style="color: var(--clr-text-muted, #6b7280); margin-top: 0.5rem;">Aún no se han publicado tutoriales.</p>
                  </div>
              @endforelse
          </div>

          @if($tutorials->hasPages())
              <div class="pagination-wrapper" style="margin-top: 2rem;">
                  {{ $tutorials->links() }}
              </div>
          @endif
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
