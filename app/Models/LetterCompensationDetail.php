<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterCompensationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_compensation_id',
        'purchase_invoice_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    public function compensation(): BelongsTo
    {
        return $this->belongsTo(LetterCompensation::class, 'letter_compensation_id');
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_invoice_id');
    }
}
