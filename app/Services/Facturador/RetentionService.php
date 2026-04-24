<?php

namespace App\Services\Facturador;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

/**
 * Servicio de gestión de retenciones en ventas (facturación).
 * 
 * Maneja:
 * - Cálculo de retenciones
 * - Validación de reglas SUNAT
 * - Aplicación a facturas
 * - Generación de reportes
 */
class RetentionService
{
    /**
     * Calcula el monto de retención basado en base y porcentaje.
     * 
     * @param float $base Base imponible para calcular retención
     * @param float $percentage Porcentaje de retención (ej: 3.00 para 3%)
     * @return float Monto retenido
     */
    public function calculate(float $base, float $percentage): float
    {
        if ($base <= 0 || $percentage <= 0) {
            return 0;
        }

        $retention = ($base * $percentage) / 100;
        
        // Redondear a 2 decimales
        return round($retention, 2);
    }

    /**
     * Aplica una retención a una factura (Invoice).
     * 
     * @param Invoice $invoice
     * @param float $base Base imponible
     * @param float $percentage Porcentaje
     * @param array $metadata Información adicional (motivo, referencia, etc.)
     * @return Invoice Factura con retención aplicada
     * 
     * @throws \Exception Si los datos no son válidos
     */
    public function applyToInvoice(
        Invoice $invoice,
        float $base,
        float $percentage,
        array $metadata = []
    ): Invoice {
        // Validación
        if (!$this->validateRetentionData($base, $percentage)) {
            throw new \Exception(
                'Datos de retención inválidos: base y porcentaje deben ser positivos.'
            );
        }

        // Calcular monto retenido
        $retentionAmount = $this->calculate($base, $percentage);

        // Aplicar a la factura
        $invoice->retention_enabled = true;
        $invoice->retention_base = $base;
        $invoice->retention_percentage = $percentage;
        $invoice->retention_amount = $retentionAmount;
        $invoice->net_total = $invoice->monto_total - $retentionAmount;
        
        // Guardar metadatos si existen
        if (!empty($metadata)) {
            $invoice->retention_info = array_merge(
                $invoice->retention_info ?? [],
                [
                    'metadata' => $metadata,
                    'applied_at' => now()->toIso8601String(),
                ]
            );
        }

        return $invoice;
    }

    /**
     * Valida datos de retención.
     */
    public function validateRetentionData(float $base, float $percentage): bool
    {
        return $base > 0 && $percentage > 0 && $percentage <= 100;
    }

    /**
     * Detecta si una factura debe tener retención por cliente.
     * (Lógica extensible basada en reglas de negocio)
     * 
     * @param Invoice $invoice
     * @return bool
     */
    public function shouldRetainByClient(Invoice $invoice): bool
    {
        // Ejemplo: verificar si el cliente tiene retención habilitada
        if ($invoice->client && method_exists($invoice->client, 'shouldRetain')) {
            return $invoice->client->shouldRetain();
        }

        // Por defecto, no retener
        return false;
    }

    /**
     * Detecta porcentaje de retención por cliente y tipo documento.
     * 
     * @param Invoice $invoice
     * @return float|null Porcentaje encontrado o null
     */
    public function getRetentionPercentageForClient(Invoice $invoice): ?float
    {
        // Ejemplo: buscar en tabla de configuración de cliente
        if ($invoice->client && isset($invoice->client->retention_percentage)) {
            return (float) $invoice->client->retention_percentage;
        }

        // Por defecto, 3% en Perú para agentes de retención
        return null;
    }

    /**
     * Genera reporte de retenciones aplicadas en un período.
     * 
     * @param int $companyId
     * @param string $fromDate YYYY-MM-DD
     * @param string $toDate YYYY-MM-DD
     * @return array
     */
    public function generateRetentionReport(
        int $companyId,
        string $fromDate,
        string $toDate
    ): array {
        $invoices = Invoice::where('company_id', $companyId)
            ->where('retention_enabled', true)
            ->whereBetween('fecha_emision', [$fromDate, $toDate])
            ->get();

        $totals = [
            'total_invoices' => 0,
            'total_base' => 0,
            'total_retention' => 0,
            'total_net' => 0,
            'invoices' => [],
        ];

        foreach ($invoices as $invoice) {
            $totals['total_invoices']++;
            $totals['total_base'] += $invoice->retention_base ?? 0;
            $totals['total_retention'] += $invoice->retention_amount ?? 0;
            $totals['total_net'] += $invoice->net_total ?? 0;

            $totals['invoices'][] = [
                'id' => $invoice->id,
                'numero' => $invoice->getSerieNumeroAttribute(),
                'cliente' => $invoice->client->nombre_cliente ?? 'N/A',
                'base' => $invoice->retention_base,
                'porcentaje' => $invoice->retention_percentage,
                'monto_retenido' => $invoice->retention_amount,
                'fecha' => $invoice->fecha_emision->format('Y-m-d'),
            ];
        }

        return $totals;
    }

    /**
     * Revierte una retención en una factura.
     * 
     * @param Invoice $invoice
     * @return Invoice
     */
    public function reverseRetention(Invoice $invoice): Invoice
    {
        $invoice->retention_enabled = false;
        $invoice->retention_base = null;
        $invoice->retention_percentage = null;
        $invoice->retention_amount = null;
        $invoice->net_total = $invoice->monto_total;
        $invoice->retention_info = null;

        return $invoice;
    }
}
