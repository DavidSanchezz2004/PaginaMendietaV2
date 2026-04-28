<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Catálogo de clientes/receptores por empresa.
 * Siempre scoped a company_id = session('company_id').
 *
 * @property int    $id
 * @property int    $company_id
 * @property string $codigo_tipo_documento   1=DNI, 4=Carnet, 6=RUC, 7=Pasaporte
 * @property string $numero_documento
 * @property string $nombre_razon_social
 * @property string $codigo_pais             PE por defecto
 * @property string|null $ubigeo
 * @property string|null $departamento
 * @property string|null $provincia
 * @property string|null $distrito
 * @property string|null $urbanizacion
 * @property string|null $direccion
 * @property string|null $correo
 * @property bool   $activo
 */
class Client extends Model
{
    use HasFactory, BelongsToActiveCompany, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero_documento', 'nombre_razon_social', 'activo'])
            ->logOnlyDirty()
            ->useLogName('cliente')
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'company_id',
        'codigo_tipo_documento',
        'numero_documento',
        'nombre_razon_social',
        'codigo_pais',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'urbanizacion',
        'direccion',
        'correo',
        'activo',
        'usuario_sol',
        'clave_sol',
        'sunat_token',
        'sunat_token_expires_at',
        'is_retainer_agent',        // NEW: 3% retention applies if true
    ];

    protected function casts(): array
    {
        return [
            'activo'                 => 'boolean',
            'is_retainer_agent'      => 'boolean',  // NEW: For 3% retention logic
            'clave_sol'              => 'encrypted',
            'sunat_token_expires_at' => 'datetime',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function getNombreClienteAttribute(): ?string
    {
        return $this->nombre_razon_social;
    }

    // ── Dirección (UX para asignación) ───────────────────────────────────

    public function default_address(): ?ClientAddress
    {
        return $this->addresses()
            ->where('is_default', true)
            ->first()
            ?? $this->addresses()
                ->where('type', 'delivery')
                ->first();
    }

    // ── Retención (3% para agentes retenedores) ──────────────────────────

    public function get_retention_percentage(): float
    {
        return $this->is_retainer_agent ? 3.0 : 0.0;
    }
}
