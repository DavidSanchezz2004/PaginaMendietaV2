<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BuzonLectura extends Model
{
    protected $table = 'buzon_lecturas';

    public $timestamps = false;

    protected $fillable = [
        'buzon_mensaje_id',
        'user_id',
        'leido_at',
    ];

    protected $casts = [
        'leido_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(BuzonMensaje::class, 'buzon_mensaje_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
