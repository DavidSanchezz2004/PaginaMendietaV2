<?php

namespace App\Services\Facturador;

use App\Models\LetterCompensation;
use App\Models\LetraCambio;
use App\Models\PagoLetra;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LetterCompensationService
{
    public function compensate(LetraCambio $letra, array $data): LetterCompensation
    {
        return DB::transaction(function () use ($letra, $data): LetterCompensation {
            $letra = LetraCambio::whereKey($letra->id)->lockForUpdate()->firstOrFail();
            $supplierId = (int) $data['supplier_id'];
            $details = collect($data['details'] ?? [])
                ->map(fn (array $detail) => [
                    'purchase_invoice_id' => (int) ($detail['purchase_invoice_id'] ?? 0),
                    'amount' => round((float) ($detail['amount'] ?? 0), 2),
                ])
                ->filter(fn (array $detail) => $detail['purchase_invoice_id'] > 0 && $detail['amount'] > 0)
                ->values();

            if ($details->isEmpty()) {
                throw ValidationException::withMessages([
                    'details' => 'Debe seleccionar al menos una factura para compensar.',
                ]);
            }

            $total = round($details->sum('amount'), 2);

            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'total_amount' => 'El monto a compensar debe ser mayor a cero.',
                ]);
            }

            if ($total > $letra->saldo) {
                throw ValidationException::withMessages([
                    'total_amount' => 'El monto total no puede superar el saldo pendiente de la letra.',
                ]);
            }

            $compensation = LetterCompensation::create([
                'bill_of_exchange_id' => $letra->id,
                'supplier_id' => $supplierId,
                'company_id' => $letra->company_id,
                'created_by' => Auth::id(),
                'compensation_date' => $data['compensation_date'],
                'currency' => $letra->codigo_moneda,
                'total_amount' => $total,
                'observation' => $data['observation'] ?? null,
            ]);

            foreach ($details as $detail) {
                $purchase = Purchase::whereKey($detail['purchase_invoice_id'])->lockForUpdate()->firstOrFail();

                if ((int) $purchase->company_id !== (int) $letra->company_id) {
                    throw ValidationException::withMessages([
                        'details' => "La factura {$purchase->serie_numero} pertenece a otra empresa.",
                    ]);
                }

                if ((int) $purchase->provider_id !== $supplierId) {
                    throw ValidationException::withMessages([
                        'details' => "La factura {$purchase->serie_numero} no pertenece al proveedor seleccionado.",
                    ]);
                }

                if ($purchase->codigo_moneda !== $letra->codigo_moneda) {
                    throw ValidationException::withMessages([
                        'details' => "La factura {$purchase->serie_numero} está en {$purchase->codigo_moneda}; la letra está en {$letra->codigo_moneda}.",
                    ]);
                }

                if ($detail['amount'] > $purchase->saldo_pendiente_pago) {
                    throw ValidationException::withMessages([
                        'details' => "El monto aplicado a {$purchase->serie_numero} supera su saldo pendiente.",
                    ]);
                }

                $compensation->details()->create([
                    'purchase_invoice_id' => $purchase->id,
                    'amount' => $detail['amount'],
                ]);

                $nuevoPagadoCompra = round((float) ($purchase->monto_pagado ?? 0) + $detail['amount'], 2);
                $nuevoEstadoCompra = $nuevoPagadoCompra >= $purchase->monto_pagable ? 'pagado' : 'parcial';

                $purchase->update([
                    'monto_pagado' => $nuevoPagadoCompra,
                    'estado_pago' => $nuevoEstadoCompra,
                ]);
            }

            $nuevoPagadoLetra = round((float) $letra->monto_pagado + $total, 2);
            $nuevoEstadoLetra = $nuevoPagadoLetra >= (float) $letra->monto ? 'compensada' : 'compensada_parcial';

            $letra->update([
                'monto_pagado' => $nuevoPagadoLetra,
                'estado' => $nuevoEstadoLetra,
            ]);

            PagoLetra::create([
                'letra_cambio_id' => $letra->id,
                'company_id' => $letra->company_id,
                'user_id' => Auth::id(),
                'fecha_pago' => $data['compensation_date'],
                'monto_pagado' => $total,
                'medio_pago' => 'compensacion',
                'referencia_pago' => 'Compensación #' . $compensation->id,
                'observaciones' => $data['observation'] ?? 'Endoso de letra aplicado contra factura de proveedor.',
            ]);

            return $compensation->load(['supplier', 'details.purchaseInvoice']);
        });
    }
}
