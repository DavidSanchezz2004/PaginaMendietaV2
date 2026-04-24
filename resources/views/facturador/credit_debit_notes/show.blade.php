@extends('layouts.app')

@section('title', $note->getTypeLabel() . ' ' . $note->serie_documento . '-' . $note->numero_documento)

@push('styles')
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    .badge-draft     { background:rgba(107, 114, 128, 0.1); color:var(--clr-text-muted, #374151); border:1px solid rgba(107, 114, 128, 0.2); }
    .badge-sent      { background:rgba(6, 95, 70, 0.1); color:var(--clr-active-bg, #065f46); border:1px solid rgba(16, 185, 129, 0.2); }
    .badge-error     { background:rgba(153, 27, 27, 0.1); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.2); }
    .badge-consulted { background:rgba(91, 33, 182, 0.1); color:#8b5cf6; border:1px solid rgba(139, 92, 246, 0.2); }
    
    .note-badge      { display:inline-flex; align-items:center; gap:0.35rem; padding:.35rem .85rem; border-radius:20px; font-size:.78rem; font-weight:700; white-space:nowrap; }
    
    .module-table th { color: var(--clr-text-muted, #6b7280); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .module-table td { color: var(--clr-text-main, #111827); font-weight: 500; font-size: 0.9rem; }
    
    .btn-action-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: rgba(0,0,0,0.04); color: var(--clr-text-main, #374151); transition: all 0.2s; text-decoration: none; font-size: 1.15rem; }
    .btn-action-icon:hover { background: rgba(0,0,0,0.08); color: var(--clr-active-bg, #1a6b57); transform: translateY(-2px); }
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
              <div style="display:flex; align-items:center; gap:1rem; flex:1;">
                <h1 style="display:flex; align-items:center; gap:0.5rem; margin:0;"><i class='bx bx-file' style="color:var(--clr-text-main);"></i> {{ $note->getTypeLabel() }}</h1>
                <span class="note-badge badge-{{ $note->isCreditNote() ? 'sent' : 'error' }}" style="font-size:.9rem;">
                  {{ $note->serie_documento }}-{{ $note->numero_documento }}
                </span>
              </div>
              <span class="note-badge badge-{{ $note->estado }}" style="font-size:.9rem;">
                {{ $note->getEstadoLabel() }}
              </span>
            </div>

            <div style="display:grid; grid-template-columns:1fr 380px; gap:1.5rem;">
              <!-- Contenido Principal -->
              <div>
                <!-- Información de la Nota -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Información de la Nota</h6>
                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; font-size:.95rem; line-height:1.8;">
                    <div>
                      <div style="color:var(--clr-text-muted); font-size:.85rem; font-weight:600; margin-bottom:.25rem;">Código Interno</div>
                      <div>{{ $note->codigo_interno }}</div>
                      
                      <div style="color:var(--clr-text-muted); font-size:.85rem; font-weight:600; margin-top:1rem; margin-bottom:.25rem;">Fecha Emisión</div>
                      <div>{{ $note->fecha_emision->format('d/m/Y H:i') }}</div>
                      
                      <div style="color:var(--clr-text-muted); font-size:.85rem; font-weight:600; margin-top:1rem; margin-bottom:.25rem;">Tipo de Nota</div>
                      <div>{{ $note->getNotaTypeLabel() }}</div>
                    </div>
                    <div>
                      <div style="color:var(--clr-text-muted); font-size:.85rem; font-weight:600; margin-bottom:.25rem;">Correo</div>
                      <div>{{ $note->correo ?? '—' }}</div>
                      
                      <div style="color:var(--clr-text-muted); font-size:.85rem; font-weight:600; margin-top:1rem; margin-bottom:.25rem;">Observación</div>
                      <div style="font-size:.9rem;">{{ $note->observacion ?? '—' }}</div>
                    </div>
                  </div>
                  @if($note->mensaje_respuesta_feasy)
                    <hr style="margin:1.5rem 0; border:0; border-top:1px solid rgba(0,0,0,.1);">
                    <div style="background:rgba(245, 158, 11, 0.08); border-left:3px solid #d97706; padding:0.75rem; border-radius:4px; font-size:.9rem;">
                      <strong style="color:#d97706;">Mensaje Feasy:</strong><br>
                      {{ $note->mensaje_respuesta_feasy }}
                    </div>
                  @endif
                </div>

                <!-- Comprobante Referenciado -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Comprobante Referenciado</h6>
                  <div style="font-size:.95rem; line-height:1.8;">
                    <div><strong>{{ $invoice->getTypeLabel() }}:</strong> {{ $invoice->serie_documento }}-{{ $invoice->numero_documento }}</div>
                    <div><strong>Cliente:</strong> {{ $invoice->nombre_razon_social_adquiriente }}</div>
                    <div><strong>RUC:</strong> {{ $invoice->numero_documento_adquiriente }}</div>
                    <div><strong>Fecha:</strong> {{ $invoice->fecha_emision->format('d/m/Y') }}</div>
                    <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(0,0,0,.1);"><strong style="font-size:1.1rem; color:var(--clr-active-bg, #1a6b57);">Monto Total: S/. {{ number_format($invoice->monto_total, 2) }}</strong></div>
                  </div>
                </div>

                <!-- Items -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Items</h6>
                  <div style="overflow-x:auto;">
                    <table class="module-table">
                      <thead>
                        <tr>
                          <th>Descripción</th>
                          <th style="text-align:right;">Cantidad</th>
                          <th style="text-align:right;">P. Unitario</th>
                          <th style="text-align:right;">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($note->lista_items as $item)
                          <tr>
                            <td>{{ $item['descripcion'] }}</td>
                            <td style="text-align:right;">{{ number_format((float)$item['cantidad'], 2) }}</td>
                            <td style="text-align:right;">S/. {{ number_format((float)$item['monto_precio_unitario'], 2) }}</td>
                            <td style="text-align:right; font-weight:700;">S/. {{ number_format((float)$item['monto_total'], 2) }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Sidebar -->
              <div>
                <!-- Totales -->
                <div style="background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.2); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1.25rem; font-weight:700; color:var(--clr-text-main);">Totales</h6>
                  <div style="font-size:.9rem; line-height:2;">
                    <div style="display:flex; justify-content:space-between;">
                      <span>Subtotal Gravado:</span>
                      <strong>S/. {{ number_format($note->monto_total_gravado, 2) }}</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                      <span>IGV {{ number_format($note->porcentaje_igv, 1) }}%:</span>
                      <strong>S/. {{ number_format($note->monto_total_igv, 2) }}</strong>
                    </div>
                    @if($note->monto_total_inafecto > 0)
                      <div style="display:flex; justify-content:space-between;">
                        <span>Inafecto:</span>
                        <strong>S/. {{ number_format($note->monto_total_inafecto, 2) }}</strong>
                      </div>
                    @endif
                    <hr style="margin:1rem 0; border:0; border-top:1px solid rgba(0,0,0,.1);">
                    <div style="display:flex; justify-content:space-between; font-size:1.2rem;">
                      <span style="font-weight:700;">TOTAL:</span>
                      <strong style="color:var(--clr-active-bg, #1a6b57);">S/. {{ number_format($note->monto_total, 2) }}</strong>
                    </div>
                  </div>
                </div>

                <!-- Acciones -->
                <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem;">
                  <h6 style="margin-bottom:1rem; font-weight:700; color:var(--clr-text-main);">Acciones</h6>
                  <div style="display:grid; gap:.75rem;">
                    @if($note->estado === 'draft')
                      <form method="POST" action="{{ route('facturador.credit-debit-notes.emit', $note) }}" style="width:100%;">
                        @csrf
                        <button type="submit" class="btn-primary" style="width:100%; text-align:center; font-size:.9rem;">
                          <i class='bx bx-send'></i> Enviar a SUNAT
                        </button>
                      </form>
                    @elseif($note->estado === 'sent')
                      <form method="POST" action="{{ route('facturador.credit-debit-notes.consult', $note) }}" style="width:100%;">
                        @csrf
                        <button type="submit" class="btn-primary" style="width:100%; text-align:center; font-size:.9rem;">
                          <i class='bx bx-search'></i> Consultar Estado
                        </button>
                      </form>
                    @elseif($note->estado === 'error')
                      <form method="POST" action="{{ route('facturador.credit-debit-notes.emit', $note) }}" style="width:100%;">
                        @csrf
                        <button type="submit" class="btn-primary" style="width:100%; text-align:center; font-size:.9rem; background:#d97706;">
                          <i class='bx bx-arrow-back'></i> Reintentar
                        </button>
                      </form>
                    @endif

                    @if($note->xml_path)
                      <a href="{{ route('facturador.credit-debit-notes.downloadXml', $note) }}" class="btn-secondary" style="width:100%; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; font-size:.9rem;">
                        <i class='bx bx-download'></i> Descargar XML
                      </a>
                    @endif

                    @if($note->url_pdf_feasy)
                      <a href="{{ $note->url_pdf_feasy }}" target="_blank" class="btn-secondary" style="width:100%; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; font-size:.9rem;">
                        <i class='bx bx-file-pdf'></i> Ver PDF
                      </a>
                    @endif

                    <a href="{{ route('facturador.credit-debit-notes.index') }}" class="btn-secondary" style="width:100%; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; font-size:.9rem; margin-top:.5rem;">
                      <i class='bx bx-arrow-back'></i> Volver
                    </a>
                  </div>
                </div>

                @if($note->codigo_respuesta_feasy)
                  <div style="background:var(--clr-bg-card); border:1px solid var(--clr-border-light); border-radius:12px; padding:1.5rem;">
                    <h6 style="margin-bottom:.75rem; font-weight:700; color:var(--clr-text-main); font-size:.9rem;">Respuesta Feasy</h6>
                    <div style="font-size:.85rem; background:rgba(0,0,0,.03); padding:.75rem; border-radius:6px; font-family:monospace; color:var(--clr-text-muted);">
                      {{ $note->codigo_respuesta_feasy }}
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>

        </div>
      </main>
    </section>
  </div>
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('[data-flash-message]').forEach((flash) => {
      const closeBtn = flash.querySelector('[data-flash-close]');
      if (closeBtn) closeBtn.addEventListener('click', () => flash.remove());
      window.setTimeout(() => { if (document.body.contains(flash)) flash.remove(); }, 4000);
    });
  </script>
@endpush
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h3>
                {{ $note->getTypeLabel() }}
                <span class="badge {{ $note->isCreditNote() ? 'bg-success' : 'bg-danger' }}">
                    {{ $note->serie_documento }}-{{ $note->numero_documento }}
                </span>
            </h3>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge 
                {{ $note->estado === 'sent' ? 'bg-success' : '' }}
                {{ $note->estado === 'draft' ? 'bg-warning' : '' }}
                {{ $note->estado === 'error' ? 'bg-danger' : '' }}
                {{ $note->estado === 'consulted' ? 'bg-info' : '' }}
            " style="font-size: 1rem;">
                {{ $note->getEstadoLabel() }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Info Principal -->
        <div class="col-md-8">
            <!-- Datos de la Nota -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Información de la Nota</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Código Interno:</strong> {{ $note->codigo_interno }}<br>
                                <strong>Fecha Emisión:</strong> {{ $note->fecha_emision->format('d/m/Y H:i') }}<br>
                                <strong>Tipo de Nota:</strong> {{ $note->getNotaTypeLabel() }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Correo:</strong> {{ $note->correo ?? '—' }}<br>
                                <strong>Observación:</strong> {{ $note->observacion ?? '—' }}
                            </p>
                        </div>
                    </div>
                    @if ($note->mensaje_respuesta_feasy)
                        <hr>
                        <div class="alert alert-warning">
                            <strong>Mensaje Feasy:</strong><br>
                            {{ $note->mensaje_respuesta_feasy }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Factura Referenciada -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Comprobante Referenciado</h6>
                </div>
                <div class="card-body">
                    <p>
                        <strong>{{ $invoice->getTypeLabel() }}:</strong> 
                        {{ $invoice->serie_documento }}-{{ $invoice->numero_documento }}<br>
                        <strong>Cliente:</strong> {{ $invoice->nombre_razon_social_adquiriente }}<br>
                        <strong>RUC:</strong> {{ $invoice->numero_documento_adquiriente }}<br>
                        <strong>Fecha:</strong> {{ $invoice->fecha_emision->format('d/m/Y') }}<br>
                        <strong>Monto Total:</strong> S/. {{ number_format($invoice->monto_total, 2) }}
                    </p>
                </div>
            </div>

            <!-- Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Items</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>P. Unit.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($note->lista_items as $item)
                                <tr>
                                    <td>{{ $item['descripcion'] }}</td>
                                    <td>{{ (float) $item['cantidad'] }}</td>
                                    <td>S/. {{ number_format((float) $item['monto_precio_unitario'], 2) }}</td>
                                    <td>S/. {{ number_format((float) $item['monto_total'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Totales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Totales</h6>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Subtotal Gravado:</strong><br>
                        S/. {{ number_format($note->monto_total_gravado, 2) }}
                    </p>
                    <p>
                        <strong>IGV ({{ number_format($note->porcentaje_igv, 2) }}%):</strong><br>
                        S/. {{ number_format($note->monto_total_igv, 2) }}
                    </p>
                    @if ($note->monto_total_inafecto > 0)
                        <p>
                            <strong>Inafecto:</strong><br>
                            S/. {{ number_format($note->monto_total_inafecto, 2) }}
                        </p>
                    @endif
                    <hr>
                    <h5 class="text-success">
                        S/. {{ number_format($note->monto_total, 2) }}
                    </h5>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Acciones</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    @if ($note->estado === 'draft')
                        <form action="{{ route('facturador.credit_debit_notes.emit', $note) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-send"></i> Enviar a SUNAT
                            </button>
                        </form>
                    @elseif($note->estado === 'sent')
                        <form action="{{ route('facturador.credit_debit_notes.consult', $note) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Consultar Estado
                            </button>
                        </form>
                    @elseif($note->estado === 'error')
                        <p class="text-danger">
                            <strong>Error:</strong> {{ $note->mensaje_respuesta_feasy }}
                        </p>
                        <form action="{{ route('facturador.credit_debit_notes.emit', $note) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-arrow-repeat"></i> Reintentar
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('facturador.credit-debit-notes.downloadXml', $note) }}" class="btn btn-secondary">
                        <i class="bi bi-file-code"></i> Descargar XML
                    </a>

                    @if ($note->url_pdf_feasy)
                        <a href="{{ $note->url_pdf_feasy }}" target="_blank" class="btn btn-info">
                            <i class="bi bi-file-pdf"></i> Ver PDF
                        </a>
                    @endif

                    <a href="{{ route('facturador.credit-debit-notes.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            @if ($note->codigo_respuesta_feasy)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Información Feasy</h6>
                    </div>
                    <div class="card-body small">
                        <p class="mb-0">
                            <strong>Código Respuesta:</strong><br>
                            {{ $note->codigo_respuesta_feasy }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
