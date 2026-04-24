<?php

namespace App\Services\Facturador;

use App\Models\Purchase;
use App\Models\ReglaContable;
use Illuminate\Support\Facades\Session;

/**
 * Asigna automáticamente los campos contables a una compra
 * basándose en las reglas configuradas por empresa.
 */
class AccountingAssignmentService
{
    /**
     * Busca la primera regla activa que haga match y aplica sus campos.
     * Actualiza la compra en BD y retorna la instancia actualizada.
     */
    public function assign(Purchase $purchase): Purchase
    {
        $regla = $this->findMatchingRule($purchase);

        if ($regla === null) {
            // Sin regla: quedan los campos vacíos → status incompleto
            return $purchase;
        }

        $fields = $regla->toAccountingFields();

        if (empty($fields)) {
            return $purchase;
        }

        $purchase->fill($fields);
        $purchase->save();

        return $purchase;
    }

    /**
     * Evalúa las reglas activas de la empresa en orden de prioridad.
     * Retorna la primera que coincida, o null si ninguna aplica.
     */
    private function findMatchingRule(Purchase $purchase): ?ReglaContable
    {
        $companyId = $purchase->company_id ?? Session::get('company_id');

        $reglas = ReglaContable::where('company_id', $companyId)
            ->where('activo', true)
            ->orderBy('prioridad')
            ->get();

        $purchaseData = [
            'provider_id'              => $purchase->provider_id,
            'numero_doc_proveedor'     => $purchase->numero_doc_proveedor,
            'codigo_tipo_documento'    => $purchase->codigo_tipo_documento,
            'glosa'                    => $purchase->glosa,
        ];

        foreach ($reglas as $regla) {
            if ($regla->matches($purchaseData)) {
                return $regla;
            }
        }

        return null;
    }

    /**
     * Variante que trabaja sobre un array (antes de persistir).
     * Retorna el array enriquecido con los campos contables.
     */
    public function assignToArray(array $data, int $companyId): array
    {
        $reglas = ReglaContable::where('company_id', $companyId)
            ->where('activo', true)
            ->orderBy('prioridad')
            ->get();

        foreach ($reglas as $regla) {
            if ($regla->matches($data)) {
                return array_merge($data, $regla->toAccountingFields());
            }
        }

        return $data;
    }
}
