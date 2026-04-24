<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    private array $globalRoles = ['admin', 'supervisor'];

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Ticket $ticket): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        if (in_array($userRole, $this->globalRoles, true)) {
            return true;
        }

        // AUXILIAR: puede ver cualquier ticket de su empresa activa
        if ($userRole === 'auxiliar') {
            return $ticket->company_id === session('company_id');
        }

        // CLIENT: solo puede ver sus propios tickets (Anti-IDOR + anti-data-leak)
        return $ticket->company_id === session('company_id')
            && $ticket->client_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; 
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        if (in_array($userRole, $this->globalRoles, true)) {
            return true;
        }

        // El Auxiliar puede cambiar el estado de un ticket si es de la empresa de su sesión (Anti-IDOR)
        if ($userRole === 'auxiliar') {
            return $ticket->company_id === session('company_id');
        }

        return false; // Cliente no puede cambiar estados
    }

    public function createMessage(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket); // Si puede verlo, puede chatear
    }
}

