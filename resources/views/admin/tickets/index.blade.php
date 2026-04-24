@extends('layouts.app')

@section('title', 'Bandeja de Entrada | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .ticket-status-badge {
          display: inline-flex; align-items: center; justify-content: center;
          padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
      }
      .status-open { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
      .status-in-progress { background-color: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
      .status-resolved { background-color: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
      .status-closed { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
      
      .inbox-list { background: var(--clr-bg-card, #fff); border-radius: 0.75rem; border: 1px solid var(--clr-border-light, #e5e7eb); overflow: hidden; }
      .inbox-item {
          display: flex; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--clr-border-light, #f3f4f6);
          transition: background-color 0.2s; text-decoration: none; color: inherit;
      }
      .inbox-item:hover { background: var(--clr-active-bg, #f9fafb); cursor: pointer; }
      .inbox-item:last-child { border-bottom: none; }
      .inbox-item-indicator { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background: var(--clr-bg-body, #f3f4f6); display: flex; align-items: center; justify-content: center; margin-right: 1.5rem; color: var(--clr-text-muted, #6b7280); }
      .inbox-item-content { flex-grow: 1; min-width: 0; }
      .inbox-item-title { font-size: 1rem; font-weight: 600; color: var(--clr-text-main, #111827); margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      .inbox-item-subtitle { font-size: 0.875rem; color: var(--clr-text-muted, #6b7280); display: flex; gap: 1rem; align-items: center; }
      .inbox-item-meta { flex-shrink: 0; text-align: right; margin-left: 1.5rem; display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem; }
      .inbox-item-date { font-size: 0.75rem; color: var(--clr-text-muted, #9ca3af); font-weight: 500; }
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
                  <h1 class="page-title">Bandeja de Entrada</h1>
                  <p class="page-description">Centro de soporte y consultas con tu asociado contable.</p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('tickets.create') }}" class="btn-primary">
                      <i class='bx bx-plus-circle'></i> Nueva Consulta
                  </a>
              </div>
          </div>
          
          <div class="inbox-list" style="margin-top: 1.5rem;">
              @forelse($tickets as $ticket)
                  <a href="{{ route('tickets.show', $ticket) }}" class="inbox-item fade-in">
                      <div class="inbox-item-indicator">
                          @if($ticket->status->value === 'open')
                              <i class='bx bx-envelope' style="color: #dc2626; font-size: 1.25rem;"></i>
                          @elseif($ticket->status->value === 'in_progress')
                              <i class='bx bx-envelope-open' style="color: #2563eb; font-size: 1.25rem;"></i>
                          @elseif($ticket->status->value === 'resolved')
                              <i class='bx bx-check-double' style="color: #059669; font-size: 1.25rem;"></i>
                          @else
                              <i class='bx bx-archive-in' style="color: #6b7280; font-size: 1.25rem;"></i>
                          @endif
                      </div>
                      <div class="inbox-item-content">
                          <h3 class="inbox-item-title">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }} - {{ $ticket->subject }}</h3>
                          <div class="inbox-item-subtitle">
                              <span><i class='bx bx-user' style="margin-right:0.25rem;"></i>{{ $ticket->client->name }}</span>
                              @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                                  <span><i class='bx bx-building' style="margin-right:0.25rem;"></i>{{ $ticket->company->name }}</span>
                              @endif
                          </div>
                      </div>
                      <div class="inbox-item-meta">
                          <span class="ticket-status-badge status-{{ str_replace('_', '-', $ticket->status->value) }}">
                              {{ $ticket->status->label() }}
                          </span>
                          <span class="inbox-item-date">{{ $ticket->updated_at->diffForHumans() }}</span>
                      </div>
                  </a>
              @empty
                  <div class="empty-state" style="text-align: center; padding: 4rem 2rem;">
                      <i class='bx bx-inbox' style="font-size: 3rem; color: var(--clr-text-muted, #9ca3af); margin-bottom: 1rem;"></i>
                      <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--clr-text-main, #374151);">Bandeja Vacía</h3>
                      <p style="color: var(--clr-text-muted, #6b7280); margin-top: 0.5rem;">No hay consultas o tickets creados aún.</p>
                  </div>
              @endforelse
          </div>

          @if($tickets->hasPages())
              <div class="pagination-wrapper" style="margin-top: 2rem;">
                  {{ $tickets->links() }}
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
      if (closeBtn) {
        closeBtn.addEventListener('click', () => hideFlashMessage(flash));
      }

      window.setTimeout(() => {
        if (document.body.contains(flash)) {
          hideFlashMessage(flash);
        }
      }, 4000);
    });
    }); // End DOMContentLoaded
  </script>
@endpush
