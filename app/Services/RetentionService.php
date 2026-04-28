<?php

namespace App\Services;

use App\Models\Invoice;
use App\Services\Facturador\RetentionAdditionalInfoService;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestión de retenciones en facturas.
 * Maneja la aplicación de 3% retención para agentes retenedores.
 * Persiste la misma base, monto y neto que se envían a SUNAT.
 */
class RetentionService
{
    public function __construct(
        private readonly RetentionAdditionalInfoService $retentionAdditionalInfoService,
    ) {
    }

    /**
     * Aplica retención a una factura si aplica.
     * Calcula y persiste:
     * - has_retention (boolean)
     * - retention_amount (monto retenido)
     * - retention_percentage (siempre 3%)
     * - retention_base / total_before_retention (total del comprobante)
     * - net_total / total_after_retention (total menos retención)
     */
    public function apply(Invoice $invoice, float $total): void
    {
        // Validar que cliente existe y cargar is_retainer_agent
        if (!$invoice->client) {
            $invoice->load('client');
        }

        $should_apply = $invoice->client
            && $invoice->client->is_retainer_agent
            && ! $invoice->indicador_detraccion
            && $total > 700;

        if (!$should_apply) {
            // No hay retención: totales iguales
            $invoice->fill([
                'retention_enabled' => false,
                'has_retention' => false,
                'retention_base' => null,
                'retention_amount' => 0,
                'retention_percentage' => 3.00,
                'net_total' => null,
                'retention_info' => null,
                'total_before_retention' => $total,
                'total_after_retention' => $total,
            ]);

            Log::debug("Retención no aplica para factura {$invoice->serie_documento}", [
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'is_retainer' => $invoice->client->is_retainer_agent ?? false,
            ]);

            return;
        }

        // Aplicar 3% retención sobre el total del comprobante.
        $percentage = 3.0;
        $amount = round($total * $percentage / 100, 2);
        $netTotal = round($total - $amount, 2);
        $retentionInfo = [
            'codigo_retencion' => '62',
            'monto_base_imponible_retencion' => round($total, 2),
            'porcentaje_retencion' => $percentage,
            'monto_retencion' => $amount,
        ];
        $currency = $invoice->codigo_moneda ?: 'PEN';
        $exchangeRate = $this->retentionAdditionalInfoService->saleRateForInvoice($invoice);
        if (strtoupper((string) $currency) === 'USD' && empty($invoice->monto_tipo_cambio) && $exchangeRate !== null) {
            $invoice->monto_tipo_cambio = $exchangeRate;
        }
        $additionalInfo = $invoice->informacion_adicional ?? [];
        $additionalInfo['informacion_adicional_3'] = $this->retentionAdditionalInfoService->build(
            $currency,
            $total,
            $percentage,
            $amount,
            $netTotal,
            $exchangeRate
        );

        $invoice->fill([
            'retention_enabled' => true,
            'has_retention' => true,
            'retention_base' => round($total, 2),
            'retention_amount' => $amount,
            'retention_percentage' => $percentage,
            'net_total' => $netTotal,
            'retention_info' => $retentionInfo,
            'informacion_adicional' => $additionalInfo,
            'total_before_retention' => round($total, 2),
            'total_after_retention' => $netTotal,
        ]);

        Log::info("Retención aplicada a factura {$invoice->serie_documento}", [
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'retention_base' => round($total, 2),
            'retention_amount' => $amount,
            'retention_percentage' => $percentage,
            'total_after_retention' => $invoice->total_after_retention,
        ]);
    }

    /**
     * Obtiene el porcentaje de retención para un cliente.
     * Retorna 3% si es agente retenedor, 0% en caso contrario.
     */
    public function get_retention_percentage_for_client(object $client): float
    {
        return $client->is_retainer_agent ? 3.0 : 0.0;
    }
}
