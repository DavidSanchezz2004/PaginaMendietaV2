<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Letra de cambio generada a partir de una compra (o manualmente).
 */
class LetraCambio extends Model
{
    use HasFactory, BelongsToActiveCompany, SoftDeletes;

    protected $table = 'letras_cambio';

    protected $fillable = [
        'company_id',
        'purchase_id',
        'invoice_id',
        'user_id',
        'numero_letra',
        'referencia',
        'tenedor_nombre',
        'tenedor_ruc',
        'tenedor_domicilio',
        'aceptante_nombre',
        'aceptante_ruc',
        'aceptante_domicilio',
        'aceptante_telefono',
        'aceptante_representante',
        'aceptante_doi',
        'lugar_giro',
        'fecha_giro',
        'fecha_vencimiento',
        'codigo_moneda',
        'monto',
        'monto_letras',
        'banco',
        'banco_oficina',
        'banco_cuenta',
        'banco_dc',
        'cuenta_contable',
        'estado',
        'monto_pagado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_giro'       => 'date',
            'fecha_vencimiento'=> 'date',
            'monto'            => 'float',
            'monto_pagado'     => 'float',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoLetra::class, 'letra_cambio_id');
    }

    // ── Accessors ───────────────────────────────────────────────────────

    public function getSaldoAttribute(): float
    {
        return round(max(0.0, $this->monto - $this->monto_pagado), 2);
    }

    public function getEstaVencidaAttribute(): bool
    {
        return $this->estado === 'pendiente'
            && $this->fecha_vencimiento->isPast();
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'Pendiente',
            'cobrado'    => 'Cobrado',
            'protestado' => 'Protestado',
            default      => ucfirst($this->estado),
        };
    }

    public function getEstadoBadgeClassAttribute(): string
    {
        return match ($this->estado) {
            'cobrado'    => 'badge bg-success',
            'protestado' => 'badge bg-danger',
            default      => $this->esta_vencida ? 'badge bg-warning text-dark' : 'badge bg-secondary',
        };
    }

    // ── Scopes ──────────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'pendiente')
                     ->where('fecha_vencimiento', '<', now()->toDateString());
    }
}
