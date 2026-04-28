<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvoiceImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'instrucciones' => new InvoiceImportTemplateInstructionsSheet(),
            'facturas' => new InvoiceImportTemplateFacturasSheet(),
            'items' => new InvoiceImportTemplateItemsSheet(),
            'ejemplo_factura_simple' => new InvoiceImportTemplateExampleSheet('ejemplo_factura_simple'),
            'ejemplo_boleta_simple' => new InvoiceImportTemplateExampleSheet('ejemplo_boleta_simple'),
            'ejemplo_factura_completa' => new InvoiceImportTemplateExampleSheet('ejemplo_factura_completa'),
        ];
    }
}
