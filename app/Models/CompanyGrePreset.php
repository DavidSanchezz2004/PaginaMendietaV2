<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyGrePreset extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'partida_ubigeo',
        'partida_direccion',
        'llegada_ubigeo',
        'modalidad',
        'unidad_peso',
        'placa',
        'conductor_dni',
        'conductor_nombre',
        'conductor_apellido',
        'conductor_licencia',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}
