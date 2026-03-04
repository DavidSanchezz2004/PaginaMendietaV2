<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    protected $fillable = [
        'company_id',
        'platform',
        'username',
        'password',
        'notes',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
