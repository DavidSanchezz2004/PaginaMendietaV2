{{-- Parcial para renderizar un item de cotización en lectura --}}
<tr>
    <td>{{ $item->descripcion }}</td>
    <td class="text-end">{{ number_format($item->cantidad, 2) }}</td>
    <td class="text-end">{{ $quote->codigo_moneda }} {{ number_format($item->monto_valor_unitario, 2) }}</td>
    <td class="text-end fw-bold">{{ $quote->codigo_moneda }} {{ number_format($item->monto_total, 2) }}</td>
</tr>
