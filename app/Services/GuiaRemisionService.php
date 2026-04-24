<?php

namespace App\Services;

use App\Models\ClientAddress;
use App\Models\GuiaRemision;
use App\Models\GuiaRemisionItem;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestión de Guías de Remisión.
 * Encapsula lógica de generación, validación y transiciones de estado.
 */
class GuiaRemisionService
{
    /**
     * Genera preview de guía antes de confirmar (UX pro).
     * Permite al usuario revisar datos antes de comprometerse.
     */
    public function preview(Purchase $purchase, ClientAddress $address): array
    {
        return [
            'purchase' => $purchase->load('items', 'client'),
            'items_count' => $purchase->items->count(),
            'total_quantity' => $purchase->items->sum('cantidad'),
            'address' => $address->load('client'),
            'numero_preview' => 'GRE-XXX-' . now()->year,
        ];
    }

    /**
     * Genera guía de remisión para una compra.
     * Flujo:
     * 1. Validar que compra está "assigned"
     * 2. Obtener lock (concurrencia)
     * 3. Generar número único (dentro de transacción)
     * 4. Crear guía + items (copiar de purchase)
     * 5. Actualizar status compra → "guided"
     */
    public function generate(
        Purchase $purchase,
        ClientAddress $address,
        ?string $motivo = 'Venta',
        array $itemPrices = []
    ): GuiaRemision {
        // VALIDACIONES
        if ($purchase->status !== 'assigned') {
            throw new \Exception('Compra debe estar en estado "assigned". Status actual: ' . $purchase->status);
        }

        if ($purchase->guia) {
            throw new \Exception('Esta compra ya tiene guía de remisión asignada');
        }

        if (!$purchase->client_id) {
            throw new \Exception('Compra no tiene cliente asignado');
        }

        // CONCURRENCIA: lockForUpdate previene race conditions
        $purchase = Purchase::lockForUpdate()->find($purchase->id);

        // Revalidar después de lock
        if ($purchase->status !== 'assigned') {
            throw new \Exception('Compra fue modificada por otro usuario. Estado actual: ' . $purchase->status);
        }

        // CREAR GUÍA (transacción para atomicidad)
        // GENERAR NÚMERO ÚNICO DENTRO de la transacción para evitar race conditions
        try {
            $guia = DB::transaction(function () use ($purchase, $address, $motivo, $itemPrices) {
                // Generar número DENTRO de transacción (garantiza unicidad por purchase_id)
                $numero = $this->generate_numero_for_purchase($purchase);
                // Crear guía
                $guia = GuiaRemision::create([
                    'company_id' => $purchase->company_id,
                    'purchase_id' => $purchase->id,
                    'client_id' => $address->client_id,
                    'client_address_id' => $address->id,
                    'numero' => $numero,
                    'fecha_emision' => now(),
                    'motivo' => $motivo,
                    'estado' => 'generated',
                ]);

                // COPIAR ITEMS (no referenciar directamente)
                // Esto permite cambios independientes en factura sin afectar compra
                foreach ($purchase->items as $item) {
                    $customPrice = isset($itemPrices[$item->id]) && is_numeric($itemPrices[$item->id])
                        ? (float) $itemPrices[$item->id]
                        : null;
                    GuiaRemisionItem::create([
                        'guia_remision_id' => $guia->id,
                        'purchase_item_id' => $item->id,
                        'quantity'         => $item->cantidad,
                        'unit'             => $item->unidad_medida,
                        'description'      => $item->descripcion,
                        'unit_price'       => $customPrice,
                    ]);
                }

                // ACTUALIZAR STATUS COMPRA
                $purchase->update_status_to_guided();

                Log::info("Guía {$numero} generada para compra {$purchase->serie_documento}", [
                    'guia_id' => $guia->id,
                    'purchase_id' => $purchase->id,
                    'company_id' => $purchase->company_id,
                ]);

                return $guia;
            });

            return $guia->load('purchase', 'client', 'clientAddress', 'items');
        } catch (\Exception $e) {
            Log::error("Error al generar guía para compra {$purchase->serie_documento}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Genera número único para guía en formato: GRE-NNNNN-AAAA
     * Donde NNNNN es el ID de la compra (purchase_id) de 5 dígitos
     * Esto garantiza unicidad sin race conditions
     */
    private function generate_numero_for_purchase(Purchase $purchase): string
    {
        $year = now()->year;
        // Usar purchase_id padded para garantizar unicidad
        return sprintf('GRE-%05d-%d', $purchase->id, $year);
    }

    /**
     * Genera número único para guía en formato: GRE-NNN-AAAA
     * Ej: GRE-001-2026
     * DEPRECATED: usar generate_numero_for_purchase() en su lugar
     */
    private function generate_numero(object $company): string
    {
        $year = now()->year;
        $count = GuiaRemision::where('company_id', $company->id)
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->count() + 1;

        return sprintf('GRE-%03d-%d', $count, $year);
    }

    /**
     * Marca guía como facturada.
     * Se ejecuta cuando se crea factura exitosamente.
     */
    public function mark_invoiced(GuiaRemision $guia, object $invoice): void
    {
        $guia->update([
            'estado' => 'invoiced',
            'invoice_id' => $invoice->id,
        ]);

        Log::info("Guía {$guia->numero} marcada como facturada", [
            'guia_id' => $guia->id,
            'invoice_id' => $invoice->id,
        ]);
    }
}
