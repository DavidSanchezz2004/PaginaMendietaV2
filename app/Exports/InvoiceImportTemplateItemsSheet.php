<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceImportTemplateItemsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'items';
    }

    public function array(): array
    {
        return [
            [
                'codigo_interno',
                'item_numero',
                'producto_codigo',
                'codigo_sunat',
                'descripcion',
                'tipo_item',
                'unidad_medida',
                'cantidad',
                'precio_unitario_con_igv',
                'afecto_igv',
            ],
            ['FAC-SIMPLE-001', '1', 'SERV001', '', 'Servicio profesional', 'S', 'NIU', '1', '1180.00', 'SI'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1:1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('1:1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F6B57');
        $sheet->freezePane('A2');

        return [];
    }
}
