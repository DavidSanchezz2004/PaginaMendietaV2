<?php

namespace App\Repositories\Eloquent;

use App\Models\Purchase;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Purchase::forActiveCompany()
            ->with(['provider', 'user'])
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($filters['accounting_status'])) {
            $query->where('accounting_status', $filters['accounting_status']);
        }

        if (! empty($filters['tipo_documento'])) {
            $query->where('codigo_tipo_documento', $filters['tipo_documento']);
        }

        if (! empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_emision', '>=', $filters['fecha_desde']);
        }

        if (! empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_emision', '<=', $filters['fecha_hasta']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('razon_social_proveedor', 'like', "%{$term}%")
                  ->orWhere('numero_doc_proveedor', 'like', "%{$term}%")
                  ->orWhere('serie_documento', 'like', "%{$term}%")
                  ->orWhere('numero_documento', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findForActiveCompany(int $id): Purchase
    {
        $purchase = Purchase::forActiveCompany()->find($id);

        if (! $purchase) {
            throw new ModelNotFoundException("Compra #{$id} no encontrada en la empresa activa.");
        }

        return $purchase;
    }

    public function create(array $data): Purchase
    {
        // company_id se fuerza desde sesión
        $data['company_id'] = session('company_id');

        return Purchase::create($data);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        // Anti-IDOR: nunca cambiar company_id
        unset($data['company_id']);

        $purchase->update($data);
        return $purchase->fresh(['provider', 'user']);
    }

    public function delete(Purchase $purchase): void
    {
        $purchase->delete();
    }
}
