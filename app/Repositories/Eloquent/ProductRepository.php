<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Implementación Eloquent del repositorio de Productos.
 * El scope forActiveCompany() garantiza multiempresa estricto.
 */
class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::forActiveCompany()
            ->orderBy('descripcion')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Product::forActiveCompany()
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get();
    }

    public function findForActiveCompany(int $id): Product
    {
        $product = Product::forActiveCompany()->find($id);

        if (! $product) {
            throw new ModelNotFoundException("Producto #{$id} no encontrado en la empresa activa.");
        }

        return $product;
    }

    public function create(array $data): Product
    {
        // company_id se fuerza desde sesión, no desde input del usuario
        $data['company_id'] = session('company_id');

        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        // Nunca permitir cambiar company_id (Anti-IDOR)
        unset($data['company_id']);

        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        // Toggle inactivo en lugar de eliminar físicamente
        // (productos pueden estar en facturas existentes)
        $product->update(['activo' => false]);
    }
}
