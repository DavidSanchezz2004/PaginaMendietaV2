<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Cotización generada por la empresa para cliente.
 * 
 * Características:
 * - Versionado: múltiples versiones de una cotización
 * - Token UUID público para compartir sin auth
 * - Estados: draft → sent → accepted / rejected
 * - Convertible a Invoice
 */
class Quote extends Model
{
    use HasFactory, BelongsToActiveCompany, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'client_id',
        'codigo_interno',
        'numero_cotizacion',
        'share_token',
        'version',
        'fecha_emision',
        'fecha_vencimiento',
        'observacion',
        'correo',
        'numero_orden_compra',
        'codigo_moneda',
        'porcentaje_igv',
        'monto_tipo_cambio',
        'monto_total_gravado',
        'monto_total_igv',
        'monto_total_descuento',
        'monto_total',
        'estado',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision'       => 'date',
            'fecha_vencimiento'   => 'date',
            'porcentaje_igv'      => 'float',
            'monto_tipo_cambio'   => 'float',
            'monto_total_gravado' => 'float',
            'monto_total_igv'     => 'float',
            'monto_total_descuento' => 'float',
            'monto_total'         => 'float',
            'sent_at'             => 'datetime',
            'accepted_at'         => 'datetime',
            'rejected_at'         => 'datetime',
        ];
    }

    // ── Boot ────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Quote $quote): void {
            if (empty($quote->share_token)) {
                $quote->share_token = (string) Str::uuid();
            }
        });
    }

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Obtiene el número de cotización completo para display.
     * Ej: 01C00100000001
     */
    public function getCodigoCompletoAttribute(): string
    {
        return $this->codigo_interno ?? "C-{$this->numero_cotizacion}";
    }

    /**
     * URL pública para compartir (sin autenticación).
     */
    public function getShareUrlAttribute(): string
    {
        return route('quotes.client.show', ['token' => $this->share_token]);
    }

    /**
     * ¿Se puede convertir a factura?
     */
    public function canBeConvertedToInvoice(): bool
    {
        return $this->estado === 'accepted' && $this->invoice_id === null;
    }

    /**
     * Calcula el saldo pendiente de ser aceptada.
     * (Útil para tracking)
     */
    public function getDiasVigenteAttribute(): int
    {
        if ($this->fecha_vencimiento) {
            return $this->fecha_vencimiento->diffInDays(now(), false);
        }
        return -1;
    }

    /**
     * ¿Ha vencido la cotización?
     */
    public function getEstaVencidaAttribute(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast()
            && !in_array($this->estado, ['accepted', 'rejected']);
    }
}
