<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuzonKeyword extends Model
{
    protected $table = 'buzon_keywords';

    protected $fillable = [
        'palabra',
        'prioridad',
        'color',
    ];

    /** Etiqueta legible de prioridad. */
    public function getPrioridadLabelAttribute(): string
    {
        return match ($this->prioridad) {
            'alta'  => 'Alta',
            'media' => 'Media',
            'baja'  => 'Baja',
            default => $this->prioridad,
        };
    }
}
