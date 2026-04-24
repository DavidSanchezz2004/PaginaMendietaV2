<?php

namespace App\Enums;

enum TicketStatusEnum: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Abierto',
            self::IN_PROGRESS => 'En Revisión',
            self::RESOLVED => 'Resuelto',
            self::CLOSED => 'Cerrado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'red',
            self::IN_PROGRESS => 'blue',
            self::RESOLVED => 'green',
            self::CLOSED => 'gray',
        };
    }
}
