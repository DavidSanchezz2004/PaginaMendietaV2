<?php

namespace App\Enums;

enum InvoiceStatusEnum: string
{
    /** Guardada en BD pero aún no lista para emitir */
    case DRAFT = 'draft';

    /** Validada y lista para enviarse a Feasy/SUNAT */
    case READY = 'ready';

    /** Enviada exitosamente a Feasy y aceptada por SUNAT */
    case SENT = 'sent';

    /** Error al enviar (token, SUNAT, red, etc.) */
    case ERROR = 'error';

    /** Consultada a Feasy después de la emisión */
    case CONSULTED = 'consulted';

    case VOIDED = 'voided';

    public function label(): string
    {
        return match($this) {
            self::DRAFT     => 'Borrador',
            self::READY     => 'Lista',
            self::SENT      => 'Enviada',
            self::ERROR     => 'Error',
            self::CONSULTED => 'Consultada',
            self::VOIDED    => 'Anulada',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DRAFT     => 'badge-secondary',
            self::READY     => 'badge-info',
            self::SENT      => 'badge-success',
            self::ERROR     => 'badge-danger',
            self::CONSULTED => 'badge-primary',
            self::VOIDED    => 'badge-secondary',
        };
    }

    /** Estados en los que ya NO se puede re-emitir */
    public function isTerminal(): bool
    {
        return in_array($this, [self::SENT, self::CONSULTED, self::VOIDED], true);
    }
}
