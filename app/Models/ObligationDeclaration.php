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
        'declared_by',
        'declared_at',
    ];

    protected $casts = [
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
