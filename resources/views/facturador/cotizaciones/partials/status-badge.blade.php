{{-- Parcial para mostrar el badge de estado de una cotización --}}
@switch($quote->estado)
    @case('draft')
        <span class="badge bg-secondary">Borrador</span>
        @break
    @case('sent')
        <span class="badge bg-info">Enviada</span>
        @if($quote->sent_at)
            <small class="ms-2 text-muted">{{ $quote->sent_at->format('d/m/Y H:i') }}</small>
        @endif
        @break
    @case('accepted')
        <span class="badge bg-success">✓ Aceptada</span>
        @if($quote->accepted_at)
            <small class="ms-2 text-muted">{{ $quote->accepted_at->format('d/m/Y H:i') }}</small>
        @endif
        @break
    @case('rejected')
        <span class="badge bg-danger">✗ Rechazada</span>
        @if($quote->rejected_at)
            <small class="ms-2 text-muted">{{ $quote->rejected_at->format('d/m/Y H:i') }}</small>
        @endif
        @break
@endswitch
