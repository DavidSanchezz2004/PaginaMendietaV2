<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para habilitar/deshabilitar el Facturador y
 * configurar el token Feasy de una empresa.
 * Solo el admin interno puede ejecutar esto.
 */
class UpdateCompanyFacturadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');
        return $this->user()->can('updateFacturadorConfig', $company);
    }

    public function rules(): array
    {
        return [
            'facturador_enabled' => ['required', 'boolean'],
            'razon_social'       => ['nullable', 'string', 'max:200'],
            'ubigeo'             => ['nullable', 'string', 'max:10'],
            'direccion_fiscal'   => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'facturador_enabled.required' => 'Debes indicar si el Facturador está habilitado.',
        ];
    }
}
