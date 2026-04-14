<?php

namespace App\Exports;

use App\Models\Invoice;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvoiceExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly int    $companyId,
        private readonly string $from,
        private readonly string $to,
    ) {
    }

    public function sheets(): array
    {
        $invoices = Invoice::where('company_id', $this->companyId)
            ->where('accounting_status', 'listo')
            ->whereBetween('fecha_emision', [$this->from, $this->to])
            ->with('client')
            ->orderBy('fecha_emision')
            ->orderBy('serie_documento')
            ->orderBy('numero_documento')
            ->get();

        return [
            'VENTAS'  => new InvoiceVentasSheet($invoices),
            'COMPRAS' => new InvoiceComprasSheet(),
        ];
    }
}
