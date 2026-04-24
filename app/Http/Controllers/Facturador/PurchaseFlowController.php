<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseFlowController extends Controller
{
    /**
     * Mostrar flujo completo de la compra (cliente, guía, factura).
     */
    public function show(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        $purchase->load('provider', 'client', 'items', 'guias', 'invoices');

        return view('facturador.purchases.guia-flow', [
            'purchase' => $purchase,
        ]);
    }
}
