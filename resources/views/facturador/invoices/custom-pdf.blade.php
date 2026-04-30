<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $invoice->serie_numero }} | PDF personalizado</title>
  <style>
    @font-face {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 400;
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('fonts/montserrat/Montserrat-Regular.ttf'))) }}") format('truetype');
    }
    @font-face {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 600;
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('fonts/montserrat/Montserrat-SemiBold.ttf'))) }}") format('truetype');
    }
    @font-face {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 700;
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('fonts/montserrat/Montserrat-Bold.ttf'))) }}") format('truetype');
    }
    @font-face {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 900;
      src: url("data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('fonts/montserrat/Montserrat-Black.ttf'))) }}") format('truetype');
    }
    @page {
      size: A4;
      margin: 6mm;
    }
    body {
      font-family: 'Montserrat', DejaVu Sans, Arial, sans-serif;
      margin: 0;
      padding: 20px;
      background: #525659;
      color: #000;
      font-size: 11px;
    }
    body.pdf-render {
      padding: 0;
      background: #fff;
    }
    .invoice-container {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      padding: 40px;
      box-shadow: 0 0 10px rgba(0,0,0,.5);
      box-sizing: border-box;
    }
    body.pdf-render .invoice-container {
      width: 100%;
      max-width: 800px;
      margin: 0;
      padding: 20px 30px;
      box-shadow: none;
    }
    @media print {
      @page { size: A4; margin: 6mm; }
      body { background: #fff; padding: 0; }
      .invoice-container { box-shadow: none; padding: 20px 30px; margin: 0; max-width: 100%; box-sizing: border-box; }
      .print-actions { display: none !important; }
      .header,
      .customer-section,
      .amount-words,
      .extra-info-section,
      .footer-section,
      .totals-container {
        break-inside: avoid;
        page-break-inside: avoid;
      }
      .items-table tr {
        break-inside: avoid;
        page-break-inside: avoid;
      }
      .footer-section {
        margin-top: 8px;
      }
    }
    .print-actions { margin-top: 30px; text-align: center; display: flex; gap: 10px; justify-content: center; }
    .print-actions button, .print-actions a {
      padding: 10px 20px;
      font-size: 14px;
      cursor: pointer;
      background: #0f766e;
      color: #fff;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 700;
    }
    .header {
      display: table;
      width: 100%;
      margin-bottom: 15px;
      table-layout: fixed;
    }
    .logo-section {
      display: table-cell;
      width: 62%;
      vertical-align: top;
      padding-right: 18px;
    }
    .logo-container {
      margin-bottom: 6px;
      min-height: 112px;
    }
    .invoice-logo {
      width: 260px;
      max-width: 260px;
      max-height: 112px;
      object-fit: contain;
      object-position: left top;
    }
    .logo-text {
      font-size: 30px;
      font-weight: 900;
      color: {{ $settings->primary_color ?? '#254a7c' }};
      line-height: 1.05;
      max-width: 360px;
    }
    .system-info { font-size: 10px; color: #000; margin-bottom: 2px; max-width: 430px; }
    .system-info.company-name {
      font-weight: 700;
      text-transform: uppercase;
      font-size: 13px;
      color: #000;
      letter-spacing: 0;
    }
    .invoice-number-box {
      border: 1px solid #aaa;
      border-radius: 8px;
      padding: 15px 30px;
      text-align: center;
      background-color: #efefef;
      display: table-cell;
      width: 38%;
      vertical-align: middle;
      min-width: 250px;
    }
    .invoice-number-box div { font-weight: 700; font-size: 16px; margin-bottom: 5px; color: #000; font-family: 'Montserrat', DejaVu Sans, Arial, sans-serif; }
    .invoice-number-box div:last-child { margin-bottom: 0; font-size: 16px; letter-spacing: 0; }
    .customer-section {
      display: table;
      width: 100%;
      table-layout: fixed;
      border-spacing: 0;
      margin-bottom: 15px;
    }
    .customer-box-left {
      display: table-cell;
      width: 60%;
      border: 1px solid #aaa;
      border-radius: 8px;
      padding: 10px 15px;
      vertical-align: top;
    }
    .customer-gap {
      display: table-cell;
      width: 3%;
      min-width: 3%;
      font-size: 1px;
      color: #fff;
    }
    .customer-box-right {
      display: table-cell;
      width: 37%;
      border: 1px solid #aaa;
      border-radius: 8px;
      padding: 10px 15px;
      vertical-align: middle;
    }
    .customer-box-left .title { font-weight: 700; font-size: 12px; margin-bottom: 5px; }
    .customer-box-left table, .customer-box-right table { border-collapse: collapse; }
    .customer-box-left td, .customer-box-right td { padding: 2px 0; font-size: 11px; vertical-align: top; line-height: 1.35; }
    td.lbl { font-weight: 700; width: 110px; white-space: nowrap; }
    .customer-box-left td.lbl { width: 60px; }
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .items-table th {
      background: #f1f3f5;
      padding: 10px 8px;
      border-top: 1px solid #aaa;
      border-bottom: 1px solid #aaa;
      font-size: 11px;
      font-weight: 700;
    }
    .items-table td { padding: 10px 8px; font-size: 11px; vertical-align: top; line-height: 1.4; }
    .center { text-align: center; }
    .left { text-align: left; }
    .right { text-align: right; }
    .items-table tbody tr { border-bottom: 1px solid #eee; }
    .items-table tbody tr:last-child { border-bottom: 1px solid #aaa; }
    .totals-container { display: block; margin-bottom: 15px; padding-right: 8px; break-inside: avoid; page-break-inside: avoid; }
    .totals-table { border-collapse: collapse; }
    .totals-container .totals-table { margin-left: auto; }
    .totals-table td { padding: 4px 0; font-size: 11px; font-weight: 700; text-align: right; }
    .totals-table td.val { width: 85px; padding-left: 10px; }
    .amount-words { border: 1px solid #aaa; border-radius: 8px; padding: 8px 12px; margin-bottom: 15px; font-size: 11px; font-weight: 700; background:#fff; }
    .extra-info-section { margin-bottom: 25px; }
    .extra-info-title { font-weight: 900; font-size: 13px; margin-bottom: 12px; color: {{ $settings->primary_color ?? '#38b6ff' }}; text-transform: uppercase; letter-spacing: .5px; }
    .cuotas-table { width: 420px; max-width: 420px; margin-right: auto; }
    .cuotas-table td.currency { text-align: left; width: 25px; padding-right: 0; }
    .cuotas-table td.amount { text-align: right; padding-left: 0; }
    .adicional-card { border: 1px solid #aaa; border-radius: 8px; padding: 15px 20px; background: #fbfbfb; }
    .adicional-table { width: 100%; border-collapse: collapse; }
    .adicional-table td { padding: 10px 0; vertical-align: top; font-size: 11px; line-height: 1.6; border-bottom: 1px solid #e0e0e0; }
    .adicional-table tr:last-child td { border-bottom: none; padding-bottom: 0; }
    .adicional-table td.lbl { font-weight: 700; width: 220px; color: #333; }
    .sub-lbl { font-weight: 600; color: #555; margin-right: 5px; }
    .footer-section { display: table; width: 100%; table-layout: fixed; break-inside: avoid; page-break-inside: avoid; }
    .footer-left { display: table-cell; width: 76%; border: 1px solid #aaa; border-radius: 8px; padding: 10px 15px; vertical-align: top; }
    .footer-info-top { font-size: 10px; }
    .footer-info-top div { margin-bottom: 3px; }
    .footer-info-bottom { text-align: center; font-size: 10px; margin-top: 15px; }
    .footer-gap { display: table-cell; width: 15px; }
    .footer-right { display: table-cell; border: 1px solid #aaa; border-radius: 8px; padding: 5px; width: 105px; height: 105px; text-align: center; vertical-align: middle; box-sizing: border-box; }
    .qr-code { width: 110px; height: 110px; max-width: 110px; max-height: 110px; object-fit: contain; }
    body.pdf-render .invoice-container { padding: 8px 12px 10px; }
    body.pdf-render .header { margin-bottom: 10px; }
    body.pdf-render .logo-section { width: 64%; }
    body.pdf-render .logo-container { min-height: 72px; margin-bottom: 2px; }
    body.pdf-render .invoice-logo { width: 150px; max-width: 150px; height: 72px; max-height: 72px; object-fit: fill; }
    body.pdf-render .system-info.company-name { font-size: 11px; }
    body.pdf-render .invoice-number-box { width: 36%; padding: 9px 14px; min-width: 0; }
    body.pdf-render .invoice-number-box div { font-size: 11px; margin-bottom: 4px; }
    body.pdf-render .invoice-number-box div:last-child { font-size: 11px; }
    body.pdf-render .customer-section { margin-bottom: 8px; }
    body.pdf-render .customer-box-left { width: 60%; }
    body.pdf-render .customer-gap { width: 3%; min-width: 3%; }
    body.pdf-render .customer-box-right { width: 37%; }
    body.pdf-render .customer-box-left,
    body.pdf-render .customer-box-right { padding: 7px 10px; }
    body.pdf-render .customer-box-left .title { font-size: 10px; margin-bottom: 4px; }
    body.pdf-render .customer-box-left td,
    body.pdf-render .customer-box-right td { font-size: 8.2px; line-height: 1.15; padding: 1px 0; }
    body.pdf-render .items-table { margin-bottom: 8px; }
    body.pdf-render .items-table th { padding: 6px 6px; font-size: 8.8px; }
    body.pdf-render .items-table td { padding: 6px 6px; font-size: 8.8px; line-height: 1.2; }
    body.pdf-render .totals-container { margin-bottom: 8px; }
    body.pdf-render .totals-table td { padding: 2px 0; font-size: 8.8px; }
    body.pdf-render .amount-words { padding: 6px 9px; margin-bottom: 8px; font-size: 8.8px; }
    body.pdf-render .extra-info-section { margin-bottom: 9px; }
    body.pdf-render .extra-info-title { font-size: 10px; margin-bottom: 6px; }
    body.pdf-render .cuotas-table { width: 420px; max-width: 420px; }
    body.pdf-render .adicional-card { padding: 8px 12px; }
    body.pdf-render .adicional-table td { padding: 4px 0; font-size: 8.4px; line-height: 1.25; }
    body.pdf-render .footer-left { width: 74%; padding: 8px 13px; }
    body.pdf-render .footer-info-top,
    body.pdf-render .footer-info-bottom { font-size: 8px; }
    body.pdf-render .footer-info-bottom { margin-top: 7px; }
    body.pdf-render .footer-gap { width: 12px; }
    body.pdf-render .footer-right { width: 118px; height: 96px; padding: 7px; }
    body.pdf-render .qr-code { width: 82px; height: 82px; max-width: 82px; max-height: 82px; }
  </style>
</head>
<body class="{{ ! empty($renderForPdf) ? 'pdf-render' : '' }}">
@php
  $logoSrc = $settings?->quote_logo_src;
  $currencySymbol = $invoice->codigo_moneda === 'USD' ? 'US$' : ($invoice->codigo_moneda === 'EUR' ? '€' : 'S/');
  $documentTitle = match ($invoice->codigo_tipo_documento) {
      '03' => 'BOLETA ELECTRÓNICA',
      '07' => 'NOTA DE CRÉDITO ELECTRÓNICA',
      '08' => 'NOTA DE DÉBITO ELECTRÓNICA',
      default => 'FACTURA ELECTRÓNICA',
  };
  $detraction = is_array($invoice->informacion_detraccion) ? $invoice->informacion_detraccion : [];
  $retention = is_array($invoice->retention_info) ? $invoice->retention_info : [];
  $cuotas = is_array($invoice->lista_cuotas) ? $invoice->lista_cuotas : [];
@endphp
  <div class="invoice-container">
    <div class="header">
      <div class="logo-section">
        <div class="logo-container">
          @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Logo" class="invoice-logo">
          @else
            <div class="logo-text">{{ $company->name }}</div>
          @endif
        </div>
        <div class="system-info company-name">{{ $company->name }}</div>
        @if($company->direccion_fiscal)
          <div class="system-info">{{ $company->direccion_fiscal }}</div>
        @endif
      </div>
      <div class="invoice-number-box">
        <div>RUC {{ $company->ruc }}</div>
        <div>{{ $documentTitle }}</div>
        <div>{{ $invoice->serie_documento }}-{{ str_pad((string) $invoice->numero_documento, 8, '0', STR_PAD_LEFT) }}</div>
      </div>
    </div>

    <div class="customer-section">
      <div class="customer-box-left">
        <div class="title">Datos del cliente</div>
        <table>
          <tr><td class="lbl">RUC/DOC</td><td>: {{ $client?->numero_documento ?? '-' }}</td></tr>
          <tr><td class="lbl">Cliente</td><td>: {{ $client?->nombre_razon_social ?? 'CLIENTE VARIOS' }}</td></tr>
          <tr><td class="lbl">Dirección</td><td>: {{ $client?->direccion ?? '-' }}</td></tr>
        </table>
      </div>
      <div class="customer-gap">&nbsp;</div>
      <div class="customer-box-right">
        <table>
          <tr><td class="lbl">Fecha de emisión</td><td>: {{ optional($invoice->fecha_emision)->format('Y-m-d') }}</td></tr>
          <tr><td class="lbl">Fecha de vencim.</td><td>: {{ optional($invoice->fecha_vencimiento)->format('Y-m-d') ?: optional($invoice->fecha_emision)->format('Y-m-d') }}</td></tr>
          <tr><td class="lbl">Moneda</td><td>: {{ $invoice->codigo_moneda }}</td></tr>
        </table>
      </div>
    </div>

    <table class="items-table">
      <thead>
        <tr>
          <th class="center" style="width:6%">Cant.</th>
          <th class="center" style="width:6%">Um</th>
          <th class="center" style="width:8%">Cod</th>
          <th class="left" style="width:40%">Descripción</th>
          <th class="right" style="width:10%">V/U</th>
          <th class="right" style="width:10%">P.Unit</th>
          <th class="right" style="width:8%">Dto.</th>
          <th class="right" style="width:12%">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->items as $item)
          <tr>
            <td class="center">{{ rtrim(rtrim(number_format((float) $item->cantidad, 3, '.', ''), '0'), '.') }}</td>
            <td class="center">{{ $item->codigo_unidad_medida }}</td>
            <td class="center">{{ $item->codigo_interno }}</td>
            <td class="left"><strong>{{ $item->descripcion }}</strong></td>
            <td class="right">{{ number_format((float) $item->monto_valor_unitario, 2) }}</td>
            <td class="right">{{ number_format((float) $item->monto_precio_unitario, 2) }}</td>
            <td class="right">{{ number_format((float) ($item->monto_descuento ?? 0), 2) }}</td>
            <td class="right">{{ number_format((float) $item->monto_total, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="totals-container">
      <table class="totals-table">
        <tr><td>Op. Gravadas: {{ $currencySymbol }}</td><td class="val">{{ number_format((float) $invoice->monto_total_gravado, 2) }}</td></tr>
        <tr><td>Op. Exoneradas: {{ $currencySymbol }}</td><td class="val">{{ number_format((float) $invoice->monto_total_exonerado, 2) }}</td></tr>
        <tr><td>Op. Inafectas: {{ $currencySymbol }}</td><td class="val">{{ number_format((float) $invoice->monto_total_inafecto, 2) }}</td></tr>
        <tr><td>IGV {{ number_format((float) $invoice->porcentaje_igv, 2) }}%: {{ $currencySymbol }}</td><td class="val">{{ number_format((float) $invoice->monto_total_igv, 2) }}</td></tr>
        <tr><td>Total a pagar: {{ $currencySymbol }}</td><td class="val">{{ number_format((float) $invoice->monto_total, 2) }}</td></tr>
      </table>
    </div>

    <div class="amount-words">SON: {{ $amountWords }}</div>

    @if(count($cuotas) > 0)
      <div class="extra-info-section">
        <div class="extra-info-title">Información de Cuotas</div>
        <table class="items-table cuotas-table">
          <thead>
            <tr><th class="center">Cuota</th><th class="center">Fec. Vencimiento</th><th class="right" colspan="2">Monto</th></tr>
          </thead>
          <tbody>
            @foreach($cuotas as $idx => $cuota)
              <tr>
                <td class="center">Cuota{{ str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT) }}</td>
                <td class="center">{{ $cuota['fecha_pago'] ?? '-' }}</td>
                <td class="currency">{{ $currencySymbol }}</td>
                <td class="amount">{{ number_format((float) ($cuota['monto'] ?? $cuota['importe'] ?? 0), 2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    @if($invoice->indicador_detraccion || $invoice->retention_enabled || $invoice->has_retention)
      <div class="extra-info-section">
        <div class="extra-info-title">Información Adicional</div>
        <div class="adicional-card">
          <table class="adicional-table">
            <tbody>
              @if($invoice->indicador_detraccion)
                <tr>
                  <td class="lbl">Cta Detracción</td>
                  <td>: {{ $detraction['cuenta_banco_detraccion'] ?? '-' }}</td>
                </tr>
                <tr>
                  <td class="lbl">Información de la Detracción</td>
                  <td>
                    <div><span class="sub-lbl">Bien o Servicio:</span> {{ $detraction['codigo_bbss_sujeto_detraccion'] ?? '-' }}</div>
                    <div><span class="sub-lbl">Porcentaje:</span> {{ number_format((float) ($detraction['porcentaje_detraccion'] ?? 0), 2) }}%</div>
                    <div><span class="sub-lbl">Monto detracción:</span> {{ $invoice->codigo_moneda }} {{ number_format((float) ($detraction['monto_detraccion'] ?? 0), 2) }}</div>
                    <div><span class="sub-lbl">Medio de pago:</span> {{ $detraction['codigo_medio_pago_detraccion'] ?? '-' }}</div>
                    <div><span class="sub-lbl">Monto neto pendiente de pago:</span> {{ $invoice->codigo_moneda }} {{ number_format((float) ($invoice->net_total ?? ((float) $invoice->monto_total - (float) ($detraction['monto_detraccion'] ?? 0))), 2) }}</div>
                  </td>
                </tr>
              @endif
              @if($invoice->retention_enabled || $invoice->has_retention)
                <tr>
                  <td class="lbl">Información Retención</td>
                  <td>
                    <div><span class="sub-lbl">Base imponible retención:</span> {{ $invoice->codigo_moneda }} {{ number_format((float) ($invoice->retention_base ?? $retention['monto_base_imponible_retencion'] ?? 0), 2) }}</div>
                    <div><span class="sub-lbl">Porcentaje retención:</span> {{ number_format((float) ($invoice->retention_percentage ?? $retention['porcentaje_retencion'] ?? 0), 2) }}%</div>
                    <div><span class="sub-lbl">Monto retención:</span> {{ $invoice->codigo_moneda }} {{ number_format((float) ($invoice->retention_amount ?? $retention['monto_retencion'] ?? 0), 2) }}</div>
                    <div><span class="sub-lbl">Monto neto pendiente de pago:</span> {{ $invoice->codigo_moneda }} {{ number_format((float) ($invoice->total_after_retention ?? $invoice->net_total ?? $invoice->monto_total), 2) }}</div>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    @endif

    <div class="footer-section">
      <div class="footer-left">
        <div class="footer-info-top">
          <div><strong>Condición de Pago:</strong> {{ (string) $invoice->forma_pago === '2' ? 'Crédito' : 'Contado' }}</div>
          <div><strong>Código Hash:</strong> {{ $hashValue }}</div>
        </div>
        <div class="footer-info-bottom">
          Representación impresa del Comprobante de Pago Electrónico.<br>
          Esta puede ser consultada en: <strong>https://portal.feasyperu.com</strong>
        </div>
      </div>
      <div class="footer-gap"></div>
      <div class="footer-right">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrValue) }}" alt="Código QR" class="qr-code">
      </div>
    </div>

    @empty($renderForPdf)
      <div class="print-actions">
        <button onclick="window.print()">Imprimir a PDF</button>
        <a href="{{ route('facturador.invoices.custom-pdf', $invoice) }}">Descargar PDF</a>
        <a href="{{ route('facturador.invoices.show', $invoice) }}">Volver</a>
      </div>
    @endempty
  </div>
</body>
</html>
