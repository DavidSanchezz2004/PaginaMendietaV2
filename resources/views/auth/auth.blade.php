@extends('layouts.app')

@section('title', 'Login  | Mendieta Estudio Contable')
@section('meta_description', 'Acceso seguro al Portal Mendieta para clientes y equipo interno. Gestiona facturación, obligaciones y documentos tributarios en un solo panel.')

@push('styles')
  {{-- Fuente específica del login --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- Tu CSS del login (public/css/login.css) --}}
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@push('scripts')
@php $recaptchaSiteKey = config('services.recaptcha.site_key', ''); @endphp

@if ($recaptchaSiteKey)
  {{-- reCAPTCHA v3: carga la API con render=site_key para invocación programática --}}
  <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var form = document.getElementById('login-form');

      form.addEventListener('submit', function (e) {
        e.preventDefault();

        grecaptcha.ready(function () {
          grecaptcha
            .execute('{{ $recaptchaSiteKey }}', { action: 'login' })
            .then(function (token) {
              document.getElementById('recaptcha_token').value = token;
              form.submit();
            });
        });
      });
    });
  </script>
@endif
@endpush

@section('content')
  <main class="auth-layout">

    <!-- SECCIÓN IZQUIERDA: FORMULARIO PREMIUM -->
    <section class="auth-form-section">
      <div class="auth-form-container">

        <div class="logo-wrapper">
          {{-- Guarda el logo en public/images/logoMendieta.png --}}
          <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta - Estudio Contable" class="brand-logo-img">
        </div>

        <header class="auth-header">
          <h1>Acceso al Sistema</h1>
          <p>Gestión contable financiera, segura y corporativa.</p>
        </header>

        <form id="login-form" action="{{ route('login.store') }}" method="POST" class="luxury-form" autocomplete="on">
          @csrf
          {{-- Token generado por reCAPTCHA v3; se rellena vía JS antes del envío --}}
          <input type="hidden" id="recaptcha_token" name="recaptcha_token">

          @if ($errors->any())
            <div class="input-field" role="alert" aria-live="assertive">
              @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
              @endforeach
            </div>
          @endif

          <!-- Campo de E-mail -->
          <div class="input-field">
            <label for="email">E-mail Corporativo</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="david@gmail.com" required>
          </div>

          <!-- Campo de Contraseña con revelar integrado -->
          <div class="input-field">
            <label for="password">Contraseña Cifrada</label>
            <div class="password-wrapper">
              <input type="password" id="password" name="password" placeholder="••••••••" required>
              <button type="button" class="btn-toggle" id="togglePassword">Mostrar</button>
            </div>
          </div>

          <!-- Botón de acción principal -->
          <button type="submit" class="btn-submit">
            Ingresar a la Plataforma
            <span class="arrow-icon">→</span>
          </button>
        </form>

        <footer class="auth-footer">
          <p>
            Acceso restringido a personal y clientes autorizados.
            <a href="#">Aviso Legal</a>.
          </p>
        </footer>
      </div>
    </section>

    <!-- SECCIÓN DERECHA: MARCA ESTABLECIDA -->
    <section class="auth-brand-section">
      <div class="brand-watermark" aria-hidden="true">MENDIETA</div>

      <div class="brand-content-wrapper">
        <div class="accent-line"></div>
        <h2>Orden y claridad<br>en cada movimiento.</h2>
        <p>
          Una base sólida para decisiones que importan. Nos establecemos como tu aliado estratégico en toda la gestión operativa y contable del día a día.
        </p>

        <div class="brand-stats">
          <div class="stat-item">
            <strong>Precisión</strong>
            <span>Operativa Contable</span>
          </div>
          <div class="divider"></div>
          <div class="stat-item">
            <strong>Confianza</strong>
            <span>Soporte Financiero</span>
          </div>
        </div>
      </div>
    </section>

  </main>
@endsection

@push('scripts')
  <script>
    const btnToggle = document.getElementById('togglePassword');
    const inputPassword = document.getElementById('password');

    if (btnToggle && inputPassword) {
      btnToggle.addEventListener('click', () => {
        const isPass = inputPassword.type === 'password';
        inputPassword.type = isPass ? 'text' : 'password';
        btnToggle.textContent = isPass ? 'Ocultar' : 'Mostrar';

        btnToggle.style.color = 'var(--color-primary)';
        setTimeout(() => { btnToggle.style.color = ''; }, 200);
      });
    }
  </script>
@endpush