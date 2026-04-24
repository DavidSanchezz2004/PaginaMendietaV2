<?php

namespace App\Services\Facturador;

use App\Models\Invoice;
use App\Models\LetraCambio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Servicio para generación y gestión de letras de cambio.
 * 
 * Solo para VENTAS (invoices) en forma de pago = crédito.
 * Las letras se generan a partir de cuotas definidas por el usuario.
 */
class LetraService
{
    /**
     * Genera letras de cambio a partir de una factura con cuotas.
     * 
     * Solo genera si:
     * - forma_pago = "2" (Crédito)
     * - hay cuotas definidas (lista_cuotas)
     * 
     * @param Invoice $invoice Factura con cuotas
     * @return Collection<LetraCambio> Letras generadas
     * @throws RuntimeException si la factura no cumple condiciones
     */
    public function generateFromInvoice(Invoice $invoice): \Illuminate\Support\Collection
    {
        // Validar que sea crédito
        if ((string) $invoice->forma_pago !== '2') {
            return collect();
        }

        // Validar que haya cuotas
        $cuotas = is_array($invoice->lista_cuotas) 
            ? $invoice->lista_cuotas 
            : json_decode($invoice->lista_cuotas, true);

        if (empty($cuotas)) {
            return collect();
        }

        Log::channel('stack')->info('[LetraService] Generando letras para factura', [
            'invoice_id'    => $invoice->id,
            'numero_cuotas' => count($cuotas),
            'forma_pago'    => $invoice->forma_pago,
        ]);

        $letras = DB::transaction(function () use ($invoice, $cuotas) {
            $generadas = collect();
            $company = $invoice->company;

            foreach ($cuotas as $index => $cuota) {
                // Calcular valores para interpolación
                $letraNum = $index + 1;
                $totalCuotas = count($cuotas);
                
                // Crear letra para esta cuota
                $letra = LetraCambio::create([
                    'company_id'              => $company->id,
                    'invoice_id'              => $invoice->id,
                    'user_id'                 => auth()->id(),
                    
                    // Identificación
                    'numero_letra'            => $this->generateLetraNumber($company),
                    'referencia'              => $invoice->serie_numero,
                    
                    // Girador (proveedor/vendor — la empresa que cobra)
                    'tenedor_nombre'          => $company->razon_social ?? $company->name,
                    'tenedor_ruc'             => $company->ruc,
                    'tenedor_domicilio'       => $company->direccion_fiscal,
                    
                    // Aceptante (cliente — quien paga)
                    'aceptante_nombre'        => $invoice->client->nombre_razon_social,
                    'aceptante_ruc'           => $invoice->client->numero_documento,
                    'aceptante_domicilio'     => $invoice->client->direccion,
                    'aceptante_telefono'      => $invoice->client->telefono ?? null,
                    'aceptante_representante' => null,
                    'aceptante_doi'           => $invoice->client->numero_documento,
                    
                    // Giro
                    'lugar_giro'              => $company->distrito ?? 'LIMA',
                    'fecha_giro'              => $invoice->fecha_emision,
                    'fecha_vencimiento'       => $cuota['fecha_pago'] ?? now(),
                    
                    // Importe
                    'codigo_moneda'           => $invoice->codigo_moneda,
                    'monto'                   => round((float) ($cuota['monto'] ?? 0), 2),
                    'monto_letras'            => $this->monetaryToWords((float) ($cuota['monto'] ?? 0)),
                    
                    // Datos bancarios (si están disponibles)
                    'banco'                   => null,
                    'banco_oficina'           => null,
                    'banco_cuenta'            => null,
                    'banco_dc'                => null,
                    
                    // Contable
                    'cuenta_contable'         => '1212', // Letras por cobrar (facturas por cobrar)
                    'estado'                  => 'pendiente',
                    'monto_pagado'            => 0,
                    'observaciones'           => "Letra #{$letraNum} de {$totalCuotas} de factura {$invoice->serie_numero}",
                ]);

                $generadas->push($letra);

                Log::channel('stack')->debug('[LetraService] Letra creada', [
                    'letra_id'        => $letra->id,
                    'invoice_id'      => $invoice->id,
                    'numero'          => $letra->numero_letra,
                    'monto'           => $letra->monto,
                    'fecha_vencimiento' => $letra->fecha_vencimiento,
                ]);
            }

            return $generadas;
        });

        return $letras;
    }

