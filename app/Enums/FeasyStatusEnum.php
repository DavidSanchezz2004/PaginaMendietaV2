<?php

namespace App\Enums;

/**
 * Estado del comprobante en el sistema Feasy/SUNAT.
 * Trazabilidad independiente de InvoiceStatusEnum para poder
 * mostrar el estado de comunicación con Feasy por separado.
 */
enum FeasyStatusEnum: string
{
    /** Aún no enviado a Feasy */
    case PENDING = 'pending';

    /** Enviado y aceptado por SUNAT (codigo_respuesta = "0") */
    case SENT = 'sent';

    /**
     * Ticket generado por SUNAT (GRE asíncrona).
     * codigo_respuesta = "A01" — equivale a "recibido, procesando".
     * El estado definitivo se obtiene al consultar.
     */
    case TICKET = 'ticket';

    /** Rechazado por SUNAT (codigo_respuesta != "0") */
    case REJECTED = 'rejected';

    /** Error de comunicación o token inválido */
    case ERROR = 'error';

    /** Consultado a Feasy exitosamente */
    case CONSULTED = 'consulted';

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'Pendiente',
            self::SENT      => 'Aceptado SUNAT',
            self::TICKET    => 'Ticket generado',
            self::REJECTED  => 'Rechazado SUNAT',
            self::ERROR     => 'Error Feasy',
            self::CONSULTED => 'Consultado',
        };
    }

    /** Devuelve true cuando SUNAT aceptó el comprobante (codigo "0") */
    public function isAccepted(): bool
    {
        return $this === self::SENT;
    }
}
