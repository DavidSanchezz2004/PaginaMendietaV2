<?php

namespace App\Models;

use App\Enums\AccountingStatusEnum;
use App\Enums\FeasyStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Comprobante de pago (factura/boleta) emitido por una empresa.
 * Siempre scoped a company_id = session('company_id').
 *
 * Campos de trazabilidad Feasy:
 *   estado_feasy, codigo_respuesta_sunat, mensaje_respuesta_sunat,
 *   nombre_archivo_xml, xml_path, sent_at, consulted_at, last_error
 */
class Invoice extends Model
{
    use HasFactory, BelongsToActiveCompany, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['codigo_interno', 'status', 'estado_feasy'])
            ->logOnlyDirty()
            ->useLogName('factura')
            ->dontSubmitEmptyLogs();
    }

    // ── Validaciones y Boot ────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($invoice) {
            // VALIDACIÓN: Si viene de guía, verificar que la compra esté lista - Solo al CREAR
            if (!$invoice->exists && $invoice->guia_remision_id) {
                $guia = $invoice->guia;
                if ($guia && $guia->purchase && !$guia->purchase->can_be_invoiced()) {
                    throw new \Exception('La compra no está lista para facturar');
                }
            }
        });
    }

    protected $fillable = [
        'company_id',
        'user_id',
        'client_id',
        'codigo_interno',
        'fecha_emision',
        'hora_emision',
        'fecha_vencimiento',
        'forma_pago',
        'lista_cuotas',         // cuotas de crédito (JSON) requeridas por Feasy cuando forma_pago=2
        'codigo_tipo_documento',
        'serie_documento',
        'numero_documento',
        'observacion',
        'correo',
        'numero_orden_compra',
        'lista_guias',          // guías de remisión adjuntas (JSON)
        'codigo_moneda',
        'porcentaje_igv',
        'monto_tipo_cambio',
        'monto_total_anticipo',
        'monto_total_gravado',
        'monto_total_inafecto',
        'monto_total_exonerado',
        'monto_total_exportacion',
        'monto_total_descuento',
        'monto_total_isc',
        'monto_total_igv',
        'monto_total_impuesto_bolsa',
        'monto_total_gratuito',
        'monto_total_otros_cargos',
        'monto_total',
        'informacion_entrega_bienes',
        'indicador_entrega_bienes',
        // SPOT (Detracción)
        'indicador_detraccion',
        'informacion_detraccion',
        // Retenciones (Ventas)
        'retention_enabled',
        'retention_base',
        'retention_percentage',
        'retention_amount',
        'net_total',
        'retention_info',
        // Información Adicional (campos libres SUNAT, aplica a 01/03/SPOT)
        'informacion_adicional',
        // GRE (Guía de Remisión Electrónica) — tipo documento "09"
        'codigo_motivo_traslado',
        'descripcion_motivo_traslado',
        'codigo_modalidad_traslado',
        'fecha_inicio_traslado',
        'codigo_unidad_medida_peso_bruto',
        'peso_bruto_total',
        'gre_punto_partida',
        'gre_punto_llegada',
        'gre_destinatario',
        'gre_vehiculos',
        'gre_conductores',
        'gre_transportista',
        'gre_documentos_relacionados',
        'estado',
        'estado_feasy',
        'codigo_respuesta_sunat',
        'mensaje_respuesta_sunat',
        'nombre_archivo_xml',
        'xml_path',
        'ruta_xml',
        'ruta_cdr',
        'ruta_reporte',
        'hash_cpe',
        'valor_qr',
        'mensaje_observacion',
        'sent_at',
        'consulted_at',
        'last_error',
        // Campos contables (completado para exportación)
        'accounting_status',
        'tipo_operacion',
        'tipo_venta',
        'cuenta_contable',
        'codigo_producto_servicio',
        'glosa',
        'centro_costo',
        'tipo_gasto',
        'sucursal',
        'vendedor',
        'es_anticipo',
        'es_documento_contingencia',
        'es_sujeto_retencion',
        'es_sujeto_percepcion',
        // Guía de Remisión (NEW: MANDATORY for new invoices)
        'guia_remision_id',
        'client_address_id',
        // Retención (NEW: persistence for SUNAT + PDF + letters)
        'has_retention',
        'total_before_retention',
        'total_after_retention',
        'letter_exchange_status',
        'letter_exchanged_at',
        'letter_exchange_observation',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision'              => 'date',
            'fecha_vencimiento'          => 'date',
            'fecha_inicio_traslado'      => 'date',
            'hora_emision'               => 'datetime:H:i:s',
            'informacion_entrega_bienes' => 'array',
            'indicador_entrega_bienes'   => 'boolean',
            'lista_guias'                => 'array',
            // SPOT
            'indicador_detraccion'       => 'boolean',
            'informacion_detraccion'     => 'array',
            // Cuotas de crédito
            'lista_cuotas'              => 'array',
            // Información adicional
            'informacion_adicional'      => 'array',
            'gre_punto_partida'          => 'array',
            'gre_punto_llegada'          => 'array',
            'gre_destinatario'           => 'array',
            'gre_vehiculos'              => 'array',
            'gre_conductores'            => 'array',
            'gre_transportista'          => 'array',
            'gre_documentos_relacionados' => 'array',
            'estado'                     => InvoiceStatusEnum::class,
            'estado_feasy'               => FeasyStatusEnum::class,
            'accounting_status'          => AccountingStatusEnum::class,
            'es_anticipo'                => 'boolean',
            'es_documento_contingencia'  => 'boolean',
            'es_sujeto_retencion'        => 'boolean',
            'es_sujeto_percepcion'       => 'boolean',
            'sent_at'                    => 'datetime',
            'consulted_at'               => 'datetime',
            // Retenciones
            'retention_enabled'          => 'boolean',
            'retention_base'             => 'float',
            'retention_percentage'       => 'float',
            'retention_amount'           => 'float',
            'net_total'                  => 'float',
            'retention_info'             => 'array',
            // Montos como float (decimal con 2 decimales en cálculos)
            'porcentaje_igv'             => 'float',
            'monto_total_gravado'        => 'float',
            'monto_total_igv'            => 'float',
            'monto_total'                => 'float',
            // NEW: Retención con totales before/after (para SUNAT + PDF + letras)
            'has_retention'              => 'boolean',
            'total_before_retention'     => 'float',
            'total_after_retention'      => 'float',
            'letter_exchanged_at'        => 'datetime',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('correlativo');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('created_at');
    }

    public function letras(): HasMany
    {
        return $this->hasMany(LetraCambio::class)->orderBy('numero_letra');
    }

    public function sendLogs(): HasMany
    {
        return $this->hasMany(InvoiceSendLog::class)->latest();
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'invoice_id');
    }

    public function guia(): BelongsTo
    {
        return $this->belongsTo(GuiaRemision::class, 'guia_remision_id');
    }

    public function clientAddress(): BelongsTo
    {
        return $this->belongsTo(ClientAddress::class, 'client_address_id');
    }

    // ── Helpers de negocio ─────────────────────────────────────────────────

    /**
     * Número de serie completo para display y búsqueda.
     * Ej: F001-00000001
     */
    public function getSerieNumeroAttribute(): string
    {
        return "{$this->serie_documento}-{$this->numero_documento}";
    }

    // ── Completitud contable ────────────────────────────────────────────────

    /**
     * Evalúa qué campos contables faltan y calcula el estado de completitud.
     * Devuelve array con: status, missing_required, missing_optional, filled_count, total_required.
     */
    public function getAccountingCompletenessAttribute(): array
    {
        $required = [
            'tipo_operacion'           => 'Tipo de operación',
            'tipo_venta'               => 'Tipo de venta',
            'cuenta_contable'          => 'Cuenta contable',
            'codigo_producto_servicio' => 'Código producto/servicio',
        ];

        // forma_pago ya existe en el documento; solo exigir si es factura/boleta
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
            if (empty($this->{$field})) {
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
     * Genera sugerencias de auto-relleno basadas en el tipo de documento y datos del cliente.
     */
    public function getAutoFillSuggestionsAttribute(): array
    {
        $suggestions = [];

        // Tipo de operación según código de comprobante
        if (empty($this->tipo_operacion)) {
            $suggestions['tipo_operacion'] = match ($this->codigo_tipo_documento ?? '') {
                '01', '03' => '0101',  // Venta interna
                '07'       => '0112',  // Nota crédito
                '08'       => '0113',  // Nota débito
                '09'       => '0116',  // Guía de remisión
                default    => '0101',
            };
        }

        // Tipo de venta según código de comprobante
        if (empty($this->tipo_venta)) {
            $suggestions['tipo_venta'] = match ($this->codigo_tipo_documento ?? '') {
                '01', '03' => 'IN',  // Interna
                '07'       => 'NC',  // Nota Crédito
                '08'       => 'ND',  // Nota Débito
                default    => 'IN',
            };
        }

        // Forma de pago si no está seteada (para facturas/boletas)
        if (empty($this->forma_pago) && in_array($this->codigo_tipo_documento ?? '', ['01', '03'])) {
            $suggestions['forma_pago'] = '01'; // Contado por defecto
        }

        // Detectar si debería marcarse como sujeto a detracción
        if (!$this->es_sujeto_retencion && $this->indicador_detraccion) {
            $suggestions['indicador_detraccion'] = true;
        }

        return $suggestions;
    }

    /**
     * ¿Se puede emitir? Solo draft o error (reintento).
     */
    public function canBeEmitted(): bool
    {
        if ($this->isLikelyRegisteredInSunatFromError() || $this->hasBlockingSendAttempt()) {
            return false;
        }

        return in_array($this->estado, [
            InvoiceStatusEnum::DRAFT,
            InvoiceStatusEnum::READY,
            InvoiceStatusEnum::ERROR,
        ], true);
    }

    /**
     * ¿Se puede consultar? Solo si ya fue enviada.
     * Las GRE (tipo 09) también pueden consultarse desde ERROR porque son asíncronas:
     * SUNAT puede haber recibido el documento aunque la respuesta haya llegado con error.
     */
    public function canBeConsulted(): bool
    {
        $states = [
            InvoiceStatusEnum::SENT,
            InvoiceStatusEnum::CONSULTED,
        ];

        if ($this->codigo_tipo_documento === '09' || $this->isLikelyRegisteredInSunatFromError()) {
            $states[] = InvoiceStatusEnum::ERROR;
        }

        return in_array($this->estado, $states, true);
    }

    /**
     * ¿Se puede anular? Solo si fue enviada/consultada y es Factura o Boleta.
     */
    public function canBeVoided(): bool
    {
        return in_array($this->estado, [
            InvoiceStatusEnum::SENT,
            InvoiceStatusEnum::CONSULTED,
        ], true)
        && in_array($this->codigo_tipo_documento, ['01', '03'], true);
    }

    public function canBeDeleted(): bool
    {
        if ($this->isLikelyRegisteredInSunatFromError()) {
            return false;
        }

        if (in_array($this->estado, [InvoiceStatusEnum::DRAFT, InvoiceStatusEnum::ERROR], true)) {
            return true;
        }

        if ($this->codigo_tipo_documento !== '09' || $this->estado !== InvoiceStatusEnum::CONSULTED) {
            return false;
        }

        $sunatCode = trim((string) $this->codigo_respuesta_sunat);
        $message = strtolower((string) $this->mensaje_respuesta_sunat);

        return $sunatCode !== '' && $sunatCode !== '0'
            || str_contains($message, 'errorcode')
            || str_contains($message, 'error:')
            || str_contains($message, 'no corresponde')
            || str_contains($message, 'rechaz');
    }

    public function canBeExchangedToLetters(): bool
    {
        return in_array($this->estado, [
            InvoiceStatusEnum::SENT,
            InvoiceStatusEnum::CONSULTED,
        ], true)
        && in_array($this->codigo_tipo_documento, ['01'], true)
        && ! $this->hasBeenExchangedToLetters()
        && $this->pendingAmountForLetters() > 0;
    }

    public function hasBeenExchangedToLetters(): bool
    {
        return $this->letter_exchange_status === 'exchanged'
            || ($this->relationLoaded('letras') ? $this->letras->isNotEmpty() : $this->letras()->exists());
    }

    public function pendingAmountForLetters(): float
    {
        $amount = $this->total_after_retention
            ?? $this->net_total
            ?? $this->monto_total;

        if ($this->indicador_detraccion && is_array($this->informacion_detraccion)) {
            $amount -= (float) ($this->informacion_detraccion['monto_detraccion'] ?? 0);
        }

        $paid = $this->relationLoaded('payments')
            ? (float) $this->payments->sum('monto')
            : (float) $this->payments()->sum('monto');

        return round(max(0, $amount - $paid), 2);
    }

    public function isLikelyRegisteredInSunatFromError(): bool
    {
        if ($this->estado !== InvoiceStatusEnum::ERROR) {
            return false;
        }

        $trace = strtolower(implode(' ', array_filter([
            (string) $this->codigo_respuesta_sunat,
            (string) $this->mensaje_respuesta_sunat,
            (string) $this->last_error,
        ])));

        return str_contains($trace, 'registrado previamente')
            || str_contains($trace, 'informado anteriormente')
            || str_contains($trace, '1032')
            || str_contains($trace, '1033');
    }

    public function hasBlockingSendAttempt(): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->sendLogs()
            ->where('action', 'emit')
            ->where(function ($query): void {
                $query->whereIn('codigo_respuesta', ['0', 'A01', '1032', '1033'])
                    ->orWhere('mensaje_respuesta', 'like', '%registrado previamente%')
                    ->orWhere('mensaje_respuesta', 'like', '%informado anteriormente%');
            })
            ->exists();
    }
}
