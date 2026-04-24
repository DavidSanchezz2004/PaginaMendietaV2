<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización #{{ $quote->numero_cotizacion }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ $settings?->primary_color ?? '#1a6b57' }};
            --secondary-color: {{ $settings?->secondary_color ?? '#e5f5f1' }};
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, rgba(26, 107, 87, 0.9) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .header-logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 1rem;
        }
        
        .company-info {
            font-size: 0.95rem;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        .quote-number {
            background: rgba(255, 255, 255, 0.15);
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid white;
        }
        
        .section-title {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }
        
        table {
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        .table-header {
            background: var(--secondary-color);
            border-bottom: 2px solid var(--primary-color);
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .table-header th {
            padding: 0.75rem;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table tr:hover {
            background: var(--secondary-color);
        }
        
        .amount-right {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .summary-box {
            background: var(--secondary-color);
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            border-top: 2px solid var(--primary-color);
            padding-top: 1rem;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .btn-accept, .btn-reject {
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-accept {
            background: #10b981;
            color: white;
        }
        
        .btn-accept:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .btn-reject {
            background: #ef4444;
            color: white;
        }
        
        .btn-reject:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .footer {
            background: #f3f4f6;
            border-top: 1px solid #e5e7eb;
            padding: 2rem;
            margin-top: 3rem;
            border-radius: 8px;
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .bank-accounts {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .bank-account-item {
            margin-bottom: 1rem;
        }
        
        .bank-account-item:last-child {
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 2rem 0;
            }
            
            .header-content {
                flex-direction: column;
            }
            
            .quote-number {
                margin-top: 1rem;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            .table-header th {
                padding: 0.5rem;
            }
            
            .table td {
                padding: 0.5rem;
            }
        }
        
        @media print {
            body { background: white; }
            .header { page-break-after: avoid; }
            .actions { display: none; }
            .footer { border-top: 2px solid #333; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-md-6">
                    @if($settings?->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="{{ $settings->company_name }}" class="header-logo">
                    @else
                        <h2 class="mb-3">{{ $settings?->company_name ?? config('app.name') }}</h2>
                    @endif
                    <div class="company-info">
                        @if($settings?->company_name)
                            <div><strong>{{ $settings->company_name }}</strong></div>
                        @endif
                        @if($settings?->ruc)
                            <div>RUC: {{ $settings->ruc }}</div>
                        @endif
                        @if($settings?->address)
                            <div>{{ $settings->address }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="quote-number">
                        <div style="font-size: 0.85rem; opacity: 0.9;">COTIZACIÓN</div>
                        <div style="font-size: 2rem; font-weight: 700;">{{ $quote->numero_cotizacion }}</div>
                        <div style="font-size: 0.85rem; opacity: 0.9;">
                            Emitida: {{ $quote->fecha_emision->format('d/m/Y') }}<br>
                            Vigencia: {{ $quote->fecha_vencimiento?->format('d/m/Y') ?? 'Sin vencimiento' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        {{-- Información del cliente --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="section-title">Información del Cliente</h5>
                <div>
                    <strong>{{ $quote->client?->nombre_cliente }}</strong><br>
                    @if($quote->client?->numero_documento)
                        {{ $quote->client->numero_documento }}<br>
                    @endif
                    @if($quote->client?->direccion)
                        {{ $quote->client->direccion }}<br>
                    @endif
                    @if($quote->client?->email)
                        <a href="mailto:{{ $quote->client->email }}">{{ $quote->client->email }}</a>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <h5 class="section-title">Detalles de la Cotización</h5>
                <div>
                    <div><strong>Estado:</strong> 
                        <span class="badge" style="background: var(--primary-color);">
                            @switch($quote->estado)
                                @case('draft') Borrador @break
                                @case('sent') Enviada @break
                                @case('accepted') ✓ Aceptada @break
                                @case('rejected') ✗ Rechazada @break
                            @endswitch
                        </span>
                    </div>
                    <div><strong>Moneda:</strong> {{ $quote->codigo_moneda }}</div>
                    <div><strong>IGV:</strong> {{ $quote->porcentaje_igv }}%</div>
                </div>
            </div>
        </div>

        {{-- Tabla de productos --}}
        <h5 class="section-title">Productos y Servicios</h5>
        <table class="table table-sm mb-4">
            <thead class="table-header">
                <tr>
                    <th>Descripción</th>
                    <th class="amount-right" style="width: 80px;">Cantidad</th>
                    <th class="amount-right" style="width: 120px;">Precio Unit.</th>
                    <th class="amount-right" style="width: 100px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quote->items as $item)
                    <tr>
                        <td>{{ $item->descripcion }}</td>
                        <td class="amount-right">{{ number_format($item->cantidad, 2) }}</td>
                        <td class="amount-right">{{ $quote->codigo_moneda }} {{ number_format($item->monto_valor_unitario, 2) }}</td>
                        <td class="amount-right"><strong>{{ $quote->codigo_moneda }} {{ number_format($item->monto_total, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">Sin items</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Resumen de totales --}}
        <div class="row">
            <div class="col-md-6">
                {{-- Bancos --}}
                @if($settings?->bank_accounts)
                    <h5 class="section-title">Datos Bancarios</h5>
                    @foreach($settings->bank_accounts as $bank)
                        <div class="bank-account-item">
                            <strong>{{ $bank['banco'] ?? 'Banco' }}</strong><br>
                            Cuenta: {{ $bank['cuenta'] }}<br>
                            @if($bank['cci'] ?? null)
                                CCI: {{ $bank['cci'] }}
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="col-md-6">
                <div class="summary-box">
                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <span class="amount-right">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_gravado, 2) }}</span>
                    </div>
                    <div class="summary-line">
                        <span>IGV ({{ $quote->porcentaje_igv }}%):</span>
                        <span class="amount-right">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_igv, 2) }}</span>
                    </div>
                    @if($quote->monto_total_descuento)
                        <div class="summary-line">
                            <span>Descuento:</span>
                            <span class="amount-right">-{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_descuento, 2) }}</span>
                        </div>
                    @endif
                    <div class="summary-total">
                        <span>Total:</span>
                        <span>{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Observaciones --}}
        @if($quote->observacion)
            <h5 class="section-title">Observaciones</h5>
            <div style="background: #f9fafb; padding: 1.25rem; border-radius: 8px; border-left: 4px solid var(--secondary-color);">
                {{ $quote->observacion }}
            </div>
        @endif

        {{-- Acciones del cliente --}}
        @if($quote->estado === 'sent')
            <div class="actions">
                <form method="POST" action="{{ route('quotes.accept', $quote->share_token) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-accept" onclick="return confirm('¿Aceptar esta cotización?')">
                        ✓ Aceptar Cotización
                    </button>
                </form>
                <form method="POST" action="{{ route('quotes.reject', $quote->share_token) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-reject" onclick="return confirm('¿Rechazar esta cotización?')">
                        ✗ Rechazar Cotización
                    </button>
                </form>
            </div>
        @elseif($quote->estado === 'accepted')
            <div style="text-align: center; padding: 2rem; background: #dcfce7; border-radius: 8px; margin: 2rem 0;">
                <h5 style="color: #059669; margin-bottom: 0.5rem;">✓ Cotización Aceptada</h5>
                <p style="color: #047857; margin-bottom: 0;">
                    Aceptada el {{ $quote->accepted_at?->format('d/m/Y H:i') }}<br>
                    Pronto nos pondremos en contacto para confirmar detalles
                </p>
            </div>
        @elseif($quote->estado === 'rejected')
            <div style="text-align: center; padding: 2rem; background: #fee2e2; border-radius: 8px; margin: 2rem 0;">
                <h5 style="color: #dc2626; margin-bottom: 0.5rem;">✗ Cotización Rechazada</h5>
                <p style="color: #991b1b; margin-bottom: 0;">
                    Rechazada el {{ $quote->rejected_at?->format('d/m/Y H:i') }}
                </p>
            </div>
        @endif

        {{-- Pie de página --}}
        <div class="footer">
            @if($settings?->quote_footer)
                <p>{{ $settings->quote_footer }}</p>
            @endif
            
            @if($settings?->quote_thanks_message)
                <p>{{ $settings->quote_thanks_message }}</p>
            @endif
            
            <p style="margin-bottom: 0; border-top: 1px solid #d1d5db; padding-top: 1rem; margin-top: 1rem;">
                <strong>Este documento es una cotización oficial.</strong> Para información adicional, contáctanos directamente.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Permitir impresión
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
