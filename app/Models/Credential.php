<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Credential extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['platform', 'username'])
            ->logOnlyDirty()
            ->useLogName('credencial')
            ->dontSubmitEmptyLogs();
    }
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
