<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obligation extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
