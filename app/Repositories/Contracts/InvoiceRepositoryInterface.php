<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contrato del repositorio de Facturas del Facturador.
 * TODAS las operaciones están scoped a company_id = session('company_id').
 */
interface InvoiceRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findForActiveCompany(int $id): Invoice;

    /**
     * Encuentra incluyendo items y cliente (eager load).
     */
    public function findWithItems(int $id): Invoice;

    /**
     * @param array<string, mixed> $data
     * @param array<int, array<string, mixed>> $items
     */
    public function createWithItems(array $data, array $items): Invoice;

    /**
     * @param array<string, mixed> $data
     */
    public function update(Invoice $invoice, array $data): Invoice;

    /**
     * Persiste la respuesta de Feasy después de emitir.
     *
     * @param array<string, mixed> $feasyResponse
     */
    public function persistEmitResponse(Invoice $invoice, array $feasyResponse): Invoice;

    /**
     * Persiste la respuesta de Feasy después de consultar.
     *
     * @param array<string, mixed> $feasyResponse
     */
    public function persistConsultResponse(Invoice $invoice, array $feasyResponse): Invoice;

    /**
     * Devuelve la serie por defecto y el siguiente número disponible
     * para cada tipo de comprobante de la empresa.
     *
     * @return array<string, array{serie: string, numero: int}>
     */
    public function nextDocumentSuggestions(int $companyId): array;
}
