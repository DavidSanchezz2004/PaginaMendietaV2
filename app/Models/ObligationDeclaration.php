<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObligationDeclaration extends Model
{
    protected $fillable = [
        'company_id',
        'period_year',
        'period_month',
        'due_group',
        'due_date',
        'presentation_date',
        'status',
        'observation',
        'declared_by',
        'declared_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'presentation_date' => 'date',
        'declared_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function declaredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declared_by');
    }
}
