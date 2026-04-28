<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceImportTemplateInstructionsSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'instrucciones';
    }

    public function array(): array
    {
        return [
            ['Plantilla de importacion de comprobantes'],
            [''],
            ['Hojas que importa el sistema'],
            ['facturas', 'Una fila por factura o boleta.'],
            ['items', 'Una fila por producto o servicio. Se vincula con facturas usando codigo_interno.'],
            [''],
            ['Tipos de uso'],
            ['Factura simple', 'Llenar una fila en facturas y una fila en items. tipo_documento = 01.'],
            ['Boleta simple', 'Llenar una fila en facturas y una fila en items. tipo_documento = 03.'],
            ['Factura completa', 'Usar varias filas en items con el mismo codigo_interno. Puede incluir detraccion o retencion.'],
            [''],
            ['Campos clave'],
            ['codigo_interno', 'Identificador temporal unico. Ej: FAC001. Debe repetirse en items.'],
            ['tipo_documento', '01 = Factura, 03 = Boleta.'],
            ['forma_pago', 'contado o credito.'],
            ['moneda', 'PEN, USD o EUR. Si es USD, tipo_cambio es obligatorio.'],
            ['afecto_igv', 'SI = gravado, EXONERADO = exonerado, NO = inafecto.'],
            ['precio_unitario_con_igv', 'Precio final unitario. Si afecto_igv = SI, el sistema separa IGV automaticamente.'],
            [''],
            ['Reglas'],
            ['No eliminar ni renombrar las hojas facturas e items.'],
            ['Cada comprobante debe tener al menos un item.'],
            ['Para una factura con muchos productos, repetir codigo_interno en la hoja items.'],
            ['La importacion crea borradores. La emision se hace despues desde el sistema.'],
            ['Puede copiar los ejemplos a las hojas facturas e items y reemplazar datos.'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:B1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F6B57');
        $sheet->getStyle('A3:B3')->getFont()->setBold(true);
        $sheet->getStyle('A7:B7')->getFont()->setBold(true);
        $sheet->getStyle('A12:B12')->getFont()->setBold(true);
        $sheet->getStyle('A21:B21')->getFont()->setBold(true);
        $sheet->getStyle('A:A')->getFont()->setBold(true);

        return [];
    }
}
