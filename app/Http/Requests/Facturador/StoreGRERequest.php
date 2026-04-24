<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class StoreGRERequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Invoice::class);
    }

    public function rules(): array
    {
        $modalidad = $this->input('codigo_modalidad_traslado');
        $esPublico  = $modalidad === '01'; // Transporte Público
        $esPrivado  = $modalidad === '02'; // Transporte Privado

        return [
            // ── Identificación del documento ──────────────────────────────
            'codigo_interno'   => ['required', 'string', 'max:30'],
            'fecha_emision'    => ['required', 'date'],
            'hora_emision'     => ['required', 'date_format:H:i'],
            'serie_documento'  => ['required', 'string', 'max:5'],
            'numero_documento' => [
                'required', 'string', 'max:10',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Invoice::forActiveCompany()
                        ->where('codigo_tipo_documento', '09')
                        ->where('serie_documento', $this->input('serie_documento'))
                        ->where('numero_documento', $value)
                        ->exists();
                    if ($exists) {
                        $fail('El número de GRE ya existe para esta empresa y serie.');
                    }
                },
            ],
            'observacion' => ['nullable', 'string', 'max:500'],
            'correo'      => ['nullable', 'email', 'max:200'],

            // ── Campos de traslado ─────────────────────────────────────────
            'codigo_motivo_traslado'         => ['required', 'string', 'max:5'],
            'descripcion_motivo_traslado'    => ['required', 'string', 'max:200'],
            'codigo_modalidad_traslado'      => ['required', 'in:01,02'],
            'fecha_inicio_traslado'          => ['required', 'date'],
            'codigo_unidad_medida_peso_bruto'=> ['required', 'string', 'max:10'],
            'peso_bruto_total'               => ['required', 'numeric', 'min:0'],

            // ── Destinatario ──────────────────────────────────────────────
            'gre_destinatario'                                     => ['required', 'array'],
            'gre_destinatario.codigo_tipo_documento_destinatario'  => ['required', 'string', 'max:2'],
            'gre_destinatario.numero_documento_destinatario'       => ['required', 'string', 'max:20'],
            'gre_destinatario.nombre_razon_social_destinatario'    => ['required', 'string', 'max:200'],

            // ── Punto de partida ──────────────────────────────────────────
            'gre_punto_partida'                         => ['required', 'array'],
            'gre_punto_partida.ubigeo_punto_partida'    => ['required', 'string', 'max:10'],
            'gre_punto_partida.direccion_punto_partida' => ['required', 'string', 'max:300'],

            // ── Punto de llegada ──────────────────────────────────────────
            'gre_punto_llegada'                        => ['required', 'array'],
            'gre_punto_llegada.ubigeo_punto_llegada'   => ['required', 'string', 'max:10'],
            'gre_punto_llegada.direccion_punto_llegada'=> ['required', 'string', 'max:300'],

            // ── Transportista (solo modalidad 01 - Transporte Público) ────
            'gre_transportista'                                        => $esPublico ? ['required', 'array'] : ['nullable'],
            'gre_transportista.codigo_tipo_documento_transportista'    => $esPublico ? ['required', 'string', 'max:2'] : ['nullable'],
            'gre_transportista.numero_documento_transportista'         => $esPublico ? ['required', 'string', 'max:20'] : ['nullable'],
            'gre_transportista.nombre_razon_social_transportista'      => $esPublico ? ['required', 'string', 'max:200'] : ['nullable'],

            // ── Vehículos (solo modalidad 02 - Transporte Privado) ────────
            'gre_vehiculos'                       => $esPrivado ? ['required', 'array', 'min:1'] : ['nullable'],
            'gre_vehiculos.*.numero_placa'        => $esPrivado ? ['required', 'string', 'max:10'] : ['nullable'],
            'gre_vehiculos.*.indicador_principal' => ['nullable', 'boolean'],

            // ── Conductores (solo modalidad 02, opcionales) ───────────────
            'gre_conductores'                         => ['nullable', 'array'],
            'gre_conductores.*.codigo_tipo_documento' => ['nullable', 'string', 'max:2'],
            'gre_conductores.*.numero_documento'      => ['nullable', 'string', 'max:20'],
            'gre_conductores.*.nombre'                => ['nullable', 'string', 'max:100'],
            'gre_conductores.*.apellido'              => ['nullable', 'string', 'max:100'],
            'gre_conductores.*.numero_licencia'       => ['nullable', 'string', 'max:20'],
            'gre_conductores.*.indicador_principal'   => ['nullable', 'boolean'],

            // ── Ítems de la guía ──────────────────────────────────────────
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.correlativo'          => ['nullable', 'integer', 'min:1'],
            'items.*.codigo_interno'       => ['required', 'string', 'max:50'],
            'items.*.codigo_unidad_medida' => ['required', 'string', 'max:10'],
            'items.*.descripcion'          => ['required', 'string', 'max:500'],
            'items.*.cantidad'             => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_modalidad_traslado.required' => 'Debe indicar la modalidad de traslado.',
            'gre_transportista.required'          => 'Para Transporte Público ingresa los datos del transportista.',
            'gre_vehiculos.required'              => 'Para Transporte Privado ingresa al menos un vehículo.',
            'items.min'                           => 'Debe agregar al menos un ítem a la guía.',
        ];
    }
}