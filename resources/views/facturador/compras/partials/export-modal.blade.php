{{--
  Modal de Exportación Excel — COMPRAS
  Uso: @include('facturador.compras.partials.export-modal')
  Requiere: window.ComprasRoutes.exportCount y exportExcel
--}}

<div id="purchase-export-modal-overlay"
     style="display:none; position:fixed; inset:0; z-index:950; background:rgba(0,0,0,.55); backdrop-filter:blur(3px);"
     aria-modal="true" role="dialog">

  <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:min(440px,96vw); border-radius:16px; background:var(--clr-bg-card,#fff); box-shadow:0 20px 60px rgba(0,0,0,.25); padding:1.75rem;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
      <h2 style="margin:0; font-size:1.05rem; font-weight:700; display:flex; align-items:center; gap:.5rem;">
        <i class='bx bx-file-export' style="color:#059669;"></i> Exportar Libro de Compras
      </h2>
      <button type="button" id="purchase-export-close"
              style="background:none; border:none; cursor:pointer; font-size:1.4rem; color:var(--clr-text-muted,#6b7280); line-height:1; padding:.25rem;">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <p style="font-size:.88rem; color:var(--clr-text-muted,#6b7280); margin:0 0 1.25rem;">
      Solo se exportarán compras con estado contable <strong style="color:#059669;">Listo</strong> en el rango seleccionado.
    </p>

    <div style="display:flex; flex-direction:column; gap:.85rem;">
      <div>
        <label style="display:block; font-size:.78rem; font-weight:600; color:var(--clr-text-muted,#6b7280); margin-bottom:.35rem;" for="pex-from">Fecha desde</label>
        <input type="date" id="pex-from" style="width:100%; padding:.6rem .85rem; border:1px solid #d1d5db; border-radius:8px; font-size:.9rem; background:transparent; color:inherit; outline:none; box-sizing:border-box;">
      </div>
      <div>
        <label style="display:block; font-size:.78rem; font-weight:600; color:var(--clr-text-muted,#6b7280); margin-bottom:.35rem;" for="pex-to">Fecha hasta</label>
        <input type="date" id="pex-to" style="width:100%; padding:.6rem .85rem; border:1px solid #d1d5db; border-radius:8px; font-size:.9rem; background:transparent; color:inherit; outline:none; box-sizing:border-box;">
      </div>
      <div id="pex-count-preview" style="display:none; padding:.65rem 1rem; background:rgba(5,150,105,.06); border:1px solid rgba(5,150,105,.2); border-radius:8px; font-size:.88rem; color:#065f46;">
        <i class='bx bx-info-circle'></i> <span id="pex-count-text">—</span>
      </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:.65rem; margin-top:1.5rem;">
      <button type="button" id="purchase-export-cancel"
              style="padding:.55rem 1.2rem; background:transparent; border:1px solid #d1d5db; border-radius:8px; cursor:pointer; font-size:.88rem;">
        Cancelar
      </button>
      <button type="button" id="purchase-export-submit"
              style="padding:.55rem 1.4rem; background:#059669; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:.88rem; font-weight:700; display:inline-flex; align-items:center; gap:.4rem;">
        <i class='bx bx-download'></i> Descargar Excel
      </button>
    </div>

  </div>
</div>

<script>
(function () {
  const overlay  = document.getElementById('purchase-export-modal-overlay');
  const btnClose = document.getElementById('purchase-export-close');
  const btnCancel = document.getElementById('purchase-export-cancel');
  const btnSubmit = document.getElementById('purchase-export-submit');
  const fromInput = document.getElementById('pex-from');
  const toInput   = document.getElementById('pex-to');
  const countDiv  = document.getElementById('pex-count-preview');
  const countText = document.getElementById('pex-count-text');

  const EXPORT_COUNT_URL = '{{ route("facturador.compras.export-count") }}';
  const EXPORT_EXCEL_URL = '{{ route("facturador.compras.export-excel") }}';

  // Defaults: primer y último día del mes actual
  const now = new Date();
  const yy  = now.getFullYear();
  const mm  = String(now.getMonth() + 1).padStart(2, '0');
  const lastDay = new Date(yy, now.getMonth() + 1, 0).getDate();
  fromInput.value = `${yy}-${mm}-01`;
  toInput.value   = `${yy}-${mm}-${lastDay}`;

  function close() { overlay.style.display = 'none'; }

  btnClose?.addEventListener('click', close);
  btnCancel?.addEventListener('click', close);
  overlay?.addEventListener('click', e => { if (e.target === overlay) close(); });

  function checkCount() {
    const from = fromInput.value;
    const to   = toInput.value;
    if (!from || !to) { countDiv.style.display = 'none'; return; }

    fetch(`${EXPORT_COUNT_URL}?from=${from}&to=${to}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      countDiv.style.display = 'block';
      countText.textContent = `${data.count} compra(s) lista(s) para exportar en ese rango.`;
    })
    .catch(() => { countDiv.style.display = 'none'; });
  }

  fromInput.addEventListener('change', checkCount);
  toInput.addEventListener('change', checkCount);
  checkCount();

  btnSubmit?.addEventListener('click', async () => {
    const from = fromInput.value;
    const to   = toInput.value;
    if (!from || !to) {
      await Swal.fire({
        title: 'Rango requerido',
        text: 'Selecciona un rango de fechas para exportar.',
        icon: 'warning',
        confirmButtonText: 'Entendido',
        customClass: {
          popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : ''
        }
      });
      return;
    }
    window.location.href = `${EXPORT_EXCEL_URL}?from=${from}&to=${to}`;
    close();
  });
})();
</script>
