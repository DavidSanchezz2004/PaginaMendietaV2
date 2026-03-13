{{-- Modal SUNAT / Declaración y Pago ───────────────────────────────────── --}}
{{-- Se dispara con data-sunat-url + data-sunat-nombre en cualquier botón.    --}}
{{-- Soporta data-sunat-portal="sunat|declaracion"                            --}}

<div id="sunat-modal" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">

  <div style="
      background:#fff; border-radius:16px; width:92vw; max-width:460px;
      display:flex; flex-direction:column; overflow:hidden;
      box-shadow:0 24px 64px rgba(0,0,0,0.3);">

    {{-- Header (color cambia según portal) --}}
    <div id="sunat-modal-header" style="display:flex; align-items:center; justify-content:space-between;
                padding:14px 20px; background:#1a3a6b; color:#fff;">
      <div style="display:flex; align-items:center; gap:10px;">
        <i id="sunat-modal-icon" class='bx bx-shield-quarter' style="font-size:1.4rem;"></i>
        <span style="font-weight:700; font-size:1rem;" id="sunat-modal-title">Portal SUNAT SOL</span>
      </div>
      <button onclick="cerrarSUNAT()" title="Cerrar" style="
          background:rgba(255,255,255,0.15); border:none; color:#fff;
          width:32px; height:32px; border-radius:8px; cursor:pointer;
          font-size:1.1rem; display:flex; align-items:center; justify-content:center;">✕</button>
    </div>

    {{-- Estado: Cargando --}}
    <div id="sunat-loading" style="display:flex; flex-direction:column;
         align-items:center; justify-content:center; gap:16px; padding:40px; background:#f8fafc;">
      <div id="sunat-spinner" style="width:48px; height:48px; border:4px solid #e2e8f0;
           border-top-color:#1a3a6b; border-radius:50%;
           animation:sunat-spin 0.8s linear infinite;"></div>
      <p id="sunat-loading-title" style="color:#1a3a6b; font-weight:700; font-size:1rem; margin:0;">
        Iniciando sesión en SUNAT...
      </p>
      <p style="color:#64748b; font-size:.85rem; margin:0;" id="sunat-loading-msg">
        Verificando credenciales SOL…
      </p>
    </div>

    {{-- Estado: Error --}}
    <div id="sunat-error" style="display:none; flex-direction:column;
         align-items:center; justify-content:center; gap:12px; padding:40px; background:#f8fafc;">
      <i class='bx bx-error-circle' style="font-size:3rem; color:#dc2626;"></i>
      <p id="sunat-error-title" style="color:#dc2626; font-weight:700; margin:0; font-size:1rem;">
        Error al conectar
      </p>
      <p style="color:#64748b; font-size:.85rem; margin:0; max-width:340px; text-align:center;"
         id="sunat-error-msg"></p>
      <button onclick="cerrarSUNAT()" style="
          margin-top:8px; padding:8px 20px; background:#1a3a6b;
          color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Cerrar</button>
    </div>

  </div>
</div>

<style>
  @keyframes sunat-spin { to { transform: rotate(360deg); } }
</style>

<script>
  // ── Configuración por portal ─────────────────────────────────────────────
  const PORTAL_CONFIG = {
    sunat: {
      label:       'Menú SOL',
      headerColor: '#1a3a6b',
      spinColor:   '#1a3a6b',
      icon:        'bx-shield-quarter',
      loadingText: 'Iniciando sesión en SUNAT...',
    },
    declaracion: {
      label:       'Declaración y Pago',
      headerColor: '#14532d',
      spinColor:   '#15803d',
      icon:        'bx-receipt',
      loadingText: 'Iniciando sesión en Declaración y Pago...',
    },
  };

  // ── Click handler ────────────────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-sunat-url]');
    if (btn) abrirSUNAT(
      btn.dataset.sunatUrl,
      btn.dataset.sunatNombre,
      btn.dataset.sunatPortal || 'sunat',
      btn.dataset.sunatRuc || '',
      btn.dataset.sunatUsuario || '',
      btn.dataset.sunatClave || ''
    );
  });

  async function abrirSUNAT(abrirUrl, razonSocial, portal, ruc, usuarioSol, claveSol) {
    const cfg = PORTAL_CONFIG[portal] || PORTAL_CONFIG.sunat;

    const modal    = document.getElementById('sunat-modal');
    const loading  = document.getElementById('sunat-loading');
    const errorBox = document.getElementById('sunat-error');
    const header   = document.getElementById('sunat-modal-header');
    const icon     = document.getElementById('sunat-modal-icon');
    const spinner  = document.getElementById('sunat-spinner');

    // Abrir popup vacío primero (evita bloqueo de popup blocker)
    const popup = window.open('', '_blank');

    // Caso especial: Declaración y Pago → sol.html + extensión.
    if (portal === 'declaracion') {
      if (!ruc || !usuarioSol || !claveSol) {
        if (popup) popup.close();
        errorBox.style.display = 'flex';
        loading.style.display  = 'none';
        document.getElementById('sunat-error-title').textContent =
          'Faltan credenciales SOL';
        document.getElementById('sunat-error-msg').textContent =
          'Configura usuario y clave SOL antes de abrir Declaración y Pago.';
        return;
      }

      // Empaquetar credenciales en el hash para que la extensión las recoja en sol.html
      const payload = btoa(JSON.stringify({
        ruc: String(ruc || '').trim(),
        usuario: String(usuarioSol || '').trim(),
        clave: String(claveSol || ''),
      }));

      const solUrl = 'https://www.sunat.gob.pe/sol.html#mdp=' + encodeURIComponent(payload);
      if (popup) popup.location.href = solUrl;
      modal.style.display = 'none';
      return;
    }

    // Aplicar estilo del portal al header y spinner
    header.style.background        = cfg.headerColor;
    spinner.style.borderTopColor   = cfg.spinColor;
    icon.className                 = `bx ${cfg.icon}`;

    // Textos
    document.getElementById('sunat-modal-title').textContent =
      razonSocial ? `${cfg.label} — ${razonSocial}` : cfg.label;
    document.getElementById('sunat-loading-title').textContent = cfg.loadingText;
    document.getElementById('sunat-loading-msg').textContent   = 'Verificando credenciales SOL…';

    // Mostrar modal
    modal.style.display    = 'flex';
    loading.style.display  = 'flex';
    errorBox.style.display = 'none';

    try {
      const res  = await fetch(abrirUrl, { headers: { 'Accept': 'application/json' } });
      const data = await res.json();

      if (!data.ok) {
        popup && popup.close();
        loading.style.display  = 'none';
        errorBox.style.display = 'flex';
        document.getElementById('sunat-error-title').textContent =
          `Error al conectar con ${cfg.label}`;
        document.getElementById('sunat-error-msg').textContent =
          data.error || 'Error desconocido.';
        return;
      }

      if (popup) popup.location.href = data.url;
      modal.style.display = 'none';

    } catch (err) {
      popup && popup.close();
      loading.style.display  = 'none';
      errorBox.style.display = 'flex';
      document.getElementById('sunat-error-title').textContent =
        `Error al conectar con ${cfg.label}`;
      document.getElementById('sunat-error-msg').textContent = err.message;
    }
  }

  function cerrarSUNAT() {
    document.getElementById('sunat-modal').style.display = 'none';
  }

  document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarSUNAT(); });
  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('sunat-modal');
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) cerrarSUNAT(); });
  });
</script>