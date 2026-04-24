@extends('layouts.app')

@section('title', "Cotización #{$quote->numero_cotizacion} | Portal Mendieta")

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Cotización #{{ $quote->numero_cotizacion }}</h1>
            <small class="text-muted">{{ $quote->codigo_interno }} • Versión {{ $quote->version }}</small>
        </div>
        <div>
            @if($quote->estado === 'draft')
                <form method="POST" action="{{ route('facturador.cotizaciones.send', $quote) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-send"></i> Enviar
                    </button>
                </form>
                <a href="{{ route('facturador.cotizaciones.edit', $quote) }}" class="btn btn-warning">
                    <i class="bx bx-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('facturador.cotizaciones.pdf', $quote) }}" class="btn btn-outline-secondary">
                <i class="bx bx-file-pdf"></i> PDF
            </a>
            <a href="{{ route('facturador.cotizaciones.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back"></i> Atrás
            </a>
        </div>
    </div>

    {{-- Badges de estado --}}
    <div class="mb-3">
        @switch($quote->estado)
            @case('draft')
                <span class="badge bg-secondary">Borrador</span>
                @break
            @case('sent')
                <span class="badge bg-info">Enviada</span>
                <small class="ms-2">{{ $quote->sent_at?->format('d/m/Y H:i') }}</small>
                @break
            @case('accepted')
                <span class="badge bg-success">Aceptada</span>
                <small class="ms-2">{{ $quote->accepted_at?->format('d/m/Y H:i') }}</small>
                @break
            @case('rejected')
                <span class="badge bg-danger">Rechazada</span>
                <small class="ms-2">{{ $quote->rejected_at?->format('d/m/Y H:i') }}</small>
                @break
        @endswitch
    </div>

    <div class="row">
        {{-- Información general --}}
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Fecha de emisión</label>
                            <p class="fw-bold">{{ $quote->fecha_emision?->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Fecha de vencimiento</label>
                            <p class="fw-bold">{{ $quote->fecha_vencimiento?->format('d/m/Y') ?? 'No especificada' }}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Cliente</label>
                        <p class="fw-bold">{{ $quote->client?->nombre_cliente ?? 'No asignado' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Observaciones</label>
                        <p>{{ $quote->observacion ?? 'Sin observaciones' }}</p>
                    </div>
                </div>
            </div>

            {{-- Items de la cotización --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Productos/Servicios</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Descripción</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quote->items as $item)
                                <tr>
                                    <td>{{ $item->descripcion }}</td>
                                    <td class="text-end">{{ number_format($item->cantidad, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->monto_valor_unitario, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->monto_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Sin items</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel lateral: totales y acciones --}}
        <div class="col-md-4">
            <div class="card sticky-top" style="top:20px;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Resumen</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_gravado, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>IGV ({{ $quote->porcentaje_igv }}%):</span>
                        <strong>{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_igv, 2) }}</strong>
                    </div>
                    @if($quote->monto_total_descuento)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Descuento:</span>
                            <strong>-{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_descuento, 2) }}</strong>
                        </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-5 fw-bold">Total:</span>
                        <span class="fs-5 fw-bold text-success">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total, 2) }}</span>
                    </div>

                    {{-- Compartir --}}
                    @if($quote->estado === 'sent')
                        <div class="mb-3">
                            <label class="text-muted small">Link para compartir</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" value="{{ $quote->getShareUrlAttribute() }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $quote->getShareUrlAttribute() }}')">
                                    <i class="bx bx-copy"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Comparte este enlace con el cliente</small>
                        </div>
                    @endif

                    {{-- Acciones --}}
                    @if($quote->estado === 'accepted' && !$quote->invoice_id)
                        <form method="POST" action="{{ route('facturador.cotizaciones.to-invoice', $quote) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-file"></i> Convertir a Factura
                            </button>
                        </form>
                    @endif

                    @if($quote->estado === 'draft')
                        <form method="POST" action="{{ route('facturador.cotizaciones.destroy', $quote) }}" 
                            onsubmit="return confirm('¿Eliminar cotización?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bx bx-trash"></i> Eliminar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
