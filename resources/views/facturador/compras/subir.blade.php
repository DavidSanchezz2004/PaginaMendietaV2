@extends('layouts.app')

@section('title', 'Subir Comprobantes — Facturador')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .drop-zone {
      border: 2.5px dashed #cbd5e1;
      border-radius: 14px;
      padding: 3rem 2rem;
      text-align: center;
      background: #f8fafc;
      cursor: pointer;
      transition: all .25s;
      position: relative;
    }
    .drop-zone:hover, .drop-zone.drag-over {
      border-color: #3b82f6;
      background: #eff6ff;
    }
    .drop-zone.drag-over { transform: scale(1.01); }
    .drop-zone input[type="file"] {
      position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%;
    }
    .drop-zone__icon { font-size: 3rem; color: #94a3b8; display: block; margin-bottom: 1rem; }
    .drop-zone.drag-over .drop-zone__icon { color: #3b82f6; }
    .token-box {
      font-family: 'Courier New', monospace; font-size: .82rem;
      background: #1e293b; color: #7dd3fc; padding: 1rem 1.25rem;
      border-radius: 8px; word-break: break-all;
    }
    .step-num {
      display: inline-flex; align-items: center; justify-content: center;
      width: 28px; height: 28px; border-radius: 50%;
      background: #3b82f6; color: #fff; font-size: .8rem; font-weight: 700;
      flex-shrink: 0;
    }
    .subir-grid {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 1.5rem;
      align-items: start;
    }
    @media (max-width: 900px) {
      .subir-grid { grid-template-columns: 1fr; }
    }
    .subir-card {
      border-radius: 14px;
      box-shadow: 0 1px 8px rgba(0,0,0,.08);
      background: #fff;
      margin-bottom: 1.25rem;
      overflow: hidden;
    }
    .subir-card-header {
      padding: .85rem 1.25rem;
      border-bottom: 1px solid #f1f5f9;
      font-weight: 600;
      font-size: .9rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .subir-card-body { padding: 1.25rem; }
    .field-input {
      width: 100%;
      padding: .5rem .75rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: .9rem;
      outline: none;
      transition: border-color .2s;
      box-sizing: border-box;
    }
    .field-input:focus { border-color: #3b82f6; }
    .field-label { font-size: .83rem; font-weight: 600; color: #374151; margin-bottom: .3rem; display: block; }
    .field-group { margin-bottom: .9rem; }
    .preview-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .75rem;
    }
    @media (max-width: 600px) { .preview-grid { grid-template-columns: 1fr; } }
    .spinner-ring {
      display: inline-block;
      width: 18px; height: 18px;
      border: 2.5px solid #fff;
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin .7s linear infinite;
      vertical-align: middle;
      margin-right: .35rem;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .alert-box {
      padding: .75rem 1rem;
      border-radius: 8px;
      font-size: .88rem;
      margin-bottom: 1rem;
    }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
    .method-badge {
      font-size: .7rem; font-weight: 700; padding: .15rem .45rem;
      border-radius: 4px; white-space: nowrap;
    }
    .method-post { background: #fef3c7; color: #b45309; }
    .items-table {
      width: 100%;
      border-collapse: collapse;
      font-size: .83rem;
    }
    .items-table th {
      background: #f1f5f9;
      padding: .45rem .6rem;
      text-align: left;
      font-weight: 600;
      color: #374151;
      white-space: nowrap;
    }
    .items-table td {
      padding: .4rem .6rem;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: top;
    }
    .items-table tbody tr:hover { background: #f8fafc; }
    .items-table input {
      width: 100%;
      padding: .25rem .4rem;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: .82rem;
      box-sizing: border-box;
    }
    .items-table input:focus { border-color: #3b82f6; outline: none; }
    .items-section { overflow-x: auto; }
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
      <span class="menu-label">MENU PRINCIPAL</span>
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
        <div class="placeholder-content module-card-wide" style="padding:1.5rem;">

  {{-- Toolbar --}}
  <div class="module-toolbar" style="margin-bottom:1.5rem;">
    <div>
      <h4 style="font-weight:700; margin:0; font-size:1.15rem;">
        <i class='bx bx-cloud-upload' style="margin-right:.4rem;"></i>Subir / Importar Comprobantes
      </h4>
      <p style="color:#6b7280; font-size:.85rem; margin:.2rem 0 0;">
        Sube un PDF y la IA extrae los datos automaticamente
      </p>
    </div>
    <a href="{{ route('facturador.compras.index') }}" class="btn-secondary">
      <i class='bx bx-arrow-back'></i> Volver a Compras
    </a>
  </div>

  @foreach(['success','warning','error'] as $f)
    @if(session($f))
      <div class="alert-box alert-{{ $f === 'error' ? 'danger' : $f }}" style="margin-bottom:1rem;">
        {{ session($f) }}
      </div>
    @endif
  @endforeach

  @php $isAdmin = in_array(auth()->user()?->role?->value, ['admin','supervisor']); @endphp
  <div class="subir-grid" style="{{ $isAdmin ? '' : 'grid-template-columns:1fr;' }}">

    <div>

      {{-- Subir PDF con IA --}}
      <div class="subir-card">
        <div class="subir-card-header">
          <i class='bx bx-brain' style="color:#8b5cf6;"></i>
          Subir PDF - Extraccion automatica con IA
        </div>
        <div class="subir-card-body">

          <div id="dropZone" class="drop-zone">
            <input type="file" id="pdfInput" accept=".pdf">
            <i class='bx bx-file-blank drop-zone__icon'></i>
            <p style="font-weight:600; margin:.5rem 0 .25rem;">Arrastra el PDF aqui o haz clic para seleccionar</p>
            <p style="color:#6b7280; font-size:.85rem; margin:0;">Solo archivos PDF - Maximo 10 MB</p>
          </div>

          <div id="selectedFileInfo" style="display:none; margin-top:.75rem; padding:.65rem .9rem;
               border-radius:8px; background:#f0fdf4; border:1px solid #bbf7d0;
               align-items:center; gap:.75rem;">
            <i class='bx bxs-file-pdf' style="color:#059669; font-size:1.4rem;"></i>
            <span id="selectedFileName" style="flex:1; font-size:.88rem; font-weight:500;"></span>
            <span id="selectedFileSize" style="color:#6b7280; font-size:.82rem;"></span>
            <button type="button" onclick="clearFile()" style="background:none; border:none; cursor:pointer; color:#dc2626; font-size:1.1rem; padding:0;">
              <i class='bx bx-x'></i>
            </button>
          </div>

          <button type="button" id="btnExtraer" class="btn-primary" disabled
                  style="margin-top:1rem; width:100%;">
            <i class='bx bx-search-alt'></i> Extraer datos con IA
          </button>

          <div id="extractResult" style="margin-top:1rem;"></div>
        </div>
      </div>

      {{-- Preview de datos extraidos --}}
      <div id="previewSection" style="display:none;">
        <div class="subir-card">
          <div class="subir-card-header" style="background:#f0fdf4;">
            <i class='bx bx-check-circle' style="color:#059669;"></i>
            Revisar y confirmar datos extraidos
          </div>
          <div class="subir-card-body">
            <div class="alert-box alert-info" style="margin-bottom:1.25rem;">
              <i class='bx bx-info-circle'></i>
              Revisa que los datos sean correctos antes de importar. Puedes editarlos si hay algun error.
            </div>

            <form method="POST" action="{{ route('facturador.compras.subir.pdf.confirmar') }}" id="confirmForm">
              @csrf

              <div class="preview-grid">
                <div class="field-group">
                  <label class="field-label">RUC Proveedor *</label>
                  <input type="text" name="numero_doc_proveedor" id="f_ruc" class="field-input" required>
                </div>
                <div class="field-group">
                  <label class="field-label">Razon Social *</label>
                  <input type="text" name="razon_social_proveedor" id="f_razon" class="field-input" required>
                </div>
                <div class="field-group">
                  <label class="field-label">Tipo Comprobante *</label>
                  <select name="codigo_tipo_documento" id="f_tipo" class="field-input" required>
                    <option value="01">01 - Factura</option>
                    <option value="03">03 - Boleta</option>
                    <option value="07">07 - Nota de Credito</option>
                    <option value="08">08 - Nota de Debito</option>
                  </select>
                </div>
                <div class="field-group">
                  <label class="field-label">Serie *</label>
                  <input type="text" name="serie_documento" id="f_serie" class="field-input" required>
                </div>
                <div class="field-group">
                  <label class="field-label">Numero *</label>
                  <input type="text" name="numero_documento" id="f_numero" class="field-input" required>
                </div>
                <div class="field-group">
                  <label class="field-label">Moneda</label>
                  <select name="codigo_moneda" id="f_moneda" class="field-input">
                    <option value="PEN">PEN - Soles</option>
                    <option value="USD">USD - Dolares</option>
                  </select>
                </div>
                <div class="field-group">
                  <label class="field-label">Fecha Emision *</label>
                  <input type="date" name="fecha_emision" id="f_fecha_emision" class="field-input" required>
                </div>
                <div class="field-group">
                  <label class="field-label">Fecha Vencimiento</label>
                  <input type="date" name="fecha_vencimiento" id="f_fecha_vcto" class="field-input">
                </div>
                <div class="field-group">
                  <label class="field-label">Base Imponible (sin IGV)</label>
                  <input type="number" step="0.01" name="base_imponible_gravadas" id="f_base" class="field-input">
                </div>
                <div class="field-group">
                  <label class="field-label">IGV</label>
                  <input type="number" step="0.01" name="igv_gravadas" id="f_igv" class="field-input">
                </div>
                <div class="field-group" style="grid-column:span 2;">
                  <label class="field-label" style="color:#059669; font-size:.9rem;">Total *</label>
                  <input type="number" step="0.01" name="monto_total" id="f_total" class="field-input"
                         style="font-size:1.1rem; font-weight:700; border-color:#059669;" required>
                </div>
                <div class="field-group">
                  <label class="field-label" style="color:#b45309;">
                    <i class='bx bx-info-circle'></i> Sujeto a Detracción (SPOT)
                  </label>
                  <select name="es_sujeto_detraccion" id="f_es_detrac" class="field-input" onchange="toggleDetraccionInfo()">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                  </select>
                </div>
              </div>

              {{-- Información detallada de SPOT/Detracción --}}
              <div id="detraccionSection" style="display:none; margin-top:1rem; border-top:1.5px dashed #f59e0b; padding-top:1rem;">
                <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.75rem;">
                  <i class='bx bx-receipt' style="color:#b45309; font-size:1.1rem;"></i>
                  <span style="font-weight:700; font-size:.88rem; color:#374151;">Detalles de Detracción (SPOT)</span>
                </div>
                <div class="preview-grid">
                  <div class="field-group" style="grid-column:span 2;">
                    <label class="field-label">Leyenda / Descripción SPOT</label>
                    <textarea name="detraccion_leyenda" id="f_detrac_leyenda" class="field-input"
                              placeholder="Ej: Operación sujeta al Sistema de Pago de Obligaciones Tributarias..."
                              style="min-height:60px; resize:vertical;"></textarea>
                  </div>
                  <div class="field-group">
                    <label class="field-label">Código Bien/Servicio</label>
                    <input type="text" name="detraccion_bien_codigo" id="f_detrac_bien_cod" class="field-input"
                           placeholder="Ej: 027">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Bien o Servicio</label>
                    <input type="text" name="detraccion_bien_descripcion" id="f_detrac_bien_desc" class="field-input"
                           placeholder="Ej: Servicio de transporte de carga">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Medio de Pago</label>
                    <input type="text" name="detraccion_medio_pago" id="f_detrac_medio" class="field-input"
                           placeholder="Ej: 001 Depósito en cuenta">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Nro. Cta. B.N.</label>
                    <input type="text" name="detraccion_numero_cuenta" id="f_detrac_cuenta" class="field-input"
                           placeholder="Ej: 00042032913">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Monto Detracción (S/.)</label>
                    <input type="number" step="0.01" name="monto_detraccion" id="f_monto_detrac" class="field-input"
                           placeholder="0.00" onchange="calcularMontoNeto()">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Porcentaje (%)</label>
                    <input type="number" step="0.01" name="detraccion_porcentaje" id="f_detrac_porc" class="field-input"
                           placeholder="Ej: 4.00" onchange="calcularMontoNeto()">
                  </div>
                  <div class="field-group">
                    <label class="field-label" style="color:#059669; font-weight:700;">Deuda Neta (Total - Detracción)</label>
                    <input type="number" step="0.01" name="monto_neto_detraccion" id="f_neto_detrac" class="field-input"
                           readonly style="background:#f0fdf4; border-color:#059669; color:#059669; font-weight:700;">
                  </div>
                </div>
                <input type="hidden" name="informacion_detraccion_json" id="f_detrac_json">
              </div>

              {{-- Items / líneas de detalle --}}
              <div id="itemsSection" style="display:none; margin-top:.5rem;">
                <div style="font-weight:600; font-size:.88rem; color:#374151; margin-bottom:.5rem;">
                  <i class='bx bx-list-ul' style="color:#3b82f6;"></i> Líneas de detalle
                </div>
                <div class="items-section">
                  <table class="items-table" id="itemsTable">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Valor Unit.</th>
                        <th>Descuento</th>
                        <th>Importe Venta</th>
                        <th>ICBPER</th>
                      </tr>
                    </thead>
                    <tbody id="itemsTbody"></tbody>
                  </table>
                </div>
                <input type="hidden" name="items_json" id="items_json">
              </div>

              {{-- Sugerencias contables de la IA --}}
              <div id="contableSection" style="display:none; margin-top:1rem; border-top:1.5px dashed #d1fae5; padding-top:1rem;">
                <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.75rem;">
                  <i class='bx bx-brain' style="color:#8b5cf6; font-size:1.1rem;"></i>
                  <span style="font-weight:700; font-size:.88rem; color:#374151;">Información Contable</span>
                  <span style="font-size:.72rem; background:#f3e8ff; color:#7c3aed; padding:.1rem .45rem; border-radius:4px; font-weight:600;">sugerida por IA — editable</span>
                </div>
                <div class="preview-grid">
                  <div class="field-group">
                    <label class="field-label">Tipo de Operación</label>
                    <select name="contable_tipo_operacion" id="f_tipo_op" class="field-input">
                      <option value="">-- Sin especificar --</option>
                      <option value="0401">0401 - Compra interna</option>
                      <option value="0402">0402 - Compra a no domiciliados</option>
                      <option value="0403">0403 - Importación definitiva</option>
                      <option value="0405">0405 - Anticipos</option>
                      <option value="0409">0409 - DUA</option>
                      <option value="0412">0412 - Nota de crédito</option>
                      <option value="0413">0413 - Nota de débito</option>
                    </select>
                  </div>
                  <div class="field-group">
                    <label class="field-label">Tipo de Compra</label>
                    <select name="contable_tipo_compra" id="f_tipo_compra" class="field-input">
                      <option value="">-- Sin especificar --</option>
                      <option value="NG">NG - Gravadas</option>
                      <option value="NI">NI - No Gravadas</option>
                      <option value="EX">EX - Exportación</option>
                      <option value="GR">GR - Gratuitas</option>
                      <option value="MX">MX - Mixtas</option>
                    </select>
                  </div>
                  <div class="field-group">
                    <label class="field-label">Cuenta Contable</label>
                    <input type="text" name="contable_cuenta_contable" id="f_cuenta" class="field-input" placeholder="Ej: 60, 421, 632...">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Cód. Producto/Servicio</label>
                    <input type="text" name="contable_codigo_producto_servicio" id="f_cod_ps" class="field-input" placeholder="Ej: 84131500...">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Forma de Pago</label>
                    <select name="contable_forma_pago" id="f_forma_pago" class="field-input">
                      <option value="">-- Sin especificar --</option>
                      <option value="01">01 - Contado</option>
                      <option value="02">02 - Crédito</option>
                      <option value="03">03 - Efectivo</option>
                      <option value="04">04 - Yape</option>
                      <option value="05">05 - Plin</option>
                      <option value="06">06 - Banco / Transferencia</option>
                      <option value="07">07 - BCP</option>
                      <option value="08">08 - BBVA</option>
                    </select>
                  </div>
                  <div class="field-group">
                    <label class="field-label">Glosa</label>
                    <input type="text" name="contable_glosa" id="f_glosa" class="field-input" placeholder="Descripción libre...">
                  </div>
                </div>
              </div>

              <div style="display:flex; gap:.75rem; margin-top:1rem;">
                <button type="submit" class="btn-primary" style="flex:1;">
                  <i class='bx bx-cloud-download'></i> Importar comprobante
                </button>
                <button type="button" onclick="resetForm()" class="btn-secondary">
                  <i class='bx bx-x'></i> Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>

    {{-- Columna lateral: solo visible para admin/supervisor --}}
    @if(auth()->user()?->role?->value === 'admin' || auth()->user()?->role?->value === 'supervisor')
    <div>

      <div class="subir-card">
        <div class="subir-card-header">
          <i class='bx bx-key' style="color:#f59e0b;"></i> API Token
        </div>
        <div class="subir-card-body">
          @if($company->api_token)
            <div class="token-box" id="tokenBox">{{ $company->api_token }}</div>
            <div style="display:flex; gap:.5rem; margin-top:.75rem;">
              <button type="button" onclick="copiarToken()" class="btn-secondary" style="font-size:.82rem; padding:.35rem .7rem;">
                <i class='bx bx-copy'></i> Copiar
              </button>
              <form method="POST" action="{{ route('api.empresa.generar-token') }}"
                    data-confirm="¿Regenerar el token? Se invalidará el anterior."
                    style="display:inline;">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">
                <button type="submit" style="font-size:.82rem; padding:.35rem .7rem;
                        border:1px solid #fca5a5; background:#fff; color:#dc2626;
                        border-radius:8px; cursor:pointer;">
                  <i class='bx bx-refresh'></i> Regenerar
                </button>
              </form>
            </div>
          @else
            <p style="color:#6b7280; font-size:.85rem; margin:0 0 .75rem;">Sin token generado.</p>
            <form method="POST" action="{{ route('api.empresa.generar-token') }}">
              @csrf
              <input type="hidden" name="company_id" value="{{ $company->id }}">
              <button type="submit" class="btn-primary" style="font-size:.83rem;">
                <i class='bx bx-plus'></i> Generar Token
              </button>
            </form>
          @endif
        </div>
      </div>

      <div class="subir-card">
        <div class="subir-card-header">
          <i class='bx bx-transfer'></i> Endpoint API
        </div>
        <div class="subir-card-body">
          <p style="font-size:.83rem; color:#6b7280; margin:0 0 .75rem;">
            Para integrar desde sistemas externos:<br>
            <code style="font-size:.8rem; background:#f1f5f9; padding:.15rem .4rem; border-radius:4px;">
              Authorization: Bearer {token}
            </code>
          </p>
          <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.3rem;">
            <span class="method-badge method-post">POST</span>
            <code style="font-size:.78rem; background:#f1f5f9; padding:.2rem .4rem; border-radius:4px; flex:1; word-break:break-all;">
              {{ url('api/compras/importar') }}
            </code>
          </div>
        </div>
      </div>

      <div class="subir-card">
        <div class="subir-card-header">
          <i class='bx bx-chip' style="color:#8b5cf6;"></i> Configuracion IA
        </div>
        <div class="subir-card-body">
          <div class="field-group">
            <span class="field-label">Modelo activo</span>
            <code style="font-size:.85rem; background:#f1f5f9; padding:.3rem .6rem; border-radius:6px; display:block;">
              {{ config('services.openai.model', 'gpt-4o-mini') }}
            </code>
          </div>
          <p style="font-size:.8rem; color:#6b7280; margin:0;">
            Cambia en <code>.env</code> con <code>OPENAI_MODEL</code>
          </p>
        </div>
      </div>

    </div>
    @endif
  </div>

        </div>
      </div>
    </main>
  </section>
</div>
@endsection

@push('scripts')
<script>
const dropZone   = document.getElementById('dropZone');
const fileInput  = document.getElementById('pdfInput');
const fileInfo   = document.getElementById('selectedFileInfo');
const fileName   = document.getElementById('selectedFileName');
const fileSize   = document.getElementById('selectedFileSize');
const btnExtraer = document.getElementById('btnExtraer');
let currentFile  = null;

['dragover','dragenter'].forEach(ev => dropZone.addEventListener(ev, e => {
  e.preventDefault(); dropZone.classList.add('drag-over');
}));
['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => {
  e.preventDefault(); dropZone.classList.remove('drag-over');
  if (e.type === 'drop' && e.dataTransfer.files.length) setFile(e.dataTransfer.files[0]);
}));
fileInput.addEventListener('change', () => { if (fileInput.files.length) setFile(fileInput.files[0]); });

function setFile(f) {
  if (f.type !== 'application/pdf') { Swal.fire({icon:'warning', title:'Archivo inválido', text:'Solo se aceptan archivos PDF.'}); return; }
  if (f.size > 10 * 1024 * 1024)    { Swal.fire({icon:'warning', title:'Archivo demasiado grande', text:'El archivo supera los 10 MB.'}); return; }
  currentFile = f;
  fileName.textContent = f.name;
  fileSize.textContent = (f.size / 1024 / 1024).toFixed(2) + ' MB';
  fileInfo.style.display = 'flex';
  btnExtraer.disabled = false;
  document.getElementById('previewSection').style.display = 'none';
  document.getElementById('extractResult').innerHTML = '';
}

function clearFile() {
  currentFile = null;
  fileInput.value = '';
  fileInfo.style.display = 'none';
  btnExtraer.disabled = true;
  document.getElementById('previewSection').style.display = 'none';
  document.getElementById('extractResult').innerHTML = '';
}

btnExtraer.addEventListener('click', async () => {
  if (!currentFile) return;

  const resultBox = document.getElementById('extractResult');
  btnExtraer.disabled = true;
  btnExtraer.innerHTML = '<span class="spinner-ring"></span> Extrayendo con IA...';
  resultBox.innerHTML  = '';
  document.getElementById('previewSection').style.display = 'none';

  const fd = new FormData();
  fd.append('pdf', currentFile);
  fd.append('_token', '{{ csrf_token() }}');

  try {
    const res  = await fetch('{{ route("facturador.compras.subir.pdf.extraer") }}', {
      method: 'POST', body: fd,
    });

    // Parsear respuesta de forma segura
    let data;
    const contentType = res.headers.get('content-type') ?? '';
    if (contentType.includes('application/json')) {
      data = await res.json();
    } else {
      // El servidor devolvio HTML (error inesperado)
      resultBox.innerHTML = `<div class="alert-box alert-danger">
        <i class='bx bx-error'></i> No se pudo procesar el PDF. Por favor intenta de nuevo o contacta al administrador.
      </div>`;
      return;
    }

    if (!res.ok) {
      resultBox.innerHTML = `<div class="alert-box alert-danger">
        <i class='bx bx-error'></i> ${escHtml(data.error ?? 'No se pudo extraer la informacion del PDF.')}
      </div>`;
    } else {
      fillPreview(data);
      document.getElementById('previewSection').style.display = 'block';
      document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
      resultBox.innerHTML = `<div class="alert-box alert-success">
        <i class='bx bx-check-circle'></i> Extraccion completada. Revisa y confirma los datos.
      </div>`;
    }
  } catch (err) {
    resultBox.innerHTML = `<div class="alert-box alert-danger">
      <i class='bx bx-error'></i> No se pudo conectar con el servidor. Intenta de nuevo.
    </div>`;
  }

  btnExtraer.disabled = false;
  btnExtraer.innerHTML = "<i class='bx bx-search-alt'></i> Extraer datos con IA";
});

function fillPreview(d) {
  setValue('f_ruc',           d.numero_doc_proveedor   ?? '');
  setValue('f_razon',         d.razon_social_proveedor ?? '');
  setValue('f_tipo',          d.codigo_tipo_documento  ?? '01');
  setValue('f_serie',         d.serie_documento        ?? '');
  setValue('f_numero',        d.numero_documento       ?? '');
  setValue('f_moneda',        d.codigo_moneda          ?? 'PEN');
  setValue('f_fecha_emision', d.fecha_emision          ?? '');
  setValue('f_fecha_vcto',    d.fecha_vencimiento      ?? '');
  setValue('f_base',          d.base_imponible_gravadas ?? '');
  setValue('f_igv',           d.igv_gravadas           ?? '');
  setValue('f_total',         d.monto_total            ?? '');
  
  // Información de detracción (SPOT)
  setValue('f_es_detrac',     d.es_sujeto_detraccion ? '1' : '0');
  if (d.es_sujeto_detraccion && d.informacion_detraccion) {
    const det = d.informacion_detraccion;
    setValue('f_detrac_leyenda',     det.leyenda ?? '');
    setValue('f_detrac_bien_cod',    det.bien_codigo ?? '');
    setValue('f_detrac_bien_desc',   det.bien_descripcion ?? '');
    setValue('f_detrac_medio',       det.medio_pago ?? '');
    setValue('f_detrac_cuenta',      det.numero_cuenta ?? '');
    setValue('f_detrac_porc',        det.porcentaje ?? '');
  }
  setValue('f_monto_detrac',  d.monto_detraccion ?? '');
  
  // Mostrar/ocultar sección de detracción y calcular neto
  toggleDetraccionInfo();

  fillItems(d.items ?? []);

  // Campos contables sugeridos por IA
  const hasContable = d.contable_tipo_operacion || d.contable_tipo_compra ||
                      d.contable_cuenta_contable || d.contable_forma_pago;
  if (hasContable) {
    setValue('f_tipo_op',     d.contable_tipo_operacion          ?? '');
    setValue('f_tipo_compra', d.contable_tipo_compra             ?? '');
    setValue('f_cuenta',      d.contable_cuenta_contable         ?? '');
    setValue('f_cod_ps',      d.contable_codigo_producto_servicio ?? '');
    setValue('f_forma_pago',  d.contable_forma_pago              ?? '');
    setValue('f_glosa',       d.contable_glosa                   ?? '');
    document.getElementById('contableSection').style.display = 'block';
  } else {
    document.getElementById('contableSection').style.display = 'none';
  }
}

function fillItems(items) {
  const tbody   = document.getElementById('itemsTbody');
  const section = document.getElementById('itemsSection');
  const hidden  = document.getElementById('items_json');

  tbody.innerHTML = '';

  if (!items || items.length === 0) {
    section.style.display = 'none';
    hidden.value = '[]';
    return;
  }

  items.forEach((item, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color:#6b7280; font-size:.78rem;">${i + 1}</td>
      <td><input type="text" data-field="descripcion"   value="${escHtml(item.descripcion ?? '')}"></td>
      <td><input type="text" data-field="unidad_medida" value="${escHtml(item.unidad_medida ?? '')}" style="width:80px;"></td>
      <td><input type="number" step="0.0001" data-field="cantidad"       value="${item.cantidad ?? 0}"       style="width:80px;"></td>
      <td><input type="number" step="0.000001" data-field="valor_unitario" value="${item.valor_unitario ?? 0}" style="width:90px;"></td>
      <td><input type="number" step="0.01" data-field="descuento"      value="${item.descuento ?? 0}"      style="width:80px;"></td>
      <td><input type="number" step="0.0001" data-field="importe_venta"  value="${item.importe_venta ?? 0}"  style="width:90px;"></td>
      <td><input type="number" step="0.01" data-field="icbper"          value="${item.icbper ?? 0}"          style="width:70px;"></td>
    `;
    tbody.appendChild(tr);
  });

  section.style.display = 'block';
  syncItemsJson();

  // Sincronizar JSON hidden cuando cambia cualquier input
  tbody.querySelectorAll('input').forEach(inp => {
    inp.addEventListener('input', syncItemsJson);
  });
}

function syncItemsJson() {
  const rows  = document.querySelectorAll('#itemsTbody tr');
  const result = [];
  rows.forEach(tr => {
    const get = (f) => tr.querySelector(`[data-field="${f}"]`)?.value ?? '';
    result.push({
      descripcion:    get('descripcion'),
      unidad_medida:  get('unidad_medida'),
      cantidad:       parseFloat(get('cantidad'))      || 0,
      valor_unitario: parseFloat(get('valor_unitario')) || 0,
      descuento:      parseFloat(get('descuento'))      || 0,
      importe_venta:  parseFloat(get('importe_venta'))  || 0,
      icbper:         parseFloat(get('icbper'))          || 0,
    });
  });
  document.getElementById('items_json').value = JSON.stringify(result);
}

function setValue(id, val) {
  const el = document.getElementById(id);
  if (el) el.value = val ?? '';
}

function toggleDetraccionInfo() {
  const esDetrac = document.getElementById('f_es_detrac').value === '1';
  document.getElementById('detraccionSection').style.display = esDetrac ? 'block' : 'none';
  if (esDetrac) {
    calcularMontoNeto();
  }
}

function calcularMontoNeto() {
  const total = parseFloat(document.getElementById('f_total').value) || 0;
  const detrac = parseFloat(document.getElementById('f_monto_detrac').value) || 0;
  const neto = total - detrac;
  document.getElementById('f_neto_detrac').value = neto.toFixed(2);
  syncDetraccionJson();
}

function syncDetraccionJson() {
  const esDetrac = document.getElementById('f_es_detrac').value === '1';
  if (!esDetrac) {
    document.getElementById('f_detrac_json').value = '';
    return;
  }
  
  const detraccionInfo = {
    leyenda:              document.getElementById('f_detrac_leyenda').value || null,
    bien_codigo:          document.getElementById('f_detrac_bien_cod').value || null,
    bien_descripcion:     document.getElementById('f_detrac_bien_desc').value || null,
    medio_pago:           document.getElementById('f_detrac_medio').value || null,
    numero_cuenta:        document.getElementById('f_detrac_cuenta').value || null,
    porcentaje:           parseFloat(document.getElementById('f_detrac_porc').value) || null,
  };
  document.getElementById('f_detrac_json').value = JSON.stringify(detraccionInfo);
}

function resetForm() { clearFile(); }

function copiarToken() {
  const text = document.getElementById('tokenBox')?.textContent?.trim();
  if (!text) return;
  navigator.clipboard.writeText(text).then(() => Swal.fire({icon:'success', title:'¡Copiado!', text:'Token copiado al portapapeles.', timer:1500, showConfirmButton:false}));
}

function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
