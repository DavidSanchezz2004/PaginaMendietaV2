<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cobro/pago registrado para un comprobante.
 *
 * @property int    $id
 * @property int    $invoice_id
 * @property string $metodo       efectivo | yape | plin | transferencia | deposito | tarjeta | otro
 * @property float  $monto
 * @property string|null $referencia   N° operación, código de transacción, etc.
 * @property string|null $notas
 */
class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'metodo',
        'monto',
        'referencia',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function metodoLabel(): string
    {
        return match($this->metodo) {
            'efectivo'      => 'Efectivo',
            'yape'          => 'Yape',
            'plin'          => 'Plin',
            'transferencia' => 'Transferencia',
            'deposito'      => 'Depósito',
            'tarjeta'       => 'Tarjeta',
            default         => ucfirst($this->metodo),
        };
    }

    public function metodoIcon(): string
    {
        return match($this->metodo) {
            'efectivo'      => 'bx-money',
            'yape'          => 'bx-mobile-alt',
            'plin'          => 'bx-mobile-alt',
            'transferencia' => 'bx-transfer',
            'deposito'      => 'bx-bank',
            'tarjeta'       => 'bx-credit-card',
            default         => 'bx-wallet',
        };
    }
}
