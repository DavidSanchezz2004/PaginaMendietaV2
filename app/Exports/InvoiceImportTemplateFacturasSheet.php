<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceImportTemplateFacturasSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'facturas';
    }

    public function array(): array
    {
        return [
            [
                'codigo_interno',
                'tipo_documento',
                'serie',
                'numero',
                'fecha_emision',
                'fecha_vencimiento',
                'forma_pago',
                'moneda',
                'tipo_cambio',
                'cliente_tipo_doc',
                'cliente_numero_doc',
                'cliente_razon_social',
                'cliente_direccion',
                'cliente_email',
                'orden_compra',
                'observacion',
                'igv_porcentaje',
                'detraccion_activa',
                'detraccion_codigo',
                'detraccion_porcentaje',
                'detraccion_cuenta_bn',
                'detraccion_medio_pago',
                'retencion_activa',
                'retencion_codigo',
                'retencion_porcentaje',
            ],
            [
                'FAC-SIMPLE-001',
                '01',
                'F001',
                '1001',
                '2026-04-27',
                '',
                'contado',
                'PEN',
                '',
                '6',
                '20123456789',
                'CLIENTE SIMPLE SAC',
                'AV LIMA 123',
                'cliente@correo.com',
                '',
                'Venta simple',
                '18',
                'NO',
                '',
                '',
                '',
                '001',
                'NO',
                '',
                '',
            ],
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
