@extends('layouts.app')

@section('title', 'Buzón SOL | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<style>
/* ── Layout principal ───────────────────────────────────────────────── */
.bz-wrap            { display:flex; flex-direction:column; height:calc(100vh - 120px); overflow:hidden; }
.bz-toolbar         { display:flex; align-items:center; gap:.5rem; padding:.6rem 1rem; background:#fff; border-bottom:1px solid #e5e7eb; flex-shrink:0; flex-wrap:wrap; }
.bz-split           { display:flex; flex:1; overflow:hidden; }

/* ── Panel izquierdo ────────────────────────────────────────────────── */
.bz-left            { width:42%; min-width:280px; display:flex; flex-direction:column; border-right:1px solid #e5e7eb; background:#f8fafc; }
.bz-left-head       { padding:.75rem 1rem; background:#1e293b; color:#fff; flex-shrink:0; }
.bz-filter-bar      { display:flex; gap:.4rem; padding:.5rem .75rem; background:#1e293b; border-bottom:1px solid #334155; flex-shrink:0; }
.bz-filter-bar input{ flex:1; font-size:.8rem; padding:.3rem .6rem; border-radius:6px; border:1px solid #475569; background:#0f172a; color:#e2e8f0; outline:none; }
.bz-filter-bar input::placeholder{ color:#64748b; }
.bz-tabs            { display:flex; gap:0; flex-shrink:0; background:#0f172a; }
.bz-tab             { flex:1; padding:.45rem .25rem; text-align:center; font-size:.75rem; color:#94a3b8; cursor:pointer; border-bottom:2px solid transparent; transition:.15s; }
.bz-tab.active      { color:#60a5fa; border-color:#60a5fa; }
.bz-list            { flex:1; overflow-y:auto; padding:.5rem; display:flex; flex-direction:column; gap:.35rem; }

/* ── Tarjetas de mensaje ────────────────────────────────────────────── */
.bz-card            { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:.65rem .85rem; cursor:pointer; transition:.15s; }
.bz-card:hover      { border-color:#93c5fd; box-shadow:0 2px 8px rgba(59,130,246,.1); }
.bz-card.active     { border-color:#3b82f6; background:#eff6ff; }
.bz-card.unread     { border-left:3px solid #3b82f6; }
.bz-card-header     { display:flex; align-items:center; gap:.4rem; margin-bottom:.25rem; }
.bz-card-asunto     { font-size:.82rem; font-weight:600; color:#1e293b; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bz-card-date       { font-size:.7rem; color:#94a3b8; white-space:nowrap; }
.bz-card-sub        { font-size:.73rem; color:#64748b; }
.bz-badge           { display:inline-block; padding:.1rem .4rem; border-radius:4px; font-size:.65rem; font-weight:700; color:#fff; }

/* ── Panel derecho ──────────────────────────────────────────────────── */
.bz-right           { flex:1; display:flex; flex-direction:column; background:#f0f4f8; }
.bz-detail-head     { padding:.65rem 1rem; background:#fff; border-bottom:1px solid #e5e7eb; flex-shrink:0; }
.bz-detail-asunto   { font-size:.9rem; font-weight:700; color:#1e293b; }
.bz-detail-meta     { font-size:.75rem; color:#64748b; margin-top:.15rem; }
.bz-iframe-wrap     { flex:1; position:relative; }
.bz-iframe-wrap iframe{ width:100%; height:100%; border:none; background:#fff; }
.bz-empty           { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#9ca3af; gap:.5rem; }
.bz-empty i         { font-size:3rem; }

/* ── Selector de empresa ────────────────────────────────────────────── */
.bz-company-sel     { font-size:.82rem; padding:.35rem .6rem; border:1px solid #d1d5db; border-radius:6px; min-width:200px; }

/* ── Botones ────────────────────────────────────────────────────────── */
.bz-btn             { display:inline-flex; align-items:center; gap:.3rem; padding:.35rem .75rem; border-radius:6px; font-size:.78rem; cursor:pointer; border:none; font-weight:500; transition:.15s; }
.bz-btn-blue        { background:#3b82f6; color:#fff; }
.bz-btn-blue:hover  { background:#2563eb; }
.bz-btn-green       { background:#10b981; color:#fff; }
.bz-btn-green:hover { background:#059669; }
.bz-btn-gray        { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.bz-btn-gray:hover  { background:#e2e8f0; }
.bz-btn-sm          { padding:.2rem .5rem; font-size:.72rem; }
.bz-btn-red         { background:#ef4444; color:#fff; }
.bz-btn-red:hover   { background:#dc2626; }

/* ── Modal keywords ─────────────────────────────────────────────────── */
.kw-row             { display:flex; align-items:center; gap:.5rem; padding:.35rem .5rem; border-bottom:1px solid #f1f5f9; }
.kw-row:last-child  { border-bottom:none; }
.kw-color-dot       { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* ── Loader ─────────────────────────────────────────────────────────── */
.bz-loader          { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.6rem; padding:2rem; color:#64748b; font-size:.82rem; }

/* ── Filtro leido ───────────────────────────────────────────────────── */
.bz-read-filter     { display:flex; gap:0; }
.bz-read-filter button{ padding:.25rem .5rem; font-size:.72rem; border:1px solid #e2e8f0; background:#fff; color:#475569; cursor:pointer; }
.bz-read-filter button:first-child{ border-radius:5px 0 0 5px; }
.bz-read-filter button:last-child { border-radius:0 5px 5px 0; }
.bz-read-filter button.active{ background:#3b82f6; color:#fff; border-color:#3b82f6; }
</style>
@endpush

@section('content')
<div class="app-layout">
  <aside class="sidebar-premium">
    <div class="sidebar-header">
      <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta" class="header-logo">
      <div class="header-text">
        <h2>Portal Mendieta</h2>
        <p>{{ auth()->user()?->role?->value === 'client' ? 'Panel cliente' : 'Panel interno' }}</p>
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
        <div class="placeholder-content module-card-wide" style="padding:0;overflow:hidden;">

<div class="bz-wrap">

    {{-- Toolbar --}}
    <div class="bz-toolbar">
        <select id="bz-company" class="bz-company-sel">
            <option value="">Selecciona empresa...</option>
            @foreach($companies as $c)
            <option value="{{ $c->id }}"
                    data-iniciar="{{ route('bandeja-sunat.iniciar',    $c) }}"
                    data-lista="{{ route('bandeja-sunat.lista',       $c) }}"
                    data-sincronizar="{{ route('bandeja-sunat.sincronizar', $c) }}"
                    data-nombre="{{ $c->name }}"
                    data-count="{{ $counts[$c->id] ?? 0 }}">
                {{ $c->name }}{{ ($counts[$c->id] ?? 0) > 0 ? ' ('.$counts[$c->id].' en BD)' : '' }}
            </option>
            @endforeach
        </select>

        <button id="btn-iniciar" class="bz-btn bz-btn-blue" disabled>
            <i class="bx bx-search"></i> Buscar buzon
        </button>

        <button id="btn-sync" class="bz-btn bz-btn-green" disabled>
            <i class="bx bx-cloud-download"></i> Sincronizar BD
        </button>

        <button id="btn-kw" class="bz-btn bz-btn-gray">
            <i class="bx bx-tag"></i> Keywords
        </button>

        <span id="bz-status" class="ms-auto text-muted" style="font-size:.78rem;"></span>
    </div>

    <div class="bz-split">

        {{-- Panel Izquierdo --}}
        <div class="bz-left">
            <div class="bz-left-head">
                <div style="font-weight:700;font-size:.85rem;" id="bz-company-name">Sin empresa seleccionada</div>
                <div style="font-size:.72rem;color:#94a3b8;" id="bz-company-info">Selecciona una empresa arriba</div>
            </div>

            <div class="bz-filter-bar">
                <input type="text" id="bz-search" placeholder="Buscar asunto / remitente...">
                <div class="bz-read-filter" id="bz-read-filter">
                    <button class="active" data-val="" onclick="setReadFilter(this,'')">Todos</button>
                    <button data-val="0" onclick="setReadFilter(this,'0')">No leidos</button>
                    <button data-val="1" onclick="setReadFilter(this,'1')">Leidos</button>
                </div>
            </div>

            <div class="bz-tabs">
                <div class="bz-tab active" data-tipo="1" onclick="cambiarTipo(1,this)">Notificaciones</div>
                <div class="bz-tab"        data-tipo="2" onclick="cambiarTipo(2,this)">Documentos</div>
                <div class="bz-tab"        data-tipo="3" onclick="cambiarTipo(3,this)">Comunicados</div>
            </div>

            <div class="bz-list" id="bz-list">
                <div class="bz-empty">
                    <i class="bx bx-envelope-open"></i>
                    <span>Selecciona una empresa para ver sus mensajes</span>
                </div>
            </div>
        </div>

        {{-- Panel Derecho --}}
        <div class="bz-right" id="bz-right">
            <div id="bz-detail-head" class="bz-detail-head" style="display:none;">
                <div class="bz-detail-asunto" id="bz-detail-asunto"></div>
                <div class="bz-detail-meta" id="bz-detail-meta"></div>
            </div>
            <div class="bz-iframe-wrap">
                <iframe id="bz-doc-frame" title="Documento SUNAT" style="display:none;"></iframe>
                <div class="bz-empty" id="bz-doc-empty">
                    <i class="bx bx-file-blank"></i>
                    <span>Selecciona un mensaje para ver su contenido</span>
                </div>
            </div>
        </div>

    </div>
</div>

        </div>{{-- module-card-wide --}}
      </div>{{-- module-content-stack --}}
    </main>
  </section>
</div>{{-- app-layout --}}

{{-- Modal Keywords — patrón nativo del proyecto (modal-overlay) --}}
<div class="modal-overlay" id="kwModal" onclick="if(event.target===this) cerrarKwModal()">
    <div class="modal-card" style="max-width:520px;">
        <div class="modal-header">
            <h3 style="font-size:.95rem;margin:0;"><i class="bx bx-tag" style="margin-right:.35rem;"></i>Palabras clave de prioridad</h3>
            <button type="button" onclick="cerrarKwModal()" style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:#64748b;line-height:1;">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <div class="modal-body" style="padding:1rem 1.5rem;">
            <div style="display:flex;gap:.5rem;margin-bottom:1rem;">
                <input type="text" id="kw-input" placeholder="Palabra clave..."
                    style="flex:2;padding:.4rem .65rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;outline:none;">
                <select id="kw-prioridad"
                    style="flex:1;padding:.4rem .5rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;">
                    <option value="alta">Alta</option>
                    <option value="media" selected>Media</option>
                    <option value="baja">Baja</option>
                </select>
                <input type="color" id="kw-color" value="#3b82f6"
                    style="width:38px;padding:.2rem;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;">
                <button onclick="agregarKeyword()" class="bz-btn bz-btn-blue" style="white-space:nowrap;">
                    <i class="bx bx-plus"></i> Agregar
                </button>
            </div>
            <div id="kw-list" style="max-height:260px;overflow-y:auto;">
                <div style="text-align:center;color:#94a3b8;padding:1.5rem 0;font-size:.82rem;">Cargando...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const BZ = {
    companyId:     null,
    urlIniciar:    null,
    urlLista:      null,
    urlSincronizar:null,
    tipo:          1,
    leidoFilter:   '',
    sessionOk:     false,
};

const _csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

document.getElementById('bz-company').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) { resetPanel(); return; }
    BZ.companyId      = opt.value;
    BZ.urlIniciar     = opt.dataset.iniciar;
    BZ.urlLista       = opt.dataset.lista;
    BZ.urlSincronizar = opt.dataset.sincronizar;
    BZ.sessionOk      = false;
    document.getElementById('bz-company-name').textContent = opt.dataset.nombre;
    document.getElementById('bz-company-info').textContent =
        parseInt(opt.dataset.count) > 0 ? opt.dataset.count + ' mensajes en BD local' : 'Sin mensajes en BD local aun';
    document.getElementById('btn-iniciar').disabled = false;
    document.getElementById('btn-sync').disabled    = true;
    setStatus('');
    cargarDesdeBD();
});

function resetPanel() {
    BZ.companyId = null;
    document.getElementById('bz-company-name').textContent = 'Sin empresa seleccionada';
    document.getElementById('bz-company-info').textContent = 'Selecciona una empresa arriba';
    document.getElementById('btn-iniciar').disabled = true;
    document.getElementById('btn-sync').disabled    = true;
    mostrarEmpty('Selecciona una empresa para ver sus mensajes');
    ocultarDetalle();
}

/* ── BD ─────────────────────────────────────────────────────────────── */
function cargarDesdeBD() {
    if (!BZ.companyId) return;
    mostrarLoader();
    const q   = encodeURIComponent(document.getElementById('bz-search').value);
    const url = BZ.urlLista + '?tipo=' + BZ.tipo + '&q=' + q + '&leido=' + BZ.leidoFilter;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) { mostrarEmpty('Error BD: ' + (data.error ?? '')); return; }
            renderizarLista(data.rows);
        })
        .catch(() => mostrarEmpty('Error de conexion'));
}

/* ── Bot login ──────────────────────────────────────────────────────── */
document.getElementById('btn-iniciar').addEventListener('click', async () => {
    if (!BZ.urlIniciar) return;
    setStatus('Iniciando sesion con SUNAT...');
    document.getElementById('btn-iniciar').disabled = true;
    mostrarLoader('Conectando con SUNAT...');

    try {
        const r    = await fetch(BZ.urlIniciar, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json' },
        });
        const data = await r.json();
        document.getElementById('btn-iniciar').disabled = false;
        if (!data.ok) {
            setStatus('Error: ' + (data.error ?? 'desconocido'));
            mostrarEmpty('No se pudo iniciar sesion');
            return;
        }
        BZ.sessionOk = true;
        document.getElementById('btn-sync').disabled = false;
        setStatus('Sesion activa - preparando datos...');
        await sincronizarConReintentos();
    } catch {
        document.getElementById('btn-iniciar').disabled = false;
        setStatus('Error de red');
        mostrarEmpty('Error de conexion');
    }
});

/* ── Sincronizar ────────────────────────────────────────────────────── */
document.getElementById('btn-sync').addEventListener('click', async () => {
    setStatus('Sincronizando...');
    document.getElementById('btn-sync').disabled = true;
    mostrarLoader('Obteniendo mensajes del bot...');
    await sincronizarConReintentos();
});

async function sincronizarConReintentos(maxIntentos = 8) {
    const delay = ms => new Promise(r => setTimeout(r, ms));
    for (let i = 0; i < maxIntentos; i++) {
        let res, data;
        try {
            res  = await fetch(BZ.urlSincronizar, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo: BZ.tipo }),
            });
            data = await res.json();
        } catch {
            document.getElementById('btn-sync').disabled = false;
            setStatus('Error de red');
            cargarDesdeBD();
            return;
        }

        if (data.ok) {
            document.getElementById('btn-sync').disabled = false;
            setStatus('Sincronizado - ' + data.nuevos + ' nuevos de ' + data.total);
            cargarDesdeBD();
            return;
        }

        // El bot aun esta en warm-up — reintentar con polling
        if (data.error === 'login_pendiente' || res.status === 202) {
            setStatus('Preparando sesion SUNAT... (' + (i + 1) + '/' + maxIntentos + ')');
            mostrarLoader('Preparando sesion SUNAT... (' + (i + 1) + '/' + maxIntentos + ')');
            await delay(3500);
            continue;
        }

        // Sesion expirada
        if (data.expired) {
            BZ.sessionOk = false;
            document.getElementById('btn-sync').disabled = true;
            setStatus('Sesion expirada - vuelve a buscar.');
            cargarDesdeBD();
            return;
        }

        // Error definitivo
        document.getElementById('btn-sync').disabled = false;
        setStatus('Error: ' + (data.error ?? 'desconocido'));
        cargarDesdeBD();
        return;
    }

    // Agoto los reintentos
    document.getElementById('btn-sync').disabled = false;
    setStatus('Tiempo de espera agotado. Intenta nuevamente.');
    cargarDesdeBD();
}

/* ── Renderizado ────────────────────────────────────────────────────── */
function renderizarLista(rows) {
    const lista = document.getElementById('bz-list');
    if (!rows.length) {
        lista.innerHTML = '<div class="bz-empty"><i class="bx bx-inbox"></i><span>Sin mensajes</span></div>';
        return;
    }
    lista.innerHTML = rows.map(m => {
        const badge = m.prioridad
            ? '<span class="bz-badge" style="background:' + m.kw_color + '">' + m.prioridad.toUpperCase() + '</span>'
            : '';
        const unread = m.leido ? '' : ' unread';
        return '<div class="bz-card' + unread + '" data-id="' + m.id + '" data-cod="' + m.cod_sunat + '"' +
            ' data-asunto="' + escHtml(m.asunto ?? '') + '" data-remitente="' + escHtml(m.remitente ?? '') + '"' +
            ' data-fecha="' + (m.fecha ?? '') + '" onclick="abrirMensaje(this)">' +
            '<div class="bz-card-header">' +
            '<span class="bz-card-asunto" title="' + escHtml(m.asunto ?? '') + '">' + escHtml(m.asunto ?? '(sin asunto)') + '</span>' +
            badge +
            '<span class="bz-card-date">' + (m.fecha ?? '') + '</span>' +
            '</div>' +
            '<div class="bz-card-sub">' + escHtml(m.remitente ?? '') + '</div>' +
            '</div>';
    }).join('');
}

/* ── Abrir mensaje ──────────────────────────────────────────────────── */
function abrirMensaje(card) {
    document.querySelectorAll('.bz-card.active').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    card.classList.remove('unread');

    document.getElementById('bz-detail-head').style.display = '';
    document.getElementById('bz-detail-asunto').textContent = card.dataset.asunto;
    document.getElementById('bz-detail-meta').textContent = 'De: ' + card.dataset.remitente + '   Fecha: ' + card.dataset.fecha;

    const urlDoc = BZ.urlLista.replace('/lista/', '/documento/') + '/' + card.dataset.cod;
    const frame = document.getElementById('bz-doc-frame');
    frame.src = urlDoc;
    frame.style.display = '';
    document.getElementById('bz-doc-empty').style.display = 'none';

    marcarLeido(card.dataset.id);
}

function marcarLeido(mensajeId) {
    const urlLeer = BZ.urlLista.replace('/lista/', '/leer/') + '/' + mensajeId;
    fetch(urlLeer, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json' },
    }).catch(() => {});
}

/* ── Filtros ────────────────────────────────────────────────────────── */
function setReadFilter(btn, val) {
    document.querySelectorAll('#bz-read-filter button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    BZ.leidoFilter = val;
    cargarDesdeBD();
}

function cambiarTipo(tipo, tab) {
    document.querySelectorAll('.bz-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    BZ.tipo = tipo;
    cargarDesdeBD();
}

/* ── Keywords ───────────────────────────────────────────────────────── */
document.getElementById('btn-kw').addEventListener('click', () => {
    cargarKeywords();
    document.getElementById('kwModal').classList.add('show');
    document.body.style.overflow = 'hidden';
});
function cerrarKwModal() {
    document.getElementById('kwModal').classList.remove('show');
    document.body.style.overflow = '';
}

function cargarKeywords() {
    const urlKw = '{{ route("bandeja-sunat.keywords") }}';
    fetch(urlKw, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            const list = document.getElementById('kw-list');
            if (!data.keywords.length) {
                list.innerHTML = '<div class="text-muted py-3 text-center" style="font-size:.8rem;">Sin palabras clave</div>';
                return;
            }
            list.innerHTML = data.keywords.map(kw =>
                '<div class="kw-row" id="kw-row-' + kw.id + '">' +
                '<div class="kw-color-dot" style="background:' + kw.color + '"></div>' +
                '<span style="flex:1;font-size:.82rem;">' + escHtml(kw.palabra) + '</span>' +
                '<span class="bz-badge" style="background:' + kw.color + '">' + kw.prioridad.toUpperCase() + '</span>' +
                '<button class="bz-btn bz-btn-red bz-btn-sm ms-1" onclick="eliminarKeyword(' + kw.id + ')">' +
                '<i class="bx bx-trash"></i></button></div>'
            ).join('');
        });
}

function agregarKeyword() {
    const palabra   = document.getElementById('kw-input').value.trim();
    const prioridad = document.getElementById('kw-prioridad').value;
    const color     = document.getElementById('kw-color').value;
    if (!palabra) { alert('Escribe una palabra clave.'); return; }
    const urlKw = '{{ route("bandeja-sunat.keywords.store") }}';
    fetch(urlKw, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ palabra, prioridad, color }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) { alert(data.message ?? 'Error al agregar'); return; }
        document.getElementById('kw-input').value = '';
        cargarKeywords();
        if (BZ.companyId) cargarDesdeBD();
    });
}

function eliminarKeyword(id) {
    if (!confirm('Eliminar esta palabra clave?')) return;
    const urlDel = '{{ url("bandeja-sunat/keywords") }}/' + id;
    fetch(urlDel, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            document.getElementById('kw-row-' + id)?.remove();
            if (BZ.companyId) cargarDesdeBD();
        }
    });
}

/* ── Helpers UI ─────────────────────────────────────────────────────── */
function mostrarLoader(msg) {
    msg = msg || 'Cargando...';
    document.getElementById('bz-list').innerHTML =
        '<div class="bz-loader"><div class="spinner-border spinner-border-sm text-primary"></div><span>' + msg + '</span></div>';
}
function mostrarEmpty(msg) {
    document.getElementById('bz-list').innerHTML =
        '<div class="bz-empty"><i class="bx bx-inbox"></i><span>' + msg + '</span></div>';
}
function ocultarDetalle() {
    document.getElementById('bz-detail-head').style.display  = 'none';
    document.getElementById('bz-doc-frame').style.display    = 'none';
    document.getElementById('bz-doc-empty').style.display    = '';
}
function setStatus(msg) { document.getElementById('bz-status').textContent = msg; }
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

let _searchTimer = null;
document.getElementById('bz-search').addEventListener('input', () => {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(cargarDesdeBD, 350);
});
</script>
@endpush