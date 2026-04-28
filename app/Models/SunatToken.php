<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class SunatToken extends Model
{
    protected $table = 'sunat_tokens';

    protected $fillable = [
        'empresa_id',
        'sunat_api_credential_id',
        'access_token',
        'token_type',
        'expires_in',
        'expires_at',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_in' => 'integer',
            'expires_at' => 'datetime',
            'generated_at' => 'datetime',
        ];
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'empresa_id');
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(SunatApiCredential::class, 'sunat_api_credential_id');
    }
}
