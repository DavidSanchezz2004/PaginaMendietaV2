{{--
  Modal de Exportación Excel
  Uso: @include('facturador.invoices.partials.export-modal')
  Requiere: rutas JS en window.AccountingRoutes
--}}

<div id="export-modal-overlay"
     style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,.55); backdrop-filter:blur(3px);"
     aria-modal="true" role="dialog" aria-labelledby="em-title">

  <div id="export-modal"
       style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:min(440px,94vw); border-radius:16px; background:var(--clr-bg-card,#fff); box-shadow:0 20px 60px rgba(0,0,0,.25); overflow:hidden;">

    {{-- Header --}}
    <div style="padding:1.25rem 1.5rem 1rem; border-bottom:1px solid var(--clr-border-light,#e5e7eb); display:flex; justify-content:space-between; align-items:center;">
      <h2 id="em-title" style="margin:0; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:.5rem;">
        <i class='bx bx-file-export' style="color:var(--clr-active-bg,#1a6b57);"></i>
        Exportar a Excel
      </h2>
      <button type="button" id="em-close" aria-label="Cerrar"
              style="background:none; border:none; cursor:pointer; font-size:1.4rem; color:var(--clr-text-muted,#6b7280); line-height:1; padding:.25rem;">
        <i class='bx bx-x'></i>
      </button>
    </div>

    {{-- Body --}}
    <form id="em-form" method="GET" action="{{ route('facturador.invoices.export-excel') }}" style="padding:1.35rem 1.5rem; display:flex; flex-direction:column; gap:1.1rem;">

      <p style="margin:0; font-size:.88rem; color:var(--clr-text-muted,#6b7280);">
        Se exportarán solo los comprobantes en estado <strong>Listo</strong> dentro del período seleccionado.
      </p>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
        <div>
          <label style="display:block; font-size:.78rem; font-weight:600; color:var(--clr-text-muted,#6b7280); margin-bottom:.3rem;" for="em-from">
            Desde
          </label>
          <input type="date" id="em-from" name="from" required
                 class="am-input"
                 value="{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div>
          <label style="display:block; font-size:.78rem; font-weight:600; color:var(--clr-text-muted,#6b7280); margin-bottom:.3rem;" for="em-to">
            Hasta
          </label>
          <input type="date" id="em-to" name="to" required
                 class="am-input"
                 value="{{ now()->format('Y-m-d') }}">
        </div>
      </div>

      {{-- Counter --}}
      <div id="em-counter-wrap"
           style="padding:.7rem 1rem; background:rgba(16,185,129,.07); border:1px solid rgba(16,185,129,.2); border-radius:8px; font-size:.85rem; display:flex; align-items:center; gap:.6rem;">
        <i class='bx bx-check-circle' style="color:#059669; font-size:1.1rem;"></i>
        <span>
          Comprobantes listos en este período:
          <strong id="em-ready-count" style="color:var(--clr-active-bg,#1a6b57); font-size:1rem;">—</strong>
        </span>
      </div>

      <div id="em-no-records"
           style="display:none; padding:.65rem 1rem; background:rgba(245,158,11,.07); border:1px solid rgba(245,158,11,.25); border-radius:8px; font-size:.83rem; color:#92400e;">
        <i class='bx bx-info-circle' style="vertical-align:middle;"></i>
        No hay comprobantes listos en este período.
      </div>

      {{-- Hojas que incluye --}}
      <div style="font-size:.78rem; color:var(--clr-text-muted,#9ca3af);">
        <strong style="color:var(--clr-text-main,#374151);">El archivo incluirá:</strong>
        <div style="display:flex; gap:.75rem; margin-top:.35rem; flex-wrap:wrap;">
          <span style="background:rgba(26,107,87,.1); color:var(--clr-active-bg,#1a6b57); padding:.2rem .6rem; border-radius:12px; font-weight:600; font-size:.75rem;">📄 VENTAS</span>
          <span style="background:rgba(30,58,95,.08); color:#1e3a5f; padding:.2rem .6rem; border-radius:12px; font-weight:600; font-size:.75rem;">📄 COMPRAS (encabezados)</span>
        </div>
      </div>

      {{-- Footer --}}
      <div style="display:flex; justify-content:flex-end; gap:.75rem; padding-top:.25rem; border-top:1px solid var(--clr-border-light,#e5e7eb);">
        <button type="button" id="em-cancel"
                style="padding:.6rem 1.4rem; border:1px solid var(--clr-border-light,#e5e7eb); background:transparent; border-radius:8px; cursor:pointer; font-size:.88rem; color:var(--clr-text-main,#374151); font-weight:500;">
          Cancelar
        </button>
        <button type="submit" id="em-submit"
                style="padding:.6rem 1.6rem; background:var(--clr-active-bg,#1a6b57); color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.88rem; font-weight:600; display:flex; align-items:center; gap:.4rem;">
          <i class='bx bx-download'></i> Exportar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  'use strict';

  const overlay   = document.getElementById('export-modal-overlay');
  const closeBtn  = document.getElementById('em-close');
  const cancelBtn = document.getElementById('em-cancel');
  const fromInput = document.getElementById('em-from');
  const toInput   = document.getElementById('em-to');
  const countEl   = document.getElementById('em-ready-count');
  const noRec     = document.getElementById('em-no-records');
  const submitBtn = document.getElementById('em-submit');
  const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  function getRoutes() { return window.AccountingRoutes ?? {}; }

  function closeExport() {
    overlay.style.display = 'none';
  }

  closeBtn?.addEventListener('click', closeExport);
  cancelBtn?.addEventListener('click', closeExport);
  overlay?.addEventListener('click', function(e) {
    if (e.target === overlay) closeExport();
  });

  document.getElementById('btn-export-excel')?.addEventListener('click', function() {
    overlay.style.display = 'block';
    fetchCount();
  });

  function fetchCount() {
    const ROUTES = getRoutes();
    if (!ROUTES.exportCount || !fromInput.value || !toInput.value) return;
    countEl.textContent = '...';
    noRec.style.display = 'none';
    submitBtn.disabled = false;

    fetch(ROUTES.exportCount + '?from=' + fromInput.value + '&to=' + toInput.value, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
      countEl.textContent = data.count ?? 0;
      if ((data.count ?? 0) === 0) {
        noRec.style.display = 'block';
        submitBtn.disabled = true;
      }
    })
    .catch(() => { countEl.textContent = '—'; });
  }

  fromInput?.addEventListener('change', fetchCount);
  toInput?.addEventListener('change', fetchCount);

  // Evitar submit si count = 0
  document.getElementById('em-form')?.addEventListener('submit', function(e) {
    if (parseInt(countEl.textContent, 10) === 0) {
      e.preventDefault();
    }
  });
})();
</script>
