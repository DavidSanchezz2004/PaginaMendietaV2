<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceImportTemplateExampleSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private readonly string $title)
    {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        return match ($this->title) {
            'ejemplo_boleta_simple' => $this->boletaSimple(),
            'ejemplo_factura_completa' => $this->facturaCompleta(),
            default => $this->facturaSimple(),
        };
    }

    public function styles(Worksheet $sheet): array
    {
        foreach ([1, 4] as $row) {
            $sheet->getStyle("{$row}:{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("{$row}:{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F6B57');
        }
        $sheet->freezePane('A2');

        return [];
    }

    private function facturaSimple(): array
    {
        return [
            ['FACTURAS'],
            ['codigo_interno', 'tipo_documento', 'serie', 'numero', 'fecha_emision', 'fecha_vencimiento', 'forma_pago', 'moneda', 'tipo_cambio', 'cliente_tipo_doc', 'cliente_numero_doc', 'cliente_razon_social', 'cliente_direccion', 'cliente_email', 'orden_compra', 'observacion', 'igv_porcentaje', 'detraccion_activa', 'detraccion_codigo', 'detraccion_porcentaje', 'detraccion_cuenta_bn', 'detraccion_medio_pago', 'retencion_activa', 'retencion_codigo', 'retencion_porcentaje'],
            ['FAC-SIMPLE-001', '01', 'F001', '1001', '2026-04-27', '', 'contado', 'PEN', '', '6', '20123456789', 'CLIENTE SIMPLE SAC', 'AV LIMA 123', 'cliente@correo.com', '', 'Venta simple', '18', 'NO', '', '', '', '001', 'NO', '', ''],
            ['ITEMS'],
            ['codigo_interno', 'item_numero', 'producto_codigo', 'codigo_sunat', 'descripcion', 'tipo_item', 'unidad_medida', 'cantidad', 'precio_unitario_con_igv', 'afecto_igv'],
            ['FAC-SIMPLE-001', '1', 'SERV001', '', 'Servicio profesional', 'S', 'NIU', '1', '1180.00', 'SI'],
        ];
    }

    private function boletaSimple(): array
    {
        return [
            ['FACTURAS'],
            ['codigo_interno', 'tipo_documento', 'serie', 'numero', 'fecha_emision', 'fecha_vencimiento', 'forma_pago', 'moneda', 'tipo_cambio', 'cliente_tipo_doc', 'cliente_numero_doc', 'cliente_razon_social', 'cliente_direccion', 'cliente_email', 'orden_compra', 'observacion', 'igv_porcentaje', 'detraccion_activa', 'detraccion_codigo', 'detraccion_porcentaje', 'detraccion_cuenta_bn', 'detraccion_medio_pago', 'retencion_activa', 'retencion_codigo', 'retencion_porcentaje'],
            ['BOL-SIMPLE-001', '03', 'B001', '5001', '2026-04-27', '', 'contado', 'PEN', '', '1', '45678912', 'CLIENTE BOLETA', 'JR AREQUIPA 456', 'boleta@correo.com', '', 'Boleta simple', '18', 'NO', '', '', '', '001', 'NO', '', ''],
            ['ITEMS'],
            ['codigo_interno', 'item_numero', 'producto_codigo', 'codigo_sunat', 'descripcion', 'tipo_item', 'unidad_medida', 'cantidad', 'precio_unitario_con_igv', 'afecto_igv'],
            ['BOL-SIMPLE-001', '1', 'PROD001', '', 'Producto de venta', 'P', 'NIU', '2', '59.00', 'SI'],
        ];
    }

    private function facturaCompleta(): array
    {
        return [
            ['FACTURAS'],
            ['codigo_interno', 'tipo_documento', 'serie', 'numero', 'fecha_emision', 'fecha_vencimiento', 'forma_pago', 'moneda', 'tipo_cambio', 'cliente_tipo_doc', 'cliente_numero_doc', 'cliente_razon_social', 'cliente_direccion', 'cliente_email', 'orden_compra', 'observacion', 'igv_porcentaje', 'detraccion_activa', 'detraccion_codigo', 'detraccion_porcentaje', 'detraccion_cuenta_bn', 'detraccion_medio_pago', 'retencion_activa', 'retencion_codigo', 'retencion_porcentaje'],
            ['FAC-COMP-001', '01', 'F001', '1002', '2026-04-27', '2026-05-27', 'credito', 'USD', '3.478', '6', '20987654321', 'CLIENTE COMPLETO SAC', 'AV INDUSTRIAL 789', 'completo@correo.com', 'OC-778', 'Factura con varios items y retencion', '18', 'NO', '', '', '', '001', 'SI', '62', '3'],
            ['ITEMS'],
            ['codigo_interno', 'item_numero', 'producto_codigo', 'codigo_sunat', 'descripcion', 'tipo_item', 'unidad_medida', 'cantidad', 'precio_unitario_con_igv', 'afecto_igv'],
            ['FAC-COMP-001', '1', 'SERV001', '', 'Servicio de consultoria', 'S', 'NIU', '1', '11977.66', 'SI'],
            ['FAC-COMP-001', '2', 'PROD002', '', 'Producto adicional', 'P', 'NIU', '3', '150.00', 'SI'],
            ['FAC-COMP-001', '3', 'PROD003', '', 'Producto inafecto', 'P', 'NIU', '5', '25.00', 'NO'],
        ];
    }
}
