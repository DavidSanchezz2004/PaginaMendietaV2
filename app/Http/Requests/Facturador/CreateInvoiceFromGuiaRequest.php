<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para creación de factura desde guía de remisión.
 * Valida que los items no excedan las cantidades disponibles en compra.
 */
class CreateInvoiceFromGuiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $guia = $this->route('guia');

        return auth()->user()->can('create', \App\Models\Invoice::class) &&
            $guia->estado === 'generated' &&
            !$guia->invoice_id &&
            $guia->purchase->can_be_invoiced() &&
            $guia->company_id === session('company_id');
    }

    public function rules(): array
    {
        $guia = $this->route('guia');

        return [
            'codigo_tipo_documento' => 'required|in:01,03',
            'serie_documento' => 'required|string|max:4',
            'numero_documento' => 'nullable|string|max:8', // Se genera automáticamente en backend
            'codigo_moneda' => 'nullable|string|max:3',
            'forma_pago' => 'required|in:1,2',
            'lista_cuotas' => 'nullable|array|max:12',
            'lista_cuotas.*.fecha_pago' => 'nullable|required_if:forma_pago,2|date_format:Y-m-d',
            'lista_cuotas.*.monto' => 'nullable|required_if:forma_pago,2|numeric|min:0.01',

            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => [
                'required',
                'exists:purchase_items,id',
                function ($attribute, $value, $fail) use ($guia) {
                    $item = \App\Models\PurchaseItem::find($value);

                    // Validar que el item pertenece a la compra
                    if ($item && $item->purchase_id !== $guia->purchase_id) {
                        $fail("El item {$value} no pertenece a esta compra");
                    }
                },
            ],
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.correlativo' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_tipo_documento.required' => 'Tipo de documento es requerido',
            'serie_documento.required' => 'Serie es requerida',
            'numero_documento.required' => 'Número es requerido',
            'items.required' => 'Debe seleccionar al menos un item',
            'items.*.purchase_item_id.required' => 'Item es requerido',
            'items.*.purchase_item_id.exists' => 'El item seleccionado no existe',
            'items.*.quantity.required' => 'Cantidad es requerida',
            'items.*.quantity.min' => 'Cantidad debe ser mayor a 0',
            'items.*.unit_price.required' => 'Precio unitario es requerido',
            'items.*.unit_price.min' => 'Precio no puede ser negativo',
        ];
    }

    /**
     * Prepara los datos para validación.
     * Convierte strings numéricos a float.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('items') && is_array($this->items)) {
            $items = [];
            foreach ($this->items as $item) {
                $items[] = [
                    'purchase_item_id' => (int) ($item['purchase_item_id'] ?? 0),
                    'quantity' => (float) ($item['quantity'] ?? 0),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'correlativo' => (int) ($item['correlativo'] ?? 1),
                ];
            }
            $this->merge(['items' => $items]);
        }
    }
}
