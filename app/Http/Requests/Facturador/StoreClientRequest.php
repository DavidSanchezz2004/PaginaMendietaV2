<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Client::class);
    }

    public function rules(): array
    {
        return [
            'codigo_tipo_documento' => ['required', 'string', 'in:1,4,6,7,A'],
            'numero_documento'      => [
                'required', 'string', 'max:20',
                function ($attribute, $value, $fail) {
                    $tipo   = $this->input('codigo_tipo_documento');
                    $exists = \App\Models\Client::forActiveCompany()
                        ->where('codigo_tipo_documento', $tipo)
                        ->where('numero_documento', $value)
                        ->exists();
                    if ($exists) {
                        $fail('Ya existe un cliente con ese documento en esta empresa.');
                    }
                },
            ],
            'nombre_razon_social'   => ['required', 'string', 'max:200'],
            'codigo_pais'           => ['required', 'string', 'size:2'],
            'ubigeo'                => ['nullable', 'string', 'max:10'],
            'departamento'          => ['nullable', 'string', 'max:100'],
            'provincia'             => ['nullable', 'string', 'max:100'],
            'distrito'              => ['nullable', 'string', 'max:100'],
            'urbanizacion'          => ['nullable', 'string', 'max:100'],
            'direccion'             => ['nullable', 'string', 'max:300'],
            'correo'                => ['nullable', 'email', 'max:200'],
            'activo'                => ['boolean'],
            'is_retainer_agent'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_tipo_documento.in' => 'Tipo de documento inválido. Valores: 1=DNI, 4=Carnet, 6=RUC, 7=Pasaporte.',
        ];
    }
}
