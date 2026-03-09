@extends('layouts.app')

@section('title', 'Noticia | Portal Mendieta')

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

          <div class="page-header simple-header" style="margin-bottom: 2rem;">
              <div>
                  <h1 class="page-title" style="font-size: 2.5rem; line-height: 1.2; margin-bottom: 0.5rem; max-width: 800px; color: var(--clr-text-main, #111827);">{{ $news->title }}</h1>
                  <p style="color: var(--clr-text-muted, #6b7280); font-size: 0.95rem;">
                      Publicado el {{ $news->published_at ? $news->published_at->format('d \\d\\e M, Y \\a \\l\\a\\s H:i') : 'No publicado' }} 
                      por {{ $news->author->name ?? 'Sistema' }}
                  </p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('news.index') }}" class="btn-secondary">
                      <i class='bx bx-arrow-back'></i> Volver
                  </a>
                  @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                  <a href="{{ route('news.edit', $news) }}" class="btn-primary">
                      <i class='bx bx-edit-alt'></i> Editar
                  </a>
                  @endif
              </div>
          </div>

          <div class="module-card" style="padding: 0; overflow: hidden; max-width: 900px; margin: 0 auto; background: var(--clr-bg-card, #fff); border: 1px solid var(--clr-border-light, #e5e7eb); box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
              @if($news->image_path)
              <div style="width: 100%; height: 400px; overflow: hidden; background: #f3f4f6;">
                  <img src="{{ Storage::url($news->image_path) }}" alt="{{ $news->title }}" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              @endif

              <div style="padding: 3rem;">
                  <div class="news-body-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--clr-text-main, #374151);">
                      {!! $news->content !!}
                  </div>
              </div>
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('styles')
<style>
    .news-body-content p {
        margin-bottom: 1.5rem;
        color: var(--clr-text-main, #374151);
    }
    .news-body-content a {
        color: var(--clr-active-bg, #3b82f6);
        text-decoration: underline;
    }
    .news-body-content ul, .news-body-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
        color: var(--clr-text-main, #374151);
    }
    .news-body-content h2, .news-body-content h3, .news-body-content h4 {
        color: var(--clr-text-main, #111827);
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    body.dark-mode .module-card {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush

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
