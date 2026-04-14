<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceVentasSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private readonly Collection $invoices)
    {
    }

    public function title(): string
    {
        return 'VENTAS';
    }

    public function collection(): Collection
    {
        return $this->invoices;
    }

    public function headings(): array
    {
        return [
            'Tipo Comprobante',
            'Serie Comprobante',
            'Número Comprobante',
            'Fecha Emisión Comprobante',
            'Fecha Vencimiento Comprobante',
            'Código Moneda Comprobante',
            'Tipo Documento Cliente',
            'Número Documento Cliente',
            'Apellidos y Nombres o Razón Social',
            'Porcentaje IGV',
            'Valor Facturado de la Exportación',
            'Importe Total Gratuitas',
            'Base Imponible Operaciones Gravadas',
            'Importe Total Exoneradas',
            'Importe Total Inafectas',
            'Descuento',
            'ISC',
            'IGV',
            'ICBPER',
            'Otros Tributos y Cargos',
            'Descuentos Globales',
            'Importe Total',
            'Tipo de Cambio',
            'Fecha Documento Modifica',
            'Tipo Documento que Modifica',
            'Serie Documento que Modifica',
            'Número Documento que Modifica',
            'Tipo de Nota',
            'Glosa',
            'Cuenta Contable',
            'Código Producto o Servicio',
            'Forma de Pago',
            'Es Pago de Anticipo',
            'Es Documento de Contingencia',
            'Es Sujeto a Detracción',
            'Es Sujeto a Retención',
            'Es Sujeto a Percepción',
            'Centro de Costo',
            'Tipo de Gasto',
            'Tipo de Venta',
            'Tipo de Operación',
            'Sucursal',
            'Vendedor',
            'Anulado',
            'Fecha Vencimiento 1ra Cuota',
            'Monto 1ra Cuota',
            'Fecha Vencimiento 2da Cuota',
            'Monto 2da Cuota',
        ];
    }

    public function map($invoice): array
    {
        $tipoDocMap = [
            '01' => '01',
            '03' => '03',
            '07' => '07',
            '08' => '08',
            '09' => '09',
        ];

        $cuotas = $invoice->lista_cuotas ?? [];
        $cuota1Fecha  = $cuotas[0]['fecha_pago'] ?? '';
        $cuota1Monto  = isset($cuotas[0]) ? (float) ($cuotas[0]['monto'] ?? $cuotas[0]['importe'] ?? 0) : '';
        $cuota2Fecha  = $cuotas[1]['fecha_pago'] ?? '';
        $cuota2Monto  = isset($cuotas[1]) ? (float) ($cuotas[1]['monto'] ?? $cuotas[1]['importe'] ?? 0) : '';

        $tipoDocCliente = $invoice->client?->codigo_tipo_documento ?? '';

        // Determinar tipo de nota si aplica (07 o 08)
        $tipoNota = '';
        if (in_array($invoice->codigo_tipo_documento, ['07', '08'])) {
            $tipoNota = $invoice->codigo_tipo_documento === '07' ? 'NC' : 'ND';
        }

        // Documento que modifica (solo para notas)
        $fechaDocModifica  = '';
        $tipoDocModifica   = '';
        $serieDocModifica  = '';
        $numeroDocModifica = '';
        if (in_array($invoice->codigo_tipo_documento, ['07', '08'])) {
            $adicional = $invoice->informacion_adicional ?? [];
            $fechaDocModifica  = $adicional['fecha_doc_modifica']  ?? '';
            $tipoDocModifica   = $adicional['tipo_doc_modifica']   ?? '';
            $serieDocModifica  = $adicional['serie_doc_modifica']  ?? '';
            $numeroDocModifica = $adicional['numero_doc_modifica'] ?? '';
        }

        $anulado = $invoice->estado->value === 'voided' ? 'S' : 'N';

        return [
            $tipoDocMap[$invoice->codigo_tipo_documento] ?? $invoice->codigo_tipo_documento,
            $invoice->serie_documento,
            $invoice->numero_documento,
            $invoice->fecha_emision?->format('d/m/Y') ?? '',
            $invoice->fecha_vencimiento?->format('d/m/Y') ?? '',
            $invoice->codigo_moneda ?? 'PEN',
            $tipoDocCliente,
            $invoice->client?->numero_documento ?? '',
            $invoice->client?->nombre_razon_social ?? '',
            $invoice->porcentaje_igv ?? 18,
            (float) ($invoice->monto_total_exportacion ?? 0),
            (float) ($invoice->monto_total_gratuito ?? 0),
            (float) ($invoice->monto_total_gravado ?? 0),
            (float) ($invoice->monto_total_exonerado ?? 0),
            (float) ($invoice->monto_total_inafecto ?? 0),
            (float) ($invoice->monto_total_descuento ?? 0),
            (float) ($invoice->monto_total_isc ?? 0),
            (float) ($invoice->monto_total_igv ?? 0),
            (float) ($invoice->monto_total_impuesto_bolsa ?? 0),
            (float) ($invoice->monto_total_otros_cargos ?? 0),
            0, // Descuentos Globales — reservado
            (float) ($invoice->monto_total ?? 0),
            $invoice->monto_tipo_cambio ? (float) $invoice->monto_tipo_cambio : '',
            $fechaDocModifica,
            $tipoDocModifica,
            $serieDocModifica,
            $numeroDocModifica,
            $tipoNota,
            $invoice->glosa ?? '',
            $invoice->cuenta_contable ?? '',
            $invoice->codigo_producto_servicio ?? '',
            $invoice->forma_pago === '1' ? '01' : ($invoice->forma_pago === '2' ? '02' : ''),
            $invoice->es_anticipo ? 'S' : 'N',
            $invoice->es_documento_contingencia ? 'S' : 'N',
            $invoice->indicador_detraccion ? 'S' : 'N',
            $invoice->es_sujeto_retencion ? 'S' : 'N',
            $invoice->es_sujeto_percepcion ? 'S' : 'N',
            $invoice->centro_costo ?? '',
            $invoice->tipo_gasto ?? '',
            $invoice->tipo_venta ?? '',
            $invoice->tipo_operacion ?? '',
            $invoice->sucursal ?? '',
            $invoice->vendedor ?? '',
            $anulado,
            $cuota1Fecha,
            $cuota1Monto,
            $cuota2Fecha,
            $cuota2Monto,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A6B57']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
