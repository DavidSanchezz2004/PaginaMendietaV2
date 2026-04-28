<?php

namespace App\Services\Facturador;

use App\Enums\AccountingStatusEnum;
use App\Enums\FeasyStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SpotDetraccion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

class InvoiceExcelImportService
{
    public function __construct(
        private readonly RetentionAdditionalInfoService $retentionAdditionalInfoService,
    ) {
    }

    /**
     * @return array{created: int, invoices: array<int, string>}
     */
    public function import(UploadedFile $file, int $companyId, int $userId): array
    {
        $sheets = Excel::toArray(null, $file);

        $facturas = $this->rowsFromSheet($sheets[0] ?? [], 'facturas');
        $items = $this->rowsFromSheet($sheets[1] ?? [], 'items');

        if (empty($facturas)) {
            throw new RuntimeException('La hoja facturas no tiene registros.');
        }
        if (empty($items)) {
            throw new RuntimeException('La hoja items no tiene registros.');
        }

        $itemsByInvoice = [];
        foreach ($items as $rowNumber => $row) {
            $codigoInterno = trim((string) ($row['codigo_interno'] ?? ''));
            if ($codigoInterno === '') {
                throw new RuntimeException("Hoja items fila {$rowNumber}: codigo_interno es obligatorio.");
            }
            $itemsByInvoice[$codigoInterno][] = ['row' => $row, 'row_number' => $rowNumber];
        }

        $created = [];

        DB::transaction(function () use ($facturas, $itemsByInvoice, $companyId, $userId, &$created): void {
            foreach ($facturas as $rowNumber => $row) {
                $codigoInterno = trim((string) ($row['codigo_interno'] ?? ''));
                if ($codigoInterno === '') {
                    throw new RuntimeException("Hoja facturas fila {$rowNumber}: codigo_interno es obligatorio.");
                }
                if (empty($itemsByInvoice[$codigoInterno])) {
                    throw new RuntimeException("Factura {$codigoInterno}: no tiene items asociados.");
                }

                $tipoDocumento = $this->normalizeTipoDocumento($row['tipo_documento'] ?? null);
                $serie = strtoupper(trim((string) ($row['serie'] ?? '')));
                $numero = trim((string) ($row['numero'] ?? ''));
                if ($serie === '' || $numero === '') {
                    throw new RuntimeException("Factura {$codigoInterno}: serie y numero son obligatorios.");
                }

                $exists = Invoice::where('company_id', $companyId)
                    ->where('codigo_tipo_documento', $tipoDocumento)
                    ->where('serie_documento', $serie)
                    ->where('numero_documento', $numero)
                    ->exists();
                if ($exists) {
                    throw new RuntimeException("Factura {$codigoInterno}: ya existe {$serie}-{$numero}.");
                }

                $client = $this->resolveClient($row, $companyId);
                $igvPct = (float) ($row['igv_porcentaje'] ?? 18);
                $parsedItems = $this->parseItems($itemsByInvoice[$codigoInterno], $codigoInterno, $igvPct);
                $totals = $this->calculateTotals($parsedItems);

                $currency = strtoupper(trim((string) ($row['moneda'] ?? 'PEN'))) ?: 'PEN';
                if (! in_array($currency, ['PEN', 'USD', 'EUR'], true)) {
                    throw new RuntimeException("Factura {$codigoInterno}: moneda invalida {$currency}.");
                }
                $exchangeRate = $this->decimalOrNull($row['tipo_cambio'] ?? null);
                if ($currency === 'USD' && ($exchangeRate === null || $exchangeRate <= 0)) {
                    throw new RuntimeException("Factura {$codigoInterno}: tipo_cambio es obligatorio para USD.");
                }

                $invoiceData = [
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'client_id' => $client->id,
                    'codigo_interno' => $codigoInterno,
                    'fecha_emision' => $this->parseDate($row['fecha_emision'] ?? null, "Factura {$codigoInterno}: fecha_emision"),
                    'hora_emision' => now()->format('H:i:s'),
                    'fecha_vencimiento' => $this->parseOptionalDate($row['fecha_vencimiento'] ?? null),
                    'forma_pago' => $this->normalizeFormaPago($row['forma_pago'] ?? 'contado'),
                    'codigo_tipo_documento' => $tipoDocumento,
                    'serie_documento' => $serie,
                    'numero_documento' => $numero,
                    'observacion' => $this->stringOrNull($row['observacion'] ?? null),
                    'correo' => $this->stringOrNull($row['cliente_email'] ?? null),
                    'numero_orden_compra' => $this->stringOrNull($row['orden_compra'] ?? null),
                    'codigo_moneda' => $currency,
                    'porcentaje_igv' => $igvPct,
                    'monto_tipo_cambio' => $exchangeRate,
                    'monto_total_gravado' => $totals['gravado'],
                    'monto_total_exonerado' => $totals['exonerado'] > 0 ? $totals['exonerado'] : null,
                    'monto_total_inafecto' => $totals['inafecto'] > 0 ? $totals['inafecto'] : null,
                    'monto_total_igv' => $totals['igv'],
                    'monto_total' => $totals['total'],
                    'estado' => InvoiceStatusEnum::DRAFT->value,
                    'estado_feasy' => FeasyStatusEnum::PENDING->value,
                    'accounting_status' => AccountingStatusEnum::PENDIENTE->value,
                    'indicador_detraccion' => false,
                    'indicador_retencion' => false,
                ];

                $this->applyDetraction($invoiceData, $row, $codigoInterno);
                $this->applyRetention($invoiceData, $row, $client, $codigoInterno);

                $invoice = Invoice::create($invoiceData);
                foreach ($parsedItems as $index => $item) {
                    InvoiceItem::create([
                        ...$item,
                        'invoice_id' => $invoice->id,
                        'company_id' => $companyId,
                        'correlativo' => $item['correlativo'] ?? ($index + 1),
                    ]);
                }

                $created[] = "{$serie}-{$numero}";
            }
        });

        return [
            'created' => count($created),
            'invoices' => $created,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rowsFromSheet(array $sheet, string $name): array
    {
        if (count($sheet) < 2) {
            return [];
        }

        $headers = array_map(fn ($value) => $this->normalizeHeader((string) $value), $sheet[0]);
        $rows = [];

        foreach (array_slice($sheet, 1) as $index => $values) {
            if ($this->isEmptyRow($values)) {
                continue;
            }
            $row = [];
            foreach ($headers as $colIndex => $header) {
                if ($header !== '') {
                    $row[$header] = $values[$colIndex] ?? null;
                }
            }
            $rows[$index + 2] = $row;
        }

        return $rows;
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace([' ', '-', '.'], '_', $value);
        return preg_replace('/[^a-z0-9_]/', '', $value) ?? '';
    }

    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }

    private function resolveClient(array $row, int $companyId): Client
    {
        $tipoDoc = trim((string) ($row['cliente_tipo_doc'] ?? '6')) ?: '6';
        $numeroDoc = trim((string) ($row['cliente_numero_doc'] ?? ''));
        $razonSocial = trim((string) ($row['cliente_razon_social'] ?? ''));

        if ($numeroDoc === '' || $razonSocial === '') {
            throw new RuntimeException('cliente_numero_doc y cliente_razon_social son obligatorios.');
        }

        return Client::firstOrCreate(
            [
                'company_id' => $companyId,
                'numero_documento' => $numeroDoc,
            ],
            [
                'codigo_tipo_documento' => $tipoDoc,
                'nombre_razon_social' => $razonSocial,
                'codigo_pais' => 'PE',
                'direccion' => $this->stringOrNull($row['cliente_direccion'] ?? null),
                'correo' => $this->stringOrNull($row['cliente_email'] ?? null),
                'activo' => true,
            ]
        );
    }

    private function normalizeTipoDocumento(mixed $value): string
    {
        $value = trim((string) $value);
        return str_pad($value !== '' ? $value : '01', 2, '0', STR_PAD_LEFT);
    }

    private function normalizeFormaPago(mixed $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        return in_array($value, ['2', 'credito', 'crédito'], true) ? '2' : '1';
    }

    private function parseDate(mixed $value, string $field): string
    {
        $date = $this->parseOptionalDate($value);
        if ($date === null) {
            throw new RuntimeException("{$field} es obligatoria.");
        }
        return $date;
    }

    private function parseOptionalDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        $timestamp = strtotime((string) $value);
        if ($timestamp === false) {
            throw new RuntimeException("Fecha invalida: {$value}.");
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * @param array<int, array{row: array<string, mixed>, row_number: int}> $rows
     * @return array<int, array<string, mixed>>
     */
    private function parseItems(array $rows, string $codigoInterno, float $igvPct): array
    {
        $items = [];
        foreach ($rows as $entry) {
            $row = $entry['row'];
            $rowNumber = $entry['row_number'];
            $description = trim((string) ($row['descripcion'] ?? ''));
            if ($description === '') {
                throw new RuntimeException("Factura {$codigoInterno}, item fila {$rowNumber}: descripcion es obligatoria.");
            }

            $quantity = (float) ($row['cantidad'] ?? 0);
            $priceWithIgv = (float) ($row['precio_unitario_con_igv'] ?? $row['precio_unitario'] ?? 0);
            if ($quantity <= 0 || $priceWithIgv <= 0) {
                throw new RuntimeException("Factura {$codigoInterno}, item fila {$rowNumber}: cantidad y precio_unitario_con_igv deben ser mayores a 0.");
            }

            $affectation = $this->affectationCode($row['afecto_igv'] ?? 'SI');
            $igvRate = $igvPct / 100;
            $valueUnit = $affectation === '10' ? round($priceWithIgv / (1 + $igvRate), 4) : round($priceWithIgv, 4);
            $valueTotal = round($valueUnit * $quantity, 4);
            $igv = $affectation === '10' ? round($valueTotal * $igvRate, 2) : 0.0;
            $lineTotal = round($valueTotal + $igv, 2);

            $items[] = [
                'correlativo' => (int) ($row['item_numero'] ?? count($items) + 1),
                'codigo_interno' => trim((string) ($row['producto_codigo'] ?? '')) ?: "{$codigoInterno}-" . str_pad((string) (count($items) + 1), 3, '0', STR_PAD_LEFT),
                'codigo_sunat' => $this->stringOrNull($row['codigo_sunat'] ?? null),
                'tipo' => strtoupper(trim((string) ($row['tipo_item'] ?? 'P'))) === 'S' ? 'S' : 'P',
                'codigo_unidad_medida' => strtoupper(trim((string) ($row['unidad_medida'] ?? 'NIU'))) ?: 'NIU',
                'descripcion' => $description,
                'cantidad' => $quantity,
                'monto_valor_unitario' => $valueUnit,
                'monto_precio_unitario' => round($priceWithIgv, 4),
                'monto_valor_total' => $valueTotal,
                'codigo_indicador_afecto' => $affectation,
                'monto_igv' => $igv,
                'monto_total' => $lineTotal,
            ];
        }

        return $items;
    }

    private function affectationCode(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) $value));
        return match ($value) {
            'SI', 'S', '10', 'GRAVADO' => '10',
            'EXONERADO', '20' => '20',
            default => '30',
        };
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array{gravado: float, exonerado: float, inafecto: float, igv: float, total: float}
     */
    private function calculateTotals(array $items): array
    {
        $totals = ['gravado' => 0.0, 'exonerado' => 0.0, 'inafecto' => 0.0, 'igv' => 0.0, 'total' => 0.0];
        foreach ($items as $item) {
            if ($item['codigo_indicador_afecto'] === '10') {
                $totals['gravado'] += (float) $item['monto_valor_total'];
            } elseif ($item['codigo_indicador_afecto'] === '20') {
                $totals['exonerado'] += (float) $item['monto_total'];
            } else {
                $totals['inafecto'] += (float) $item['monto_total'];
            }
            $totals['igv'] += (float) $item['monto_igv'];
            $totals['total'] += (float) $item['monto_total'];
        }

        return array_map(fn (float $value): float => round($value, 2), $totals);
    }

