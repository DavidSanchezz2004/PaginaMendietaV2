<?php

namespace App\Models;

use App\Enums\AccountingStatusEnum;
use App\Enums\TipoCompraEnum;
use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Comprobante de compra recibido por una empresa.
 * Registro manual — no conectado a Feasy/SUNAT.
 * Siempre scoped a company_id = session('company_id').
 */
class Purchase extends Model
{
    use HasFactory, BelongsToActiveCompany, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero_documento', 'accounting_status'])
            ->logOnlyDirty()
            ->useLogName('compra')
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'company_id',
        'user_id',
        'provider_id',
        'client_id',                   // NEW: for assignment flow
        'status',                       // NEW: workflow status
        'codigo_tipo_documento',
        'serie_documento',
        'numero_documento',
        'fecha_emision',
        'fecha_vencimiento',
        'tipo_doc_proveedor',
        'numero_doc_proveedor',
        'razon_social_proveedor',
        'codigo_moneda',
        'monto_tipo_cambio',
        'porcentaje_igv',
        'base_imponible_gravadas',
        'igv_gravadas',
        'monto_no_gravado',
        'monto_exonerado',
        'monto_exportacion',
        'monto_isc',
        'monto_icbper',
        'otros_tributos',
        'monto_descuento',
        'monto_total',
        'monto_pagado',
        'estado_pago',
        'forma_pago',
        'lista_cuotas',
        'anio_emision_dua',
        'fecha_doc_modifica',
        'tipo_doc_modifica',
        'serie_doc_modifica',
        'numero_doc_modifica',
        'tipo_nota',
        'tipo_compra',
        'tipo_operacion',
        'cuenta_contable',
        'codigo_producto_servicio',
        'glosa',
        'centro_costo',
        'tipo_gasto',
        'sucursal',
        'comprador',
        'es_anticipo',
        'es_documento_contingencia',
        'es_sujeto_detraccion',
        'es_sujeto_retencion',
        'es_sujeto_percepcion',
        'monto_detraccion',
        'monto_retencion',
        // Retenciones (Compras)
        'retention_enabled',
        'retention_base',
        'retention_percentage',
        'retention_amount',
        'net_total',
        'retention_info',
        'accounting_status',
        'cuenta_igv',
        'observacion',
        'errores_validacion',
        // GRE (Guía de Remisión Electrónica)
        'gre_numero',
        'gre_fecha_inicio_traslado',
        'gre_motivo_traslado',
        'gre_punto_partida',
        'gre_punto_llegada',
        'gre_destinatario_ruc',
        'gre_destinatario_razon_social',
        'gre_documento_relacionado',
        'gre_bienes_descripcion',
        'gre_cantidad_bienes',
        'gre_unidad_medida',
        'gre_peso_bruto',
        'gre_unidad_medida_peso',
        'gre_datos_vehiculo',
        'gre_datos_conductor',
        'gre_privado_transporte',
        'gre_retorno_vehiculo_vacio',
        'gre_transbordo_programado',
        'gre_notas',
        'gre_registrado_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision'             => 'date',
            'fecha_vencimiento'         => 'date',
            'fecha_doc_modifica'        => 'date',
            'lista_cuotas'              => 'array',
            'accounting_status'         => AccountingStatusEnum::class,
            'tipo_compra'               => TipoCompraEnum::class,
            'status'                    => 'string',  // NEW: workflow status
            'es_anticipo'               => 'boolean',
            'es_documento_contingencia' => 'boolean',
            'es_sujeto_detraccion'      => 'boolean',
            'es_sujeto_retencion'       => 'boolean',
            'es_sujeto_percepcion'      => 'boolean',
            'base_imponible_gravadas'  => 'float',
            'igv_gravadas'             => 'float',
            'monto_total'              => 'float',
            'monto_pagado'             => 'float',
            'monto_detraccion'         => 'float',
            'monto_neto_detraccion'    => 'float',
            'informacion_detraccion'   => 'array',
            'monto_retencion'          => 'float',
            'monto_tipo_cambio'        => 'float',
            'retention_enabled'        => 'boolean',
            'retention_base'           => 'float',
            'retention_percentage'     => 'float',
            'retention_amount'         => 'float',
            'net_total'                => 'float',
            'retention_info'           => 'array',
            'errores_validacion'       => 'array',
            // GRE casts
            'gre_fecha_inicio_traslado' => 'date',
            'gre_cantidad_bienes'      => 'integer',
            'gre_peso_bruto'           => 'float',
            'gre_datos_vehiculo'       => 'array',
            'gre_datos_conductor'      => 'array',
            'gre_privado_transporte'   => 'boolean',
            'gre_retorno_vehiculo_vacio' => 'boolean',
            'gre_transbordo_programado' => 'boolean',
            'gre_registrado_en'        => 'datetime',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function guias(): HasMany
    {
        return $this->hasMany(GuiaRemision::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, GuiaRemision::class);
    }

