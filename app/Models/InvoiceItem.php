<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de detalle de una factura (invoice_items).
 * company_id redundante para queries Anti-IDOR directas.
 *
 * @property int    $id
 * @property int    $invoice_id
 * @property int    $company_id
 * @property int    $correlativo
 * @property string $codigo_interno
 * @property string|null $codigo_sunat
 * @property string $tipo               P=Producto, S=Servicio
 * @property string $codigo_unidad_medida
 * @property string $descripcion
 * @property float  $cantidad
 * @property float  $monto_valor_unitario   Sin IGV
 * @property float  $monto_precio_unitario  Con IGV
 * @property float|null $monto_descuento
 * @property float  $monto_valor_total      cantidad * valor_unitario
 * @property string|null $codigo_isc
 * @property float|null $monto_isc
 * @property string $codigo_indicador_afecto
 * @property float  $monto_igv
 * @property float|null $monto_impuesto_bolsa
 * @property float  $monto_total
 */
class InvoiceItem extends Model
{
    use HasFactory, BelongsToActiveCompany;

    protected $fillable = [
        'invoice_id',
        'company_id',
        'correlativo',
        'codigo_interno',
        'codigo_sunat',
        'tipo',
        'codigo_unidad_medida',
        'descripcion',
        'cantidad',
        'monto_valor_unitario',
        'monto_precio_unitario',
        'monto_descuento',
        'monto_valor_total',
        'codigo_isc',
        'monto_isc',
        'codigo_indicador_afecto',
        'monto_igv',
        'monto_impuesto_bolsa',
        'monto_total',
    ];

    protected function casts(): array
    {
        return [
            'cantidad'              => 'float',
            'monto_valor_unitario'  => 'float',
            'monto_precio_unitario' => 'float',
            'monto_descuento'       => 'float',
            'monto_valor_total'     => 'float',
            'monto_isc'             => 'float',
            'monto_igv'             => 'float',
            'monto_impuesto_bolsa'  => 'float',
            'monto_total'           => 'float',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
