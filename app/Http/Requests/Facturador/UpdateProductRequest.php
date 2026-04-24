<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');
        return $this->user()->can('update', $product);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'codigo_interno'          => [
                'required', 'string', 'max:50',
                function ($attribute, $value, $fail) use ($product) {
                    $exists = \App\Models\Product::forActiveCompany()
                        ->where('codigo_interno', $value)
                        ->where('id', '!=', $product->id)
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
}
