<?php

namespace App\Repositories\Contracts;

use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PurchaseRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findForActiveCompany(int $id): Purchase;

    public function create(array $data): Purchase;

    public function update(Purchase $purchase, array $data): Purchase;

    public function delete(Purchase $purchase): void;
}
