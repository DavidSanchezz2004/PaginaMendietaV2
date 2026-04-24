<?php

namespace App\Services\Facturador;

use App\Models\Purchase;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use App\Services\Facturador\AccountingAssignmentService;
use App\Services\Facturador\PurchaseValidationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseRepositoryInterface $purchaseRepository,
        private readonly ProviderService $providerService,
        private readonly PurchaseValidationService $validationService,
        private readonly AccountingAssignmentService $assignmentService,
    ) {}

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->purchaseRepository->paginate($perPage, $filters);
    }

    public function create(array $data): Purchase
    {
        // Sincronizar/crear proveedor en catálogo
        $tipoDoc   = $data['tipo_doc_proveedor'] ?? '6';
        $numeroDoc = $data['numero_doc_proveedor'] ?? '';
        $razon     = $data['razon_social_proveedor'] ?? '';

        if ($numeroDoc && $razon) {
            $provider = $this->providerService->findOrCreate($tipoDoc, $numeroDoc, $razon);
            $data['provider_id'] = $provider->id;
        }

        $data['user_id'] = auth()->id();

        return $this->purchaseRepository->create($data);
    }

    public function find(int $id): Purchase
    {
        return $this->purchaseRepository->findForActiveCompany($id);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        return $this->purchaseRepository->update($purchase, $data);
    }

    public function delete(Purchase $purchase): void
    {
        $this->purchaseRepository->delete($purchase);
    }

    /**
     * Guarda campos contables y recalcula accounting_status.
     */
    public function saveAccounting(Purchase $purchase, array $data): Purchase
    {
        $cuotas = $purchase->lista_cuotas ?? [];
        if (in_array($data['forma_pago'] ?? '', ['2', '02'], true)) {
            $cuotas = [];
            if (! empty($data['cuota_1_fecha']) && ! empty($data['cuota_1_monto'])) {
                $cuotas[] = [
                    'fecha_pago' => $data['cuota_1_fecha'],
                    'monto'      => (float) $data['cuota_1_monto'],
                    'moneda'     => $purchase->codigo_moneda ?? 'PEN',
                ];
            }
            if (! empty($data['cuota_2_fecha']) && ! empty($data['cuota_2_monto'])) {
                $cuotas[] = [
                    'fecha_pago' => $data['cuota_2_fecha'],
                    'monto'      => (float) $data['cuota_2_monto'],
                    'moneda'     => $purchase->codigo_moneda ?? 'PEN',
                ];
            }
        }

        $purchase->fill([
            'tipo_operacion'            => $data['tipo_operacion'],
            'tipo_compra'               => $data['tipo_compra'],
            'cuenta_contable'           => $data['cuenta_contable'],
            'codigo_producto_servicio'  => $data['codigo_producto_servicio'],
            'forma_pago'                => $data['forma_pago'] ?? null,
            'glosa'                     => $data['glosa'] ?? null,
            'centro_costo'              => $data['centro_costo'] ?? null,
            'tipo_gasto'                => $data['tipo_gasto'] ?? null,
            'sucursal'                  => $data['sucursal'] ?? null,
            'comprador'                 => $data['comprador'] ?? null,
            'es_anticipo'               => (bool) ($data['es_anticipo'] ?? false),
            'es_documento_contingencia' => (bool) ($data['es_documento_contingencia'] ?? false),
            'es_sujeto_detraccion'      => (bool) ($data['es_sujeto_detraccion'] ?? false),
            'es_sujeto_retencion'       => (bool) ($data['es_sujeto_retencion'] ?? false),
            'es_sujeto_percepcion'      => (bool) ($data['es_sujeto_percepcion'] ?? false),
            'lista_cuotas'              => ! empty($cuotas) ? $cuotas : $purchase->lista_cuotas,
        ]);

        $completeness = $purchase->accounting_completeness;
        $purchase->accounting_status = $completeness['status'];
        $purchase->save();

        return $purchase;
    }

    /**
     * Valida los datos extraídos del PDF y, si son correctos, asigna
     * automáticamente las cuentas contables según las reglas de la empresa.
     *
     * @param  array $data       Datos crudos del JSON de n8n / IA
     * @return array{data: array, validacion: array{ok: bool, errores: array, status: string}}
     */
    public function validarYAsignar(array $data): array
    {
        $validacion = $this->validationService->validate($data);

        $data['accounting_status']    = $validacion['status'];
        $data['errores_validacion']   = $validacion['ok'] ? null : $validacion['errores'];

        if ($validacion['ok'] && isset($data['company_id'])) {
            $data = $this->assignmentService->assignToArray($data, (int) $data['company_id']);
        }

        return ['data' => $data, 'validacion' => $validacion];
    }
}
