<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización #{{ $quote->numero_cotizacion }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: {{ $settings?->primary_color ?? '#1a6b57' }};
            --secondary-color: {{ $settings?->secondary_color ?? '#e5f5f1' }};
        }
        
        body {
            font-family: Arial, sans-serif;
            color: #333;
            font-size: 11pt;
            line-height: 1.4;
            background: white;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        .header {
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }
        
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        
        .company-info {
            font-size: 10pt;
            line-height: 1.5;
        }
        
        .company-info strong {
            font-size: 12pt;
            display: block;
            margin-bottom: 5px;
        }
        
        .quote-info {
            text-align: right;
        }
        
        .quote-info-box {
            background: var(--secondary-color);
            border: 2px solid var(--primary-color);
            padding: 12px;
            border-radius: 4px;
        }
        
        .quote-number {
            font-size: 16pt;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .quote-label {
            font-size: 9pt;
            color: #666;
        }
        
        .section-title {
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11pt;
            margin: 15px 0 8px 0;
            border-left: 4px solid var(--primary-color);
        }
        
        .client-info, .quote-details {
            display: inline-block;
            width: 48%;
            vertical-align: top;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        
        .client-info {
            margin-right: 4%;
        }
        
        .label {
            font-weight: bold;
            color: var(--primary-color);
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        
        thead {
            background: var(--secondary-color);
        }
        
        th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-size: 9pt;
        }
        
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .amount {
            text-align: right;
            font-family: monospace;
        }
        
        .summary {
            width: 45%;
            margin-left: auto;
            margin-top: 15px;
        }
        
        .summary-line {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            padding: 6px 0;
            font-size: 10pt;
        }
        
        .summary-line.total {
            border-top: 2px solid var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding: 10px 0;
            font-weight: bold;
            font-size: 12pt;
            color: var(--primary-color);
        }
        
        .observations {
            background: #f9fafb;
            border-left: 4px solid var(--secondary-color);
            padding: 10px;
            margin: 15px 0;
            font-size: 10pt;
        }
        
        .bank-accounts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
            font-size: 9pt;
        }
        
        .bank-account {
            background: #f9fafb;
            padding: 8px;
            border-radius: 3px;
            border-left: 3px solid var(--primary-color);
        }
        
        .footer {
            border-top: 1px solid #d1d5db;
            margin-top: 20px;
            padding-top: 10px;
            font-size: 9pt;
            color: #666;
        }
        
        .no-print {
            display: none;
        }
        
        @page {
            size: A4;
            margin: 10mm;
        }
        
        @media print {
            .page {
                box-shadow: none;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Encabezado --}}
        <div class="header">
            <div class="header-content">
                <div>
                    @if($settings?->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="logo">
                    @endif
                    <div class="company-info">
                        @if($settings?->company_name)
                            <strong>{{ $settings->company_name }}</strong>
                        @endif
                        @if($settings?->ruc)
                            RUC: {{ $settings->ruc }}
                        @endif
                        @if($settings?->address)
                            <div>{{ $settings->address }}</div>
                        @endif
                    </div>
                </div>
                <div class="quote-info">
                    <div class="quote-info-box">
                        <div class="quote-label">COTIZACIÓN</div>
                        <div class="quote-number">{{ $quote->numero_cotizacion }}</div>
                        <div class="quote-label">
                            Emitida: {{ $quote->fecha_emision->format('d/m/Y') }}<br>
                            Vigencia: {{ $quote->fecha_vencimiento?->format('d/m/Y') ?? 'S.V.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Información de cliente y detalles --}}
        <div style="margin-bottom: 15px;">
            <div class="client-info">
                <div class="label">INFORMACIÓN DEL CLIENTE</div>
                <div>
                    <strong>{{ $quote->client?->nombre_cliente }}</strong><br>
                    @if($quote->client?->numero_documento)
                        {{ $quote->client->numero_documento }}<br>
                    @endif
                    @if($quote->client?->direccion)
                        {{ $quote->client->direccion }}<br>
                    @endif
                </div>
            </div>
            <div class="quote-details">
                <div class="label">DETALLES DE LA COTIZACIÓN</div>
                <div>
                    Estado: <strong>
                        @switch($quote->estado)
                            @case('draft') Borrador @break
                            @case('sent') Enviada @break
                            @case('accepted') ✓ Aceptada @break
                            @case('rejected') ✗ Rechazada @break
                        @endswitch
                    </strong><br>
                    Moneda: <strong>{{ $quote->codigo_moneda }}</strong><br>
                    IGV: <strong>{{ $quote->porcentaje_igv }}%</strong>
                </div>
            </div>
        </div>

        {{-- Tabla de productos --}}
        <div class="section-title">PRODUCTOS Y SERVICIOS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Descripción</th>
                    <th style="width: 15%; text-align: right;">Cantidad</th>
                    <th style="width: 18%; text-align: right;">Precio Unit.</th>
                    <th style="width: 17%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quote->items as $item)
                    <tr>
                        <td>{{ $item->descripcion }}</td>
                        <td class="amount">{{ number_format($item->cantidad, 2) }}</td>
                        <td class="amount">{{ $quote->codigo_moneda }} {{ number_format($item->monto_valor_unitario, 2) }}</td>
                        <td class="amount"><strong>{{ $quote->codigo_moneda }} {{ number_format($item->monto_total, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">Sin items</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Resumen de totales --}}
        <div class="summary">
            <div class="summary-line">
                <span>Subtotal:</span>
                <span class="amount">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_gravado, 2) }}</span>
            </div>
            <div class="summary-line">
                <span>IGV ({{ $quote->porcentaje_igv }}%):</span>
                <span class="amount">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_igv, 2) }}</span>
            </div>
            @if($quote->monto_total_descuento)
                <div class="summary-line">
                    <span>Descuento:</span>
                    <span class="amount">-{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_descuento, 2) }}</span>
                </div>
            @endif
            <div class="summary-line total">
                <span>TOTAL:</span>
                <span class="amount">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total, 2) }}</span>
            </div>
        </div>

        {{-- Observaciones --}}
        @if($quote->observacion)
            <div class="section-title">OBSERVACIONES</div>
            <div class="observations">
                {{ $quote->observacion }}
            </div>
        @endif

        {{-- Datos bancarios --}}
        @if($settings?->bank_accounts && count($settings->bank_accounts) > 0)
            <div class="section-title">DATOS BANCARIOS</div>
            <div class="bank-accounts">
                @foreach($settings->bank_accounts as $bank)
                    <div class="bank-account">
                        <strong>{{ $bank['banco'] ?? 'Banco' }}</strong><br>
                        Cuenta: {{ $bank['cuenta'] }}<br>
                        @if($bank['cci'] ?? null)
                            CCI: {{ $bank['cci'] }}
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Pie de página --}}
        <div class="footer">
            @if($settings?->quote_thanks_message)
                <div style="margin-bottom: 8px;">{{ $settings->quote_thanks_message }}</div>
            @endif
            <div style="border-top: 1px solid #d1d5db; padding-top: 8px;">
                Documento generado automáticamente. Para información adicional, contáctenos.
            </div>
        </div>
    </div>
</body>
</html>
