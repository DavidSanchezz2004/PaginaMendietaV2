@extends('layouts.app')

@section('title', 'Buzón SOL | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<style>
/* ── Variables & Setup ────────────────────────────────────────────── */
:root {
    --bz-bg-main: #f8fafc;
    --bz-bg-panel: #ffffff;
    --bz-border: #e2e8f0;
    --bz-border-hover: #cbd5e1;
    --bz-text-main: #0f172a;
    --bz-text-muted: #64748b;
    --bz-primary: #3b82f6;
    --bz-primary-hover: #2563eb;
    --bz-success: #10b981;
    --bz-success-hover: #059669;
    --bz-radius-lg: 12px;
    --bz-radius-md: 8px;
    --bz-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --bz-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --bz-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}

/* ── Layout principal ───────────────────────────────────────────────── */
.bz-wrap            { display:flex; flex-direction:column; height:calc(100vh - 120px); overflow:hidden; background: var(--bz-bg-main); border-radius: var(--bz-radius-lg); box-shadow: var(--bz-shadow-sm); border: 1px solid var(--bz-border); font-family: 'Inter', system-ui, -apple-system, sans-serif; }
.bz-toolbar         { display:flex; align-items:center; gap:0.75rem; padding:1rem 1.25rem; background:var(--bz-bg-panel); border-bottom:1px solid var(--bz-border); flex-shrink:0; flex-wrap:wrap; z-index:10; }
.bz-split           { display:flex; flex:1; overflow:hidden; }

/* ── Panel izquierdo ────────────────────────────────────────────────── */
.bz-left            { width:40%; min-width:320px; display:flex; flex-direction:column; border-right:1px solid var(--bz-border); background:#fdfdfd; }
.bz-left-head       { padding:1.25rem 1.5rem; background:linear-gradient(135deg, #1e293b, #0f172a); color:#fff; flex-shrink:0; position:relative; overflow:hidden; }
.bz-left-head::after{ content:''; position:absolute; right:-20px; top:-20px; width:100px; height:100px; background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius:50%; pointer-events:none; }
#bz-company-name    { font-size:1.05rem; font-weight:600; letter-spacing:-0.01em; margin-bottom:0.25rem; }
#bz-company-info    { font-size:0.8rem; color:#94a3b8; font-weight:500; }

.bz-filter-bar      { display:flex; align-items:center; gap:0.75rem; padding:0.85rem 1.25rem; background:var(--bz-bg-panel); border-bottom:1px solid var(--bz-border); flex-shrink:0; }
.bz-filter-bar input{ flex:1; font-size:0.85rem; padding:0.55rem 0.85rem; border-radius:var(--bz-radius-md); border:1px solid var(--bz-border); background:#f8fafc; color:var(--bz-text-main); outline:none; transition:all 0.2s; }
.bz-filter-bar input:focus{ border-color:var(--bz-primary); background:#fff; box-shadow:0 0 0 3px rgba(59,130,246,0.1); }
.bz-filter-bar input::placeholder{ color:#94a3b8; }

.bz-tabs            { display:flex; gap:0; flex-shrink:0; background:var(--bz-bg-panel); border-bottom:1px solid var(--bz-border); }
.bz-tab             { flex:1; padding:0.85rem 0.5rem; text-align:center; font-size:0.82rem; font-weight:600; color:var(--bz-text-muted); cursor:pointer; border-bottom:2px solid transparent; transition:all 0.2s; }
.bz-tab:hover       { color:var(--bz-text-main); background:#f8fafc; }
.bz-tab.active      { color:var(--bz-primary); border-color:var(--bz-primary); background:#eff6ff; }

.bz-list            { flex:1; overflow-y:auto; padding:1rem; display:flex; flex-direction:column; gap:0.6rem; }
.bz-list::-webkit-scrollbar { width:6px; }
.bz-list::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:10px; }
.bz-list::-webkit-scrollbar-track { background:transparent; }

/* ── Tarjetas de mensaje ────────────────────────────────────────────── */
.bz-card            { border:1px solid var(--bz-border); border-radius:var(--bz-radius-md); padding:1.2rem 1.25rem; cursor:pointer; transition:all 0.25s ease; position:relative; overflow:hidden; animation: fadeIn 0.3s ease-out backwards; min-height: 80px; display: flex; flex-direction: column; justify-content: center; }
.bz-card:hover      { border-color:var(--bz-border-hover); box-shadow:var(--bz-shadow-md); transform:translateY(-1px); }
.bz-card.active     { border-color:var(--bz-primary); box-shadow:0 0 0 1px var(--bz-primary); }

.bz-card.unread { border-left: 4px solid var(--bz-primary); background: #f0f7ff; }
.bz-card.unread .bz-card-asunto { font-weight: 700; color: var(--bz-text-main); }

.bz-card:not(.unread) { background: #f8fbf9; border-left: 4px solid #6ee7b7; }
.bz-card:not(.unread) .bz-card-asunto { font-weight: 500; color: #475569; }

.bz-card-header     { display:flex; align-items:center; gap:0.6rem; margin-bottom:0.4rem; }
.bz-card-asunto     { font-size:0.9rem; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bz-card-date       { font-size:0.75rem; color:var(--bz-text-muted); white-space:nowrap; font-weight:500; }
.bz-card-sub        { font-size:0.8rem; color:var(--bz-text-muted); display:-webkit-box; -webkit-line-clamp:1; line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; }
.bz-badge           { display:inline-block; padding:0.15rem 0.6rem; border-radius:9999px; font-size:0.65rem; font-weight:600; color:#fff; letter-spacing:0.02em; text-transform:uppercase; }

/* ── Panel derecho ──────────────────────────────────────────────────── */
.bz-right           { flex:1; display:flex; flex-direction:column; background:var(--bz-bg-main); }
.bz-detail-head     { padding:1.25rem 1.75rem; background:var(--bz-bg-panel); border-bottom:1px solid var(--bz-border); flex-shrink:0; box-shadow:var(--bz-shadow-sm); z-index:5; }
.bz-detail-asunto   { font-size:1.15rem; font-weight:700; color:var(--bz-text-main); line-height:1.4; margin-bottom:0.25rem; }
.bz-detail-meta     { font-size:0.85rem; color:var(--bz-text-muted); font-weight:500; }
.bz-iframe-wrap     { flex:1; position:relative; padding:1.25rem; }
.bz-iframe-wrap iframe{ width:100%; height:100%; border:none; background:var(--bz-bg-panel); border-radius:var(--bz-radius-md); box-shadow:var(--bz-shadow-md); }
.bz-empty           { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#9ca3af; gap:1.25rem; text-align:center; padding:2rem; }
.bz-empty i         { font-size:3.5rem; color:#cbd5e1; background:#f1f5f9; padding:1.5rem; border-radius:50%; box-shadow:inset 0 2px 4px rgba(0,0,0,0.05); }
.bz-empty span      { font-size:0.95rem; font-weight:500; }

/* ── Selector de empresa ────────────────────────────────────────────── */
.bz-company-sel     { font-size:0.85rem; padding:0.6rem 2.25rem 0.6rem 1rem; border:1px solid var(--bz-border); border-radius:var(--bz-radius-md); min-width:260px; background-color:var(--bz-bg-panel); color:var(--bz-text-main); font-weight:500; cursor:pointer; outline:none; appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:16px; transition:all 0.2s; }
.bz-company-sel:hover{ border-color:var(--bz-border-hover); }
.bz-company-sel:focus{ border-color:var(--bz-primary); box-shadow:0 0 0 3px rgba(59,130,246,0.1); }

/* ── Botones ────────────────────────────────────────────────────────── */
.bz-btn             { display:inline-flex; align-items:center; justify-content:center; gap:0.4rem; padding:0.6rem 1.15rem; border-radius:var(--bz-radius-md); font-size:0.85rem; cursor:pointer; border:1px solid transparent; font-weight:600; transition:all 0.2s; }
.bz-btn i           { font-size:1.15rem; margin-top:-1px; }
.bz-btn:disabled    { opacity:0.6; cursor:not-allowed; }

.bz-btn-blue        { background:var(--bz-primary); color:#fff; box-shadow:0 1px 2px rgba(59,130,246,0.3); }
.bz-btn-blue:hover:not(:disabled){ background:var(--bz-primary-hover); transform:translateY(-1px); box-shadow:0 4px 6px rgba(59,130,246,0.25); }

.bz-btn-green       { background:var(--bz-success); color:#fff; box-shadow:0 1px 2px rgba(16,185,129,0.3); }
.bz-btn-green:hover:not(:disabled){ background:var(--bz-success-hover); transform:translateY(-1px); box-shadow:0 4px 6px rgba(16,185,129,0.25); }

.bz-btn-gray        { background:#fff; color:var(--bz-text-main); border-color:var(--bz-border); box-shadow:var(--bz-shadow-sm); }
.bz-btn-gray:hover:not(:disabled){ background:#f8fafc; border-color:var(--bz-border-hover); }

.bz-btn-sm          { padding:0.3rem 0.6rem; font-size:0.75rem; }
.bz-btn-red         { background:transparent; color:#ef4444; border:1px solid #fecaca; }
.bz-btn-red:hover:not(:disabled){ background:#fef2f2; border-color:#ef4444; }

/* ── Modal keywords ─────────────────────────────────────────────────── */
.modal-overlay      { background:rgba(15,23,42,0.5) !important; backdrop-filter:blur(4px); }
.modal-card         { border-radius:var(--bz-radius-lg) !important; box-shadow:var(--bz-shadow-lg) !important; border:1px solid var(--bz-border) !important; overflow:hidden; }
.modal-header       { border-bottom:1px solid var(--bz-border) !important; padding:1.25rem 1.5rem !important; background:#f8fafc; }
.kw-row             { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-bottom:1px solid var(--bz-border); transition:background 0.2s; }
.kw-row:hover       { background:#f8fafc; }
.kw-row:last-child  { border-bottom:none; }
.kw-color-dot       { width:12px; height:12px; border-radius:50%; flex-shrink:0; box-shadow:inset 0 0 0 1px rgba(0,0,0,0.1); }

/* ── Loader ─────────────────────────────────────────────────────────── */
.bz-loader          { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1.25rem; padding:3rem; color:var(--bz-text-muted); font-size:0.95rem; font-weight:500; text-align:center; }
.spinner-border     { display:inline-block; width:2.5rem; height:2.5rem; border:0.25em solid #cbd5e1; border-right-color:var(--bz-primary); border-radius:50%; animation:spinner-border .75s linear infinite; }
@keyframes spinner-border { to { transform: rotate(360deg); } }

/* ── Filtro leido ───────────────────────────────────────────────────── */
.bz-read-filter     { display:flex; background:#f1f5f9; padding:0.25rem; border-radius:var(--bz-radius-md); gap:0.25rem; border:1px solid #e2e8f0; }

/* ── Filtro keywords ────────────────────────────────────────────────── */
.bz-kw-bar          { display:flex; align-items:center; gap:0.5rem; padding:0.6rem 1.25rem; background:#fafafa; border-bottom:1px solid var(--bz-border); flex-shrink:0; }
.bz-kw-bar label    { font-size:.75rem; font-weight:600; color:var(--bz-text-muted); white-space:nowrap; }
.bz-kw-bar select   { flex:1; font-size:.8rem; padding:.35rem .6rem; border:1px solid var(--bz-border); border-radius:var(--bz-radius-md); background:#fff; color:var(--bz-text-main); outline:none; cursor:pointer; transition:border-color .2s; }
.bz-kw-bar select:focus { border-color:var(--bz-primary); box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.bz-read-filter button{ flex:1; padding:0.45rem 0.75rem; font-size:0.75rem; font-weight:600; border:none; border-radius:6px; background:transparent; color:var(--bz-text-muted); cursor:pointer; transition:all 0.2s; white-space:nowrap; }
.bz-read-filter button:hover:not(.active){ color:var(--bz-text-main); background:rgba(255,255,255,0.5); }
.bz-read-filter button.active{ background:#fff; color:var(--bz-primary); box-shadow:0 1px 3px rgba(0,0,0,0.1); }

/* ── Status ─────────────────────────────────────────────────────────── */
#bz-status { background:#f1f5f9; padding:0.45rem 0.85rem; border-radius:20px; font-weight:500; color:var(--bz-text-muted); font-size:0.75rem; letter-spacing:0.01em; margin-left:auto; }

/* ── Animaciones ────────────────────────────────────────────────────── */
@keyframes fadeIn {
    from { opacity:0; transform:translateY(8px); }
    to { opacity:1; transform:translateY(0); }
}
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

        <div style="position:relative;" id="leer-todo-wrap">
            <button id="btn-leer-todo" class="bz-btn bz-btn-gray" disabled>
                <i class="bx bx-check-double"></i> Leer todo
            </button>
            <div id="leer-todo-panel" style="display:none;position:absolute;top:calc(100% + 6px);left:0;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,.12);padding:1rem;z-index:200;min-width:190px;">
                <div style="font-size:.75rem;font-weight:700;color:#64748b;margin-bottom:.6rem;text-transform:uppercase;letter-spacing:.04em;">Marcar leídos por año</div>
                <select id="leer-todo-anio" style="width:100%;padding:.4rem .5rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;margin-bottom:.6rem;outline:none;"></select>
                <button onclick="ejecutarLeerTodo()" class="bz-btn bz-btn-blue" style="width:100%;justify-content:center;">
                    <i class="bx bx-check"></i> Confirmar
                </button>
            </div>
        </div>

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
                    <button data-val="0" onclick="setReadFilter(this,'0')">No leídos</button>
                    <button data-val="1" onclick="setReadFilter(this,'1')">Leídos</button>
                </div>
            </div>

            <div class="bz-kw-bar">
                <label><i class="bx bx-tag"></i> Keyword:</label>
                <select id="bz-kw-filter" onchange="setKwFilter(this.value)">
                    <option value="">Todas</option>
                </select>
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
            <div style="margin-bottom:.6rem;">
                <input type="text" id="kw-filter" placeholder="Filtrar palabras clave..."
                    oninput="filtrarKeywords(this.value)"
                    style="width:100%;padding:.4rem .65rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;outline:none;">
            </div>
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
    kwFilter:      '',
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
    document.getElementById('btn-iniciar').disabled  = false;
    document.getElementById('btn-sync').disabled     = true;
    document.getElementById('btn-leer-todo').disabled = false;
    setStatus('');
    ocultarDetalle();
    cargarDesdeBD();
});

function resetPanel() {
    BZ.companyId  = null;
    BZ.kwFilter   = '';
    document.getElementById('bz-kw-filter').value    = '';
    document.getElementById('bz-company-name').textContent = 'Sin empresa seleccionada';
    document.getElementById('bz-company-info').textContent = 'Selecciona una empresa arriba';
    document.getElementById('btn-iniciar').disabled   = true;
    document.getElementById('btn-sync').disabled      = true;
    document.getElementById('btn-leer-todo').disabled = true;
    mostrarEmpty('Selecciona una empresa para ver sus mensajes');
    ocultarDetalle();
}

/* ── BD ─────────────────────────────────────────────────────────────── */
function cargarDesdeBD() {
    if (!BZ.companyId) return;
    mostrarLoader();
    const q   = encodeURIComponent(document.getElementById('bz-search').value);
    const url = BZ.urlLista + '?tipo=' + BZ.tipo + '&q=' + q + '&leido=' + BZ.leidoFilter + '&kw=' + encodeURIComponent(BZ.kwFilter);
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
            ' data-asunto="' + escHtml(m.asunto ?? '') + '" data-remitente="' + escHtml(m.remitente || 'SUNAT') + '"' +
            ' data-fecha="' + (m.fecha ?? '') + '" data-tiene-doc="' + (m.tiene_documento ?? 1) + '" onclick="abrirMensaje(this)">' +
            '<div class="bz-card-header">' +
            '<span class="bz-card-asunto" title="' + escHtml(m.asunto ?? '') + '">' + escHtml(m.asunto ?? '(sin asunto)') + '</span>' +
            badge +
            '<span class="bz-card-date">' + (m.fecha ?? '') + '</span>' +
            '</div>' +
            '<div class="bz-card-sub">' + escHtml(m.remitente || 'SUNAT') + '</div>' +
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

    const tieneDoc = card.dataset.tieneDoc;
    const frame = document.getElementById('bz-doc-frame');
    const emptyUI = document.getElementById('bz-doc-empty');
    
    if (tieneDoc == '0' || tieneDoc === 'false') {
        frame.style.display = 'none';
        emptyUI.style.display = '';
        emptyUI.innerHTML = '<i class="bx bx-text"></i><span>Este mensaje es solo de texto o no incluye PDF/HTML.</span>';
    } else {
        const urlDoc = BZ.urlLista.replace('/lista/', '/documento/') + '/' + card.dataset.cod;
        frame.src = urlDoc;
        frame.style.display = '';
        emptyUI.style.display = 'none';
    }

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

function setKwFilter(val) {
    BZ.kwFilter = val;
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
    document.getElementById('kw-filter').value = '';
    filtrarKeywords('');
}

let _allKws = [];

function cargarKeywords() {
    const urlKw = '{{ route("bandeja-sunat.keywords") }}';
    fetch(urlKw, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            _allKws = data.keywords;
            filtrarKeywords(document.getElementById('kw-filter').value);
            // Poblar el select de filtro con las keywords del usuario
            const sel     = document.getElementById('bz-kw-filter');
            const current = sel.value;
            sel.innerHTML = '<option value="">Todas</option>';
            _allKws.forEach(kw => {
                const opt = document.createElement('option');
                opt.value       = kw.palabra;
                opt.textContent = kw.palabra.charAt(0).toUpperCase() + kw.palabra.slice(1);
                if (kw.color) opt.style.color = kw.color;
                sel.appendChild(opt);
            });
            // Mantener selección activa si sigue existiendo
            if ([...sel.options].some(o => o.value === current)) sel.value = current;
        });
}

function renderizarKeywords(kws) {
    const list = document.getElementById('kw-list');
    if (!kws.length) {
        list.innerHTML = '<div class="text-muted py-3 text-center" style="font-size:.8rem;">Sin palabras clave</div>';
        return;
    }
    list.innerHTML = kws.map(kw =>
        '<div class="kw-row" id="kw-row-' + kw.id + '">' +
        '<div class="kw-color-dot" style="background:' + kw.color + '"></div>' +
        '<span style="flex:1;font-size:.82rem;">' + escHtml(kw.palabra) + '</span>' +
        '<span class="bz-badge" style="background:' + kw.color + '">' + kw.prioridad.toUpperCase() + '</span>' +
        '<button class="bz-btn bz-btn-red bz-btn-sm ms-1" onclick="eliminarKeyword(' + kw.id + ')">' +
        '<i class="bx bx-trash"></i></button></div>'
    ).join('');
}

function filtrarKeywords(q) {
    const filtered = q.trim()
        ? _allKws.filter(kw =>
            kw.palabra.toLowerCase().includes(q.toLowerCase()) ||
            kw.prioridad.toLowerCase().includes(q.toLowerCase()))
        : _allKws;
    renderizarKeywords(filtered);
}

function agregarKeyword() {
    const palabra   = document.getElementById('kw-input').value.trim();
    const prioridad = document.getElementById('kw-prioridad').value;
    const color     = document.getElementById('kw-color').value;
    if (!palabra) { Swal.fire({icon:'warning', title:'Campo requerido', text:'Escribe una palabra clave.'}); return; }
    const urlKw = '{{ route("bandeja-sunat.keywords.store") }}';
    fetch(urlKw, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ palabra, prioridad, color }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) { Swal.fire({icon:'error', title:'Error', text: data.message ?? 'Error al agregar'}); return; }
        document.getElementById('kw-input').value = '';
        document.getElementById('kw-filter').value = '';
        cargarKeywords();
        if (BZ.companyId) cargarDesdeBD();
    });
}

async function eliminarKeyword(id) {
    const elimResult = await Swal.fire({
      title: '¿Eliminar palabra clave?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, eliminar'
    });
    if (!elimResult.isConfirmed) return;
    const urlDel = '{{ url("bandeja-sunat/keywords") }}/' + id;
    fetch(urlDel, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            _allKws = _allKws.filter(k => k.id !== id);
            filtrarKeywords(document.getElementById('kw-filter').value);
            if (BZ.companyId) cargarDesdeBD();
        }
    });
}

/* ── Leer todo por año ──────────────────────────────────────────────── */
(function poblarAnios() {
    const sel = document.getElementById('leer-todo-anio');
    [2026, 2025].forEach(y => {
        const opt = document.createElement('option');
        opt.value = y; opt.textContent = y;
        sel.appendChild(opt);
    });
})();

document.getElementById('btn-leer-todo').addEventListener('click', (e) => {
    e.stopPropagation();
    const panel = document.getElementById('leer-todo-panel');
    panel.style.display = panel.style.display === 'none' ? '' : 'none';
});

document.addEventListener('click', (e) => {
    if (!document.getElementById('leer-todo-wrap').contains(e.target)) {
        document.getElementById('leer-todo-panel').style.display = 'none';
    }
});

async function ejecutarLeerTodo() {
    if (!BZ.companyId) return;
    const anio        = document.getElementById('leer-todo-anio').value;
    const urlLeerTodo = BZ.urlLista.replace('/lista/', '/leer-todo/');
    document.getElementById('leer-todo-panel').style.display = 'none';
    setStatus('Marcando año ' + anio + '...');
    try {
        const r    = await fetch(urlLeerTodo, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ anio }),
        });
        const data = await r.json();
        if (data.ok) {
            setStatus(data.marcados + ' mensajes marcados como leídos (' + anio + ')');
            cargarDesdeBD();
        } else {
            setStatus('Error: ' + (data.error ?? 'desconocido'));
        }
    } catch {
        setStatus('Error de red');
    }
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
    const emptyUI = document.getElementById('bz-doc-empty');
    emptyUI.style.display    = '';
    emptyUI.innerHTML = '<i class="bx bx-file-blank"></i><span>Selecciona un mensaje para ver su contenido</span>';
}
function setStatus(msg) { document.getElementById('bz-status').textContent = msg; }
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Poblar el select de keywords al cargar la página
cargarKeywords();

let _searchTimer = null;
document.getElementById('bz-search').addEventListener('input', () => {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(cargarDesdeBD, 350);
});
</script>
@endpush