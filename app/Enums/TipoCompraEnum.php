<?php

namespace App\Enums;

enum TipoCompraEnum: string
{
    case NO_GRAVADAS   = 'NI'; // Adquisiciones no gravadas
    case GRAVADAS      = 'NG'; // Adquisiciones gravadas (con IGV)
    case EXPORTACION   = 'EX'; // Exportación
    case GRATUITAS     = 'GR'; // Adquisiciones gratuitas
    case MIXTAS        = 'MX'; // Mixtas (gravadas + no gravadas)

    public function label(): string
    {
        return match ($this) {
            self::NO_GRAVADAS  => 'No Gravadas',
            self::GRAVADAS     => 'Gravadas',
            self::EXPORTACION  => 'Exportación',
            self::GRATUITAS    => 'Gratuitas',
            self::MIXTAS       => 'Mixtas',
        };
    }
}