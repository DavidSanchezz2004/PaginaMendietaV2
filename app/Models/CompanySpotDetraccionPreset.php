<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySpotDetraccionPreset extends Model
{
    protected $fillable = [
        'company_id',
        'spot_detraccion_id',
        'name',
        'codigo_bbss_sujeto_detraccion',
        'porcentaje_detraccion',
        'cuenta_banco_detraccion',
        'codigo_medio_pago_detraccion',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_detraccion' => 'float',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function spotDetraccion(): BelongsTo
    {
        return $this->belongsTo(SpotDetraccion::class);
    }
}
