<?php

namespace App\Services\Facturador;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service de negocio para el catálogo de Clientes del Facturador.
 */
class ClientService
{
    public function __construct(
        private readonly ClientRepositoryInterface $repository,
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

    public function find(int $id): Client
    {
        return $this->repository->findForActiveCompany($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Client
    {
        return $this->repository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Client $client, array $data): Client
    {
        return $this->repository->update($client, $data);
    }

    public function deactivate(Client $client): void
    {
        $this->repository->delete($client);
    }
}
