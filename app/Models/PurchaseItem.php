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
        'valor_unitario',
        'descuento',
        'importe_venta',
        'icbper',
    ];

    protected function casts(): array
    {
        return [
            'cantidad'       => 'float',
            'valor_unitario' => 'float',
            'descuento'      => 'float',
            'importe_venta'  => 'float',
            'icbper'         => 'float',
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
}
