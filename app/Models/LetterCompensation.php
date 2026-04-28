<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterCompensation extends Model
{
    use HasFactory;

    protected $table = 'letter_compensations';

    protected $fillable = [
        'bill_of_exchange_id',
        'supplier_id',
        'company_id',
        'created_by',
        'compensation_date',
        'currency',
        'total_amount',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'compensation_date' => 'date',
            'total_amount' => 'float',
        ];
    }

    public function letraCambio(): BelongsTo
    {
        return $this->belongsTo(LetraCambio::class, 'bill_of_exchange_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'supplier_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(LetterCompensationDetail::class);
    }
}
