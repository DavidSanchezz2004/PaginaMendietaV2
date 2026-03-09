<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Implementación Eloquent del repositorio de Clientes del Facturador.
 * El scope forActiveCompany() garantiza multiempresa estricto.
 */
class ClientRepository implements ClientRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Client::forActiveCompany()
            ->orderBy('nombre_razon_social')
            ->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return Client::forActiveCompany()
            ->where('activo', true)
            ->orderBy('nombre_razon_social')
            ->get();
    }

    public function findForActiveCompany(int $id): Client
    {
        $client = Client::forActiveCompany()->find($id);

        if (! $client) {
            throw new ModelNotFoundException("Cliente #{$id} no encontrado en la empresa activa.");
        }

        return $client;
    }

    public function create(array $data): Client
    {
        // company_id se fuerza desde sesión (nunca del input)
        $data['company_id'] = session('company_id');

        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        // Nunca cambiar company_id (Anti-IDOR)
        unset($data['company_id']);

        $client->update($data);
        return $client->fresh();
    }

    public function delete(Client $client): void
    {
        // Toggle inactivo (puede tener facturas históricas)
        $client->update(['activo' => false]);
    }
}
