<?php

namespace App\Services\Facturador;

use Carbon\Carbon;

/**
 * Valida los datos de un comprobante de compra extraídos por IA/n8n.
 * No persiste nada — solo retorna el resultado de la validación.
 */
class PurchaseValidationService
{
    private const IGV_RATE       = 0.18;
    private const IGV_TOLERANCE  = 0.06; // tolerancia de ±S/0.06 por redondeo

    /**
     * Valida los campos del comprobante.
     *
     * @param  array $data  Datos crudos (pueden venir de n8n o del formulario)
     * @return array{
     *     ok: bool,
     *     errores: array<string, string>,
     *     status: string,
     *     data: array
     * }
     */
    public function validate(array $data): array
    {
        $errores = [];

        // ── 1. RUC proveedor ───────────────────────────────────────────────
        $ruc = trim($data['numero_doc_proveedor'] ?? '');
        $tipoDoc = $data['tipo_doc_proveedor'] ?? '6';

        if ($tipoDoc === '6') {
            if (! preg_match('/^\d{11}$/', $ruc)) {
                $errores['numero_doc_proveedor'] = 'RUC inválido: debe tener exactamente 11 dígitos numéricos.';
            } else {
                $digit = $this->calcularDigitoVerificadorRuc($ruc);
                if ($digit === null) {
                    $errores['numero_doc_proveedor'] = 'RUC inválido: dígito verificador incorrecto.';
                }
            }
        }

        // ── 2. Serie del comprobante ───────────────────────────────────────
        $serie = strtoupper(trim($data['serie_documento'] ?? ''));
        if ($serie !== '') {
            $codigoTipoDoc = $data['codigo_tipo_documento'] ?? '01';
            $serieValida = match ($codigoTipoDoc) {
                '01' => (bool) preg_match('/^F\d{3}$/', $serie),                         // Factura: F001
                '03' => (bool) preg_match('/^B\d{3}$|^EB\d{2,3}$/', $serie),          // Boleta:  B001, EB01, EB001
                '07' => (bool) preg_match('/^F\d{3}$|^B\d{3}$|^EB\d{2,3}$/', $serie), // NC
                '08' => (bool) preg_match('/^F\d{3}$|^B\d{3}$|^EB\d{2,3}$/', $serie), // ND
                default => true, // DUA, otros: no validar serie
            };
            if (! $serieValida) {
                $errores['serie_documento'] = "Serie '{$serie}' inválida para el tipo de comprobante '{$codigoTipoDoc}'.";
            }
        }

        // ── 3. Número correlativo ─────────────────────────────────────────
        $numero = trim($data['numero_documento'] ?? '');
        if ($numero === '') {
            $errores['numero_documento'] = 'Número de documento requerido.';
        } elseif (! preg_match('/^\d+$/', $numero)) {
            $errores['numero_documento'] = 'Número de documento debe ser numérico.';
        }

        // ── 4. Fecha no futura ────────────────────────────────────────────
        $fechaRaw = $data['fecha_emision'] ?? null;
        if ($fechaRaw) {
            try {
                $fecha = Carbon::parse($fechaRaw);
                if ($fecha->isFuture()) {
                    $errores['fecha_emision'] = 'La fecha de emisión no puede ser futura.';
                }
            } catch (\Exception) {
                $errores['fecha_emision'] = 'Fecha de emisión inválida.';
            }
        } else {
            $errores['fecha_emision'] = 'Fecha de emisión requerida.';
        }

        // ── 5. IGV = base × 18% ───────────────────────────────────────────
        $base = (float) ($data['base_imponible_gravadas'] ?? 0);
        $igv  = (float) ($data['igv_gravadas'] ?? 0);

        if ($base > 0 && (int) ($data['porcentaje_igv'] ?? 18) === 18) {
            $igvEsperado = round($base * self::IGV_RATE, 2);
            if (abs($igv - $igvEsperado) > self::IGV_TOLERANCE) {
                $errores['igv_gravadas'] = sprintf(
                    'IGV incorrecto: se recibió %.2f, se esperaba %.2f (18%% de %.2f). Diferencia: %.2f.',
                    $igv, $igvEsperado, $base, abs($igv - $igvEsperado)
                );
            }
        }

        // ── 6. Total = base + IGV + otros montos ──────────────────────────
        $total       = (float) ($data['monto_total'] ?? 0);
        $exonerado   = (float) ($data['monto_exonerado'] ?? 0);
        $noGravado   = (float) ($data['monto_no_gravado'] ?? 0);
        $motoroIsc   = (float) ($data['monto_isc'] ?? 0);
        $icbper      = (float) ($data['monto_icbper'] ?? 0);
        $otrosTrib   = (float) ($data['otros_tributos'] ?? 0);
        $descuento   = (float) ($data['monto_descuento'] ?? 0);

        $totalEsperado = round($base + $igv + $exonerado + $noGravado + $motoroIsc + $icbper + $otrosTrib - $descuento, 2);

        if (abs($total - $totalEsperado) > self::IGV_TOLERANCE) {
            $errores['monto_total'] = sprintf(
                'Total incorrecto: se recibió %.2f, se esperaba %.2f. Diferencia: %.2f.',
                $total, $totalEsperado, abs($total - $totalEsperado)
            );
        }

        $ok     = empty($errores);
        $status = $ok ? 'pendiente' : 'observado';

        return [
            'ok'     => $ok,
            'errores'=> $errores,
            'status' => $status,
            'data'   => $data,
        ];
    }

    /**
     * Calcula el dígito verificador del RUC peruano.
     * Retorna null si el algoritmo falla (RUC inválido).
     */
    private function calcularDigitoVerificadorRuc(string $ruc): ?int
    {
        if (strlen($ruc) !== 11) {
            return null;
        }

        $factores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;
        for ($i = 0; $i < 10; $i++) {
            $suma += (int) $ruc[$i] * $factores[$i];
        }

        $resto     = $suma % 11;
        $diferencia = 11 - $resto;

        $digitoEsperado = match (true) {
            $diferencia >= 10 => $diferencia - 10,
            default           => $diferencia,
        };

        return (int) $ruc[10] === $digitoEsperado ? $digitoEsperado : null;
    }
}
