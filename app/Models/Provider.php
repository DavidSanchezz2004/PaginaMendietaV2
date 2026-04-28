<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory, BelongsToActiveCompany;

    protected $fillable = [
        'company_id',
        'tipo_documento',
        'numero_documento',
        'nombre_razon_social',
        'nombre_comercial',
        'direccion',
        'telefono',
        'email',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function letterCompensations(): HasMany
    {
        return $this->hasMany(LetterCompensation::class, 'supplier_id');
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    public function getNombreDisplayAttribute(): string
    {
        return $this->nombre_comercial ?? $this->nombre_razon_social;
    }
}
