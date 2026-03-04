<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    public function rules(): array
    {
        return [
            'codigo_interno'          => [
                'required', 'string', 'max:50',
                // Unique scoped por empresa activa (no usar DB unique rule global)
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Product::forActiveCompany()
                        ->where('codigo_interno', $value)
                        ->exists();
                    if ($exists) {
                        $fail('El código interno ya existe en esta empresa.');
                    }
                },
            ],
            'codigo_sunat'            => ['nullable', 'string', 'max:50'],
            'tipo'                    => ['required', 'in:P,S'],
            'codigo_unidad_medida'    => ['required', 'string', 'max:10'],
            'descripcion'             => ['required', 'string', 'max:500'],
            'valor_unitario'          => ['required', 'numeric', 'min:0'],
            'precio_unitario'         => ['required', 'numeric', 'min:0'],
            'codigo_indicador_afecto' => ['required', 'string', 'in:10,20,30,40'],
            'activo'                  => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.in'                    => 'El tipo debe ser P (Producto) o S (Servicio).',
            'codigo_indicador_afecto.in' => 'Código afecto inválido. Use: 10=Gravado, 20=Exonerado, 30=Inafecto, 40=Exportación.',
        ];
    }
}
