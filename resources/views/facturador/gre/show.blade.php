@extends('layouts.app')

@section('title', 'GRE ' . $invoice->serie_numero . ' — Facturador | Portal Mendieta')

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .badge-draft     { background:rgba(107,114,128,.1);  color:#374151; border:1px solid rgba(107,114,128,.2); }
    .badge-ready     { background:rgba(30,64,175,.1);    color:#3b82f6; border:1px solid rgba(59,130,246,.2); }
    .badge-sent      { background:rgba(6,95,70,.1);      color:#065f46; border:1px solid rgba(16,185,129,.2); }
    .badge-error     { background:rgba(153,27,27,.1);    color:#ef4444; border:1px solid rgba(239,68,68,.2); }
    .badge-consulted { background:rgba(91,33,182,.1);    color:#8b5cf6; border:1px solid rgba(139,92,246,.2); }
    .badge-voided    { background:rgba(107,114,128,.12); color:#6b7280; border:1px solid rgba(107,114,128,.25); text-decoration:line-through; }
    .gre-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.35rem .85rem; border-radius:20px; font-size:.78rem; font-weight:700; }

    .show-grid   { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
    .show-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.25rem; }
    @media(max-width:768px){ .show-grid,.show-grid-3{ grid-template-columns:1fr; } }

    .info-card    { background:var(--clr-bg-card,#fff); border:1px solid var(--clr-border-light,#e5e7eb); border-radius:16px; padding:1.5rem; box-shadow:0 4px 15px rgba(0,0,0,.02); }
    .info-card h3 { font-size:.85rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:var(--clr-text-muted,#6b7280); margin:0 0 1.2rem; display:flex; align-items:center; gap:.5rem; }
    .info-card h3 i { font-size:1.1rem; color:var(--clr-text-main,#111827); }

    .dl-row    { display:flex; font-size:.9rem; margin-bottom:.6rem; gap:.5rem; }
    .dl-row dt { min-width:145px; color:var(--clr-text-muted,#6b7280); flex-shrink:0; font-weight:500; }
    .dl-row dd { margin:0; font-weight:600; color:var(--clr-text-main,#111827); word-break:break-word; }

    .action-strip { display:flex; flex-wrap:wrap; gap:.75rem; margin-bottom:1.5rem; }
    .action-strip form { display:inline-block; margin:0; }
    .module-table th { color:var(--clr-text-muted,#6b7280); font-weight:700; text-transform:uppercase; font-size:.75rem; letter-spacing:.05em; }
    .module-table td { color:var(--clr-text-main,#111827); font-weight:500; font-size:.9rem; }
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

        @foreach(['success' => null, 'error' => 'module-alert--error', 'warning' => 'module-alert--warning', 'info' => null] as $flashKey => $flashClass)
          @if(session($flashKey))
            <div class="placeholder-content module-alert module-flash {{ $flashClass }}" data-flash-message>
              <p>{{ session($flashKey) }}</p>
              <button type="button" class="module-flash-close" aria-label="Cerrar" data-flash-close><i class='bx bx-x'></i></button>
            </div>
          @endif
        @endforeach

        <div class="placeholder-content module-card-wide">

          {{-- Título + estado --}}
          <div class="module-toolbar">
            <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
              <h1>{{ $invoice->serie_numero }}</h1>
              @if($invoice->estado_feasy->value === 'ticket')
                <span class="gre-badge" style="background:rgba(234,179,8,.12); color:#92400e; border:1px solid rgba(234,179,8,.35);">
                  <i class='bx bx-time-five'></i> Ticket SUNAT (pendiente)
                </span>
              @else
                <span class="gre-badge badge-{{ $invoice->estado->value }}">{{ $invoice->estado->label() }}</span>
              @endif
              <span style="font-size:.75rem; color:#6b7280; background:rgba(26,107,87,.08); padding:.2rem .6rem; border-radius:6px; font-weight:700;">
                GRE · Modalidad {{ $invoice->codigo_modalidad_traslado }}
                {{ $invoice->codigo_modalidad_traslado === '01' ? '(Público)' : '(Privado)' }}
              </span>
            </div>
            <a href="{{ route('facturador.gre.index') }}" class="btn-secondary">
              <i class='bx bx-arrow-back'></i> Volver
            </a>
          </div>

          {{-- Acciones --}}
          <div class="action-strip">

            {{-- Aviso ticket pendiente --}}
            @if($invoice->estado_feasy->value === 'ticket')
              <div style="width:100%; padding:.65rem 1rem; background:rgba(234,179,8,.1); border:1px solid rgba(234,179,8,.35); border-radius:10px; font-size:.85rem; color:#92400e; display:flex; align-items:center; gap:.5rem;">
                <i class='bx bx-info-circle' style="font-size:1.1rem;"></i>
                <span>SUNAT recibió el ticket. Usa <strong>Consultar SUNAT</strong> para conocer el resultado definitivo.</span>
              </div>
            @endif
            @can('emit', $invoice)
              @if($invoice->canBeEmitted())
                <form method="POST" action="{{ route('facturador.gre.emit', $invoice) }}">
                  @csrf
                  <button type="submit" class="btn-primary">
                    <i class='bx bx-send'></i> Emitir a SUNAT
                  </button>
                </form>
              @endif
            @endcan

            @can('void', $invoice)
              @if($invoice->canBeVoided())
                <form method="POST" action="{{ route('facturador.gre.void', $invoice) }}" id="form-void">
                  @csrf
                  <input type="hidden" name="motivo" id="void-motivo" value="">
                  <button type="button" class="btn-secondary" id="btn-void" style="color:#dc2626; border-color:#fca5a5;">
                    <i class='bx bx-x-circle'></i> Anular
                  </button>
                </form>
              @endif
            @endcan

            @can('consult', $invoice)
              @if($invoice->canBeConsulted())
                <form method="POST" action="{{ route('facturador.gre.consult', $invoice) }}">
                  @csrf
                  <button type="submit" class="btn-secondary">
                    <i class='bx bx-refresh'></i> Consultar SUNAT
                  </button>
                </form>
              @endif
            @endcan

            @can('delete', $invoice)
              <form method="POST" action="{{ route('facturador.gre.destroy', $invoice) }}"
                    data-confirm="¿Eliminar esta GRE? Esta acción no se puede deshacer.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary" style="color:#ef4444; border-color:#fca5a5;">
                  <i class='bx bx-trash'></i> Eliminar
                </button>
              </form>
            @endcan

            @if($invoice->xml_path)
              @can('downloadXml', $invoice)
                <a href="{{ route('facturador.gre.xml', $invoice) }}" class="btn-secondary">
                  <i class='bx bx-file'></i> XML local
                </a>
              @endcan
            @endif

            @if($invoice->ruta_xml)
              <a href="{{ $invoice->ruta_xml }}" target="_blank" rel="noopener" class="btn-secondary">
                <i class='bx bx-download'></i> Descargar XML
              </a>
            @endif

            @if($invoice->ruta_cdr)
              <a href="{{ $invoice->ruta_cdr }}" target="_blank" rel="noopener" class="btn-secondary">
                <i class='bx bx-shield-check'></i> Descargar CDR
              </a>
            @endif

            @if($invoice->ruta_reporte)
              <a href="{{ $invoice->ruta_reporte }}" target="_blank" rel="noopener" class="btn-primary">
                <i class='bx bx-file-pdf'></i> Ver PDF
              </a>
            @endif
          </div>

          {{-- ── Datos del documento ──────────────────────────────────── --}}
          <div class="show-grid" style="margin-bottom:1.25rem;">
            <div class="info-card">
              <h3><i class='bx bx-receipt'></i> Datos del Documento</h3>
              <div class="dl-row"><dt>Tipo</dt><dd>09 — Guía de Remisión</dd></div>
              <div class="dl-row"><dt>Serie-Número</dt><dd><code>{{ $invoice->serie_numero }}</code></dd></div>
              <div class="dl-row"><dt>Código interno</dt><dd>{{ $invoice->codigo_interno ?? '—' }}</dd></div>
              <div class="dl-row"><dt>Fecha emisión</dt><dd>{{ $invoice->fecha_emision->format('d/m/Y') }} <span style="color:#9ca3af;font-size:.8rem;">{{ $invoice->hora_emision }}</span></dd></div>
              <div class="dl-row"><dt>Fecha inicio traslado</dt><dd>{{ $invoice->fecha_inicio_traslado?->format('d/m/Y') ?? '—' }}</dd></div>
              <div class="dl-row"><dt>Peso bruto total</dt><dd>{{ $invoice->peso_bruto_total }} {{ $invoice->codigo_unidad_medida_peso_bruto }}</dd></div>
              @if($invoice->observacion)
                <div class="dl-row"><dt>Observación</dt><dd>{{ $invoice->observacion }}</dd></div>
              @endif
              @if($invoice->correo)
                <div class="dl-row"><dt>Correo</dt><dd>{{ $invoice->correo }}</dd></div>
              @endif
            </div>

            <div class="info-card">
              <h3><i class='bx bx-transfer-alt'></i> Traslado</h3>
              <div class="dl-row"><dt>Modalidad</dt>
                <dd>
                  <span style="font-weight:700; color:var(--clr-active-bg,#1a6b57);">
                    {{ $invoice->codigo_modalidad_traslado }}
                    {{ $invoice->codigo_modalidad_traslado === '01' ? '— Transporte Público' : '— Transporte Privado' }}
                  </span>
                </dd>
              </div>
              <div class="dl-row"><dt>Motivo</dt><dd>{{ $invoice->codigo_motivo_traslado }} — {{ $invoice->descripcion_motivo_traslado }}</dd></div>

              @php
                $partida  = $invoice->gre_punto_partida  ?? [];
                $llegada  = $invoice->gre_punto_llegada  ?? [];
              @endphp
              <div class="dl-row"><dt>Punto partida</dt><dd>{{ $partida['ubigeo_punto_partida'] ?? '—' }} — {{ $partida['direccion_punto_partida'] ?? '—' }}</dd></div>
              <div class="dl-row"><dt>Punto llegada</dt><dd>{{ $llegada['ubigeo_punto_llegada'] ?? '—' }} — {{ $llegada['direccion_punto_llegada'] ?? '—' }}</dd></div>
            </div>
          </div>

          {{-- ── Destinatario ─────────────────────────────────────────── --}}
          @php $dest = $invoice->gre_destinatario ?? []; @endphp
          <div class="info-card" style="margin-bottom:1.25rem;">
            <h3><i class='bx bx-user-check'></i> Destinatario</h3>
            <div class="show-grid-3">
              <div class="dl-row" style="flex-direction:column;"><dt>Tipo Doc</dt><dd>{{ $dest['codigo_tipo_documento_destinatario'] ?? '—' }}</dd></div>
              <div class="dl-row" style="flex-direction:column;"><dt>N° Documento</dt><dd>{{ $dest['numero_documento_destinatario'] ?? '—' }}</dd></div>
              <div class="dl-row" style="flex-direction:column;"><dt>Razón Social / Nombre</dt><dd>{{ $dest['nombre_razon_social_destinatario'] ?? '—' }}</dd></div>
            </div>
          </div>

          {{-- ── Transportista (Modalidad 01) ────────────────────────── --}}
          @if($invoice->codigo_modalidad_traslado === '01' && $invoice->gre_transportista)
            @php $trans = $invoice->gre_transportista; @endphp
            <div class="info-card" style="margin-bottom:1.25rem; border-left:4px solid var(--clr-active-bg,#1a6b57);">
              <h3><i class='bx bx-bus'></i> Transportista</h3>
              <div class="show-grid-3">
                <div class="dl-row" style="flex-direction:column;"><dt>Tipo Doc</dt><dd>{{ $trans['codigo_tipo_documento_transportista'] ?? '—' }}</dd></div>
                <div class="dl-row" style="flex-direction:column;"><dt>N° Documento</dt><dd>{{ $trans['numero_documento_transportista'] ?? '—' }}</dd></div>
                <div class="dl-row" style="flex-direction:column;"><dt>Razón Social</dt><dd>{{ $trans['nombre_razon_social_transportista'] ?? '—' }}</dd></div>
              </div>
            </div>
          @endif

          {{-- ── Vehículos (Modalidad 02) ─────────────────────────────── --}}
          @if($invoice->codigo_modalidad_traslado === '02' && $invoice->gre_vehiculos)
            <div class="info-card" style="margin-bottom:1.25rem; border-left:4px solid var(--clr-active-bg,#1a6b57);">
              <h3><i class='bx bx-car'></i> Vehículos</h3>
              <table class="module-table" style="width:auto; min-width:300px;">
                <thead><tr><th>#</th><th>Placa</th><th>Principal</th></tr></thead>
                <tbody>
                  @foreach($invoice->gre_vehiculos as $vi => $v)
                    <tr>
                      <td style="color:#9ca3af;">{{ $vi + 1 }}</td>
                      <td><strong>{{ $v['numero_placa'] ?? '—' }}</strong></td>
                      <td>{{ ($v['indicador_principal'] ?? false) ? '✔ Sí' : '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          {{-- ── Conductores (Modalidad 02) ───────────────────────────── --}}
          @if($invoice->codigo_modalidad_traslado === '02' && !empty($invoice->gre_conductores))
            <div class="info-card" style="margin-bottom:1.25rem;">
              <h3><i class='bx bx-id-card'></i> Conductores</h3>
              <div style="overflow-x:auto;">
                <table class="module-table">
                  <thead>
                    <tr>
                      <th>#</th><th>Tipo Doc</th><th>N° Doc</th><th>Nombre</th><th>Apellido</th><th>Licencia</th><th>Principal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($invoice->gre_conductores as $ci => $c)
                      <tr>
                        <td style="color:#9ca3af;">{{ $ci + 1 }}</td>
                        <td>{{ $c['codigo_tipo_documento'] ?? '—' }}</td>
                        <td>{{ $c['numero_documento'] ?? '—' }}</td>
                        <td>{{ $c['nombre'] ?? '—' }}</td>
                        <td>{{ $c['apellido'] ?? '—' }}</td>
                        <td>{{ $c['numero_licencia'] ?? '—' }}</td>
                        <td>{{ ($c['indicador_principal'] ?? false) ? '✔ Sí' : '—' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif

          {{-- ── Ítems de la guía ─────────────────────────────────────── --}}
          <div class="module-table-wrap" style="margin-bottom:1.25rem;">
            <table class="module-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Unidad</th>
                  <th style="text-align:right;">Cantidad</th>
                </tr>
              </thead>
              <tbody>
                @foreach($invoice->items as $i => $item)
                  <tr>
                    <td style="color:#9ca3af;">{{ $i + 1 }}</td>
                    <td><small>{{ $item->codigo_interno ?? '—' }}</small></td>
                    <td>{{ $item->descripcion }}</td>
                    <td>{{ $item->codigo_unidad_medida }}</td>
                    <td style="text-align:right; font-weight:600;">{{ number_format($item->cantidad, 4) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- ── Respuesta SUNAT ──────────────────────────────────────── --}}
          @if($invoice->mensaje_respuesta_sunat)
            @php
              $isOk        = in_array($invoice->estado->value, ['sent','consulted']);
              $lastErr     = $invoice->last_error ? json_decode($invoice->last_error, true) : null;
              $feasyErrors = $lastErr['errors'] ?? [];
              $feasyMsg    = $lastErr['message'] ?? null;
              $httpStatus  = $lastErr['http_status'] ?? null;
            @endphp
            <div class="info-card" style="border-left:4px solid {{ $isOk ? '#10b981' : '#ef4444' }};{{ $isOk ? '' : ' background:rgba(239,68,68,.03);' }}">
              <h3><i class='bx bx-info-circle'></i> Respuesta SUNAT</h3>
              <p style="font-size:.9rem; color:var(--clr-text-main,#111827); margin:0;">{{ $invoice->mensaje_respuesta_sunat }}</p>
              @if($invoice->codigo_respuesta_sunat)
                <p style="font-size:.8rem; color:#9ca3af; margin:.5rem 0 0;">Código SUNAT: {{ $invoice->codigo_respuesta_sunat }}</p>
              @endif

              {{-- Detalle del error Feasy --}}
              @if(!$isOk && ($feasyErrors || $feasyMsg))
                <div style="margin-top:1rem; padding:.75rem; background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.2); border-radius:10px;">
                  <p style="font-size:.78rem; font-weight:800; text-transform:uppercase; color:#ef4444; margin:0 0 .5rem; letter-spacing:.04em;">
                    <i class='bx bx-error-circle'></i> Detalle del error
                  </p>
                  @if($feasyMsg)
                    <p style="font-size:.85rem; color:#374151; margin:0 0 .4rem;">{{ $feasyMsg }}</p>
                  @endif
                  @if($httpStatus)
                    <p style="font-size:.78rem; color:#9ca3af; margin:0 0 .4rem;">HTTP {{ $httpStatus }}</p>
                  @endif
                  @if($feasyErrors)
                    <ul style="margin:.4rem 0 0; padding-left:1.2rem; font-size:.85rem; color:#374151;">
                      @foreach($feasyErrors as $errItem)
                        <li style="margin-bottom:.2rem;">
                          @if(is_array($errItem))
                            {{ implode(' — ', array_filter($errItem)) }}
                          @else
                            {{ $errItem }}
                          @endif
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </div>
              @endif
            </div>
          @endif

        </div>
      </div>
    </main>
  </section>
</div>

@if($invoice->canBeVoided())
  {{-- Modal anular --}}
  <div id="modal-void" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--clr-bg-card,#fff); border-radius:14px; padding:1.75rem; max-width:440px; width:95%; box-shadow:0 20px 60px rgba(0,0,0,.2);">
      <h3 style="font-size:1.1rem; font-weight:800; margin:0 0 .75rem;">Anular GRE</h3>
      <p style="font-size:.9rem; color:#6b7280; margin:0 0 1rem;">Ingresa el motivo de anulación:</p>
      <textarea id="void-motivo-input" rows="3"
                style="width:100%; border:1px solid #d1d5db; border-radius:9px; padding:.6rem .8rem; font-size:.9rem; resize:none;"
                placeholder="Ej: Error en datos del destinatario"></textarea>
      <div style="display:flex; justify-content:flex-end; gap:.6rem; margin-top:1rem;">
        <button type="button" onclick="document.getElementById('modal-void').style.display='none'"
                class="btn-secondary">Cancelar</button>
        <button type="button" onclick="submitVoid()" class="btn-primary" style="background:#dc2626; border-color:#dc2626;">
          Confirmar Anulación
        </button>
      </div>
    </div>
  </div>
@endif
@endsection

@push('scripts')
@if($invoice->canBeVoided())
<script>
  document.getElementById('btn-void')?.addEventListener('click', function () {
    document.getElementById('modal-void').style.display = 'flex';
    document.getElementById('void-motivo-input').focus();
  });
  function submitVoid() {
    const motivo = document.getElementById('void-motivo-input').value.trim();
    if (!motivo) { 
      Swal.fire({
        title: 'Motivo requerido',
        text: 'Ingresa el motivo de anulación.',
        icon: 'warning',
        confirmButtonText: 'OK',
        customClass: { popup: document.body.classList.contains('dark-mode') ? 'swal2-dark' : '' }
      });
      return; 
    }
    document.getElementById('void-motivo').value = motivo;
    document.getElementById('form-void').submit();
  }
</script>
@endif
@endpush
