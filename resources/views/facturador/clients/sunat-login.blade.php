{{--
    Esta vista ya no se usa. El flujo fue reemplazado por sunat-autosubmit.blade.php
    (formulario autosubmit directo a SUNAT, sin microservicio bot_cookies).
    Se mantiene este archivo solo como referencia histórica.
--}}
@extends('layouts.app')

@section('title', 'Iniciando sesión en SUNAT...')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .sunat-loading-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 60vh;
      text-align: center;
      gap: 1.5rem;
      padding: 2rem;
    }

    .sunat-logo-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .75rem;
      font-size: 1.4rem;
      font-weight: 700;
      color: #1a3a6b;
    }

    .sunat-logo-badge img {
      height: 48px;
      object-fit: contain;
    }

    /* Spinner */
    .sunat-spinner {
      width: 64px;
      height: 64px;
      border: 6px solid #e5e7eb;
      border-top-color: #1a3a6b;
      border-radius: 50%;
      animation: sunat-spin .9s linear infinite;
    }

    @keyframes sunat-spin {
      to { transform: rotate(360deg); }
    }

    .sunat-loading-title {
      font-size: 1.35rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0;
    }

    .sunat-loading-subtitle {
      font-size: .95rem;
      color: #64748b;
      margin: 0;
      max-width: 380px;
    }

    .sunat-steps {
      display: flex;
      flex-direction: column;
      gap: .5rem;
      font-size: .85rem;
      color: #6b7280;
    }

    .sunat-step {
      display: flex;
      align-items: center;
      gap: .5rem;
      transition: color .3s;
    }

    .sunat-step.is-done   { color: #16a34a; font-weight: 600; }
    .sunat-step.is-active { color: #1a3a6b; font-weight: 700; }
    .sunat-step.is-done .step-icon   { color: #16a34a; }
    .sunat-step.is-active .step-icon { color: #1a3a6b; }

    .sunat-error-box {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      border-radius: .75rem;
      padding: 1rem 1.5rem;
      color: #991b1b;
      font-size: .9rem;
      max-width: 420px;
      display: none;
    }

    .sunat-error-box strong { display: block; margin-bottom: .25rem; }

    .sunat-back-btn {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      padding: .5rem 1.1rem;
      border-radius: .5rem;
      background: #f1f5f9;
      color: #1e293b;
      font-size: .9rem;
      font-weight: 600;
      text-decoration: none;
      border: 1px solid #e2e8f0;
      cursor: pointer;
      display: none;
    }

    .sunat-back-btn:hover { background: #e2e8f0; }
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
        'userName'    => auth()->user()?->name,
        'userEmail'   => auth()->user()?->email,
      ])

      <main class="main-content">
        <div class="module-content-stack">
          <div class="placeholder-content module-card-wide">

            <div class="sunat-loading-wrap" id="sunat-loading-wrap">

              {{-- Logo / Badge --}}
              <div class="sunat-logo-badge">
                <i class='bx bx-shield-quarter' style="font-size:2rem;"></i>
                Portal SUNAT SOL
              </div>

              {{-- Spinner --}}
              <div class="sunat-spinner" id="sunat-spinner"></div>

              {{-- Título y subtítulo --}}
              <p class="sunat-loading-title">Iniciando sesión en SUNAT...</p>
              <p class="sunat-loading-subtitle">
                Por favor espere. Será redirigido automáticamente al portal de SUNAT.<br>
                <small style="color:#94a3b8;">Cliente: {{ $client->nombre_razon_social }}</small>
              </p>

              {{-- Pasos --}}
              <div class="sunat-steps">
                <span class="sunat-step is-active" id="step-1">
                  <i class='bx bx-loader-circle step-icon'></i> Conectando con el servicio de autenticación
                </span>
                <span class="sunat-step" id="step-2">
                  <i class='bx bx-log-in step-icon'></i> Verificando credenciales con SUNAT
                </span>
                <span class="sunat-step" id="step-3">
                  <i class='bx bx-cookie step-icon'></i> Aplicando sesión al navegador
                </span>
                <span class="sunat-step" id="step-4">
                  <i class='bx bx-link-external step-icon'></i> Redirigiendo al portal de SUNAT
                </span>
              </div>

              {{-- Error box --}}
              <div class="sunat-error-box" id="sunat-error-box">
                <strong id="sunat-error-title">Error</strong>
                <span id="sunat-error-detail"></span>
              </div>

              {{-- Botón volver (visible solo en error) --}}
              <a href="{{ route('facturador.clients.index') }}" class="sunat-back-btn" id="sunat-back-btn">
                <i class='bx bx-arrow-back'></i> Volver al listado de clientes
              </a>

            </div>

          </div>
        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
<script>
(function () {
  'use strict';

  const SESSION_URL  = @json(route('facturador.clients.sunat-session', $client));
  const CSRF_TOKEN   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  // ── Helpers de UI ─────────────────────────────────────────────────────────
  function setStep(stepId, state) {
    const el = document.getElementById(stepId);
    if (!el) return;
    el.classList.remove('is-active', 'is-done');
    if (state) el.classList.add(state);
  }

  function showError(title, detail) {
    document.getElementById('sunat-spinner').style.opacity = '0.2';
    const box = document.getElementById('sunat-error-box');
    document.getElementById('sunat-error-title').textContent  = title;
    document.getElementById('sunat-error-detail').textContent = detail;
    box.style.display = 'block';
    document.getElementById('sunat-back-btn').style.display = 'inline-flex';
  }

  // ── Inyectar cookies no-httpOnly vía JS ───────────────────────────────────
  // NOTA: las cookies httpOnly NO pueden ser inyectadas por JavaScript (limitación
  // de seguridad del navegador). Para esas cookies la redirección al portal de
  // SUNAT usará la URL de retorno que el microservicio ya autenticó.
  function setCookieJs(cookie) {
    if (cookie.httpOnly) return; // no se puede inyectar por JS
    let str = `${encodeURIComponent(cookie.name)}=${encodeURIComponent(cookie.value)}`;
    str += `; path=${cookie.path || '/'}`;
    if (cookie.expires) str += `; expires=${new Date(cookie.expires * 1000).toUTCString()}`;
    if (cookie.secure  && location.protocol === 'https:') str += '; secure';
    if (cookie.sameSite) str += `; samesite=${cookie.sameSite}`;
    // domain: solo podemos fijar el dominio actual y sus subdominios
    // (cross-domain via JS no es posible por seguridad del navegador)
    try { document.cookie = str; } catch (_) {}
  }

  // ── Flujo principal ────────────────────────────────────────────────────────
  async function initSunatLogin() {
    // Paso 1: conectar
    setStep('step-1', 'is-active');

    let data;
    try {
      const resp = await fetch(SESSION_URL, {
        method:  'POST',
        headers: {
          'Content-Type':  'application/json',
          'Accept':        'application/json',
          'X-CSRF-TOKEN':  CSRF_TOKEN,
        },
      });
      data = await resp.json();
    } catch (err) {
      setStep('step-1', null);
      showError(
        'Error de conexión',
        'No se pudo conectar con el servidor. Verifique su conexión e intente nuevamente.'
      );
      return;
    }

    setStep('step-1', 'is-done');

    if (!data.ok) {
      const mensajes = {
        credenciales_invalidas : 'El usuario SOL o la clave SOL son incorrectos.',
        sin_credenciales       : 'El cliente no tiene credenciales SOL configuradas.',
        servicio_no_disponible : 'El servicio de autenticación no está disponible en este momento.',
      };
      showError(
        'No se pudo iniciar sesión en SUNAT',
        mensajes[data.error] ?? (data.detalle || 'Error desconocido.')
      );
      return;
    }

    // Paso 2: credenciales OK
    setStep('step-2', 'is-done');

    // Paso 3: aplicar cookies no-httpOnly
    setStep('step-3', 'is-active');
    (data.cookies || []).forEach(setCookieJs);
    setStep('step-3', 'is-done');

    // Paso 4: redirigir
    setStep('step-4', 'is-active');
    await new Promise(r => setTimeout(r, 400)); // breve pausa visual

    // Abrir en esta misma ventana/pestaña (fue abierta con target="_blank" desde el botón)
    window.location.href = data.redirect_url;
  }

  // Arrancar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSunatLogin);
  } else {
    initSunatLogin();
  }
})();
</script>
@endpush
