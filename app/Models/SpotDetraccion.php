<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo de bienes y servicios sujetos a detracción SPOT.
 *
 * @property int    $id
 * @property string $codigo        Código SUNAT (ej. "022", "030")
 * @property string $descripcion
 * @property float  $porcentaje    Porcentaje vigente (ej. 4.00, 10.00)
 * @property bool   $activo
 */
class SpotDetraccion extends Model
{
    protected $table = 'spot_detracciones';

    protected $fillable = ['codigo', 'descripcion', 'porcentaje', 'activo'];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'float',
            'activo'     => 'boolean',
        ];
    }

    /** Solo los códigos vigentes. */
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('codigo');
    }
}
