<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <title>Cotización {{ $cotNumber }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  @php
    $colors = $settings->colors;
    $primary = $colors['primary'];
    $secondary = $colors['secondary'];
    $logoSrc = $settings->quote_logo_src ?: asset('images/logoMendieta.png');
    $issuerName = $settings->company_name ?: $company->name;
    $issuerRuc = $settings->ruc ?: $company->ruc;
    $paymentAccounts = $settings->quote_payment_info ?? $settings->bank_accounts ?? [];
    $showIgv = $aplicaIgv && ($settings->show_igv_breakdown ?? true);
  @endphp
  <style>
    @page { size:A4; margin:0; }
    * { -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; box-sizing:border-box; }
    html,body{margin:0;padding:0;width:100%;min-height:100%;font-family:"Montserrat",sans-serif;background:#eef2f4;color:#1f2937}
    .sheet{width:210mm;min-height:297mm;margin:0 auto;background:#fff;display:flex;flex-direction:column}
    .no-print{background:{{ $primary }};padding:10px 15mm;display:flex;gap:10px;align-items:center}
    .no-print button{background:#fff;color:{{ $primary }};border:none;border-radius:6px;padding:8px 18px;font:600 13px "Montserrat";cursor:pointer}
    .header{background:{{ $primary }};color:#fff;padding:20px 15mm;display:grid;grid-template-columns:1fr 1.1fr 1fr;align-items:center;gap:14px}
    .logo-box{width:128px;height:62px;display:flex;align-items:center;justify-content:flex-start;overflow:hidden}
    .logo-box img{max-width:126px;max-height:60px;object-fit:contain}
    .title{text-align:center;font-size:23px;font-weight:800;letter-spacing:0}
    .cot-number{text-align:center;font-size:12px;margin-top:4px;opacity:.92}
    .meta{text-align:right;font-size:12px;line-height:1.75}
    .info-box{display:grid;grid-template-columns:1fr 1fr;gap:15px;padding:16px 15mm}
    .box{border-left:4px solid {{ $primary }};padding:14px;border-radius:7px;background:#f8fafc;min-height:96px}
    .box.empresa{background:{{ $secondary }}}
    .label{font-size:10px;font-weight:800;color:{{ $primary }};text-transform:uppercase}
    .box h2{font-size:14px;margin:5px 0;font-weight:800;color:#111827}
    .box p{font-size:11px;margin:2px 0;line-height:1.45}
    .section{padding:8px 15mm}
    .section h3{border-bottom:2px solid {{ $primary }};padding-bottom:5px;color:{{ $primary }};font-size:14px;margin:0 0 10px}
    .section p{font-size:12px;line-height:1.55;margin:0}
    table{width:100%;border-collapse:collapse;margin-top:10px;font-size:12px}
    th{background:{{ $primary }};color:#fff;padding:8px;text-align:left;font-weight:700}
    td{border:1px solid #e5e7eb;padding:8px;vertical-align:top}
    .amount{text-align:right;white-space:nowrap}
    .total-box{margin:8px 15mm 0 auto;width:72mm;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden}
    .total-row{display:flex;justify-content:space-between;padding:8px 10px;font-size:12px;border-bottom:1px solid #e5e7eb}
    .total-row:last-child{border-bottom:none;background:{{ $secondary }};font-size:16px;font-weight:800;color:{{ $primary }}}
    .payment{padding:12px 15mm;page-break-inside:avoid}
    .payment h3,.terms h3{color:{{ $primary }};font-size:14px;margin:0 0 10px}
    .payment-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .bank{background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px;font-size:11px;line-height:1.5;display:flex;gap:10px;align-items:center}
    .bank-icon{width:54px;height:54px;flex:0 0 54px;border-radius:8px;background:#fff;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#94a3b8;font-size:.68rem;text-align:center}
    .bank-icon img{max-width:50px;max-height:50px;object-fit:contain}
    .bank strong{font-size:12px;color:#111827}
    .terms{padding:8px 15mm;font-size:11px;line-height:1.55;color:#475569}
    .footer{margin-top:auto;padding:12px 15mm 10px;font-size:11px;color:#475569}
    .signatures{display:flex;justify-content:space-between;padding:0 15mm 16mm;margin-top:48px}
    .line{border-top:1px solid #111827;width:180px;padding-top:6px;text-align:center;font-size:11px}
    @media print{body{background:#fff}.sheet{margin:0;width:210mm;min-height:297mm}.no-print{display:none!important}}
  </style>
</head>
<body>
<div class="sheet">
  <div class="no-print">
    <button onclick="window.print()">Imprimir / Guardar PDF</button>
    <button onclick="history.back()">Volver</button>
    @isset($quote)
      <button onclick="location.href='{{ route('facturador.cotizaciones.show', $quote) }}'">Ver registro guardado</button>
    @endisset
  </div>

  <div class="header">
    <div class="logo-box"><img src="{{ $logoSrc }}" alt="{{ $issuerName }}"></div>
    <div>
      <div class="title">COTIZACIÓN</div>
      <div class="cot-number">N° {{ $cotNumber }}</div>
    </div>
    <div class="meta">
      Fecha: {{ \Carbon\Carbon::parse($fechaEmision)->format('d/m/Y') }}<br>
      Válido hasta: {{ \Carbon\Carbon::parse($fechaVencimiento)->format('d/m/Y') }}
    </div>
  </div>

  <div class="info-box">
    <div class="box empresa">
      <span class="label">Emisor</span>
      <h2>{{ $issuerName }}</h2>
      @if($issuerRuc)<p><strong>RUC:</strong> {{ $issuerRuc }}</p>@endif
      @if($settings->address)<p>{{ $settings->address }}</p>@endif
      @if($settings->phone || $settings->email)<p>{{ collect([$settings->phone, $settings->email])->filter()->join(' · ') }}</p>@endif
      @if($settings->website)<p>{{ $settings->website }}</p>@endif
    </div>
    <div class="box cliente">
      <span class="label">Cliente</span>
      <h2>{{ $clienteNombre }}</h2>
      <p><strong>{{ $clienteTipoDoc == '6' ? 'RUC' : 'DNI' }}:</strong> {{ $clienteNumeroDoc }}</p>
    </div>
  </div>

  @if($descripcion)
    <div class="section">
      <h3>Descripción del Servicio</h3>
      <p>{{ $descripcion }}</p>
    </div>
  @endif

  <div class="section">
    <h3>Detalle</h3>
    <table>
      <thead>
        <tr>
          <th>Servicio</th>
          <th class="amount">Cantidad</th>
          <th class="amount">Precio</th>
          <th class="amount">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $item)
          <tr>
            <td>{{ $item['servicio'] }}</td>
            <td class="amount">{{ number_format($item['cantidad'], 2) }}</td>
            <td class="amount">S/ {{ number_format($item['precio'], 2) }}</td>
            <td class="amount">S/ {{ number_format($item['total'], 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="total-box">
    @if($showIgv)
      <div class="total-row"><span>Subtotal</span><strong>S/ {{ number_format($subtotal, 2) }}</strong></div>
      <div class="total-row"><span>IGV (18%)</span><strong>S/ {{ number_format($igv, 2) }}</strong></div>
    @endif
    <div class="total-row"><span>Total</span><strong>S/ {{ number_format($total, 2) }}</strong></div>
  </div>

  @if(($settings->show_bank_accounts ?? true) && count($paymentAccounts))
    <div class="payment">
      <h3>Datos de Pago</h3>
      <div class="payment-grid">
        @foreach($paymentAccounts as $account)
          <div class="bank">
            <div class="bank-icon">
              @if(!empty($account['icon_base64']))
                <img src="{{ $account['icon_base64'] }}" alt="{{ $account['banco'] ?? 'Banco' }}">
              @else
                {{ $account['moneda'] ?? '' }}
              @endif
            </div>
            <div>
              <strong>{{ $account['banco'] ?? 'Cuenta bancaria' }}</strong>
              @if(!empty($account['titular']))<br>Titular: {{ $account['titular'] }}@endif
              @if(!empty($account['moneda']))<br>Moneda: {{ $account['moneda'] }}@endif
              @if(!empty($account['cuenta']))<br>Cuenta: {{ $account['cuenta'] }}@endif
              @if(!empty($account['cci']))<br>CCI: {{ $account['cci'] }}@endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  @if($settings->quote_terms)
    <div class="terms">
      <h3>Términos y Condiciones</h3>
      {!! nl2br(e($settings->quote_terms)) !!}
    </div>
  @endif

  <div class="footer">
    {!! nl2br(e($settings->quote_footer ?: 'Cotización válida hasta la fecha indicada. Quedamos atentos a su confirmación.')) !!}
    @if($settings->quote_thanks_message)
      <br><br>{!! nl2br(e($settings->quote_thanks_message)) !!}
    @endif
  </div>

  <div class="signatures">
    <div class="line">Firma del Cliente</div>
    <div class="line">{{ $issuerName }}</div>
  </div>
</div>
</body>
</html>
