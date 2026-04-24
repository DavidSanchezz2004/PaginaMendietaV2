<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Configuración de branding y visualización para cotizaciones por empresa.
 * 
 * Almacena:
 * - Logo y colores
 * - Cuentas bancarias
 * - Textos personalizables
 * - Configuración de PDF
 */
class CompanySetting extends Model
{
    use HasFactory;

    protected $table = 'company_settings';

    protected $fillable = [
        'company_id',
        'logo_path',
        'primary_color',
        'secondary_color',
        'company_name',
        'ruc',
        'address',
        'phone',
        'email',
        'website',
        'bank_accounts',
        'quote_footer',
        'quote_terms',
        'quote_thanks_message',
        'show_igv_breakdown',
        'show_bank_accounts',
        'require_client_email',
        'paper_size',
        'paper_orientation',
    ];

    protected function casts(): array
    {
        return [
            'bank_accounts'         => 'array',
            'show_igv_breakdown'    => 'boolean',
            'show_bank_accounts'    => 'boolean',
            'require_client_email'  => 'boolean',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Obtiene el path del logo para mostrar en vistas.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }

    /**
     * Obtiene las cuentas bancarias formateadas.
     */
    public function getBankAccountsFormattedAttribute(): array
    {
        return array_map(function ($account) {
            return [
                'banco' => $account['banco'] ?? 'N/A',
                'cuenta' => $account['cuenta'] ?? '',
                'cci' => $account['cci'] ?? '',
                'moneda' => $account['moneda'] ?? 'PEN',
            ];
        }, $this->bank_accounts ?? []);
    }

    /**
     * Obtiene colores con valores por defecto si no están configurados.
     */
    public function getColorsAttribute(): array
    {
        return [
            'primary' => $this->primary_color ?? '#000000',
            'secondary' => $this->secondary_color ?? '#CCCCCC',
        ];
    }
}