    private function applyDetraction(array &$invoiceData, array $row, string $codigoInterno): void
    {
        if (! $this->truthy($row['detraccion_activa'] ?? null)) {
            return;
        }

        $codigo = trim((string) ($row['detraccion_codigo'] ?? ''));
        $pct = (float) ($row['detraccion_porcentaje'] ?? 0);
        $cuenta = preg_replace('/[^0-9]/', '', (string) ($row['detraccion_cuenta_bn'] ?? ''));
        $medio = trim((string) ($row['detraccion_medio_pago'] ?? '001')) ?: '001';
        if ($codigo === '' || $pct <= 0 || strlen($cuenta) !== 11) {
            throw new RuntimeException("Factura {$codigoInterno}: detraccion_codigo, detraccion_porcentaje y cuenta BN de 11 digitos son obligatorios si hay detraccion.");
        }

        $amount = round((float) $invoiceData['monto_total'] * $pct / 100, 2);
        $net = round((float) $invoiceData['monto_total'] - $amount, 2);
        $spot = SpotDetraccion::where('codigo', $codigo)->first();
        $desc = $spot?->descripcion ?? "Codigo {$codigo}";
        $desc = preg_replace('/\s*\(\d+%\)$/', '', $desc);

        $invoiceData['indicador_detraccion'] = true;
        $invoiceData['informacion_detraccion'] = [
            'codigo_bbss_sujeto_detraccion' => $codigo,
            'porcentaje_detraccion' => $pct,
            'monto_detraccion' => $amount,
            'cuenta_banco_detraccion' => $cuenta,
            'codigo_medio_pago_detraccion' => $medio,
        ];
        $invoiceData['informacion_adicional']['informacion_adicional_1'] = $cuenta;
        $invoiceData['informacion_adicional']['informacion_adicional_2'] =
            "Leyenda:\n" .
            "Operacion sujeta al Sistema de Pago de Obligaciones Tributarias con el Gobierno Central\n" .
            "Bien o Servicio: {$codigo} - {$desc}\n" .
            "Porcentaje de detraccion: " . number_format($pct, 0) . "%\n" .
            "Monto detraccion: PEN " . number_format($amount, 2, '.', ',') . "\n" .
            "Nro. Cta. Banco de la Nacion: {$cuenta}\n" .
            "Medio de pago: {$medio}\n" .
            "Monto neto pendiente de pago: PEN " . number_format($net, 2, '.', ',');
    }

