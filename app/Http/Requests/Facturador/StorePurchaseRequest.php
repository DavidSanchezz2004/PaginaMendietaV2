<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se hace en el controller
    }

    public function rules(): array
    {
        return [
            'codigo_tipo_documento'   => 'required|in:01,03,07,08,00',
            'serie_documento'         => 'nullable|string|max:10',
            'numero_documento'        => 'required|string|max:20',
            'fecha_emision'           => 'required|date',
            'fecha_vencimiento'       => 'nullable|date|after_or_equal:fecha_emision',

            // Proveedor
            'tipo_doc_proveedor'      => 'required|in:1,4,6,7,A,B,D,E,G',
            'numero_doc_proveedor'    => 'required|string|max:20',
            'razon_social_proveedor'  => 'required|string|max:200',

            // Moneda e importes
            'codigo_moneda'           => 'required|in:PEN,USD,EUR',
            'monto_tipo_cambio'       => 'nullable|numeric|min:0',
            'porcentaje_igv'          => 'required|integer|in:0,8,10,18',
            'base_imponible_gravadas' => 'required|numeric|min:0',
            'igv_gravadas'            => 'required|numeric|min:0',
            'monto_no_gravado'        => 'nullable|numeric|min:0',
            'monto_exonerado'         => 'nullable|numeric|min:0',
            'monto_exportacion'       => 'nullable|numeric|min:0',
            'monto_isc'               => 'nullable|numeric|min:0',
            'monto_icbper'            => 'nullable|numeric|min:0',
            'otros_tributos'          => 'nullable|numeric|min:0',
            'monto_descuento'         => 'nullable|numeric|min:0',
            'monto_total'             => 'required|numeric|min:0',

            // Forma de pago
            'forma_pago'              => 'nullable|in:01,02,03,04,05,06,07,08',

            // Nota/DUA
            'anio_emision_dua'        => 'nullable|digits:4',
            'tipo_doc_modifica'       => 'nullable|string|max:2',
            'serie_doc_modifica'      => 'nullable|string|max:10',
            'numero_doc_modifica'     => 'nullable|string|max:20',
            'fecha_doc_modifica'      => 'nullable|date',
            'tipo_nota'               => 'nullable|string|max:4',

            // Opcional
            'observacion'             => 'nullable|string|max:500',
        ];
    }
}
