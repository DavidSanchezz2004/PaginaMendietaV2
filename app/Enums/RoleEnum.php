<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case SUPERVISOR = 'supervisor';
    case AUXILIAR = 'auxiliar';
    case ACCOUNTANT = 'accountant';
    case CLIENT = 'client';
}
