<?php

namespace App\Services\Facturador;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service de negocio para el catálogo de Productos del Facturador.
 * Delega persistencia al repositorio; aquí solo lógica de negocio.
 */
class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return $this->repository->allActive();
    }

    public function find(int $id): Product
    {
        return $this->repository->findForActiveCompany($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Product
    {
        return $this->repository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Product $product, array $data): Product
    {
        return $this->repository->update($product, $data);
    }

    public function deactivate(Product $product): void
    {
        $this->repository->delete($product);
    }
}
