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
                    $exists = \App\Models\Invoice::withTrashed()
                        ->where('company_id', session('company_id'))
                        ->where('codigo_tipo_documento', '09')
                        ->where('serie_documento', $this->input('serie_documento'))
                        ->where('numero_documento', $value)
                        ->exists();
                    if ($exists) {
                        $fail('El número de GRE ya fue reservado o usado para esta empresa y serie.');
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
            'gre_punto_partida.ubigeo_punto_partida'    => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'gre_punto_partida.direccion_punto_partida' => ['required', 'string', 'max:300'],

            // ── Punto de llegada ──────────────────────────────────────────
            'gre_punto_llegada'                        => ['required', 'array'],
            'gre_punto_llegada.ubigeo_punto_llegada'   => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'gre_punto_llegada.direccion_punto_llegada'=> ['required', 'string', 'max:300'],

            // ── Documentos relacionados (opcional) ───────────────────────
            'gre_documentos_relacionados'                                     => ['nullable', 'array', 'max:20'],
            'gre_documentos_relacionados.*.codigo_tipo_documento'             => ['required_with:gre_documentos_relacionados', 'nullable', 'string', 'max:2'],
            'gre_documentos_relacionados.*.descripcion_tipo_documento'        => ['nullable', 'string', 'max:80'],
            'gre_documentos_relacionados.*.serie_documento'                   => ['required_with:gre_documentos_relacionados', 'nullable', 'string', 'max:10'],
            'gre_documentos_relacionados.*.numero_documento'                  => ['required_with:gre_documentos_relacionados', 'nullable', 'string', 'max:20'],
            'gre_documentos_relacionados.*.codigo_tipo_documento_emisor'      => ['nullable', 'string', 'in:1,6'],
            'gre_documentos_relacionados.*.numero_documento_emisor'           => [
                'required_with:gre_documentos_relacionados',
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $index = explode('.', $attribute)[1] ?? null;
                    $tipo = $index !== null
                        ? (string) $this->input("gre_documentos_relacionados.{$index}.codigo_tipo_documento_emisor", '6')
                        : '6';
                    $digits = preg_replace('/\D/', '', (string) $value);

                    if ($tipo === '6' && ! preg_match('/^[0-9]{11}$/', $digits)) {
                        $fail('El RUC del emisor del documento relacionado debe tener 11 dígitos.');
                    }

                    if ($tipo === '1' && ! preg_match('/^[0-9]{8}$/', $digits)) {
                        $fail('El DNI del emisor del documento relacionado debe tener 8 dígitos.');
                    }
                },
            ],

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
            'codigo_interno.required' => 'No se generó el código interno de la guía. Revisa serie y número.',
            'codigo_modalidad_traslado.required' => 'Debe indicar la modalidad de traslado.',
            'codigo_motivo_traslado.required' => 'Selecciona el motivo de traslado.',
            'descripcion_motivo_traslado.required' => 'Ingresa la descripción del motivo de traslado.',
            'fecha_inicio_traslado.required' => 'Ingresa la fecha de inicio del traslado.',
            'peso_bruto_total.required' => 'Ingresa el peso bruto total de la carga.',
            'codigo_unidad_medida_peso_bruto.required' => 'Selecciona la unidad de medida del peso.',
            'gre_destinatario.numero_documento_destinatario.required' => 'Ingresa el documento del destinatario.',
            'gre_destinatario.nombre_razon_social_destinatario.required' => 'Ingresa la razón social o nombre del destinatario.',
            'gre_punto_partida.ubigeo_punto_partida.required' => 'Ingresa el ubigeo del punto de partida.',
            'gre_punto_partida.ubigeo_punto_partida.regex' => 'El ubigeo del punto de partida debe tener 6 dígitos.',
            'gre_punto_partida.direccion_punto_partida.required' => 'Ingresa la dirección del punto de partida.',
            'gre_punto_llegada.ubigeo_punto_llegada.required' => 'Ingresa el ubigeo del punto de llegada.',
            'gre_punto_llegada.ubigeo_punto_llegada.regex' => 'El ubigeo del punto de llegada debe tener 6 dígitos.',
            'gre_punto_llegada.direccion_punto_llegada.required' => 'Ingresa la dirección del punto de llegada.',
            'gre_documentos_relacionados.*.codigo_tipo_documento.required_with' => 'Selecciona el tipo del documento relacionado.',
            'gre_documentos_relacionados.*.serie_documento.required_with' => 'Ingresa la serie del documento relacionado.',
            'gre_documentos_relacionados.*.numero_documento.required_with' => 'Ingresa el número del documento relacionado.',
            'gre_documentos_relacionados.*.numero_documento_emisor.required_with' => 'Ingresa el documento del emisor del documento relacionado.',
            'gre_transportista.required'          => 'Para Transporte Público ingresa los datos del transportista.',
            'gre_vehiculos.required'              => 'Para Transporte Privado ingresa al menos un vehículo.',
            'gre_vehiculos.*.numero_placa.required' => 'Ingresa la placa del vehículo.',
            'items.min'                           => 'Debe agregar al menos un ítem a la guía.',
            'items.*.descripcion.required' => 'Cada ítem debe tener descripción.',
            'items.*.codigo_interno.required' => 'Cada ítem debe tener código interno.',
            'items.*.cantidad.required' => 'Cada ítem debe tener cantidad.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $numero = trim((string) $this->input('numero_documento', ''));
        $companyRuc = (string) (\App\Models\Company::find((int) session('company_id'))?->ruc ?? '');
        $documentosRelacionados = collect($this->input('gre_documentos_relacionados', []))
            ->map(function ($document) use ($companyRuc) {
                if (! is_array($document)) {
                    return $document;
                }

                $document['codigo_tipo_documento_emisor'] = $document['codigo_tipo_documento_emisor'] ?? '6';
                $document['numero_documento_emisor'] = preg_replace(
                    '/\D/',
                    '',
                    (string) ($document['numero_documento_emisor'] ?? $companyRuc)
                );

                return $document;
            })
            ->all();

        if ($numero !== '' && ctype_digit($numero)) {
            $this->merge([
                'numero_documento' => str_pad($numero, 8, '0', STR_PAD_LEFT),
                'gre_documentos_relacionados' => $documentosRelacionados,
            ]);

            return;
        }

        $this->merge(['gre_documentos_relacionados' => $documentosRelacionados]);
    }
}
