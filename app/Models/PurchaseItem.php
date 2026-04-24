<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de detalle de un comprobante de compra.
 * company_id redundante para Anti-IDOR sin JOIN.
 */
class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'company_id',
        'correlativo',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'invoiced_quantity',        // NEW: Track partial invoicing
        'valor_unitario',
        'descuento',
        'importe_venta',
        'icbper',
    ];

    protected function casts(): array
    {
        return [
            'cantidad'           => 'float',
            'invoiced_quantity'  => 'float',  // NEW: For partial invoicing
            'valor_unitario'     => 'float',
            'descuento'          => 'float',
            'importe_venta'      => 'float',
            'icbper'             => 'float',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function guiaItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GuiaRemisionItem::class);
    }

    // ── Helpers para facturación parcial ────────────────────────────────────

    public function remaining_quantity(): float
    {
        return (float) ($this->cantidad - $this->invoiced_quantity);
    }
}
