<?php

namespace App\Http\Requests\Facturador;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Invoice::class);
    }

    public function rules(): array
    {
        $isGre = $this->input('codigo_tipo_documento') === '09';

        return [
            // ── Identificación del documento ──────────────────────────────
            'client_id' => $isGre
                ? ['nullable', 'integer']
                : [
                    'required', 'integer',
                    function ($attribute, $value, $fail) {
                        $exists = \App\Models\Client::forActiveCompany()
                            ->where('id', $value)
                            ->where('activo', true)
                            ->exists();
                        if (! $exists) {
                            $fail('El cliente seleccionado no pertenece a esta empresa o está inactivo.');
                        }
                    },
                ],
            'codigo_interno'        => ['required', 'string', 'max:30'],
            'fecha_emision'         => ['required', 'date'],
            'hora_emision'          => ['required', 'date_format:H:i'],
            'fecha_vencimiento'     => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'forma_pago'            => $isGre ? ['nullable'] : ['required', 'in:1,2'],
            'codigo_tipo_documento' => ['required', 'in:01,03,07,08,09'],
            'serie_documento'       => ['required', 'string', 'max:5'],
            'numero_documento'      => [
                'required', 'string', 'max:10',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Invoice::forActiveCompany()
                        ->where('codigo_tipo_documento', $this->input('codigo_tipo_documento'))
                        ->where('serie_documento', $this->input('serie_documento'))
                        ->where('numero_documento', $value)
                        ->exists();
                    if ($exists) {
                        $fail('El número de comprobante ya existe para esta empresa, tipo y serie.');
                    }
                },
            ],
            'observacion'        => ['nullable', 'string', 'max:500'],
            'correo'             => ['nullable', 'email', 'max:200'],
            'numero_orden_compra' => ['nullable', 'string', 'max:50'],
            'codigo_moneda'      => $isGre ? ['nullable', 'in:PEN,USD,EUR'] : ['required', 'in:PEN,USD,EUR'],
            'porcentaje_igv'     => $isGre ? ['nullable', 'numeric'] : ['required', 'numeric', 'in:18,0'],
            'monto_tipo_cambio'  => ['nullable', 'numeric', 'min:0'],

            // ── Montos (solo para Factura/Boleta) ─────────────────────────
            'monto_total_gravado'   => $isGre ? ['nullable', 'numeric', 'min:0'] : ['required', 'numeric', 'min:0'],
            'monto_total_igv'       => $isGre ? ['nullable', 'numeric', 'min:0'] : ['required', 'numeric', 'min:0'],
            'monto_total'           => $isGre ? ['nullable', 'numeric', 'min:0'] : ['required', 'numeric', 'min:0.01'],
            'monto_total_anticipo'      => ['nullable', 'numeric', 'min:0'],
            'monto_total_inafecto'      => ['nullable', 'numeric', 'min:0'],
            'monto_total_exonerado'     => ['nullable', 'numeric', 'min:0'],
            'monto_total_exportacion'   => ['nullable', 'numeric', 'min:0'],
            'monto_total_descuento'     => ['nullable', 'numeric', 'min:0'],
            'monto_total_isc'           => ['nullable', 'numeric', 'min:0'],
            'monto_total_impuesto_bolsa' => ['nullable', 'numeric', 'min:0'],
            'monto_total_gratuito'      => ['nullable', 'numeric', 'min:0'],
            'monto_total_otros_cargos'  => ['nullable', 'numeric', 'min:0'],

            // ── Entrega de bienes (solo Factura/Boleta, opcional) ─────────
            'indicador_entrega_bienes'                           => ['boolean'],
            'informacion_entrega_bienes'                         => ['nullable', 'array'],
            'informacion_entrega_bienes.codigo_pais_entrega'     => ['nullable', 'string', 'size:2'],
            'informacion_entrega_bienes.ubigeo_entrega'          => ['nullable', 'string', 'max:10'],
            'informacion_entrega_bienes.departamento_entrega'    => ['nullable', 'string', 'max:100'],
            'informacion_entrega_bienes.provincia_entrega'       => ['nullable', 'string', 'max:100'],
            'informacion_entrega_bienes.distrito_entrega'        => ['nullable', 'string', 'max:100'],
            'informacion_entrega_bienes.urbanizacion_entrega'    => ['nullable', 'string', 'max:100'],
            'informacion_entrega_bienes.direccion_entrega'       => ['nullable', 'string', 'max:300'],

            // ── Campos GRE (Guía de Remisión, tipo "09") ──────────────────
            'codigo_motivo_traslado'      => $isGre ? ['required', 'string', 'max:5']   : ['nullable'],
            'descripcion_motivo_traslado' => $isGre ? ['required', 'string', 'max:200'] : ['nullable'],
            'codigo_modalidad_traslado'   => $isGre ? ['required', 'in:01,02']          : ['nullable'],
            'fecha_inicio_traslado'       => $isGre ? ['required', 'date']              : ['nullable'],
            'codigo_unidad_medida_peso_bruto' => $isGre ? ['required', 'string', 'max:10'] : ['nullable'],
            'peso_bruto_total'            => $isGre ? ['required', 'numeric', 'min:0']  : ['nullable'],

            'gre_punto_partida'                          => $isGre ? ['required', 'array'] : ['nullable'],
            'gre_punto_partida.ubigeo_punto_partida'     => $isGre ? ['required', 'string', 'max:10']  : ['nullable'],
            'gre_punto_partida.direccion_punto_partida'  => $isGre ? ['required', 'string', 'max:300'] : ['nullable'],

            'gre_punto_llegada'                         => $isGre ? ['required', 'array'] : ['nullable'],
            'gre_punto_llegada.ubigeo_punto_llegada'    => $isGre ? ['required', 'string', 'max:10']  : ['nullable'],
            'gre_punto_llegada.direccion_punto_llegada' => $isGre ? ['required', 'string', 'max:300'] : ['nullable'],

            'gre_destinatario'                                        => $isGre ? ['required', 'array']        : ['nullable'],
            'gre_destinatario.codigo_tipo_documento_destinatario'    => $isGre ? ['required', 'string']        : ['nullable'],
            'gre_destinatario.numero_documento_destinatario'         => $isGre ? ['required', 'string', 'max:20']  : ['nullable'],
            'gre_destinatario.nombre_razon_social_destinatario'      => $isGre ? ['required', 'string', 'max:200'] : ['nullable'],

            'gre_vehiculos'                       => $isGre ? ['required', 'array', 'min:1'] : ['nullable'],
            'gre_vehiculos.*.numero_placa'        => $isGre ? ['required', 'string', 'max:10'] : ['nullable'],
            'gre_vehiculos.*.indicador_principal' => ['nullable', 'boolean'],

            'gre_conductores'                           => ['nullable', 'array'],
            'gre_conductores.*.codigo_tipo_documento'   => ['nullable', 'string'],
            'gre_conductores.*.numero_documento'        => ['nullable', 'string', 'max:20'],
            'gre_conductores.*.nombre'                  => ['nullable', 'string', 'max:100'],
            'gre_conductores.*.apellido'                => ['nullable', 'string', 'max:100'],
            'gre_conductores.*.numero_licencia'         => ['nullable', 'string', 'max:20'],
            'gre_conductores.*.indicador_principal'     => ['nullable', 'boolean'],

            // ── Detracción SPOT ────────────────────────────────────────────
            // Solo aplica a Facturas (01) con monto total > S/ 700.
            'indicador_detraccion'                                 => ['boolean'],
            'informacion_detraccion'                               => ['nullable', 'array'],
            'informacion_detraccion.codigo_bbss_sujeto_detraccion' => ['required_if:indicador_detraccion,1', 'nullable', 'string', 'max:5'],
            'informacion_detraccion.porcentaje_detraccion'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'informacion_detraccion.monto_detraccion'              => ['nullable', 'numeric', 'min:0'],
            'informacion_detraccion.cuenta_banco_detraccion'       => ['required_if:indicador_detraccion,1', 'nullable', 'string', 'max:20'],
            'informacion_detraccion.codigo_medio_pago_detraccion'  => ['nullable', 'string', 'max:5'],

            // ── Retención (Compras) ────────────────────────────────────────
            // Aplica cuando se facturas con retención a terceros (3% típicamente).
            'indicador_retencion'                                  => ['boolean'],
            'informacion_retencion'                                => ['nullable', 'array'],
            'informacion_retencion.codigo_retencion'               => ['required_if:indicador_retencion,1', 'nullable', 'string', 'max:5'],
            'informacion_retencion.porcentaje_retencion'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'informacion_retencion.monto_base_imponible_retencion' => ['nullable', 'numeric', 'min:0'],
            'informacion_retencion.monto_retencion'                => ['nullable', 'numeric', 'min:0'],

            // ── Información Adicional (campos libres SUNAT) ───────────────
            // Aplica a Factura (01), Boleta (03) y comprobantes con SPOT.
            // Hasta 10 campos clave-valor definidos por la empresa.
            'informacion_adicional'                                => ['nullable', 'array', 'max:10'],
            'informacion_adicional.informacion_adicional_1'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_2'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_3'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_4'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_5'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_6'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_7'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_8'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_9'        => ['nullable', 'string', 'max:500'],
            'informacion_adicional.informacion_adicional_10'       => ['nullable', 'string', 'max:500'],

            // ── Cuotas de crédito (forma_pago = 2) ───────────────────────
            // Solo se exigen cuando el comprobante es a crédito.
            'lista_cuotas'                => ['nullable', 'array', 'max:12'],
            'lista_cuotas.*.fecha_pago'   => ['nullable', 'required_if:forma_pago,2', 'date_format:Y-m-d'],
            'lista_cuotas.*.monto'        => ['nullable', 'required_if:forma_pago,2', 'numeric', 'min:0.01'],

            // ── Guías de remisión adjuntas (solo Factura/Boleta, opcional) ─
            // Permite referenciar GRE ya emitidas. Solo aplica a tipos 01 y 03.
            'lista_guias'                              => ['nullable', 'array', 'max:20'],
            'lista_guias.*.codigo_tipo_documento'      => ['required_with:lista_guias', 'nullable', 'string', 'in:09,31'],
            'lista_guias.*.serie_documento'            => ['required_with:lista_guias', 'nullable', 'string', 'max:10'],
            'lista_guias.*.numero_documento'           => ['required_with:lista_guias', 'nullable', 'string', 'max:20'],

            // ── Items ──────────────────────────────────────────────────────
            'items'                           => ['required', 'array', 'min:1'],
            'items.*.codigo_interno'          => ['required', 'string', 'max:50'],
            'items.*.codigo_sunat'            => ['nullable', 'string', 'max:50'],
            'items.*.tipo'                    => $isGre ? ['nullable'] : ['required', 'in:P,S'],
            'items.*.codigo_unidad_medida'    => ['required', 'string', 'max:10'],
            'items.*.descripcion'             => ['required', 'string', 'max:500'],
            'items.*.cantidad'                => ['required', 'numeric', 'min:0.0001'],
            'items.*.monto_valor_unitario'    => ['nullable', 'numeric', 'min:0'],
            'items.*.monto_precio_unitario'   => $isGre ? ['nullable'] : ['required', 'numeric', 'min:0'],
            'items.*.monto_descuento'         => ['nullable', 'numeric', 'min:0'],
            'items.*.monto_valor_total'       => ['nullable', 'numeric', 'min:0'],
            'items.*.codigo_isc'              => ['nullable', 'string', 'max:5'],
            'items.*.monto_isc'               => ['nullable', 'numeric', 'min:0'],
            'items.*.codigo_indicador_afecto' => $isGre ? ['nullable'] : ['required', 'string', 'in:10,20,30,40'],
            'items.*.monto_igv'               => $isGre ? ['nullable'] : ['required', 'numeric', 'min:0'],
            'items.*.monto_impuesto_bolsa'    => ['nullable', 'numeric', 'min:0'],
            'items.*.monto_total'             => $isGre ? ['nullable'] : ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Normaliza el número para que un valor manual como "50" se guarde como "00000050".
     * Esto evita duplicados lógicos entre "50" y "00000050" y permite que el correlativo siga avanzando.
     */
    protected function prepareForValidation(): void
    {
        $numero = trim((string) $this->input('numero_documento', ''));

        if ($numero !== '' && ctype_digit($numero)) {
            $this->merge([
                'numero_documento' => str_pad($numero, 8, '0', STR_PAD_LEFT),
            ]);
        }
    }

    /**
     * Validaciones adicionales de coherencia de totales.
     * Solo aplica a Facturas/Boletas (no a GRE tipo 09).
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        if ($this->input('codigo_tipo_documento') === '09') {
            return; // GRE no tiene totales financieros
        }

        $validator->after(function ($v) {
            $gravado = (float) $this->input('monto_total_gravado', 0);
            $igv     = (float) $this->input('monto_total_igv', 0);
            $total   = (float) $this->input('monto_total', 0);

            if (abs(round($gravado + $igv, 2) - round($total, 2)) > 0.05) {
                $v->errors()->add(
                    'monto_total',
                    "Los totales no cuadran: gravado({$gravado}) + igv({$igv}) ≠ total({$total})."
                );
            }

            $sumItems = collect($this->input('items', []))
                ->sum(fn ($item) => (float) ($item['monto_total'] ?? 0));

            if (abs(round($sumItems, 2) - round($total, 2)) > 0.05) {
                $v->errors()->add(
                    'items',
                    "La suma de items ({$sumItems}) no coincide con monto_total ({$total})."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'codigo_tipo_documento.in' => 'Tipo de documento inválido. 01=Factura, 03=Boleta, 07=N.Crédito, 08=N.Débito, 09=GRE.',
            'forma_pago.in'            => '1=Contado, 2=Crédito.',
            'items.min'                => 'Debe agregar al menos un ítem.',
            'monto_total.min'          => 'El monto total debe ser mayor a 0.',
            'gre_vehiculos.min'        => 'Debe registrar al menos un vehículo.',
            'informacion_detraccion.cuenta_banco_detraccion.required_if'       => 'La cuenta del Banco de la Nación es obligatoria para comprobantes con detracción SPOT.',
            'informacion_detraccion.codigo_bbss_sujeto_detraccion.required_if' => 'El código del bien o servicio (SUNAT) es obligatorio para comprobantes con detracción SPOT.',
        ];
    }
}
