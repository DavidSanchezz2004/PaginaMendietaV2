<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoLetra extends Model
{
    use HasFactory;

    protected $table = 'pagos_letras';

    protected $fillable = [
        'letra_cambio_id',
        'company_id',
        'user_id',
        'fecha_pago',
        'monto_pagado',
        'medio_pago',
        'referencia_pago',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago'   => 'date',
            'monto_pagado' => 'float',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────────

    public function letraCambio(): BelongsTo
    {
        return $this->belongsTo(LetraCambio::class, 'letra_cambio_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    public function getMedioPagoLabelAttribute(): string
    {
        return match ($this->medio_pago) {
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            'cheque'        => 'Cheque',
            'yape'          => 'Yape',
            'plin'          => 'Plin',
            'compensacion'  => 'Endoso / Compensación',
            default         => ucfirst($this->medio_pago),
        };
    }
}
