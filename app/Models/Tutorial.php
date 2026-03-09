<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Company;

class Tutorial extends Model
{
    protected $fillable = [
        'title',
        'description',
        'video_url',
        'is_global',
        'company_id',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where(function ($q) use ($companyId) {
            $q->where('is_global', true)
              ->orWhere('company_id', $companyId);
        });
    }
}