    /**
     * Marcar una letra como pagada.
     * 
     * @param LetraCambio $letra Letra a marcar como pagada
     * @param float $monto Monto pagado (default: monto total)
     * @param string $medio Medio de pago (transferencia, efectivo, cheque, yape)
     * @param string|null $referencia Referencia de pago (comprobante, número de transferencia)
     */
    public function markAsPaid(
        LetraCambio $letra,
        float $monto = 0,
        string $medio = 'transferencia',
        ?string $referencia = null
    ): void
    {
        if ($monto <= 0) {
            $monto = $letra->monto;
        }

        $saldoAnterior = $letra->saldo;

        DB::transaction(function () use ($letra, $monto, $medio, $referencia, $saldoAnterior) {
            // Actualizar monto pagado
            $letra->update([
                'monto_pagado' => $letra->monto_pagado + $monto,
                'estado'       => $letra->saldo <= 0 ? 'cobrado' : 'pendiente',
            ]);

            // Registrar pago en tabla de pagos
            $letra->pagos()->create([
                'company_id'       => $letra->company_id,
                'user_id'          => auth()->id(),
                'fecha_pago'       => now()->toDateString(),
                'monto_pagado'     => $monto,
                'medio_pago'       => $medio,
                'referencia_pago'  => $referencia,
            ]);

            Log::channel('stack')->info('[LetraService] Letra marcada como pagada', [
                'letra_id'        => $letra->id,
                'monto_pagado'    => $monto,
                'saldo_anterior'  => $saldoAnterior,
                'saldo_nuevo'     => $letra->saldo,
                'estado_nuevo'    => $letra->estado,
            ]);
        });
    }

    /**
     * Generar número único de letra para la empresa.
     * Formato: "NNN-YY" donde NNN es secuencial y YY es año.
     * 
     * @param \App\Models\Company $company Empresa
     * @return string Número de letra
     */
    private function generateLetraNumber(\App\Models\Company $company): string
    {
        $año = now()->year % 100; // 26 para 2026

        // Contar letras del año actual
        $count = LetraCambio::where('company_id', $company->id)
            ->whereYear('fecha_giro', now()->year)
            ->count();

        $numero = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return "{$numero}-{$año}";
    }

    /**
     * Convertir monto a palabras (para el campo monto_letras).
     * 
     * @param float $numero Número a convertir
     * @return string Número en letras
     */
    private function monetaryToWords(float $numero): string
    {
        $unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        $especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
        $decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

        $entero = (int) $numero;
        $decimales = round(($numero - $entero) * 100);

        if ($entero === 0) {
            $texto = 'cero';
        } else {
            $miles = intval($entero / 1000);
            $resto = $entero % 1000;
            $cents = intval($resto / 100);
            $dezenas = intval(($resto % 100) / 10);
            $units = $resto % 10;

            $texto = '';

            if ($miles > 0) {
                if ($miles === 1) {
                    $texto = 'mil';
                } else {
                    $texto = $unidades[$miles] . ' mil';
                }
            }

            if ($cents > 0) {
                $texto .= ($texto ? ' ' : '') . $centenas[$cents];
            }

            if ($dezenas === 1 && $units < 10) {
                $texto .= ($texto ? ' ' : '') . $especiales[$units];
            } else {
                if ($dezenas > 0) {
                    $texto .= ($texto ? ' ' : '') . $decenas[$dezenas];
                }
                if ($units > 0) {
                    $texto .= ($texto ? ' ' : '') . $unidades[$units];
                }
            }
        }

        // Agregar decimales si los hay
        if ($decimales > 0) {
            $texto .= " con {$decimales}/100";
        }

        return ucfirst($texto);
    }

    /**
     * Actualizar estado de letras vencidas automáticamente.
     * Se ejecuta vía scheduler o manualmente.
     * 
     * @param \App\Models\Company $company Empresa (opcional)
     * @return int Número de letras actualizadas
     */
    public function updateVencidas(?Company $company = null): int
    {
        $query = LetraCambio::where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now()->toDateString());

        if ($company) {
            $query->where('company_id', $company->id);
        }

        $count = $query->update(['estado' => 'vencida']);

        if ($count > 0) {
            Log::channel('stack')->info('[LetraService] Letras actualizadas a vencidas', [
                'cantidad' => $count,
            ]);
        }

        return $count;
    }
}
