<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de detalle de una guía de remisión.
 * Copia de purchase_item pero independiente para permitir cambios sin afectar compra.
 */
class GuiaRemisionItem extends Model
{
    protected $fillable = [
        'guia_remision_id',
        'purchase_item_id',
        'quantity',
        'unit',
        'description',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'float',
            'unit_price' => 'float',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function guia(): BelongsTo
    {
        return $this->belongsTo(GuiaRemision::class, 'guia_remision_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }
}
