<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ruc',
        'name',
        'status',
        // ── Campos Facturador / SUNAT ──────────────────────────────────────
        'razon_social',
        'ubigeo',
        'direccion_fiscal',
        'feasy_token',       // encrypted
        'facturador_enabled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facturador_enabled' => 'boolean',
            // feasy_token se guarda cifrado en BD
            'feasy_token'        => 'encrypted',
        ];
    }

    // ── Relaciones base ────────────────────────────────────────────────────

    public function companyUsers(): HasMany
    {
        return $this->hasMany(\App\Models\CompanyUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class)
            ->using(\App\Models\CompanyUser::class)
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    // ── Relaciones Facturador (siempre children de esta empresa) ──────────

    public function products(): HasMany
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(\App\Models\Client::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(\App\Models\InvoiceItem::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Verifica que el token Feasy esté configurado.
     * (El cast 'encrypted' devuelve null si el campo está vacío en BD)
     */
    public function hasFeasyToken(): bool
    {
        return ! empty($this->feasy_token);
    }
}
