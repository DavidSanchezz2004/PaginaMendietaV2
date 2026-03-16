<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @php
    $metaTitle = trim($__env->yieldContent('title', 'Portal Mendieta | Estudio Contable'));
    $metaDescription = trim($__env->yieldContent('meta_description', 'Plataforma integral para gestión contable, tributaria y financiera. Accede a tus empresas, comprobantes y obligaciones en un solo lugar.'));
    $metaImage = trim($__env->yieldContent('meta_image', asset('images/logoMendieta.png')));
    $metaUrl = url()->current();
  @endphp

  <title>@yield('title', 'Estudio Contable Mendieta')</title>

  <meta name="description" content="{{ $metaDescription }}">
  <meta name="robots" content="index,follow">

  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_PE">
  <meta property="og:site_name" content="Portal Mendieta">
  <meta property="og:title" content="{{ $metaTitle }}">
  <meta property="og:description" content="{{ $metaDescription }}">
  <meta property="og:url" content="{{ $metaUrl }}">
  <meta property="og:image" content="{{ $metaImage }}">

  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="{{ $metaTitle }}">
  <meta name="twitter:description" content="{{ $metaDescription }}">
  <meta name="twitter:image" content="{{ $metaImage }}">

  <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon-96x96.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">

  {{-- Fuentes globales --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- CSS por vista --}}
  @stack('styles')

  @push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
@endpush
</head>
<body>
  <script>
    // Previene el destello blanco (FOUC) aplicando el tema oscuro inmediatamentente
    if (localStorage.getItem('mendieta-theme') === 'dark') {
      document.body.classList.add('dark-mode');
    }
  </script>

  @yield('content')

  {{-- Lib global (icons) --}}
  <script src="https://unpkg.com/lucide@latest"></script>

  <script>
    // Inicializar Lucide
    if (window.lucide) lucide.createIcons();

    // Clase base para el body
    document.body.classList.add('mendieta-admin');

    // Toggle de Submenús en Sidebar
    document.addEventListener('click', (event) => {
      const toggleBtn = event.target.closest('.toggle-submenu');
      if (toggleBtn) {
        event.preventDefault();
        const navItem = toggleBtn.closest('.nav-item');
        if (navItem) {
          navItem.classList.toggle('open');
          const icon = toggleBtn.querySelector('.chevron');
          if (icon) {
            icon.classList.toggle('bx-chevron-down', !navItem.classList.contains('open'));
            icon.classList.toggle('bx-chevron-up', navItem.classList.contains('open'));
          }
        }
      }
    });

    // Modo Oscuro / Tema
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    
    const applyTheme = (theme) => {
      const isDark = theme === 'dark';
      document.body.classList.toggle('dark-mode', isDark);
      if (themeIcon) {
        themeIcon.classList.toggle('bx-moon', !isDark);
        themeIcon.classList.toggle('bx-sun', isDark);
      }
    };

    const savedTheme = localStorage.getItem('mendieta-theme') || 'light';
    applyTheme(savedTheme);

    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        localStorage.setItem('mendieta-theme', nextTheme);
        applyTheme(nextTheme);
      });
    }

    // Dropdown de Perfil
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    if (profileBtn && profileDropdown) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
      });
      document.addEventListener('click', (event) => {
        const container = document.getElementById('profile-container');
        if (container && !container.contains(event.target)) {
          profileDropdown.classList.remove('show');
        }
      });
    }
  </script>

  {{-- JS por vista --}}
  @stack('scripts')
</body>
</html>