    public function letras(): HasMany
    {
        return $this->hasMany(LetraCambio::class);
    }

    public function letterCompensationDetails(): HasMany
    {
        return $this->hasMany(LetterCompensationDetail::class, 'purchase_invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class)->orderBy('correlativo');
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    public function getSerieNumeroAttribute(): string
    {
        $serie = $this->serie_documento ?? '—';
        $num   = $this->numero_documento ?? '—';
        return "{$serie}-{$num}";
    }

    public function getMontoPagableAttribute(): float
    {
        $total = (float) $this->monto_total
            - (float) ($this->monto_detraccion ?? 0)
            - (float) ($this->monto_retencion ?? 0);

        if ($total <= 0) {
            $total = (float) ($this->net_total ?? 0)
                ?: (float) ($this->monto_neto_detraccion ?? 0)
                ?: (float) $this->monto_total;
        }

        return round(max(0.0, $total), 2);
    }

    public function getSaldoPendientePagoAttribute(): float
    {
        return round(max(0.0, $this->monto_pagable - (float) ($this->monto_pagado ?? 0)), 2);
    }

    public function getEstadoPagoLabelAttribute(): string
    {
        return match ($this->estado_pago) {
            'parcial' => 'Parcialmente pagada',
            'pagado' => 'Pagada / Cancelada',
            default   => 'Pendiente',
        };
    }

    // ── Flujo de facturaciónN: Estado y validación ──────────────────────

    public function update_status_to_guided(): void
    {
        $this->update(['status' => 'guided']);
    }

    public function can_be_invoiced(): bool
    {
        return $this->status === 'guided' && $this->client_id !== null;
    }

    public function is_fully_invoiced(): bool
    {
        $total_qty = (float) $this->items->sum('quantity');
        $invoiced_qty = (float) $this->items->sum('invoiced_quantity');
        return $invoiced_qty >= $total_qty && $total_qty > 0;
    }

    public function is_partially_invoiced(): bool
    {
        $invoiced_qty = (float) $this->items->sum('invoiced_quantity');
        return $invoiced_qty > 0 && !$this->is_fully_invoiced();
    }

    public function update_status_based_on_items(): void
    {
        if ($this->is_fully_invoiced()) {
            $this->update(['status' => 'invoiced']);
        } elseif ($this->is_partially_invoiced()) {
            $this->update(['status' => 'partially_invoiced']);
        }
    }

    // ── Completitud contable ─────────────────────────────────────────────

    public function getAccountingCompletenessAttribute(): array
    {
        $required = [
            'tipo_operacion'           => 'Tipo de operación',
            'tipo_compra'              => 'Tipo de compra',
            'cuenta_contable'          => 'Cuenta contable',
            'codigo_producto_servicio' => 'Código producto/servicio',
        ];

        if (in_array($this->codigo_tipo_documento ?? '', ['01', '03'])) {
            $required['forma_pago'] = 'Forma de pago';
        }

        $optional = [
            'glosa'        => 'Glosa',
            'centro_costo' => 'Centro de costo',
            'tipo_gasto'   => 'Tipo de gasto',
        ];

        $missingRequired = [];
        foreach ($required as $field => $label) {
            $value = $this->{$field};
            if ($value === null || $value === '') {
                $missingRequired[$field] = $label;
            }
        }

        $missingOptional = [];
        foreach ($optional as $field => $label) {
            if (empty($this->{$field})) {
                $missingOptional[$field] = $label;
            }
        }

        $filledCount = count($required) - count($missingRequired);

        if (count($missingRequired) === 0) {
            $status = AccountingStatusEnum::LISTO;
        } elseif ($filledCount > 0) {
            $status = AccountingStatusEnum::PENDIENTE;
        } else {
            $status = AccountingStatusEnum::INCOMPLETO;
        }

        return [
            'status'           => $status,
            'missing_required' => $missingRequired,
            'missing_optional' => $missingOptional,
            'filled_count'     => $filledCount,
            'total_required'   => count($required),
        ];
    }

    /**
     * Sugerencias de auto-relleno para compras.
     */
    public function getAutoFillSuggestionsAttribute(): array
    {
        $suggestions = [];

        if (empty($this->tipo_operacion)) {
            $suggestions['tipo_operacion'] = match ($this->codigo_tipo_documento ?? '') {
                '01', '03' => '0401',  // Compra interna
                '07'       => '0412',  // Nota crédito compra
                '08'       => '0413',  // Nota débito compra
                '00'       => '0409',  // DUA
                default    => '0401',
            };
        }

        if (empty($this->tipo_compra)) {
            $suggestions['tipo_compra'] = TipoCompraEnum::GRAVADAS->value;
        }

        if (empty($this->forma_pago) && in_array($this->codigo_tipo_documento ?? '', ['01', '03'])) {
            $suggestions['forma_pago'] = '01';
        }

        return $suggestions;
    }
}
