<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para crear una cotización.
 */
class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se verifica en el controlador con policies
    }

    public function rules(): array
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_emision',
            'observacion' => 'nullable|string|max:500',
            'correo' => 'nullable|email',
            'numero_orden_compra' => 'nullable|string|max:50',
            'codigo_moneda' => 'required|in:PEN,USD',
            'porcentaje_igv' => 'required|numeric|min:0|max:100',
            'items_json' => 'required|json',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_emision.required' => 'La fecha de emisión es requerida.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la emisión.',
            'porcentaje_igv.required' => 'El porcentaje de IGV es requerido.',
            'items_json.required' => 'Debe agregar al menos un item a la cotización.',
            'items_json.json' => 'Los items deben ser un JSON válido.',
        ];
    }
}
