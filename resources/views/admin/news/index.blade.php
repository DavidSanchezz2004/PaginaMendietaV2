@extends('layouts.app')

@section('title', 'Noticias | Portal Mendieta')

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
                  <h1 class="page-title">Noticias</h1>
                  <p class="page-description">Mantente al tanto de las últimas novedades tributarias y contables.</p>
              </div>
              <div class="header-actions">
                  @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                      <a href="{{ route('news.create') }}" class="btn-primary">
                          <i class='bx bx-plus-circle'></i> Publicar Noticia
                      </a>
                  @endif
              </div>
          </div>
          
          <div class="news-layout" style="margin-top: 1.5rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
              @forelse($news as $article)
                  <article class="news-card fade-in" style="background: var(--clr-bg-card, white); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--clr-border-light, #e5e7eb); display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
                      @if($article->image_path)
                          <div class="news-card-image" style="height: 180px; width: 100%; overflow: hidden;">
                              <img src="{{ Storage::url($article->image_path) }}" alt="{{ $article->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                          </div>
                      @endif
                      <div class="news-card-content" style="padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column;">
                          <div class="news-meta" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                              <span style="font-size: 0.875rem; color: var(--clr-text-muted, #6b7280); font-weight: 500;">
                                  <i class='bx bx-calendar'></i> 
                                  {{ $article->published_at ? $article->published_at->format('d/m/Y') : 'Borrador' }}
                              </span>
                          </div>
                          <h3 class="news-title" style="font-size: 1.125rem; font-weight: 700; color: var(--clr-text-main, #111827); margin-bottom: 0.5rem; line-height: 1.4;">
                              {{ $article->title }}
                          </h3>
                          <p class="news-excerpt" style="font-size: 0.95rem; color: var(--clr-text-muted, #4b5563); line-height: 1.5; flex-grow: 1;">
                              {{ $article->excerpt ?? Str::limit(strip_tags($article->content), 120) }}
                          </p>
                          <div class="news-actions" style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                              <a href="{{ route('news.show', $article) }}" class="btn-secondary" style="flex: 1; text-align: center; font-size: 0.875rem; padding: 0.5rem; justify-content: center;">Leer más</a>
                              
                              @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                  <a href="{{ route('news.edit', $article) }}" class="btn-primary" style="padding: 0.5rem 0.75rem; display: flex; align-items: center; justify-content: center;" title="Editar">
                                      <i class='bx bx-edit-alt'></i>
                                  </a>
                              @endif
                          </div>
                      </div>
                  </article>
              @empty
                  <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: var(--clr-bg-card, white); border-radius: 12px; border: 1px dashed var(--clr-border-light, #d1d5db);">
                      <i class='bx bx-news' style="font-size: 3rem; color: var(--clr-text-muted, #9ca3af); margin-bottom: 1rem;"></i>
                      <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--clr-text-main, #374151);">No hay noticias disponibles</h3>
                      <p style="color: var(--clr-text-muted, #6b7280); margin-top: 0.5rem;">Aún no se han publicado noticias.</p>
                  </div>
              @endforelse
          </div>

          @if($news->hasPages())
              <div class="pagination-wrapper" style="margin-top: 2rem;">
                  {{ $news->links() }}
              </div>
          @endif
        </div>
      </main>
    </section>
  </div>
@endsection

@push('styles')
<style>
    body.dark-mode .news-card {
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
