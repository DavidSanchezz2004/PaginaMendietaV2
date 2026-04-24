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

  <style>
    /* Modal Aviso Legal - Coherente con Mendieta */
    .legal-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
    }

    .legal-modal-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(5px);
      cursor: pointer;
    }

    .legal-modal-content {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      width: 90%;
      max-width: 550px;
      max-height: 80vh;
      display: flex;
      flex-direction: column;
      animation: modalSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      margin: 1rem;
    }

    @keyframes modalSlideIn {
      from {
        transform: translate(-50%, -50%) scale(0.95);
      }
      to {
        transform: translate(-50%, -50%) scale(1);
      }
    }

    .legal-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem 2rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .legal-modal-header h2 {
      color: var(--color-primary);
      font-weight: 700;
      margin: 0;
      font-size: 1.25rem;
    }

    .legal-modal-close {
      background: none;
      border: none;
      font-size: 1.8rem;
      color: #6b7280;
      cursor: pointer;
      padding: 0;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.2s;
      line-height: 1;
    }

    .legal-modal-close:hover {
      color: var(--color-text-dark);
    }

    .legal-modal-body {
      padding: 2rem;
      overflow-y: auto;
      line-height: 1.5;
      color: var(--color-text-dark);
      flex: 1;
    }

    .legal-modal-body h5 {
      color: var(--color-primary);
      font-weight: 700;
      margin-top: 1.5rem;
      margin-bottom: 0.8rem;
      font-size: 1rem;
    }

    .legal-modal-body h5:first-child {
      margin-top: 0;
    }

    .legal-modal-body p {
      margin-bottom: 0.8rem;
      font-size: 0.95rem;
    }

    .legal-modal-body ul {
      margin-left: 1.5rem;
      margin-bottom: 1rem;
      padding-left: 0;
    }

    .legal-modal-body ul li {
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }

    .legal-modal-footer {
      padding: 1.25rem 2rem;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      background-color: #fafbfc;
      display: flex;
      justify-content: flex-end;
    }

    .legal-modal-btn-close {
      background-color: var(--color-primary);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 4px;
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .legal-modal-btn-close:hover {
      background-color: var(--color-primary-light);
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(10, 27, 20, 0.15);
    }

    /* Scroll personalizado */
    .legal-modal-body::-webkit-scrollbar {
      width: 8px;
    }

    .legal-modal-body::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.05);
    }

    .legal-modal-body::-webkit-scrollbar-thumb {
      background: var(--color-border);
      border-radius: 4px;
    }

    .legal-modal-body::-webkit-scrollbar-thumb:hover {
      background: var(--color-primary);
    }

    #legalNoticeLink {
      cursor: pointer;
      transition: color 0.2s;
    }

    #legalNoticeLink:hover {
      color: var(--color-primary);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .legal-modal-content {
        width: 95%;
        max-height: 90vh;
      }

      .legal-modal-header h2 {
        font-size: 1.1rem;
      }

      .legal-modal-header {
        padding: 1.2rem 1.5rem;
      }

      .legal-modal-body {
        padding: 1.5rem;
      }

      .legal-modal-footer {
        padding: 1rem 1.5rem;
      }
    }
  </style>
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
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tu-correo@gmail.com" required>
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
            <a href="#" id="legalNoticeLink">Aviso Legal</a>.
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

  <!-- Modal Aviso Legal (Custom) -->
  <div id="legalNoticeModal" class="legal-modal" style="display: none;">
    <div class="legal-modal-overlay" id="legalModalOverlay"></div>
    <div class="legal-modal-content">
      <div class="legal-modal-header">
        <h2 id="legalNoticeLabel">Aviso Legal — Portal Mendieta</h2>
        <button type="button" class="legal-modal-close" id="legalModalClose">&times;</button>
      </div>
      <div class="legal-modal-body">
        <h5>Identificación del sistema</h5>
        <p>Portal Mendieta es una plataforma digital utilizada para la gestión contable, tributaria y administrativa de empresas y contribuyentes en el Perú.</p>
        <p>El sistema es operado por Estudio Contable Mendieta y está destinado exclusivamente para el uso de clientes autorizados y personal interno.</p>

        <h5>Acceso al sistema</h5>
        <p>El acceso a esta plataforma está restringido únicamente a usuarios registrados y autorizados.</p>
        <p>Cada usuario es responsable de mantener la confidencialidad de sus credenciales de acceso, incluyendo su correo electrónico y contraseña.</p>
        <p>El uso de credenciales por terceros sin autorización está estrictamente prohibido.</p>

        <h5>Uso adecuado de la plataforma</h5>
        <p>El usuario se compromete a utilizar la plataforma únicamente para fines relacionados con la gestión contable, administrativa o tributaria de su empresa.</p>
        <p><strong>Queda prohibido:</strong></p>
        <ul>
          <li>Compartir credenciales con terceros.</li>
          <li>Intentar acceder a información de otros usuarios o empresas.</li>
          <li>Manipular o alterar información del sistema sin autorización.</li>
          <li>Utilizar la plataforma para fines ilícitos.</li>
        </ul>

        <h5>Protección de la información</h5>
        <p>Portal Mendieta implementa medidas de seguridad destinadas a proteger la información contable, financiera y tributaria almacenada en el sistema.</p>
        <p>La información registrada en la plataforma será utilizada exclusivamente para fines relacionados con los servicios contables y administrativos prestados por el Estudio Contable Mendieta.</p>

        <h5>Limitación de responsabilidad</h5>
        <p>El Estudio Contable Mendieta no se responsabiliza por:</p>
        <ul>
          <li>El uso indebido del sistema por parte de los usuarios.</li>
          <li>El acceso no autorizado ocasionado por negligencia en el manejo de credenciales.</li>
          <li>Interrupciones temporales del sistema por mantenimiento, actualizaciones o causas técnicas.</li>
        </ul>

        <h5>Legislación aplicable</h5>
        <p>El uso de esta plataforma se rige por la legislación vigente de la República del Perú.</p>

        <h5>Contacto</h5>
        <p>Para consultas relacionadas con el acceso o uso del sistema, puede comunicarse con el Estudio Contable Mendieta a través de los canales oficiales de atención.</p>
      </div>
      <div class="legal-modal-footer">
        <button type="button" class="legal-modal-btn-close" id="legalModalBtnClose">Cerrar</button>
      </div>
    </div>
  </div>
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

    // Modal Aviso Legal
    const legalNoticeLink = document.getElementById('legalNoticeLink');
    const legalNoticeModal = document.getElementById('legalNoticeModal');
    const legalModalOverlay = document.getElementById('legalModalOverlay');
    const legalModalClose = document.getElementById('legalModalClose');
    const legalModalBtnClose = document.getElementById('legalModalBtnClose');

    function openLegalModal(e) {
      e.preventDefault();
      legalNoticeModal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function closeLegalModal() {
      legalNoticeModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    if (legalNoticeLink) {
      legalNoticeLink.addEventListener('click', openLegalModal);
    }

    if (legalModalClose) {
      legalModalClose.addEventListener('click', closeLegalModal);
    }

    if (legalModalBtnClose) {
      legalModalBtnClose.addEventListener('click', closeLegalModal);
    }

    if (legalModalOverlay) {
      legalModalOverlay.addEventListener('click', closeLegalModal);
    }

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' && legalNoticeModal && legalNoticeModal.style.display === 'block') {
        closeLegalModal();
      }
    });
  </script>
@endpush