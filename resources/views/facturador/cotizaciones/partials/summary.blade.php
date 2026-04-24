{{-- Parcial para mostrar el resumen de totales --}}
<div class="card-body">
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted">Subtotal:</span>
        <strong>{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_gravado, 2) }}</strong>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted">IGV ({{ $quote->porcentaje_igv }}%):</span>
        <strong>{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_igv, 2) }}</strong>
    </div>
    @if($quote->monto_total_descuento)
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Descuento:</span>
            <strong>-{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total_descuento, 2) }}</strong>
        </div>
    @endif
    <hr class="my-3">
    <div class="d-flex justify-content-between">
        <span class="fs-5 fw-bold">Total:</span>
        <span class="fs-5 fw-bold text-success">{{ $quote->codigo_moneda }} {{ number_format($quote->monto_total, 2) }}</span>
    </div>
</div>
