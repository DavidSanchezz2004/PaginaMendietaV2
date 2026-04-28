<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Guía de Remisión de Transportista (GRE).
 * Documento que autoriza el transporte de mercancías entre locales.
 * Puente entre Compra y Factura.
 */
class GuiaRemision extends Model
{
    use HasFactory, BelongsToActiveCompany, SoftDeletes;

    protected $table = 'guia_remisions';

    protected $fillable = [
        'company_id',
        'purchase_id',
        'client_id',
        'client_address_id',
        'numero',
        'fecha_emision',
        'motivo',
        'gre_payload',
        'estado',
        'invoice_id',
    ];

    protected $dates = [
        'fecha_emision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'gre_payload' => 'array',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientAddress(): BelongsTo
    {
        return $this->belongsTo(ClientAddress::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GuiaRemisionItem::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function getTotalItemsCount(): float
    {
        return (float) $this->items()
            ->sum('quantity');
    }

    public function is_invoiced(): bool
    {
        return $this->estado === 'invoiced' && $this->invoice_id !== null;
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'draft' => 'Borrador',
            'generated' => 'Generada',
            'invoiced' => 'Facturada',
            default => $this->estado,
        };
    }

    public function getEstadoBadgeClassAttribute(): string
    {
        return match($this->estado) {
            'draft' => 'badge bg-secondary',
            'generated' => 'badge bg-info',
            'invoiced' => 'badge bg-success',
            default => 'badge bg-secondary',
        };
    }
}
