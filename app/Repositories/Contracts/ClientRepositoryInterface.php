<?php

namespace App\Repositories\Contracts;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contrato del repositorio de Clientes del Facturador.
 * TODAS las operaciones están scoped a company_id = session('company_id').
 */
interface ClientRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function allActive(): Collection;

    public function findForActiveCompany(int $id): Client;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Client;

    /**
     * @param array<string, mixed> $data
     */
    public function update(Client $client, array $data): Client;

    public function delete(Client $client): void;
}
