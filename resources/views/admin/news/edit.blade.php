@extends('layouts.app')

@section('title', 'Editar Noticia | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <style>
      .quill-wrapper { background: var(--clr-bg-card, #fff); border-radius: 0.5rem; border: 1px solid var(--clr-border-light, #d1d5db); overflow: hidden; }
      .ql-toolbar.ql-snow { border: none; border-bottom: 1px solid var(--clr-border-light, #d1d5db); background: var(--clr-bg-body, #f9fafb); padding: 0.75rem; transition: background-color 0.2s; }
      .ql-container.ql-snow { border: none; font-family: inherit; font-size: 1rem; min-height: 250px; color: var(--clr-text-main, #374151); }
      .ql-editor { padding: 1rem; }
      
      body.dark-mode .ql-snow .ql-stroke {
          stroke: var(--clr-text-main, #e5e7eb);
      }
      body.dark-mode .ql-snow .ql-fill, body.dark-mode .ql-snow .ql-stroke.ql-fill {
          fill: var(--clr-text-main, #e5e7eb);
      }
      body.dark-mode .ql-snow .ql-picker-label {
          color: var(--clr-text-main, #e5e7eb);
      }
      body.dark-mode .ql-snow .ql-picker-options {
          background-color: var(--clr-bg-card, #1f2937);
          border-color: var(--clr-border-light, #374151);
      }
      body.dark-mode .ql-snow.ql-toolbar button:hover .ql-stroke,
      body.dark-mode .ql-snow .ql-toolbar button:hover .ql-stroke,
      body.dark-mode .ql-snow.ql-toolbar button.ql-active .ql-stroke,
      body.dark-mode .ql-snow .ql-toolbar button.ql-active .ql-stroke {
          stroke: var(--clr-active-bg, #3b82f6);
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
                  <h1 class="page-title">Editar Noticia</h1>
                  <p class="page-description">Realizar modificaciones a la publicación seleccionada.</p>
              </div>
              <div class="header-actions">
                  <a href="{{ route('news.index') }}" class="btn-secondary">
                      <i class='bx bx-arrow-back'></i> Volver
                  </a>
              </div>
          </div>

          <div class="module-card">
              <form class="module-form form-grid is-editing" id="news-form" method="POST" action="{{ route('news.update', $news) }}" enctype="multipart/form-data">
                  @csrf
                  @method('PATCH')

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="title" class="form-label" style="color: var(--clr-text-main, #374151);">Título <span style="color:red">*</span></label>
                      <input type="text" class="form-input @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $news->title) }}" required autofocus style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                      @error('title')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="excerpt" class="form-label" style="color: var(--clr-text-main, #374151);">Extracto Breve</label>
                      <input type="text" class="form-input @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" value="{{ old('excerpt', $news->excerpt) }}" style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                      @error('excerpt')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label for="image" class="form-label" style="color: var(--clr-text-main, #374151);">Imagen de Portada (Dejar vacío para mantener la actual)</label>
                      @if($news->image_path)
                          <div style="margin-bottom: 0.5rem;">
                              <img src="{{ Storage::url($news->image_path) }}" alt="Portada actual" style="width: 150px; border-radius: 8px; border: 1px solid var(--clr-border-light, #e5e7eb);">
                          </div>
                      @endif
                      <input type="file" class="form-input @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                      @error('image')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group" style="grid-column: 1 / -1;">
                      <label class="form-label" style="color: var(--clr-text-main, #374151);">Contenido de la Noticia <span style="color:red">*</span></label>
                      <div class="quill-wrapper @error('content') is-invalid @enderror">
                          <div id="editor-container" style="min-height: 250px; color: var(--clr-text-main, #111827);">{!! old('content', $news->content) !!}</div>
                      </div>
                      <input type="hidden" name="content" id="content">
                      @error('content')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="form-group">
                      <label for="status" class="form-label" style="color: var(--clr-text-main, #374151);">Estado <span style="color:red">*</span></label>
                      <select class="form-input @error('status') is-invalid @enderror" id="status" name="status" required style="background-color: var(--clr-bg-body, #f9fafb); color: var(--clr-text-main, #111827); border-color: var(--clr-border-light, #e5e7eb);">
                          <option value="draft" {{ old('status', $news->published_at ? 'published' : 'draft') == 'draft' ? 'selected' : '' }}>Borrador (Oculto)</option>
                          <option value="published" {{ old('status', $news->published_at ? 'published' : 'draft') == 'published' ? 'selected' : '' }}>Publicado (Visible a todos)</option>
                      </select>
                      @error('status')
                          <div class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                      @enderror
                  </div>

                  <div class="module-actions" style="grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center;">
                      <div>
                          <button type="button" form="delete-form" class="btn-secondary" style="color: #dc2626; border-color: #fca5a5; background-color: #fef2f2;" onclick="Swal.fire({title:'¿Estás seguro?',text:'¿Deseas eliminar esta noticia?',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',cancelButtonText:'Cancelar',confirmButtonText:'Sí, eliminar'}).then(r=>{if(r.isConfirmed)document.getElementById('delete-form').submit()})">
                              <i class='bx bx-trash'></i> Eliminar
                          </button>
                      </div>
                      <div style="display: flex; gap: 1rem;">
                          <button type="button" class="btn-secondary" onclick="window.location='{{ route('news.index') }}'">Cancelar</button>
                          <button type="submit" class="btn-primary">
                              <i class='bx bx-save'></i> Guardar Cambios
                          </button>
                      </div>
                  </div>
              </form>

              <form id="delete-form" action="{{ route('news.destroy', $news) }}" method="POST" style="display: none;">
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
  <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
  <script>
    document.body.classList.add('mendieta-admin');

    // Sidebar & Theme init...
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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quill Initialization
        if (typeof Quill !== 'undefined') {
            var quill = new Quill('#editor-container', {
                theme: 'snow',
                placeholder: 'Escribe el contenido detallado de la noticia aquí...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['link', 'clean']
                    ]
                }
            });

            var form = document.querySelector('#news-form');
            if (form) {
                form.onsubmit = function() {
                    var contentInput = document.querySelector('input[name=content]');
                    contentInput.value = quill.root.innerHTML;
                    if(contentInput.value === '<p><br></p>') {
                        contentInput.value = '';
                    }
                };
            }
        } else {
            console.error("Quill JS failed to load.");
        }
    });
  </script>
@endpush
