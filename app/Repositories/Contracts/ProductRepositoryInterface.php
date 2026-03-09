<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contrato del repositorio de Productos del Facturador.
 * TODAS las operaciones están scoped a company_id = session('company_id').
 */
interface ProductRepositoryInterface
{
    /**
     * Lista paginada de productos activos de la empresa activa.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Todos los productos activos para dropdowns/selects.
     */
    public function allActive(): Collection;

    /**
     * Busca un producto por ID dentro de la empresa activa (Anti-IDOR).
     */
    public function findForActiveCompany(int $id): Product;

    /**
     * Crea un producto. company_id se inyecta desde session en el repositorio.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Product;

    /**
     * Actualiza un producto ya validado como perteneciente a la empresa activa.
     *
     * @param array<string, mixed> $data
     */
    public function update(Product $product, array $data): Product;

    /**
     * Elimina un producto (soft-delete o toggle activo según negocio).
     */
    public function delete(Product $product): void;
}
