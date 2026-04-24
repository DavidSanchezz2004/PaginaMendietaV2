<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propuesta de Servicios – {{ $regimen }}</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
      font-size: 12px;
      color: #1a1a1a;
      background: #f4f4f4;
    }

    /* ── PÁGINA A4 ── */
    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      background: #fff;
      display: flex;
      flex-direction: column;
    }

    /* ── BARRA SUPERIOR VERDE ── */
    .header-bar {
      background: #013b33;
      padding: 22px 32px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .header-bar .brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .header-bar .brand img {
      height: 52px;
      object-fit: contain;
    }

    .header-bar .brand-text h1 {
      font-size: 17px;
      font-weight: 700;
      color: #fff;
      letter-spacing: .3px;
    }

    .header-bar .brand-text p {
      font-size: 10px;
      color: #a8d8c8;
      margin-top: 2px;
    }

    .header-bar .contact {
      text-align: right;
      font-size: 9.5px;
      color: #a8d8c8;
      line-height: 1.6;
    }

    .header-bar .contact strong {
      color: #fff;
      display: block;
      font-size: 10px;
    }

    /* ── FRANJA TÍTULO ── */
    .title-bar {
      background: #025c47;
      padding: 14px 32px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .title-bar .badge {
      background: #fff;
      color: #013b33;
      font-size: 9px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
      letter-spacing: .5px;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .title-bar h2 {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      line-height: 1.3;
    }

    /* ── INTRO ── */
    .intro {
      padding: 18px 32px 0;
    }

    .intro p {
      font-size: 11px;
      color: #4b5563;
      line-height: 1.65;
      max-width: 90%;
    }

    /* ── SERVICIOS ── */
    .services {
      padding: 18px 32px 0;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .service-card {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      overflow: hidden;
    }

    .service-card .sc-header {
      background: #013b33;
      padding: 9px 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .service-card .sc-header .sc-num {
      background: rgba(255,255,255,.18);
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .service-card .sc-header h3 {
      font-size: 11px;
      font-weight: 700;
      color: #fff;
      line-height: 1.3;
    }

    .service-card .sc-body {
      padding: 10px 14px;
      background: #fff;
    }

    .service-card .sc-body ul {
      list-style: none;
      padding: 0;
    }

    .service-card .sc-body ul li {
      font-size: 10.5px;
      color: #374151;
      padding: 3px 0;
      padding-left: 14px;
      position: relative;
      line-height: 1.4;
    }

    .service-card .sc-body ul li::before {
      content: '✓';
      position: absolute;
      left: 0;
      color: #025c47;
      font-weight: 700;
      font-size: 10px;
    }

    /* ── NOTA INCLUYE TODO ── */
    .note-bar {
      margin: 18px 32px 0;
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 8px;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 10.5px;
      color: #166534;
    }

    .note-bar svg {
      flex-shrink: 0;
    }

    /* ── FOOTER ── */
    .footer {
      margin-top: auto;
      padding: 14px 32px;
      background: #013b33;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 9px;
      color: #a8d8c8;
    }

    .footer strong {
      color: #fff;
    }

    .footer .footer-right {
      text-align: right;
      line-height: 1.7;
    }

    /* ── TOOLBAR IMPRESIÓN ── */
    .print-toolbar {
      position: fixed;
      top: 16px;
      right: 16px;
      display: flex;
      gap: 8px;
      z-index: 999;
    }

    .print-toolbar button {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      cursor: pointer;
      font-family: inherit;
      font-weight: 600;
    }

    .btn-print   { background: #013b33; color: #fff; }
    .btn-print:hover { background: #025c47; }
    .btn-back    { background: #f3f4f6; color: #374151; }
    .btn-back:hover  { background: #e5e7eb; }

    @media print {
      body { background: #fff; }
      .print-toolbar { display: none; }
      .page { margin: 0; }
      * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }

    @page { size: A4; margin: 0; }
  </style>
</head>
<body>

  {{-- ── BARRA DE HERRAMIENTAS ── --}}
  <div class="print-toolbar">
    <button class="btn-back" onclick="window.close()">
      ← Volver
    </button>
    <button class="btn-print" onclick="window.print()">
      🖨️ Imprimir / PDF
    </button>
  </div>

  <div class="page">

    {{-- ── ENCABEZADO ── --}}
    <div class="header-bar">
      <div class="brand">
        <img src="{{ asset('images/logoMendieta.png') }}" alt="Mendieta">
        <div class="brand-text">
          <h1>Estudio Contable Mendieta</h1>
          <p>Asesoría Contable & Tributaria</p>
        </div>
      </div>
      <div class="contact">
        <strong>Contacto</strong>
        contactos@mscontables.com<br>
        +51 950 235 495<br>
        Lima, Perú
      </div>
    </div>

    {{-- ── TÍTULO ── --}}
    <div class="title-bar">
      <span class="badge">Propuesta de Servicio</span>
      <h2>{{ $titulo }}</h2>
    </div>

    {{-- ── INTRO ── --}}
    <div class="intro">
      <p>{{ $descripcion }}</p>
    </div>

    {{-- ── SERVICIOS ── --}}
    <div class="services">
      @foreach ($servicios as $index => $servicio)
        <div class="service-card">
          <div class="sc-header">
            <div class="sc-num">{{ $index + 1 }}</div>
            <h3>{{ $servicio['titulo'] }}</h3>
          </div>
          <div class="sc-body">
            <ul>
              @foreach ($servicio['items'] as $item)
                <li>{{ $item }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endforeach
    </div>

    {{-- ── NOTA ── --}}
    <div class="note-bar">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="#16a34a" stroke-width="2"/>
        <path d="M12 8v4m0 4h.01" stroke="#16a34a" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <span>
        <strong>Servicio mensual completo:</strong>
        todos los módulos indicados están incluidos en el paquete. Frente a cualquier consulta o requerimiento adicional, nuestro equipo lo atenderá sin costo extra.
      </span>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
      <div>
        <strong>Estudio Contable Mendieta</strong><br>
        Documento generado el {{ now()->format('d/m/Y') }}
      </div>
      <div class="footer-right">
        Confidencial – Solo para uso del cliente<br>
        mscontables.com
      </div>
    </div>

  </div>

</body>
</html>
