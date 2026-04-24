@extends('layouts.app')

@section('title', "Editar Cotización #{$quote->numero_cotizacion} | Portal Mendieta")

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Editar Cotización #{{ $quote->numero_cotizacion }}</h1>
    </div>

    <form method="POST" action="{{ route('facturador.cotizaciones.update', $quote) }}" id="quoteForm">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Datos generales --}}
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Información General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cliente <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" disabled>
                                    <option value="{{ $quote->client_id }}">{{ $quote->client?->nombre_cliente ?? 'Sin cliente' }}</option>
                                </select>
                                <small class="text-muted">No puede modificarse</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Moneda <span class="text-danger">*</span></label>
                                <select name="codigo_moneda" class="form-select @error('codigo_moneda') is-invalid @enderror" disabled>
                                    <option value="{{ $quote->codigo_moneda }}" selected>{{ $quote->codigo_moneda }}</option>
                                </select>
                                <small class="text-muted">No puede modificarse</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de emisión <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_emision" class="form-control @error('fecha_emision') is-invalid @enderror"
                                    value="{{ old('fecha_emision', $quote->fecha_emision->format('Y-m-d')) }}" required>
                                @error('fecha_emision')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de vencimiento</label>
                                <input type="date" name="fecha_vencimiento" class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                                    value="{{ old('fecha_vencimiento', $quote->fecha_vencimiento?->format('Y-m-d')) }}">
                                @error('fecha_vencimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">IGV (%)</label>
                            <input type="number" name="porcentaje_igv" class="form-control @error('porcentaje_igv') is-invalid @enderror"
                                value="{{ old('porcentaje_igv', $quote->porcentaje_igv) }}" step="0.01" min="0" max="100" required>
                            @error('porcentaje_igv')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observacion" class="form-control" rows="3">{{ old('observacion', $quote->observacion) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="card mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Productos/Servicios</h5>
                        <button type="button" class="btn btn-sm btn-success" onclick="addItem()">
                            <i class="bx bx-plus"></i> Agregar item
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción</th>
                                    <th style="width:100px;">Cantidad</th>
                                    <th style="width:120px;">Precio Unit.</th>
                                    <th style="width:100px;">Total</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTable">
                                {{-- Items cargados aquí --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Panel totales --}}
            <div class="col-md-4">
                <div class="card sticky-top" style="top:20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Resumen</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="subtotal">S/ 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>IGV (<span id="igvPercent">{{ $quote->porcentaje_igv }}</span>%):</span>
                            <strong id="igvAmount">S/ 0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fs-5 fw-bold">Total:</span>
                            <span class="fs-5 fw-bold text-success" id="totalAmount">S/ 0.00</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bx bx-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('facturador.cotizaciones.show', $quote) }}" class="btn btn-outline-secondary w-100">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- JSON oculto con items --}}
        <input type="hidden" name="items_json" id="items_json" value="[]">
    </form>
</div>

<script>
let items = [];
let itemCounter = 0;

function addItem() {
    itemCounter++;
    const html = `
        <tr id="item-${itemCounter}">
            <td><input type="text" class="form-control form-control-sm item-desc" placeholder="Descripción"></td>
            <td><input type="number" class="form-control form-control-sm item-qty" value="1" step="0.01" min="0"></td>
            <td><input type="number" class="form-control form-control-sm item-price" value="0.00" step="0.01" min="0"></td>
            <td><input type="text" class="form-control form-control-sm item-total" readonly value="0.00"></td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${itemCounter})">×</button></td>
        </tr>
    `;
    document.getElementById('itemsTable').insertAdjacentHTML('beforeend', html);
    attachItemListeners();
}

function removeItem(id) {
    document.getElementById(`item-${id}`)?.remove();
    calculateTotals();
}

function attachItemListeners() {
    document.querySelectorAll('.item-qty, .item-price').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });
}

function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('#itemsTable tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        const total = qty * price;
        row.querySelector('.item-total').value = total.toFixed(2);
        subtotal += total;
    });

    const igvPercent = parseFloat(document.querySelector('input[name="porcentaje_igv"]').value || 18);
    const igvAmount = subtotal * (igvPercent / 100);
    const total = subtotal + igvAmount;

    document.getElementById('subtotal').textContent = formatMoney(subtotal);
    document.getElementById('igvAmount').textContent = formatMoney(igvAmount);
    document.getElementById('totalAmount').textContent = formatMoney(total);
    document.getElementById('igvPercent').textContent = igvPercent;

    updateItemsJson();
}

function updateItemsJson() {
    const itemsArray = [];
    document.querySelectorAll('#itemsTable tr').forEach(row => {
        itemsArray.push({
            descripcion: row.querySelector('.item-desc').value,
            cantidad: parseFloat(row.querySelector('.item-qty').value || 0),
            monto_valor_unitario: parseFloat(row.querySelector('.item-price').value || 0),
            codigo_unidad_medida: 'UND',
            monto_valor_total: parseFloat(row.querySelector('.item-total').value || 0),
            codigo_indicador_afecto: '10'
        });
    });
    document.getElementById('items_json').value = JSON.stringify(itemsArray);
}

function formatMoney(amount) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: '{{ $quote->codigo_moneda }}' === 'USD' ? 'USD' : 'PEN'
    }).format(amount);
}

// Cargar items existentes
document.addEventListener('DOMContentLoaded', () => {
    const existingItems = @json($quote->items);
    
    if (existingItems.length === 0) {
        addItem();
    } else {
        existingItems.forEach(item => {
            itemCounter++;
            const html = `
                <tr id="item-${itemCounter}">
                    <td><input type="text" class="form-control form-control-sm item-desc" value="${item.descripcion}"></td>
                    <td><input type="number" class="form-control form-control-sm item-qty" value="${item.cantidad}" step="0.01" min="0"></td>
                    <td><input type="number" class="form-control form-control-sm item-price" value="${item.monto_valor_unitario}" step="0.01" min="0"></td>
                    <td><input type="text" class="form-control form-control-sm item-total" readonly value="${item.monto_total}"></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${itemCounter})">×</button></td>
                </tr>
            `;
            document.getElementById('itemsTable').insertAdjacentHTML('beforeend', html);
        });
        attachItemListeners();
        calculateTotals();
    }
    
    document.querySelector('input[name="porcentaje_igv"]').addEventListener('input', calculateTotals);
});

// Validar antes de enviar
document.getElementById('quoteForm').addEventListener('submit', function(e) {
    const items = JSON.parse(document.getElementById('items_json').value);
    if (items.length === 0 || items.every(i => !i.descripcion)) {
        e.preventDefault();
        alert('Debe agregar al menos un item a la cotización');
        return false;
    }
});
</script>

<style>
.item-qty, .item-price, .item-total { font-family: monospace; text-align: right; }
</style>
@endsection
