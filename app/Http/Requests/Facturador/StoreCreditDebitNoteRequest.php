<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditDebitNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'codigo_tipo_documento' => 'required|in:07,08',
            'codigo_tipo_nota' => 'required|in:01,02,03,04',
            'serie_documento' => 'required|string|max:4|regex:/^[A-Z0-9]+$/',
            'numero_documento' => 'required|numeric|digits:8',
            'codigo_interno' => 'required|string|max:20|unique:credit_debit_notes,codigo_interno',
            'fecha_emision' => 'required|date',
            'hora_emision' => 'nullable|date_format:H:i',
            'observacion' => 'nullable|string|max:500',
            'correo' => 'nullable|email|max:255',
            'porcentaje_igv' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.codigo_interno' => 'required|string|max:50',
            'items.*.descripcion' => 'required|string|max:500',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.monto_precio_unitario' => 'required|numeric|min:0.01',
            'items.*.codigo_indicador_afecto' => 'required|in:10,30,40',
            'items.*.tipo' => 'in:P,S',
            'items.*.codigo_unidad_medida' => 'required|string|max:3',
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Debe seleccionar una factura o boleta.',
            'invoice_id.exists' => 'La factura seleccionada no existe.',
            'codigo_tipo_documento.in' => 'Tipo de comprobante inválido.',
            'codigo_tipo_nota.in' => 'Tipo de nota inválido.',
            'codigo_interno.unique' => 'El código interno ya existe.',
            'items.required' => 'Debe agregar al menos un item.',
        ];
    }
}
