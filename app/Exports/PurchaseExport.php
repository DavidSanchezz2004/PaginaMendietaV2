<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PurchaseExport implements WithMultipleSheets
{
    public function __construct(
        private int $companyId,
        private string $from,
        private string $to
    ) {}

    public function sheets(): array
    {
        $purchases = Purchase::where('company_id', $this->companyId)
            ->where('accounting_status', 'listo')
            ->whereBetween('fecha_emision', [$this->from, $this->to])
            ->with('provider')
            ->orderBy('fecha_emision')
            ->orderBy('serie_documento')
            ->orderBy('numero_documento')
            ->get();

        return [new InvoiceComprasSheet($purchases)];
    }
}
