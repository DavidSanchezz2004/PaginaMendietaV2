<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'format',
        'file_path',
        'external_url',
        'period_month',
        'period_year',
        'status',
        'uploaded_by',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function uploader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function reportUserStatuses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReportUserStatus::class);
    }
}
