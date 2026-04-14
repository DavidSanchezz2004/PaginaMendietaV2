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

/**
 * Hoja "DETALLE" del Excel de compras.
 * Cada fila es un ítem (línea de detalle) de un comprobante.
 */
class PurchaseItemsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    /** @param Collection<int, \App\Models\Purchase> $purchases */
    public function __construct(private Collection $purchases) {}

    public function title(): string
    {
        return 'DETALLE';
    }

    /** Aplana purchases → items */
    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->purchases as $purchase) {
            foreach ($purchase->items as $item) {
                // Adjuntar referencia al comprobante en cada ítem para el mapping
                $item->_purchase = $purchase;
                $rows->push($item);
            }
        }

        return $rows;
    }

    /** @param \App\Models\PurchaseItem $row */
    public function map($row): array
    {
        $p = $row->_purchase;

        return [
            $p->codigo_tipo_documento,
            $p->serie_documento,
            $p->numero_documento,
            $p->fecha_emision?->format('d/m/Y') ?? '',
            $p->numero_doc_proveedor,
            $p->provider?->nombre_razon_social ?? $p->razon_social_proveedor ?? '',
            $p->codigo_moneda,
            $row->correlativo,
            $row->descripcion,
            $row->unidad_medida,
            $row->cantidad,
            $row->valor_unitario,
            $row->descuento,
            $row->importe_venta,
            $row->icbper,
        ];
    }

    public function headings(): array
    {
        return [
            'Tipo Comprobante',
            'Serie',
            'Número',
            'Fecha Emisión',
            'RUC Proveedor',
            'Razón Social Proveedor',
            'Moneda',
            '#',
            'Descripción',
            'Unidad de Medida',
            'Cantidad',
            'Valor Unitario',
            'Descuento',
            'Importe de Venta',
            'ICBPER',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E5F3A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
