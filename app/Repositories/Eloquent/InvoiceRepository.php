<?php

namespace App\Repositories\Eloquent;

use App\Enums\FeasyStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Implementación Eloquent del repositorio de Facturas.
 * Scoping estricto por empresa activa en TODAS las operaciones.
 * Persistencia de respuesta Feasy centralizada aquí.
 */
class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::forActiveCompany()
            ->with(['client', 'letras', 'payments'])
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['serie'])) {
            $query->where('serie_documento', $filters['serie']);
        }

        if (! empty($filters['search'])) {
            $query->whereHas('client', fn ($q) =>
                $q->where('nombre_razon_social', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('numero_documento', 'like', '%' . $filters['search'] . '%')
            );
        }

        return $query->paginate($perPage);
    }

    public function findForActiveCompany(int $id): Invoice
    {
        $invoice = Invoice::forActiveCompany()->find($id);

        if (! $invoice) {
            throw new ModelNotFoundException("Factura #{$id} no encontrada en la empresa activa.");
        }

        return $invoice;
    }

    public function findWithItems(int $id): Invoice
    {
        $invoice = Invoice::forActiveCompany()
            ->with(['client', 'items', 'user'])
            ->find($id);

        if (! $invoice) {
            throw new ModelNotFoundException("Factura #{$id} no encontrada en la empresa activa.");
        }

        return $invoice;
    }

    public function createWithItems(array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($data, $items): Invoice {
            // company_id se fuerza desde sesión (no del input)
            $data['company_id'] = session('company_id');
            $data['estado']      = InvoiceStatusEnum::DRAFT->value;
            $data['estado_feasy'] = FeasyStatusEnum::PENDING->value;

            $invoice = Invoice::create($data);

            foreach ($items as $index => $item) {
                InvoiceItem::create([
                    ...$item,
                    'invoice_id'  => $invoice->id,
                    'company_id'  => $invoice->company_id, // redundante Anti-IDOR
                    'correlativo' => $item['correlativo'] ?? ($index + 1),
                ]);
            }

            return $invoice->load(['items', 'client']);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        // Anti-IDOR: nunca cambiar company_id
        unset($data['company_id']);

        $invoice->update($data);
        return $invoice->fresh(['items', 'client']);
    }

    /**
     * Devuelve la serie por defecto y el siguiente número disponible
     * para cada tipo de comprobante, según lo que ya existe en la empresa activa.
     *
     * Estructura: ['01' => ['serie'=>'F001','numero'=>3], '03'=>[...], ...]
     */
    public function nextDocumentSuggestions(int $companyId): array
    {
        $defaults = [
            '01' => 'F001',
            '03' => 'B001',
            '07' => 'FC01',
            '08' => 'FD01',
            '09' => 'T001',
        ];

        $suggestions = [];

        foreach ($defaults as $tipo => $defaultSerie) {
            // Última serie usada para este tipo en esta empresa
            $lastSerie = Invoice::withTrashed()
                ->where('company_id', $companyId)
                ->where('codigo_tipo_documento', $tipo)
                ->orderBy('id', 'desc')
                ->value('serie_documento');

            $serie = $lastSerie ?? $defaultSerie;

            // Siguiente número = max actual + 1 para esa serie
            $lastNum = Invoice::withTrashed()
                ->where('company_id', $companyId)
                ->where('codigo_tipo_documento', $tipo)
                ->where('serie_documento', $serie)
                ->max(DB::raw('CAST(numero_documento AS UNSIGNED)'));

            $suggestions[$tipo] = [
                'serie'  => $serie,
                'numero' => ($lastNum ?? 0) + 1,
            ];
        }

        return $suggestions;
    }

    /**
     * Persiste el resultado de FeasyService::sendFactura().
     * Mapea la respuesta normalizada a los campos de trazabilidad.
     */
    public function persistEmitResponse(Invoice $invoice, array $feasyResponse): Invoice
    {
        $isSuccess   = $feasyResponse['success'] ?? false;
        $data        = $feasyResponse['data'] ?? [];
        $codigoSunat = $data['codigo_respuesta'] ?? null;

        // GRE (tipo 09) devuelve "A01" (ticket asíncrono): recibido por SUNAT, pendiente resolución.
        $isGreTicket   = $invoice->codigo_tipo_documento === '09'
                      && $isSuccess
                      && $codigoSunat === 'A01';
        $isAcceptedByS = $isSuccess && $codigoSunat === '0';

        if ($isGreTicket) {
            $estadoInvoice = InvoiceStatusEnum::SENT->value;
            $estadoFeasy   = FeasyStatusEnum::TICKET->value;
        } elseif ($isAcceptedByS) {
            $estadoInvoice = InvoiceStatusEnum::SENT->value;
            $estadoFeasy   = FeasyStatusEnum::SENT->value;
        } else {
            $estadoInvoice = InvoiceStatusEnum::ERROR->value;
            $estadoFeasy   = $isSuccess ? FeasyStatusEnum::REJECTED->value : FeasyStatusEnum::ERROR->value;
        }

        $updateData = [
            'estado'                  => $estadoInvoice,
            'estado_feasy'            => $estadoFeasy,
            'codigo_respuesta_sunat'  => $codigoSunat,
            'mensaje_respuesta_sunat' => $data['mensaje_respuesta'] ?? ($feasyResponse['message'] ?? null),
            'nombre_archivo_xml'      => $data['nombre_archivo_xml'] ?? null,
            'sent_at'                 => ($isAcceptedByS || $isGreTicket) ? now() : null,
            'last_error'              => (! $isSuccess && ! $isGreTicket) ? json_encode($feasyResponse) : null,
        ];

        if (! empty($data['codigo_hash'])) {
            $updateData['hash_cpe'] = $data['codigo_hash'];
        }
        if (! empty($data['valor_qr'])) {
            $updateData['valor_qr'] = $data['valor_qr'];
        }
        if (! empty($data['ruta_xml'])) {
            $updateData['ruta_xml'] = $data['ruta_xml'];
        }
        if (! empty($data['ruta_cdr'])) {
            $updateData['ruta_cdr'] = $data['ruta_cdr'];
        }
        if (! empty($data['ruta_reporte'])) {
            $updateData['ruta_reporte'] = $data['ruta_reporte'];
        }

        $invoice->update($updateData);

        return $invoice->fresh();
    }

    /**
     * Persiste el resultado de FeasyService::consultar().
     */
    public function persistConsultResponse(Invoice $invoice, array $feasyResponse): Invoice
    {
        $isSuccess   = $feasyResponse['success'] ?? false;
        $data        = $feasyResponse['data'] ?? [];

        $updateData = [
            'consulted_at'            => now(),
            'codigo_respuesta_sunat'  => $data['codigo_respuesta'] ?? $invoice->codigo_respuesta_sunat,
            'mensaje_respuesta_sunat' => $data['mensaje_respuesta'] ?? $invoice->mensaje_respuesta_sunat,
            'last_error'              => ! $isSuccess ? json_encode($feasyResponse) : null,
        ];

        if ($isSuccess) {
            $updateData['estado']       = InvoiceStatusEnum::CONSULTED->value;
            $updateData['estado_feasy'] = FeasyStatusEnum::CONSULTED->value;
        }

        // Campos de trazabilidad que devuelve Feasy al consultar
        if (! empty($data['nombre_archivo_xml'])) {
            $updateData['nombre_archivo_xml'] = $data['nombre_archivo_xml'];
        }
        if (! empty($data['ruta_xml'])) {
            $updateData['ruta_xml'] = $data['ruta_xml'];
        }
        if (! empty($data['ruta_cdr'])) {
            $updateData['ruta_cdr'] = $data['ruta_cdr'];
        }
        if (! empty($data['ruta_reporte'])) {
            $updateData['ruta_reporte'] = $data['ruta_reporte'];
        }
        if (! empty($data['codigo_hash'])) {
            $updateData['hash_cpe'] = $data['codigo_hash'];
        }
        if (! empty($data['valor_qr'])) {
            $updateData['valor_qr'] = $data['valor_qr'];
        }
        if (! empty($data['mensaje_observacion'])) {
            $updateData['mensaje_observacion'] = $data['mensaje_observacion'];
        }

        $invoice->update($updateData);
        return $invoice->fresh();
    }
}
