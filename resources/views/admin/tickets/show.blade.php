@extends('layouts.app')

@section('title', 'Consulta #' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) . ' | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
      .ticket-header-meta {
          padding: 1.5rem; background: var(--clr-active-bg, #f9fafb); border-bottom: 1px solid var(--clr-border-light, #e5e7eb);
          display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;
      }
      .ticket-status-badge {
          display: inline-flex; align-items: center; justify-content: center;
          padding: 0.35rem 1rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;
      }
      .status-open { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
      .status-in-progress { background-color: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
      .status-resolved { background-color: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
      .status-closed { background-color: var(--clr-bg-body, #f3f4f6); color: var(--clr-text-muted, #4b5563); border: 1px solid var(--clr-border-light, #e5e7eb); }

      .chat-container {
          padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem;
          max-height: 600px; overflow-y: auto; background: var(--clr-bg-card, #fff);
      }
      .chat-message {
          display: flex; max-width: 85%;
      }
      .chat-message.self {
          align-self: flex-end; flex-direction: row-reverse;
      }
      .chat-message.other {
          align-self: flex-start;
      }
      .chat-avatar {
          width: 40px; height: 40px; border-radius: 50%; background: var(--clr-border-light, #e5e7eb);
          display: flex; align-items: center; justify-content: center; color: var(--clr-text-muted, #6b7280); font-weight: 600;
          flex-shrink: 0; margin: 0 1rem;
      }
      .chat-bubble {
          padding: 1rem 1.25rem; border-radius: 1rem; position: relative;
      }
      .chat-message.self .chat-bubble {
          background: #2563eb; color: #fff; border-bottom-right-radius: 0.25rem;
      }
      .chat-message.other .chat-bubble {
          background: var(--clr-bg-body, #f3f4f6); color: var(--clr-text-main, #1f2937); border-bottom-left-radius: 0.25rem; border: 1px solid var(--clr-border-light, #e5e7eb);
      }
      .chat-meta {
          font-size: 0.75rem; margin-top: 0.5rem; display: flex; gap: 0.5rem; opacity: 0.8;
      }
      .chat-message.self .chat-meta { justify-content: flex-end; color: var(--clr-text-muted, #bfdbfe); }
      .chat-message.other .chat-meta { justify-content: flex-start; color: var(--clr-text-muted, #6b7280); }
      
      .chat-attachment {
          display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem;
          border-radius: 0.5rem; margin-top: 0.75rem; text-decoration: none; font-size: 0.875rem;
          transition: all 0.2s;
      }
      .chat-message.self .chat-attachment { background: rgba(255,255,255,0.2); color: #fff; }
      .chat-message.self .chat-attachment:hover { background: rgba(255,255,255,0.3); }
      .chat-message.other .chat-attachment { background: var(--clr-bg-card, #fff); color: #3b82f6; border: 1px solid #bfdbfe; }
      .chat-message.other .chat-attachment:hover { background: var(--clr-active-bg, #eff6ff); }

      .chat-composer {
          padding: 1.5rem; background: var(--clr-active-bg, #f9fafb); border-top: 1px solid var(--clr-border-light, #e5e7eb);
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
                  <h1 class="page-title">Consulta #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</h1>
                  <p class="page-description">Hilo de soporte de la empresa.</p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('tickets.index') }}" class="btn-secondary">
                      <i class='bx bx-arrow-back'></i> Volver
                  </a>
              </div>
          </div>

          <div class="module-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
              
              <!-- Ticket Header Information -->
              <div class="ticket-header-meta">
                  <div>
                      <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--clr-text-main, #111827); margin-bottom: 0.5rem;">{{ $ticket->subject }}</h2>
                      <div style="font-size: 0.875rem; color: var(--clr-text-muted, #6b7280); display: flex; gap: 1.5rem; flex-wrap: wrap;">
                          <span><i class='bx bx-building' style="margin-right: 0.25rem;"></i> {{ $ticket->company->name }}</span>
                          <span><i class='bx bx-user' style="margin-right: 0.25rem;"></i> {{ $ticket->client->name }}</span>
                          <span><i class='bx bx-calendar' style="margin-right: 0.25rem;"></i> Creado el {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                      </div>
                  </div>
                  
                  <div style="display: flex; gap: 1rem; align-items: center;">
                      <span class="ticket-status-badge status-{{ str_replace('_', '-', $ticket->status->value) }}">
                          {{ $ticket->status->label() }}
                      </span>

                      @if(auth()->user()->role === \App\Enums\RoleEnum::ADMIN || auth()->user()->role === \App\Enums\RoleEnum::SUPERVISOR)
                      <form action="{{ route('tickets.status.update', $ticket) }}" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                          @csrf
                          @method('PATCH')
                          <select name="status" class="form-input" style="padding: 0.35rem 2rem 0.35rem 0.75rem; font-size: 0.85rem; background-color: var(--clr-bg-body, #fff); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);" onchange="this.form.submit()">
                              <option value="open" {{ $ticket->status->value === 'open' ? 'selected' : '' }}>Marcar Abierto</option>
                              <option value="in_progress" {{ $ticket->status->value === 'in_progress' ? 'selected' : '' }}>Marcar En Revisión</option>
                              <option value="resolved" {{ $ticket->status->value === 'resolved' ? 'selected' : '' }}>Marcar Resuelto</option>
                              <option value="closed" {{ $ticket->status->value === 'closed' ? 'selected' : '' }}>Marcar Cerrado</option>
                          </select>
                      </form>
                      @endif
                  </div>
              </div>

              <!-- Chat Area -->
              <div class="chat-container" id="chat-container">
                  @foreach($ticket->messages as $message)
                      @php
                          $isSelf = $message->user_id === auth()->id();
                      @endphp
                      <div class="chat-message {{ $isSelf ? 'self' : 'other' }}">
                          <div class="chat-avatar" title="{{ $message->user->name }}">
                              {{ strtoupper(substr($message->user->name, 0, 1)) }}
                          </div>
                          <div style="display: flex; flex-direction: column;">
                              <div class="chat-bubble">
                                  @if(!$isSelf)
                                      <div style="font-size: 0.75rem; font-weight: 700; margin-bottom: 0.25rem; color: var(--clr-text-main, #4b5563);">{{ $message->user->name }}</div>
                                  @endif
                                  
                                  <div style="line-height: 1.5; white-space: pre-wrap;">{{ $message->message }}</div>
                                  
                                  @if($message->attachment_path)
                                      <a href="{{ Storage::url($message->attachment_path) }}" target="_blank" class="chat-attachment" download>
                                          <i class='bx bx-paperclip'></i> {{ $message->attachment_name }}
                                      </a>
                                  @endif
                              </div>
                              <div class="chat-meta">
                                  <span>{{ $message->created_at->format('H:i') }}</span>
                                  @if($isSelf)
                                      <i class='bx bx-check-double'></i>
                                  @else
                                      <span>&bull; {{ $message->created_at->format('d/m/Y') }}</span>
                                  @endif
                              </div>
                          </div>
                      </div>
                  @endforeach
              </div>

              <!-- Composer Area -->
              <div class="chat-composer">
                  <form action="{{ route('tickets.message.store', $ticket) }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 1rem; flex-direction: column;">
                      @csrf
                      <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                          <div style="flex-grow: 1;">
                              <textarea name="message" class="form-input @error('message') is-invalid @enderror" rows="2" placeholder="{{ $ticket->status->value === 'closed' ? 'Responder reabrirá la consulta...' : 'Escribe tu respuesta aquí...' }}" style="resize: none; background-color: var(--clr-bg-card, #fff); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);" required>{{ old('message') }}</textarea>
                          </div>
                          
                          <div class="file-upload-wrapper" style="position: relative; padding: 0; width: 50px; height: 50px; flex-shrink: 0; border-color: var(--clr-border-light, #d1d5db); background: var(--clr-bg-card, #fff); color: var(--clr-text-muted, #6b7280);" title="Adjuntar archivo">
                              <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10;">
                              <i class='bx bx-paperclip' style="font-size: 1.5rem; position: relative; z-index: 1; pointer-events: none;"></i>
                          </div>

                          <button type="submit" class="btn-primary" style="height: 50px; padding: 0 1.5rem; flex-shrink: 0;">
                              <i class='bx bx-send'></i> Enviar
                          </button>
                      </div>
                      
                      @error('message')
                          <div style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</div>
                      @enderror
                      @error('attachment')
                          <div style="color: #dc2626; font-size: 0.85rem;">{{ $message }}</div>
                      @enderror
                  </form>
              </div>

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

        // Auto-scroll al fondo del chat
        const chatContainer = document.getElementById('chat-container');
        if(chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

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
    }); // End DOMContentLoaded
  </script>
@endpush
