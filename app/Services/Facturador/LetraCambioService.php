<?php

namespace App\Services\Facturador;

use App\Models\LetraCambio;
use App\Models\PagoLetra;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LetraCambioService
{
    /**
     * Genera N letras de cambio a partir de una compra.
     *
     * @param  Purchase $purchase   Compra origen
     * @param  array    $cuotas     [['dias' => 30, 'porcentaje' => 50], ...]
     *                              Si porcentajes no suman 100, se normaliza.
     * @param  array    $opciones   ['lugar_giro', 'banco', 'banco_cuenta', ...]
     * @return Collection<LetraCambio>
     */
    public function canjear(Purchase $purchase, array $cuotas, array $opciones = []): Collection
    {
        // Calcular monto neto (deducir detracción y retención)
        $montoNeto = $purchase->monto_total
            - ($purchase->monto_detraccion ?? 0)
            - ($purchase->monto_retencion ?? 0);
        $montoNeto = max(0.0, round($montoNeto, 2));

        // Normalizar porcentajes si no suman exactamente 100
        $totalPct = array_sum(array_column($cuotas, 'porcentaje'));
        if ($totalPct <= 0) {
            // Si no se especificaron porcentajes, distribuir equitativamente
            $pctCada = round(100 / count($cuotas), 4);
            $cuotas  = array_map(fn ($c) => array_merge($c, ['porcentaje' => $pctCada]), $cuotas);
        }

        $companyId = $purchase->company_id;
        $anioCorto = now()->format('y');
        $letrasCreadas = collect();

        DB::transaction(function () use ($purchase, $cuotas, $opciones, $montoNeto, $companyId, $anioCorto, &$letrasCreadas) {
            $secuenciaBase = $this->nextSequence($companyId);
            $fechaGiro     = $purchase->fecha_emision ?? now()->toDateString();
            $tenedorNombre = $purchase->company?->razon_social ?? $purchase->company?->name ?? '';
            $tenedorRuc    = $purchase->company?->ruc ?? '';

            foreach (array_values($cuotas) as $index => $cuota) {
                $dias       = (int) ($cuota['dias'] ?? 30);
                $porcentaje = (float) ($cuota['porcentaje'] ?? (100 / count($cuotas)));
                $monto      = round($montoNeto * $porcentaje / 100, 2);

                // Ajuste de redondeo en la última cuota
                if ($index === count($cuotas) - 1) {
                    $sumaPrevias = $letrasCreadas->sum('monto');
                    $monto       = round($montoNeto - $sumaPrevias, 2);
                }

                $numero = str_pad($secuenciaBase + $index, 3, '0', STR_PAD_LEFT) . '-' . $anioCorto;

                $letra = LetraCambio::create([
                    'company_id'              => $companyId,
                    'purchase_id'             => $purchase->id,
                    'user_id'                 => Auth::id(),
                    'numero_letra'            => $numero,
                    'referencia'              => $purchase->serie_numero,
                    'tenedor_nombre'          => $tenedorNombre,
                    'tenedor_ruc'             => $tenedorRuc,
                    'aceptante_nombre'        => $purchase->razon_social_proveedor ?? '',
                    'aceptante_ruc'           => $purchase->numero_doc_proveedor ?? '',
                    'lugar_giro'              => $opciones['lugar_giro'] ?? 'LIMA',
                    'fecha_giro'              => $fechaGiro,
                    'fecha_vencimiento'       => Carbon::parse($fechaGiro)->addDays($dias)->toDateString(),
                    'codigo_moneda'           => $purchase->codigo_moneda ?? 'PEN',
                    'monto'                   => $monto,
                    'monto_letras'            => $this->numeroALetras($monto, $purchase->codigo_moneda ?? 'PEN'),
                    'banco'                   => $opciones['banco'] ?? null,
                    'banco_oficina'           => $opciones['banco_oficina'] ?? null,
                    'banco_cuenta'            => $opciones['banco_cuenta'] ?? null,
                    'banco_dc'                => $opciones['banco_dc'] ?? null,
                    'cuenta_contable'         => $opciones['cuenta_contable'] ?? '4201',
                    'estado'                  => 'pendiente',
                    'monto_pagado'            => 0,
                ]);

                $letrasCreadas->push($letra);
            }
        });

        return $letrasCreadas;
    }

    /**
     * Registra un pago parcial o total de una letra.
     * Actualiza estado a 'cobrado' si monto_pagado >= monto.
     */
    public function registrarPago(LetraCambio $letra, array $data): PagoLetra
    {
        $pago = DB::transaction(function () use ($letra, $data) {
            $pago = PagoLetra::create([
                'letra_cambio_id' => $letra->id,
                'company_id'      => $letra->company_id,
                'user_id'         => Auth::id(),
                'fecha_pago'      => $data['fecha_pago'] ?? now()->toDateString(),
                'monto_pagado'    => (float) $data['monto_pagado'],
                'medio_pago'      => $data['medio_pago'] ?? 'transferencia',
                'referencia_pago' => $data['referencia_pago'] ?? null,
                'observaciones'   => $data['observaciones'] ?? null,
            ]);

            $nuevoPagado = round($letra->monto_pagado + $pago->monto_pagado, 2);
            $nuevoEstado = $nuevoPagado >= $letra->monto ? 'cobrado' : 'pendiente';

            $letra->update([
                'monto_pagado' => $nuevoPagado,
                'estado'       => $nuevoEstado,
            ]);

            return $pago;
        });

        return $pago;
    }

    /**
     * Calcula el siguiente número de secuencia para letras de la empresa.
     */
    private function nextSequence(int $companyId): int
    {
        $anioCorto = now()->format('y');
        $ultimo = LetraCambio::where('company_id', $companyId)
            ->whereYear('created_at', now()->year)
            ->orderByDesc('id')
            ->value('numero_letra');

        if ($ultimo && preg_match('/^(\d+)-' . $anioCorto . '$/', $ultimo, $m)) {
            return (int) $m[1] + 1;
        }

        return 1;
    }

    /**
     * Convierte número a letras en español (simplificado para montos típicos).
     */
    public function numeroALetras(float $monto, string $moneda = 'PEN'): string
    {
        $partes     = explode('.', number_format($monto, 2, '.', ''));
        $entero     = (int) $partes[0];
        $centavos   = $partes[1] ?? '00';
        $monedaLabel = $moneda === 'USD' ? 'Dólares Americanos' : 'Soles';

        $enteroLetras = $this->enterosALetras($entero);

        return ucfirst($enteroLetras) . " y {$centavos}/100 {$monedaLabel}";
    }

    private function enterosALetras(int $numero): string
    {
        if ($numero === 0) return 'cero';

        $unidades  = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
                      'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete',
                      'dieciocho', 'diecinueve', 'veinte'];
        $decenas   = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $centenas  = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

        if ($numero <= 20) return $unidades[$numero];
        if ($numero < 100) {
            $d = intdiv($numero, 10);
            $u = $numero % 10;
            return $u === 0 ? $decenas[$d] : $decenas[$d] . ' y ' . $unidades[$u];
        }
        if ($numero === 100) return 'cien';
        if ($numero < 1000) {
            $c  = intdiv($numero, 100);
            $resto = $numero % 100;
            return $centenas[$c] . ($resto > 0 ? ' ' . $this->enterosALetras($resto) : '');
        }
        if ($numero < 2000) {
            $resto = $numero % 1000;
            return 'mil' . ($resto > 0 ? ' ' . $this->enterosALetras($resto) : '');
        }
        if ($numero < 1_000_000) {
            $miles = intdiv($numero, 1000);
            $resto = $numero % 1000;
            return $this->enterosALetras($miles) . ' mil' . ($resto > 0 ? ' ' . $this->enterosALetras($resto) : '');
        }
        if ($numero < 1_000_000_000) {
            $millones = intdiv($numero, 1_000_000);
            $resto    = $numero % 1_000_000;
            $sufijo   = $millones === 1 ? 'un millón' : $this->enterosALetras($millones) . ' millones';
            return $sufijo . ($resto > 0 ? ' ' . $this->enterosALetras($resto) : '');
        }

        return (string) $numero;
    }
}
