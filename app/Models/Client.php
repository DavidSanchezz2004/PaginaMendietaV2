<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    use HasFactory, BelongsToActiveCompany;

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
    ];

    protected function casts(): array
    {
        return [
            'activo'    => 'boolean',
            'clave_sol' => 'encrypted',
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
}
