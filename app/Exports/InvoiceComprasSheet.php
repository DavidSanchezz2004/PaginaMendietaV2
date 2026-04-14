<?php

namespace App\Exports;

use App\Models\Purchase;
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

class InvoiceComprasSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $purchases) {}

    public function title(): string
    {
        return 'COMPRAS';
    }

    public function collection(): Collection
    {
        return $this->purchases;
    }

    /** @param Purchase $row */
    public function map($row): array
    {
        $formaLabel = match ((string) $row->forma_pago) {
            '01', '1' => 'Contado',
            '02', '2' => 'Crédito',
            '03', '3' => 'Efectivo',
            '04', '4' => 'Yape',
            '05', '5' => 'Plin',
            '06', '6' => 'Banco / Transferencia',
            '07', '7' => 'BCP',
            '08', '8' => 'BBVA',
            default => $row->forma_pago ?? '',
        };

        $tipoCompraLabel = $row->tipo_compra?->label() ?? $row->tipo_compra ?? '';

        return [
            $row->codigo_tipo_documento,
            $row->serie_documento,
            $row->numero_documento,
            $row->fecha_emision?->format('d/m/Y') ?? '',
            $row->fecha_vencimiento?->format('d/m/Y') ?? '',
            $row->codigo_moneda,
            $row->tipo_doc_proveedor,
            $row->numero_doc_proveedor,
            $row->provider?->nombre_razon_social ?? $row->razon_social_proveedor ?? '',
            $row->porcentaje_igv,
            $row->base_imponible_gravadas,
            $row->monto_exonerado,
            $row->monto_no_gravado,
            $row->igv_gravadas,
            $row->monto_total,
            $row->monto_tipo_cambio,
            $row->tipo_operacion,
            $tipoCompraLabel,
            $row->cuenta_contable,
            $row->codigo_producto_servicio,
            $formaLabel,
            $row->glosa,
            $row->centro_costo,
            $row->tipo_gasto,
            $row->sucursal,
            $row->comprador,
            $row->es_sujeto_detraccion ? 'Sí' : 'No',
            $row->es_sujeto_retencion  ? 'Sí' : 'No',
            $row->es_sujeto_percepcion ? 'Sí' : 'No',
            $row->es_anticipo          ? 'Sí' : 'No',
            $row->es_documento_contingencia ? 'Sí' : 'No',
            $row->anio_emision_dua,
            $row->tipo_doc_modifica,
            $row->serie_doc_modifica,
            $row->numero_doc_modifica,
            $row->fecha_doc_modifica?->format('d/m/Y') ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Tipo Comprobante',
            'Serie',
            'Número',
            'Fecha Emisión',
            'Fecha Vencimiento',
            'Moneda',
            'Tipo Doc. Proveedor',
            'Nro. Doc. Proveedor',
            'Razón Social Proveedor',
            '% IGV',
            'Base Imponible Gravadas',
            'Base Imponible Exoneradas',
            'Base Imponible Inafectas',
            'IGV',
            'Monto Total',
            'Tipo de Cambio',
            'Tipo Operación',
            'Tipo Compra',
            'Cuenta Contable',
            'Cód. Producto/Servicio',
            'Forma de Pago',
            'Glosa',
            'Centro de Costo',
            'Tipo de Gasto',
            'Sucursal',
            'Comprador',
            'Sujeto Detracción',
            'Sujeto Retención',
            'Sujeto Percepción',
            'Es Anticipo',
            'Doc. Contingencia',
            'Año Emisión DUA',
            'Doc. Modifica Tipo',
            'Doc. Modifica Serie',
            'Doc. Modifica Número',
            'Doc. Modifica Fecha',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
