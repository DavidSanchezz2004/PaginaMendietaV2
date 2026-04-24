<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Item (línea) de una cotización.
 * Estructura similar a InvoiceItem pero más simplificada.
 */
class QuoteItem extends Model
{
    use HasFactory, BelongsToActiveCompany;

    protected $fillable = [
        'quote_id',
        'company_id',
        'codigo_interno',
        'codigo_sunat',
        'tipo',
        'descripcion',
        'codigo_unidad_medida',
        'cantidad',
        'monto_valor_unitario',
        'monto_precio_unitario',
        'monto_descuento',
        'monto_valor_total',
        'codigo_indicador_afecto',
        'monto_igv',
        'monto_total',
    ];

    protected function casts(): array
    {
        return [
            'cantidad'                => 'float',
            'monto_valor_unitario'    => 'float',
            'monto_precio_unitario'   => 'float',
            'monto_descuento'         => 'float',
            'monto_valor_total'       => 'float',
            'monto_igv'               => 'float',
            'monto_total'             => 'float',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Calcula el monto total del item.
     */
    public function calcularTotal(): float
    {
        $valorTotal = $this->cantidad * ($this->monto_valor_unitario ?? 0);
        $conDescuento = $valorTotal - ($this->monto_descuento ?? 0);
        $igv = $conDescuento * ($this->monto_igv ? ($this->monto_igv / 100) : 0);
        
        return round($conDescuento + $igv, 2);
    }

    /**
     * Valida que los cálculos sean correctos.
     */
    public function getValidacionAttribute(): array
    {
        $calculado = $this->calcularTotal();
        $guardado = $this->monto_total ?? 0;
        
        return [
            'es_valido' => abs($calculado - $guardado) < 0.01, // Tolerancia de 1 centavo
            'calculado' => $calculado,
            'guardado' => $guardado,
        ];
    }
}
