<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\FacturadorPolicyTrait;

/**
 * Policy para las Facturas del Facturador.
 * Incluye acciones especiales: emit (emitir) y consult (consultar Feasy).
 * Regla transversal: canAccessFacturador() + Anti-IDOR en cada acción.
 */
class InvoicePolicy
{
    use FacturadorPolicyTrait;

    public function viewAny(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($invoice);
    }

    public function create(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        // Anti-IDOR + solo se puede editar si está en borrador
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($invoice)
            && $invoice->canBeEmitted();
    }

    /**
     * Emitir factura a Feasy/SUNAT.
     */
    public function emit(User $user, Invoice $invoice): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($invoice)
            && $invoice->canBeEmitted();
    }

    /**
     * Consultar estado del comprobante en Feasy.
     */
    public function consult(User $user, Invoice $invoice): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($invoice)
            && $invoice->canBeConsulted();
    }

    /**
     * Descargar XML del comprobante (storage privado).
     */
    public function downloadXml(User $user, Invoice $invoice): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($invoice)
            && $invoice->xml_path !== null;
    }

    /**
     * Solo se pueden eliminar borradores o comprobantes con error.
     * Los enviados/aceptados por SUNAT son intocables.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($invoice)
            && $invoice->canBeDeleted();
    }

    /**
     * Anular comprobante: solo Facturas/Boletas enviadas o consultadas.
     */
    public function void(User $user, Invoice $invoice): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($invoice)
            && $invoice->canBeVoided();
    }
}
