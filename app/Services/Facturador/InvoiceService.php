<?php

namespace App\Services\Facturador;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

/**
 * Service de orquestación para Facturas del Facturador.
 *
 * Flujo completo:
 *   1. create()   → Borrador en BD (sin enviar)
 *   2. emit()     → Validar → FeasyService::sendFactura → persistir respuesta
 *   3. consult()  → FeasyService::consultar → persistir → guardar XML
 */
class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
        private readonly FeasyService $feasyService,
    ) {
    }

    // ── Catálogo / Listado ─────────────────────────────────────────────

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function find(int $id): Invoice
    {
        return $this->repository->findForActiveCompany($id);
    }

    public function findWithItems(int $id): Invoice
    {
        return $this->repository->findWithItems($id);
    }

    /**
     * Devuelve sugerencias de serie + siguiente número para cada tipo de comprobante.
     */
    public function getDocumentSuggestions(): array
    {
        return $this->repository->nextDocumentSuggestions((int) session('company_id'));
    }

    // ── Creación ───────────────────────────────────────────────────────

    /**
     * Crea la factura borrador + sus items en una transacción atómica.
     *
     * @param array<string, mixed>              $data
     * @param array<int, array<string, mixed>>  $items
     */
    public function create(array $data, array $items): Invoice
    {
        if (empty($items)) {
            throw new RuntimeException('Una factura debe tener al menos un item.');
        }

        return $this->repository->createWithItems($data, $items);
    }

    // ── Emisión a Feasy/SUNAT ──────────────────────────────────────────

    /**
     * Emite la factura a Feasy y persiste el resultado.
     *
     * Checklist de validaciones pre-emisión (todas en FeasyService):
     *  ✓ invoice.company_id == session('company_id')  (Anti-IDOR)
     *  ✓ company.facturador_enabled = true
     *  ✓ FEASY_TOKEN global configurado (config('services.feasy.token'))
     *  ✓ monto_total > 0
     *  ✓ totales coherentes (gravado + igv ≈ total)
     *  ✓ items con monto_total > 0
     *
     * @throws RuntimeException si la factura no puede emitirse
     */
    public function emit(Invoice $invoice): Invoice
    {
        if (! $invoice->canBeEmitted()) {
            throw new RuntimeException(
                "La factura {$invoice->serie_numero} no puede emitirse en estado '{$invoice->estado->value}'."
            );
        }

        // Cargar items si no están cargados
        if (! $invoice->relationLoaded('items')) {
            $invoice->load(['items', 'client', 'company']);
        }

        // FeasyService valida y envía (lanza RuntimeException si falla validación)
        // sendComprobante elige automáticamente el endpoint: enviar_factura (01) o enviar_boleta (03)
        $response = $this->feasyService->sendComprobante($invoice);

        // Guardar XML si Feasy lo devuelve en la respuesta de emisión
        $this->handleXmlFromResponse($invoice, $response);

        // Persistir resultado (éxito o error)
        return $this->repository->persistEmitResponse($invoice, $response);
    }

    // ── Consulta a Feasy ───────────────────────────────────────────────

    /**
     * Consulta el estado del comprobante en Feasy y persiste resultado + XML.
     *
     * @throws RuntimeException si la factura no puede consultarse
     */
    public function consult(Invoice $invoice): Invoice
    {
        if (! $invoice->canBeConsulted()) {
            throw new RuntimeException(
                "La factura {$invoice->serie_numero} no puede consultarse en estado '{$invoice->estado->value}'."
            );
        }

        if (! $invoice->relationLoaded('company')) {
            $invoice->load('company');
        }

        $response = $this->feasyService->consultar(
            company: $invoice->company,
            tipo:    $invoice->codigo_tipo_documento,
            serie:   $invoice->serie_documento,
            numero:  $invoice->numero_documento,
        );

        // Si la respuesta incluye XML en base64 o como string, guardarlo
        $this->handleXmlFromResponse($invoice, $response);

        return $this->repository->persistConsultResponse($invoice, $response);
    }

    // ── Anulación ──────────────────────────────────────────────────────

    /**
     * Envía la solicitud de anulación a Feasy y actualiza el estado.
     *
     * @throws RuntimeException
     */
    public function void(Invoice $invoice, string $motivo): Invoice
    {
        if (! $invoice->canBeVoided()) {
            throw new RuntimeException(
                "La factura {$invoice->serie_numero} no puede anularse en estado '{$invoice->estado->value}'."
            );
        }

        if (! $invoice->relationLoaded('company')) {
            $invoice->load('company');
        }

        $response = $this->feasyService->void($invoice, $invoice->company, $motivo);

        if ($response['success']) {
            $invoice->update([
                'estado'                  => \App\Enums\InvoiceStatusEnum::VOIDED,
                'mensaje_respuesta_sunat' => $response['message'] ?? 'Anulado correctamente.',
            ]);
        } else {
            $invoice->update([
                'estado'     => \App\Enums\InvoiceStatusEnum::ERROR,
                'last_error' => json_encode($response),
            ]);
        }

        return $invoice->fresh();
    }

    // ── Storage XML ────────────────────────────────────────────────────

    /**
     * Si la respuesta de consultar trae XML, guardarlo en storage privado
     * y actualizar xml_path en la factura.
     *
     * FeasaPeru puede devolverlo como:
     *  - response.data.xml_base64
     *  - response.data.xml_content
     *  - response.data.nombre_archivo_xml (solo nombre, sin contenido)
     */
    private function handleXmlFromResponse(Invoice $invoice, array $response): void
    {
        $data = $response['data'] ?? [];

        $xmlContent = $data['xml_base64'] ?? $data['xml_content'] ?? null;
        $filename   = $data['nombre_archivo_xml'] ?? null;

        if ($xmlContent && $filename) {
            $xmlPath = $this->feasyService->saveXml(
                companyId: $invoice->company_id,
                filename:  $filename,
                content:   $xmlContent,
            );

            // Actualizar xml_path directamente (sin pasar por repositorio completo)
            $invoice->update(['xml_path' => $xmlPath]);
        }
    }
}
