<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SunatComprobanteValidacion extends Model
{
    protected $table = 'sunat_comprobante_validaciones';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'sunat_api_credential_id',
        'ruc_consultante',
        'num_ruc_emisor',
        'cod_comp',
        'numero_serie',
        'numero',
        'fecha_emision',
        'monto',
        'success',
        'message',
        'estado_cp',
        'estado_cp_texto',
        'estado_ruc',
        'estado_ruc_texto',
        'cond_domi_ruc',
        'cond_domi_ruc_texto',
        'observaciones',
        'error_code',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'monto' => 'decimal:2',
            'success' => 'boolean',
            'observaciones' => 'array',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(SunatApiCredential::class, 'sunat_api_credential_id');
    }
}
