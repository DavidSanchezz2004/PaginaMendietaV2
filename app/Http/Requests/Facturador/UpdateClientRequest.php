<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');
        return $this->user()->can('update', $client);
    }

    public function rules(): array
    {
        $client = $this->route('client');

        return [
            'codigo_tipo_documento' => ['required', 'string', 'in:1,4,6,7,A'],
            'numero_documento'      => [
                'required', 'string', 'max:20',
                function ($attribute, $value, $fail) use ($client) {
                    $tipo   = $this->input('codigo_tipo_documento');
                    $exists = \App\Models\Client::forActiveCompany()
                        ->where('codigo_tipo_documento', $tipo)
                        ->where('numero_documento', $value)
                        ->where('id', '!=', $client->id)
                        ->exists();
                    if ($exists) {
                        $fail('Ya existe otro cliente con ese documento en esta empresa.');
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
            // Credenciales SOL (opcionales, solo para clientes RUC)
            'usuario_sol'           => ['nullable', 'string', 'max:20'],
            'clave_sol'             => ['nullable', 'string', 'max:100'],
        ];
    }
}
