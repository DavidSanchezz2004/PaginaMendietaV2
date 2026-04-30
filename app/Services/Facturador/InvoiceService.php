<?php

namespace App\Services\Facturador;

use App\Models\Invoice;
use App\Models\InvoiceSendLog;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
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
        private readonly RetentionAdditionalInfoService $retentionAdditionalInfoService,
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

        $this->ensureMonthlyDocumentQuotaIsAvailable($invoice);

        // Cargar items si no están cargados
        if (! $invoice->relationLoaded('items')) {
            $invoice->load(['items', 'client', 'company']);
        }

        $this->refreshRetentionAdditionalInfo($invoice);

        // FeasyService valida y envía (lanza RuntimeException si falla validación)
        // sendComprobante elige automáticamente el endpoint: enviar_factura (01) o enviar_boleta (03)
        $response = $this->feasyService->sendComprobante($invoice);
        $this->recordSendLog($invoice, 'emit', $response);

        // Guardar XML si Feasy lo devuelve en la respuesta de emisión
        $this->handleXmlFromResponse($invoice, $response);

        // Persistir resultado (éxito o error)
        return $this->repository->persistEmitResponse($invoice, $response);
    }

    private function ensureMonthlyDocumentQuotaIsAvailable(Invoice $invoice): void
    {
        $limit = (int) config('facturador.monthly_document_limit', 500);

        if ($limit <= 0) {
            return;
        }

        $period = Carbon::parse($invoice->fecha_emision ?? now())->startOfMonth();
        $used = Invoice::query()
            ->whereBetween('fecha_emision', [$period->toDateString(), $period->copy()->endOfMonth()->toDateString()])
            ->whereIn('estado', ['sent', 'consulted'])
            ->count();

        if ($used >= $limit) {
            throw new RuntimeException(
                "No se puede emitir {$invoice->serie_numero}: el plan mensual de {$limit} comprobantes ya fue consumido para " .
                $period->locale('es')->translatedFormat('F Y') . '.'
            );
        }
    }

    private function refreshRetentionAdditionalInfo(Invoice $invoice): void
    {
        if (! $invoice->retention_enabled && ! $invoice->has_retention) {
            return;
        }

        $base = (float) ($invoice->retention_base ?? $invoice->monto_total ?? 0);
        $percentage = (float) ($invoice->retention_percentage ?? 3);
        $amount = (float) ($invoice->retention_amount ?? 0);

        if ($amount <= 0 && $base > 0 && $percentage > 0) {
            $amount = round($base * $percentage / 100, 2);
        }

        if ($base <= 0 || $percentage <= 0 || $amount <= 0) {
            return;
        }

        $netTotal = (float) ($invoice->net_total ?? round($base - $amount, 2));
        $exchangeRate = $this->retentionAdditionalInfoService->saleRateForInvoice($invoice);
        $additionalInfo = $invoice->informacion_adicional ?? [];
        $additionalInfo['informacion_adicional_3'] = $this->retentionAdditionalInfoService->build(
            (string) ($invoice->codigo_moneda ?? 'PEN'),
            $base,
            $percentage,
            $amount,
            $netTotal,
            $exchangeRate
        );

        $updates = ['informacion_adicional' => $additionalInfo];
        if (strtoupper((string) $invoice->codigo_moneda) === 'USD' && empty($invoice->monto_tipo_cambio) && $exchangeRate !== null) {
            $updates['monto_tipo_cambio'] = $exchangeRate;
        }

        $invoice->forceFill($updates)->save();
        $invoice->refresh()->loadMissing(['items', 'client', 'company']);
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
        $this->recordSendLog($invoice, 'consult', $response);

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
        $this->recordSendLog($invoice, 'void', $response);

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

    private function recordSendLog(Invoice $invoice, string $action, array $response): void
    {
        $attemptNumber = InvoiceSendLog::where('invoice_id', $invoice->id)
            ->where('action', $action)
            ->max('attempt_number');

        $data = $response['data'] ?? [];

        InvoiceSendLog::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'attempt_number' => ((int) $attemptNumber) + 1,
            'endpoint' => $response['_endpoint'] ?? null,
            'codigo_tipo_documento' => $invoice->codigo_tipo_documento,
            'serie_documento' => $invoice->serie_documento,
            'numero_documento' => $invoice->numero_documento,
            'codigo_interno' => $invoice->codigo_interno,
            'monto_total' => $invoice->monto_total,
            'success' => (bool) ($response['success'] ?? false),
            'http_status' => (int) ($response['http_status'] ?? 0),
            'codigo_respuesta' => $data['codigo_respuesta'] ?? null,
            'mensaje_respuesta' => $data['mensaje_respuesta'] ?? ($response['message'] ?? null),
            'request_payload' => $response['_request_payload'] ?? null,
            'response_payload' => $response,
        ]);
    }
}
