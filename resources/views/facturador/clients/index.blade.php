@extends('layouts.app')

@section('title', 'Clientes — Facturador | Portal Mendieta')

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
        'userName'    => auth()->user()?->name,
        'userEmail'   => auth()->user()?->email,
      ])

      <main class="main-content">
        <div class="module-content-stack">

          @foreach(['status' => null, 'success' => null, 'error' => 'module-alert--error'] as $flashKey => $flashClass)
            @if(session($flashKey))
              <div class="placeholder-content module-alert module-flash {{ $flashClass }}" data-flash-message>
                <p>{{ session($flashKey) }}</p>
                <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
              </div>
            @endif
          @endforeach

          <div class="placeholder-content module-card-wide">
            <div class="module-toolbar">
              <h1>Catálogo de Clientes</h1>
              <div style="display:flex; gap:.5rem; align-items:center;">
                <a href="{{ route('facturador.index') }}" class="btn-secondary" style="font-size:.85rem;">
                  <i class='bx bx-building'></i> Cambiar empresa
                </a>
                @can('create', \App\Models\Client::class)
                  <a href="{{ route('facturador.clients.create') }}" class="btn-primary">
                    <i class='bx bx-plus'></i> Nuevo Cliente
                  </a>
                @endcan
              </div>
            </div>

            <div class="module-table-wrap">
              <table class="module-table">
                <thead>
                  <tr>
                    <th>Tipo Doc.</th>
                    <th>N° Documento</th>
                    <th>Razón Social</th>
                    <th>País</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th class="cell-action">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($clients as $client)
                    <tr>
                      <td>{{ $client->codigo_tipo_documento }}</td>
                      <td>{{ $client->numero_documento }}</td>
                      <td>{{ $client->nombre_razon_social }}</td>
                      <td>{{ $client->pais }}</td>
                      <td>{{ $client->correo_electronico ?? '—' }}</td>
                      <td>
                        <span class="companies-status-pill {{ $client->activo ? 'is-active' : 'is-inactive' }}">
                          {{ $client->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                      </td>
                      <td class="cell-action">
                        <div class="action-wrapper">
                          @can('update', $client)
                            <a href="{{ route('facturador.clients.edit', $client) }}" class="btn-action-icon" title="Editar">
                              <i class='bx bx-pencil'></i>
                            </a>
                          @endcan
                          @if($client->codigo_tipo_documento === '6')
                            @can('update', $client)
                              <button
                                type="button"
                                class="btn-action-icon btn-sunat"
                                title="Abrir SUNAT autenticado"
                                data-sunat-url="{{ route('facturador.clients.abrir-sunat', $client) }}"
                                data-sunat-nombre="{{ $client->nombre_razon_social }}"
                                style="background:#1a3a6b; color:#fff; border-radius:.4rem; padding:.28rem .52rem; font-size:.78rem; font-weight:700; letter-spacing:.03em; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:.25rem; white-space:nowrap;">
                                <i class='bx bx-shield-quarter'></i> SUNAT
                              </button>
                            @endcan
                          @endif
                          @can('delete', $client)
                            <form method="POST" action="{{ route('facturador.clients.destroy', $client) }}" data-confirm-delete>
                              @csrf @method('DELETE')
                              <button type="submit" class="btn-action-icon" title="Eliminar">
                                <i class='bx bx-trash'></i>
                              </button>
                            </form>
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="7">No hay clientes registrados.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($clients->hasPages())
              <div style="margin-top:1rem;">{{ $clients->links() }}</div>
            @endif
          </div>

        </div>
      </main>
    </section>
  </div>

  {{-- ── Modal SUNAT (loading / error) ─────────────────────────────────── --}}
  <div id="sunat-modal" style="
      display:none; position:fixed; inset:0; z-index:9999;
      background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">

    <div style="
        background:#fff; border-radius:16px; width:92vw; max-width:460px;
        display:flex; flex-direction:column; overflow:hidden;
        box-shadow:0 24px 64px rgba(0,0,0,0.3);">

      {{-- Header --}}
      <div style="display:flex; align-items:center; justify-content:space-between;
                  padding:14px 20px; background:#1a3a6b; color:#fff;">
        <div style="display:flex; align-items:center; gap:10px;">
          <i class='bx bx-shield-quarter' style="font-size:1.4rem;"></i>
          <span style="font-weight:700; font-size:1rem;" id="sunat-modal-title">Portal SUNAT SOL</span>
        </div>
        <button onclick="cerrarSUNAT()" title="Cerrar" style="
            background:rgba(255,255,255,0.15); border:none; color:#fff;
            width:32px; height:32px; border-radius:8px; cursor:pointer;
            font-size:1.1rem; display:flex; align-items:center; justify-content:center;">
          ✕
        </button>
      </div>

      {{-- Estado: Cargando --}}
      <div id="sunat-loading" style="display:flex; flex-direction:column;
           align-items:center; justify-content:center; gap:16px; padding:40px; background:#f8fafc;">
        <div style="width:48px; height:48px; border:4px solid #e2e8f0;
             border-top-color:#1a3a6b; border-radius:50%;
             animation:sunat-spin 0.8s linear infinite;"></div>
        <p style="color:#1a3a6b; font-weight:700; font-size:1rem; margin:0;">Iniciando sesión en SUNAT...</p>
        <p style="color:#64748b; font-size:.85rem; margin:0;" id="sunat-loading-msg">Verificando credenciales SOL…</p>
      </div>

      {{-- Estado: Error --}}
      <div id="sunat-error" style="display:none; flex-direction:column;
           align-items:center; justify-content:center; gap:12px; padding:40px; background:#f8fafc;">
        <i class='bx bx-error-circle' style="font-size:3rem; color:#dc2626;"></i>
        <p style="color:#dc2626; font-weight:700; margin:0; font-size:1rem;">Error al conectar con SUNAT</p>
        <p style="color:#64748b; font-size:.85rem; margin:0; max-width:340px; text-align:center;" id="sunat-error-msg"></p>
        <button onclick="cerrarSUNAT()" style="
            margin-top:8px; padding:8px 20px; background:#1a3a6b;
            color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
          Cerrar
        </button>
      </div>

    </div>
  </div>

  <style>
    @keyframes sunat-spin { to { transform: rotate(360deg); } }
  </style>

@endsection

@push('scripts')
  <script>
    document.querySelectorAll('[data-confirm-delete]').forEach(f => {
      f.addEventListener('submit', e => { if (!confirm('¿Eliminar este cliente?')) e.preventDefault(); });
    });
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4000);
    });

    // ── SUNAT Modal ────────────────────────────────────────────────────────
    // Delegación de eventos: evita pasar valores con comillas en onclick inline
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-sunat-url]');
      if (btn) abrirSUNAT(btn.dataset.sunatUrl, btn.dataset.sunatNombre);
    });

    async function abrirSUNAT(abrirUrl, razonSocial) {
      const modal    = document.getElementById('sunat-modal');
      const loading  = document.getElementById('sunat-loading');
      const errorBox = document.getElementById('sunat-error');
      const loadMsg  = document.getElementById('sunat-loading-msg');

      // Mostrar spinner
      modal.style.display    = 'flex';
      loading.style.display  = 'flex';
      errorBox.style.display = 'none';
      document.getElementById('sunat-modal-title').textContent =
        razonSocial ? `SUNAT — ${razonSocial}` : 'Portal SUNAT SOL';
      loadMsg.textContent = 'Iniciando sesión en SUNAT…';

      function mostrarError(msg) {
        loading.style.display  = 'none';
        errorBox.style.display = 'flex';
        document.getElementById('sunat-error-msg').textContent = msg;
      }

      try {
        // Paso 1: POST /proxy/create → token inmediato (status: pending)
        const res  = await fetch(abrirUrl, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.ok) { mostrarError(data.error || 'Error desconocido.'); return; }

        // Paso 2: polling a /sunat-status/{token} cada 3 segundos (máx 90s)
        const statusUrl  = data.status_url;
        const maxMs      = 90_000;
        const intervalMs = 3_000;
        const started    = Date.now();
        let dots = 0;

        while (true) {
          if (Date.now() - started > maxMs) {
            mostrarError('SUNAT tardó demasiado. Intenta de nuevo.');
            return;
          }

          dots = (dots % 3) + 1;
          loadMsg.textContent = 'Verificando credenciales SOL' + '.'.repeat(dots);

          await new Promise(r => setTimeout(r, intervalMs));

          const sr   = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
          const stat = await sr.json();

          if (stat.status === 'ready') {
            modal.style.display = 'none';
            window.open(stat.ext_inject_url, '_blank');
            return;
          }

          if (stat.status === 'error') {
            mostrarError(stat.detalle || stat.error || 'El bot reportó un error.');
            return;
          }

          // status === 'pending' → seguir esperando
        }

      } catch (err) {
        mostrarError(err.message);
      }
    }

    function cerrarSUNAT() {
      document.getElementById('sunat-modal').style.display = 'none';
    }

    // Cerrar con Escape
    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarSUNAT(); });

    // Cerrar haciendo click en el fondo oscuro
    document.getElementById('sunat-modal').addEventListener('click', function (e) {
      if (e.target === this) cerrarSUNAT();
    });
  </script>
@endpush
