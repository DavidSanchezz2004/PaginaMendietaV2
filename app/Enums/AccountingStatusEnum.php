<?php

namespace App\Enums;

enum AccountingStatusEnum: string
{
    case INCOMPLETO = 'incompleto';
    case PENDIENTE  = 'pendiente';
    case LISTO      = 'listo';
    case OBSERVADO  = 'observado';

    public function label(): string
    {
        return match($this) {
            self::INCOMPLETO => 'Incompleto',
            self::PENDIENTE  => 'Pendiente',
            self::LISTO      => 'Listo',
            self::OBSERVADO  => 'Observado',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::INCOMPLETO => 'accounting-badge--incompleto',
            self::PENDIENTE  => 'accounting-badge--pendiente',
            self::LISTO      => 'accounting-badge--listo',
            self::OBSERVADO  => 'accounting-badge--observado',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::INCOMPLETO => '✗',
            self::PENDIENTE  => '⚠',
            self::LISTO      => '✓',
            self::OBSERVADO  => '⚑',
        };
    }
}
