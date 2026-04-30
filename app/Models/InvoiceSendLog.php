<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceSendLog extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'user_id',
        'action',
        'attempt_number',
        'endpoint',
        'codigo_tipo_documento',
        'serie_documento',
        'numero_documento',
        'codigo_interno',
        'monto_total',
        'success',
        'http_status',
        'codigo_respuesta',
        'mensaje_respuesta',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'monto_total' => 'decimal:2',
            'success' => 'boolean',
            'http_status' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
