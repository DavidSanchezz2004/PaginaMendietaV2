<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para generación de guía de remisión.
 */
class GenerateGuiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $purchase = $this->route('purchase');

        return auth()->user()->can('create', \App\Models\GuiaRemision::class) &&
            $purchase->status === 'assigned' &&
            !$purchase->guia &&
            $purchase->company_id === session('company_id');
    }

    public function rules(): array
    {
        return [
            'client_address_id' => [
                'required',
                'exists:client_addresses,id',
                // Validar que la dirección pertenece al cliente de la compra
                function ($attribute, $value, $fail) {
                    $purchase = $this->route('purchase');
                    $address = \App\Models\ClientAddress::find($value);

                    if ($address && $address->client_id !== $purchase->client_id) {
                        $fail('La dirección debe pertenecera al cliente asignado.');
                    }
                },
            ],
            'motivo'         => 'required|string|max:100',
            'items_prices'   => 'nullable|array',
            'items_prices.*' => 'nullable|numeric|min:0',
            'gre_payload'    => 'nullable|json',
        ];
    }

    public function messages(): array
    {
        return [
            'client_address_id.required' => 'Debe seleccionar una dirección',
            'client_address_id.exists' => 'La dirección seleccionada no existe',
            'motivo.required' => 'Debe ingresar el motivo del traslado',
            'motivo.max' => 'El motivo no puede exceder 100 caracteres',
        ];
    }
}
