@extends('layouts.app')

@section('title', 'Cotizaciones | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .quotes-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.2rem}
    .quotes-header h1{margin:0;font-size:1.8rem}
    .quotes-header p{margin:.25rem 0 0;color:#64748b}
    .quotes-actions{display:flex;gap:.6rem;flex-wrap:wrap}
    .quotes-filter{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;border:1px solid #e5e7eb;border-radius:12px;padding:1rem;background:#fff;margin-bottom:1rem}
    .quotes-filter input,.quotes-filter select{border:1px solid #dbe3ea;border-radius:8px;padding:.6rem .75rem;font:inherit;min-height:40px}
    .quotes-filter input{min-width:250px}
    .quotes-table-wrap{border:1px solid #e5e7eb;border-radius:12px;background:#fff;overflow:hidden}
    .quotes-table{width:100%;border-collapse:collapse;font-size:.9rem}
    .quotes-table th{background:#f8fafc;color:#64748b;text-transform:uppercase;font-size:.72rem;letter-spacing:.02em;text-align:left;padding:.8rem 1rem;border-bottom:1px solid #e5e7eb}
    .quotes-table td{padding:.85rem 1rem;border-bottom:1px solid #edf2f7;vertical-align:middle}
    .quotes-table tr:last-child td{border-bottom:none}
    .quote-number{font-weight:800;color:#0f172a}
    .quote-client{font-weight:700;color:#111827;max-width:330px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .quote-muted{color:#64748b;font-size:.8rem}
    .quote-total{font-weight:800;color:#0f766e;white-space:nowrap}
    .status-pill{display:inline-flex;align-items:center;border-radius:999px;padding:.24rem .65rem;font-size:.75rem;font-weight:800;border:1px solid transparent}
    .status-draft{background:#f1f5f9;color:#475569;border-color:#cbd5e1}
    .status-sent{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
    .status-accepted{background:#ecfdf5;color:#047857;border-color:#a7f3d0}
    .status-rejected{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
    .quote-actions{display:flex;align-items:center;gap:.4rem}
    .icon-btn{width:34px;height:34px;border-radius:8px;border:1px solid #e5e7eb;background:#f8fafc;color:#0f766e;display:inline-flex;align-items:center;justify-content:center;text-decoration:none}
    .icon-btn:hover{background:#eef7f5;border-color:#b7d9d0}
    .quote-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:90;display:none;align-items:center;justify-content:center;padding:1rem}
    .quote-modal-backdrop.open{display:flex}
    .quote-modal{width:min(760px,96vw);max-height:88vh;overflow:auto;background:#fff;border-radius:14px;box-shadow:0 24px 60px rgba(15,23,42,.25)}
    .quote-modal-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.25rem;border-bottom:1px solid #e5e7eb}
    .quote-modal-head h2{font-size:1.1rem;margin:0}
    .quote-modal-body{padding:1.25rem}
    .quote-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem;margin-bottom:1rem}
    .quote-detail-box{background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:.8rem}
    .quote-detail-box span{display:block;font-size:.72rem;font-weight:800;color:#64748b;text-transform:uppercase}
    .quote-detail-box strong{display:block;margin-top:.2rem;color:#111827}
    .quote-message{width:100%;min-height:110px;border:1px solid #dbe3ea;border-radius:10px;padding:.8rem;font:inherit;resize:vertical}
    .modal-items{width:100%;border-collapse:collapse;font-size:.86rem;margin-top:.8rem}
    .modal-items th{background:#f8fafc;color:#64748b;text-align:left;padding:.55rem;border-bottom:1px solid #e5e7eb}
    .modal-items td{padding:.55rem;border-bottom:1px solid #edf2f7}
    .modal-actions{display:flex;justify-content:flex-end;gap:.6rem;flex-wrap:wrap;padding:1rem 1.25rem;border-top:1px solid #e5e7eb;background:#f8fafc}
    .empty-row{text-align:center;color:#64748b;padding:2.5rem!important}
    .pagination-wrap{padding:1rem;border-top:1px solid #e5e7eb}
    @media(max-width:900px){.quotes-header{flex-direction:column}.quotes-table-wrap{overflow-x:auto}.quotes-filter input{min-width:100%;flex:1}.quotes-table{min-width:860px}}
  </style>
@endpush

@section('content')
<div class="app-layout">
  <aside class="sidebar-premium">
    <div class="sidebar-header">
      <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
      <div class="header-text"><h2>Portal Mendieta</h2><p>Panel interno</p></div>
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
        @if(session('success'))
          <div class="placeholder-content module-alert" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="placeholder-content module-alert">{{ session('error') }}</div>
        @endif

        <div class="placeholder-content module-card-wide">
          <div class="quotes-header">
            <div>
              <h1>Cotizaciones</h1>
              <p>Registro de cotizaciones generadas para la empresa activa.</p>
            </div>
            <div class="quotes-actions">
              <a href="{{ route('facturador.quotations.create') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;">
                <i class='bx bx-plus'></i> Nueva cotización
              </a>
              <a href="{{ route('facturador.quote-settings.edit') }}" class="btn-secondary" style="display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;">
                <i class='bx bx-palette'></i> Configurar
              </a>
            </div>
          </div>

          <form method="GET" class="quotes-filter">
            <i class='bx bx-filter-alt' style="font-size:1.25rem;color:#64748b;"></i>
            <input type="text" name="search" placeholder="Buscar número, código u observación..." value="{{ request('search') }}">
            <select name="estado">
              <option value="">Todos los estados</option>
              <option value="draft" @selected(request('estado') === 'draft')>Borrador</option>
              <option value="sent" @selected(request('estado') === 'sent')>Enviada</option>
              <option value="accepted" @selected(request('estado') === 'accepted')>Aceptada</option>
              <option value="rejected" @selected(request('estado') === 'rejected')>Rechazada</option>
            </select>
            <button type="submit" class="btn-primary" style="display:inline-flex;align-items:center;gap:.35rem;">
              <i class='bx bx-search'></i> Filtrar
            </button>
            @if(request()->hasAny(['search','estado']))
              <a href="{{ route('facturador.cotizaciones.index') }}" class="btn-secondary" style="text-decoration:none;">Limpiar</a>
            @endif
          </form>

          <div class="quotes-table-wrap">
            <table class="quotes-table">
              <thead>
                <tr>
                  <th>Número</th>
                  <th>Cliente</th>
                  <th>Fecha</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Versión</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                @forelse($quotes as $quote)
                  @php
                    $statusClass = [
                      'draft' => 'status-draft',
                      'sent' => 'status-sent',
                      'accepted' => 'status-accepted',
                      'rejected' => 'status-rejected',
                    ][$quote->estado] ?? 'status-draft';
                    $statusLabel = [
                      'draft' => 'Borrador',
                      'sent' => 'Enviada',
                      'accepted' => 'Aceptada',
                      'rejected' => 'Rechazada',
                    ][$quote->estado] ?? ucfirst($quote->estado);
                    $clientName = $quote->client?->nombre_cliente ?? 'cliente';
                    $validUntil = $quote->fecha_vencimiento?->format('d/m/Y') ?? 'la fecha indicada';
                    $quoteUrl = route('facturador.cotizaciones.pdf', $quote);
                    $whatsappMessage = "Hola 👋 Te compartimos la cotización {$quote->codigo_interno}. 📄\n\n🗓️ Vigencia: {$validUntil}\n\nTe enviamos el PDF con el detalle para tu revisión. Quedamos atentos.";
                    $modalItemsJson = $quote->items->map(fn($item) => [
                      "descripcion" => $item->descripcion,
                      "cantidad" => number_format((float) $item->cantidad, 2),
                      "precio" => number_format((float) $item->monto_valor_unitario, 2),
                      "total" => number_format((float) $item->monto_total, 2),
                    ])->values()->toJson(JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);
                  @endphp
                  <tr>
                    <td>
                      <div class="quote-number">{{ $quote->numero_cotizacion }}</div>
                      <div class="quote-muted">{{ $quote->codigo_interno }}</div>
                    </td>
                    <td>
                      <div class="quote-client">{{ $quote->client?->nombre_cliente ?? 'Sin cliente' }}</div>
                      @if($quote->client?->numero_documento)
                        <div class="quote-muted">{{ $quote->client->numero_documento }}</div>
                      @endif
                    </td>
                    <td>{{ $quote->fecha_emision?->format('d/m/Y') }}</td>
                    <td><span class="quote-total">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total, 2) }}</span></td>
                    <td><span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    <td>v{{ $quote->version }}</td>
                    <td>
                      <div class="quote-actions">
                        <button type="button" class="icon-btn btnOpenQuote" title="Ver"
                          data-number="{{ e($quote->codigo_interno) }}"
                          data-client="{{ e($clientName) }}"
                          data-document="{{ e($quote->client?->numero_documento ?? '') }}"
                          data-date="{{ e($quote->fecha_emision?->format('d/m/Y') ?? '') }}"
                          data-valid="{{ e($validUntil) }}"
                          data-total="{{ e($quote->codigo_moneda . ' ' . number_format($quote->monto_total, 2)) }}"
                          data-status="{{ e($statusLabel) }}"
                          data-url="{{ e($quoteUrl) }}"
                          data-message="{{ e($whatsappMessage) }}"
                          data-items="{{ e($modalItemsJson) }}">
                          <i class='bx bx-show'></i>
                        </button>
                        <button type="button" class="icon-btn btnCopyQuote" title="Copiar mensaje" data-message="{{ e($whatsappMessage) }}">
                          <i class='bx bx-copy'></i>
                        </button>
                        <a href="{{ route('facturador.cotizaciones.pdf', $quote) }}" class="icon-btn" title="PDF"><i class='bx bx-file'></i></a>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="empty-row">No hay cotizaciones registradas.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>

            @if($quotes->hasPages())
              <div class="pagination-wrap">{{ $quotes->links() }}</div>
            @endif
          </div>
        </div>
      </div>
    </main>
  </section>
</div>

<div class="quote-modal-backdrop" id="quoteModalBackdrop" aria-hidden="true">
  <div class="quote-modal" role="dialog" aria-modal="true" aria-labelledby="quoteModalTitle">
    <div class="quote-modal-head">
      <h2 id="quoteModalTitle">Detalle de cotización</h2>
      <button type="button" class="icon-btn" id="btnCloseQuoteModal" title="Cerrar"><i class='bx bx-x'></i></button>
    </div>
    <div class="quote-modal-body">
      <div class="quote-detail-grid">
        <div class="quote-detail-box"><span>Número</span><strong id="modalNumber">-</strong></div>
        <div class="quote-detail-box"><span>Cliente</span><strong id="modalClient">-</strong></div>
        <div class="quote-detail-box"><span>Fecha</span><strong id="modalDate">-</strong></div>
        <div class="quote-detail-box"><span>Válido hasta</span><strong id="modalValid">-</strong></div>
        <div class="quote-detail-box"><span>Total</span><strong id="modalTotal">-</strong></div>
        <div class="quote-detail-box"><span>Estado</span><strong id="modalStatus">-</strong></div>
      </div>

      <h3 style="font-size:.95rem;margin:0 0 .5rem;">Ítems</h3>
      <table class="modal-items">
        <thead>
          <tr>
            <th>Descripción</th>
            <th>Cant.</th>
            <th>Precio</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody id="modalItems"></tbody>
      </table>

      <h3 style="font-size:.95rem;margin:1rem 0 .5rem;">Mensaje sugerido</h3>
      <textarea class="quote-message" id="modalMessage" readonly></textarea>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn-secondary" id="btnCopyModalMessage"><i class='bx bx-copy'></i> Copiar mensaje</button>
      <a href="#" class="btn-primary" id="modalPdfLink" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;"><i class='bx bx-file'></i> Abrir PDF</a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const backdrop = document.getElementById('quoteModalBackdrop');
  const modalItems = document.getElementById('modalItems');
  const modalMessage = document.getElementById('modalMessage');
  const modalPdfLink = document.getElementById('modalPdfLink');

  function copyText(text) {
    if (navigator.clipboard) {
      return navigator.clipboard.writeText(text);
    }
    const tmp = document.createElement('textarea');
    tmp.value = text;
    document.body.appendChild(tmp);
    tmp.select();
    document.execCommand('copy');
    tmp.remove();
    return Promise.resolve();
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[char]));
  }

  document.addEventListener('click', function(e){
    const copyBtn = e.target.closest('.btnCopyQuote');
    if (copyBtn) {
      copyText(copyBtn.dataset.message || '').then(() => {
        copyBtn.innerHTML = "<i class='bx bx-check'></i>";
        setTimeout(() => copyBtn.innerHTML = "<i class='bx bx-copy'></i>", 1200);
      });
      return;
    }

    const openBtn = e.target.closest('.btnOpenQuote');
    if (!openBtn) return;

    document.getElementById('modalNumber').textContent = openBtn.dataset.number || '-';
    document.getElementById('modalClient').textContent = openBtn.dataset.client || '-';
    document.getElementById('modalDate').textContent = openBtn.dataset.date || '-';
    document.getElementById('modalValid').textContent = openBtn.dataset.valid || '-';
    document.getElementById('modalTotal').textContent = openBtn.dataset.total || '-';
    document.getElementById('modalStatus').textContent = openBtn.dataset.status || '-';
    modalMessage.value = openBtn.dataset.message || '';
    modalPdfLink.href = openBtn.dataset.url || '#';

    let items = [];
    try { items = JSON.parse(openBtn.dataset.items || '[]'); } catch (_) { items = []; }
    modalItems.innerHTML = items.length
      ? items.map(item => `<tr><td>${escapeHtml(item.descripcion)}</td><td>${escapeHtml(item.cantidad)}</td><td>${escapeHtml(item.precio)}</td><td>${escapeHtml(item.total)}</td></tr>`).join('')
      : '<tr><td colspan="4" style="text-align:center;color:#64748b;">Sin ítems</td></tr>';

    backdrop.classList.add('open');
    backdrop.setAttribute('aria-hidden', 'false');
  });

  document.getElementById('btnCloseQuoteModal')?.addEventListener('click', () => {
    backdrop.classList.remove('open');
    backdrop.setAttribute('aria-hidden', 'true');
  });

  backdrop?.addEventListener('click', e => {
    if (e.target === backdrop) {
      backdrop.classList.remove('open');
      backdrop.setAttribute('aria-hidden', 'true');
    }
  });

  document.getElementById('btnCopyModalMessage')?.addEventListener('click', function(){
    copyText(modalMessage.value || '').then(() => {
      const original = this.innerHTML;
      this.innerHTML = "<i class='bx bx-check'></i> Copiado";
      setTimeout(() => this.innerHTML = original, 1200);
    });
  });
})();
</script>
@endpush
