<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class SunatApiCredential extends Model
{
    protected $table = 'sunat_api_credentials';

    protected $fillable = [
        'empresa_id',
        'ruc_consultante',
        'client_id',
        'client_secret',
        'scope',
        'token_url',
        'consulta_url',
        'is_active',
        'last_token_generated_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_token_generated_at' => 'datetime',
        ];
    }

    public function setClientSecretAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['client_secret'] = Crypt::encryptString($value);
    }

    public function getClientSecretAttribute(?string $value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'empresa_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(SunatToken::class, 'sunat_api_credential_id');
    }

    public function validaciones(): HasMany
    {
        return $this->hasMany(SunatComprobanteValidacion::class, 'sunat_api_credential_id');
    }
}
