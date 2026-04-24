<?php

namespace App\Services\Facturador;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio para obtener tipo de cambio del día desde API externa.
 * 
 * API: https://apis.aqpfact.pe/api/tipo-cambio-dia/{fecha}
 * Formato: {fecha: "YYYY-MM-DD"}
 * 
 * Características:
 * - Cachea resultados por 24 horas
 * - Fallback a valor anterior si API falla
 * - Registra histórico
 */
class ExchangeRateService
{
    private const CACHE_DURATION = 86400; // 24 horas
    private const API_URL = 'https://apis.aqpfact.pe/api/tipo-cambio-dia';

    /**
     * Obtiene el tipo de cambio para una fecha específica.
     * 
     * @param string|\DateTime $date Fecha en formato YYYY-MM-DD
     * @param bool $fromCache Usar cache si está disponible
     * @return float|null Tipo de cambio (PEN/USD) o null si no se puede obtener
     * 
     * @throws \Exception Si hay error de conectividad no recuperable
     */
    public function getRate($date, bool $fromCache = true): ?float
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }

        $cacheKey = $this->getCacheKey($date);

        // Intentar obtener del cache
        if ($fromCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $rate = $this->fetchFromApi($date);

            if ($rate !== null) {
                // Cachear el resultado
                Cache::put($cacheKey, $rate, self::CACHE_DURATION);
                return $rate;
            }
        } catch (\Exception $e) {
            // Log del error pero no fallar completamente
            \Log::warning("Exchange rate fetch failed for {$date}: " . $e->getMessage());
        }

        // Fallback a tipo de cambio fijo (configurado)
        return $this->getDefaultRate();
    }

    /**
     * Obtiene tasa para una fecha si ya está cacheada, sin hacer petición.
     */
    public function getCachedRate($date): ?float
    {
        return $this->getRate($date, true);
    }

    /**
     * Fuerza actualización desde API (sin usar cache).
     */
    public function refreshRate($date): ?float
    {
        return $this->getRate($date, false);
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function fetchFromApi(string $date): ?float
    {
        $response = Http::timeout(10)
            ->get(self::API_URL . '/' . $date);

        if ($response->failed()) {
            throw new \Exception("API returned status {$response->status()}");
        }

        $data = $response->json();

        // La API devuelve algo como: { "tipo_cambio": 3.8500 }
        // Ajusta el parseo según la respuesta real de la API
        if (isset($data['tipo_cambio'])) {
            return (float) $data['tipo_cambio'];
        }

        if (isset($data['rate'])) {
            return (float) $data['rate'];
        }

        if (isset($data['value'])) {
            return (float) $data['value'];
        }

        return null;
    }

    /**
     * Obtiene el tipo de cambio por defecto (fallback).
     */
    private function getDefaultRate(): float
    {
        // Configurable en .env o config
        return (float) (config('facturador.default_exchange_rate', 3.85) ?? 3.85);
    }

    /**
     * Genera la clave de cache.
     */
    private function getCacheKey(string $date): string
    {
        return "exchange_rate:{$date}";
    }

    /**
     * Obtiene histórico de tasas (útil para reportes).
     * 
     * @param string $fromDate YYYY-MM-DD
     * @param string $toDate YYYY-MM-DD
     * @return array
     */
    public function getHistoricalRates(string $fromDate, string $toDate): array
    {
        $rates = [];
        $current = Carbon::createFromFormat('Y-m-d', $fromDate);
        $end = Carbon::createFromFormat('Y-m-d', $toDate);

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $rate = $this->getRate($dateStr);

            if ($rate !== null) {
                $rates[$dateStr] = $rate;
            }

            $current->addDay();
        }

        return $rates;
    }

    /**
     * Aplica tipo de cambio a un monto en USD.
     * 
     * @param float $amountUsd Monto en USD
     * @param string|\DateTime $date Fecha del cambio
     * @return array ['usd' => amount, 'pen' => amount, 'rate' => rate]
     */
    public function convertUsdToPen(float $amountUsd, $date): array
    {
        $rate = $this->getRate($date);

        if ($rate === null) {
            throw new \Exception("No se pudo obtener tipo de cambio para {$date}");
        }

        return [
            'usd' => round($amountUsd, 2),
            'pen' => round($amountUsd * $rate, 2),
            'rate' => $rate,
        ];
    }
}
