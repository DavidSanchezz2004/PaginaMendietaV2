<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BuzonMensaje extends Model
{
    protected $table = 'buzon_mensajes';

    protected $fillable = [
        'company_id',
        'cod_sunat',
        'asunto',
        'remitente',
        'fecha',
        'tipo',
        'detalle_json',
    ];

    protected $casts = [
        'fecha' => 'date',
        'tipo'  => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(BuzonLectura::class, 'buzon_mensaje_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    /** 
     * Devuelve el nivel de prioridad basado en keywords coincidentes en el asunto.
     * Prioridades: alta > media > baja > null (sin coincidencia)
     */
    public function getPrioridadAttribute(): ?string
    {
        if (! $this->asunto) {
            return null;
        }

        $keywords = BuzonKeyword::orderByRaw("FIELD(prioridad,'alta','media','baja')")->get();
        $asuntoLower = mb_strtolower($this->asunto);

        foreach ($keywords as $kw) {
            if (str_contains($asuntoLower, mb_strtolower($kw->palabra))) {
                return $kw->prioridad;
            }
        }

        return null;
    }

    /** Color del badge de prioridad. */
    public function getPrioridadColorAttribute(): string
    {
        $colores = [
            'alta'  => '#ef4444',
            'media' => '#f59e0b',
            'baja'  => '#3b82f6',
        ];

        return $colores[$this->prioridad] ?? '#6b7280';
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function leidoPor(int $userId): bool
    {
        return $this->lecturas()->where('user_id', $userId)->exists();
    }
}
