<?php

namespace App\Models;

use App\Enums\AccountingStatusEnum;
use App\Enums\TipoCompraEnum;
use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'accounting_status',
        'cuenta_igv',
        'observacion',
        'errores_validacion',
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
            'es_anticipo'               => 'boolean',
            'es_documento_contingencia' => 'boolean',
            'es_sujeto_detraccion'      => 'boolean',
            'es_sujeto_retencion'       => 'boolean',
            'es_sujeto_percepcion'      => 'boolean',
            'base_imponible_gravadas'  => 'float',
            'igv_gravadas'             => 'float',
            'monto_total'              => 'float',
            'monto_detraccion'         => 'float',
            'monto_neto_detraccion'    => 'float',
            'informacion_detraccion'   => 'array',
            'monto_retencion'          => 'float',
            'monto_tipo_cambio'        => 'float',
            'errores_validacion'       => 'array',
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

    public function letras(): HasMany
    {
        return $this->hasMany(LetraCambio::class);
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
