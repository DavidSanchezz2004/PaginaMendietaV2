<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catálogo de productos/servicios por empresa.
 * Siempre scoped a company_id = session('company_id').
 *
 * @property int    $id
 * @property int    $company_id
 * @property string $codigo_interno
 * @property string|null $codigo_sunat
 * @property string $tipo              P=Producto, S=Servicio
 * @property string $codigo_unidad_medida
 * @property string $descripcion
 * @property float  $valor_unitario    Sin IGV
 * @property float  $precio_unitario   Con IGV
 * @property string $codigo_indicador_afecto
 * @property bool   $activo
 */
class Product extends Model
{
    use HasFactory, BelongsToActiveCompany;

    protected $fillable = [
        'company_id',
        'codigo_interno',
        'codigo_sunat',
        'tipo',
        'codigo_unidad_medida',
        'descripcion',
        'valor_unitario',
        'precio_unitario',
        'codigo_indicador_afecto',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'valor_unitario'  => 'decimal:10',
            'precio_unitario' => 'decimal:10',
            'activo'          => 'boolean',
        ];
    }

    // ── Relaciones ─────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
