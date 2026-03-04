<?php

namespace App\Models;

use App\Enums\RoleEnum;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => RoleEnum::class,
            'password' => 'hashed',
        ];
    }

    public function companyUsers(): HasMany
    {
        return $this->hasMany(\App\Models\CompanyUser::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Company::class)
            ->using(\App\Models\CompanyUser::class)
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Reports read by the user (useful for dashboard unread metrics).
     */
    public function readReports(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Report::class, 'report_user_status')
            ->withPivot(['read_at', 'valued_at'])
            ->whereNotNull('report_user_status.read_at')
            ->withTimestamps();
    }
}
