<?php

namespace App\Services\Facturador;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RetentionAdditionalInfoService
{
    public function build(
        string $currency,
        float $base,
        float $percentage,
        float $amount,
        float $netTotal,
        ?float $exchangeRate = null
    ): string {
        $currency = strtoupper($currency ?: 'PEN');
        $displayCurrency = $currency;
        $displayBase = $base;
        $displayAmount = $amount;

        if ($currency === 'USD' && $exchangeRate !== null && $exchangeRate > 0) {
            $displayCurrency = 'PEN';
            $displayBase = round($base * $exchangeRate, 2);
            $displayAmount = round($amount * $exchangeRate, 2);
        }

        return
            "Base imponible retencion: {$displayCurrency} " . number_format($displayBase, 2, '.', ',') . "\n" .
            "Porcentaje retencion: " . number_format($percentage, 2) . "%\n" .
            "Monto retencion: {$displayCurrency} " . number_format($displayAmount, 2, '.', ',') . "\n" .
            "Monto neto pendiente de pago: {$currency} " . number_format($netTotal, 2, '.', ',');
    }

    /**
     * Usa primero el tipo de cambio guardado en la factura. Si no existe y la
     * factura está en USD, consulta el tipo de cambio venta SUNAT del día.
     */
    public function saleRateForInvoice(Invoice $invoice): ?float
    {
        if (strtoupper((string) $invoice->codigo_moneda) !== 'USD') {
            return null;
        }

        $storedRate = (float) ($invoice->monto_tipo_cambio ?? 0);
        if ($storedRate > 0) {
            return $storedRate;
        }

        return $this->saleRateForDate($invoice->fecha_emision?->format('Y-m-d'));
    }

    /**
     * Igual que saleRateForInvoice(), pero para datos validados antes de crear
     * el modelo. Devuelve null si la moneda no requiere conversion.
     *
     * @param array<string, mixed> $data
     */
    public function saleRateForInvoiceData(array $data): ?float
    {
        if (strtoupper((string) ($data['codigo_moneda'] ?? 'PEN')) !== 'USD') {
            return null;
        }

        $storedRate = (float) ($data['monto_tipo_cambio'] ?? 0);
        if ($storedRate > 0) {
            return $storedRate;
        }

        return $this->saleRateForDate((string) ($data['fecha_emision'] ?? ''));
    }

    private function saleRateForDate(?string $date): ?float
    {
        if (empty($date)) {
            return null;
        }

        try {
            $response = Http::timeout(6)
                ->acceptJson()
                ->get("https://apis.aqpfact.pe/api/tipo-cambio-dia/{$date}");

            if (! $response->successful()) {
                Log::warning('[RetentionAdditionalInfoService] No se pudo obtener tipo de cambio', [
                    'date' => $date,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $rate = (float) ($response->json('data.venta') ?? $response->json('data.sale') ?? 0);

            return $rate > 0 ? $rate : null;
        } catch (\Throwable $e) {
            Log::warning('[RetentionAdditionalInfoService] Error consultando tipo de cambio', [
                'date' => $date,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
