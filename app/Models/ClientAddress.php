<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dirección de entrega/fiscal de un cliente.
 * Soporta múltiples direcciones (fiscal, de entrega, etc).
 */
class ClientAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'street',
        'city',
        'state',
        'postal_code',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->street,
            $this->city,
            $this->state,
            $this->postal_code,
        ])
            ->filter()
            ->join(', ');
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'fiscal' => 'Dirección Fiscal',
            'delivery' => 'Dirección de Entrega',
            default => $this->type,
        };
    }
}
