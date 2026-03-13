<?php

namespace App\Models;

use App\Enums\FeasyStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    use HasFactory, BelongsToActiveCompany, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['codigo_interno', 'status', 'estado_feasy'])
            ->logOnlyDirty()
            ->useLogName('factura')
            ->dontSubmitEmptyLogs();
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
            'estado'                     => InvoiceStatusEnum::class,
            'estado_feasy'               => FeasyStatusEnum::class,
            'sent_at'                    => 'datetime',
            'consulted_at'               => 'datetime',
            // Montos como float (decimal con 2 decimales en cálculos)
            'porcentaje_igv'             => 'float',
            'monto_total_gravado'        => 'float',
            'monto_total_igv'            => 'float',
            'monto_total'                => 'float',
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

    // ── Helpers de negocio ─────────────────────────────────────────────────

    /**
     * Número de serie completo para display y búsqueda.
     * Ej: F001-00000001
     */
    public function getSerieNumeroAttribute(): string
    {
        return "{$this->serie_documento}-{$this->numero_documento}";
    }

    /**
     * ¿Se puede emitir? Solo draft o error (reintento).
     */
    public function canBeEmitted(): bool
    {
        return in_array($this->estado, [
            InvoiceStatusEnum::DRAFT,
            InvoiceStatusEnum::READY,
            InvoiceStatusEnum::ERROR,
        ], true);
    }

    /**
     * ¿Se puede consultar? Solo si ya fue enviada.
     */
    public function canBeConsulted(): bool
    {
        return in_array($this->estado, [
            InvoiceStatusEnum::SENT,
            InvoiceStatusEnum::CONSULTED,
        ], true);
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
}