    private function applyRetention(array &$invoiceData, array $row, Client $client, string $codigoInterno): void
    {
        if (! empty($invoiceData['indicador_detraccion'])) {
            return;
        }

        $mustRetain = $this->truthy($row['retencion_activa'] ?? null)
            || ((bool) $client->is_retainer_agent && (float) $invoiceData['monto_total'] > 700);
        if (! $mustRetain) {
            return;
        }

        $pct = (float) ($row['retencion_porcentaje'] ?? 3);
        if ($pct <= 0) {
            throw new RuntimeException("Factura {$codigoInterno}: retencion_porcentaje debe ser mayor a 0.");
        }

        $amount = round((float) $invoiceData['monto_total'] * $pct / 100, 2);
        $netTotal = round((float) $invoiceData['monto_total'] - $amount, 2);
        $retentionInfo = [
            'codigo_retencion' => trim((string) ($row['retencion_codigo'] ?? '62')) ?: '62',
            'monto_base_imponible_retencion' => (float) $invoiceData['monto_total'],
            'porcentaje_retencion' => $pct,
            'monto_retencion' => $amount,
        ];

        $invoiceData['indicador_retencion'] = true;
        $invoiceData['informacion_retencion'] = $retentionInfo;
        $invoiceData['retention_enabled'] = true;
        $invoiceData['has_retention'] = true;
        $invoiceData['retention_base'] = (float) $invoiceData['monto_total'];
        $invoiceData['retention_percentage'] = $pct;
        $invoiceData['retention_amount'] = $amount;
        $invoiceData['net_total'] = $netTotal;
        $invoiceData['retention_info'] = $retentionInfo;
        $invoiceData['total_before_retention'] = (float) $invoiceData['monto_total'];
        $invoiceData['total_after_retention'] = $netTotal;
        $invoiceData['informacion_adicional']['informacion_adicional_3'] = $this->retentionAdditionalInfoService->build(
            (string) $invoiceData['codigo_moneda'],
            (float) $invoiceData['monto_total'],
            $pct,
            $amount,
            $netTotal,
            $this->decimalOrNull($invoiceData['monto_tipo_cambio'] ?? null)
        );
    }

    private function truthy(mixed $value): bool
    {
        return in_array(mb_strtoupper(trim((string) $value)), ['1', 'SI', 'S', 'TRUE', 'YES'], true);
    }

    private function decimalOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return round((float) $value, 4);
    }

    private function stringOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
