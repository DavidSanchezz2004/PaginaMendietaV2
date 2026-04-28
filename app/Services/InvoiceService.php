<?php

namespace App\Services;

use App\Enums\AccountingStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\FeasyStatusEnum;
use App\Models\GuiaRemision;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para crear facturas desde guías de remisión.
 * Encapsula lógica de facturación con validaciones y retención.
 */
class InvoiceService
{
    public function __construct(
        private RetentionService $retentionService
    ) {}

    /**
     * Crea factura a partir de una guía de remisión.
     * Flujo:
     * 1. Validar que guía está en estado "generated" y sin factura
     * 2. Validar que compra está "guided"
     * 3. Obtener lock concurrencia
     * 4. Crear factura con datos de guía
     * 5. Copiar items con validación de cantidades
     * 6. Actualizar invoiced_quantity en purchase_items
     * 7. Aplicar retención si cliente es agente retenedor
     * 8. Actualizar status de compra
     * 9. Marcar guía como facturada
     */
    public function create_from_guia(GuiaRemision $guia, array $data): Invoice
    {
        // VALIDACIONES
        if ($guia->estado !== 'generated') {
            throw new \Exception("Guía debe estar en estado 'generated'. Estado actual: {$guia->estado}");
        }

        if ($guia->invoice_id) {
            throw new \Exception('Esta guía ya fue facturada');
        }

        if (!$guia->purchase->can_be_invoiced()) {
            throw new \Exception('La compra no está lista para facturar (status debe ser "guided")');
        }

        // Convertir forma_pago a códigos numéricos SUNAT
        $formaPagoMap = [
            'contado' => '1',
            'Contado' => '1',
            '1' => '1',
            '2' => '2',
            'Crédito' => '2',
            'credito' => '2',
        ];
        $data['forma_pago'] = $formaPagoMap[$data['forma_pago'] ?? 'contado'] ?? '1';
        $listaCuotas = $data['lista_cuotas'] ?? null;
        if (is_string($listaCuotas)) {
            $decoded = json_decode($listaCuotas, true);
            $listaCuotas = is_array($decoded) ? $decoded : null;
        }
        $fechaVencimiento = now()->toDateString();
        if ($data['forma_pago'] === '2' && is_array($listaCuotas) && ! empty($listaCuotas)) {
            $lastCuota = collect($listaCuotas)
                ->pluck('fecha_pago')
                ->filter()
                ->sort()
                ->last();
            $fechaVencimiento = $lastCuota ?: now()->addMonthNoOverflow()->toDateString();
        }

        // CONCURRENCIA: lockForUpdate previene race conditions
        $guia = GuiaRemision::lockForUpdate()->find($guia->id);
        $purchase = $guia->purchase;

        // CREAR FACTURA (transacción para atomicidad)
        try {
            $invoice = DB::transaction(function () use ($guia, $purchase, $data, $listaCuotas, $fechaVencimiento) {
                // Generar codigo_interno (tipo + serie + numero) - requerido por la BD
                $codigoInterno = $data['codigo_tipo_documento'] . 
                                 str_pad($data['serie_documento'], 5, '0', STR_PAD_LEFT) . 
                                 str_pad($data['numero_documento'], 8, '0', STR_PAD_LEFT);

                // Crear factura con datos básicos de guía
                $invoice = Invoice::create([
                    'company_id' => $purchase->company_id,
                    'user_id' => auth()->id(),
                    'guia_remision_id' => $guia->id,
                    'client_id' => $guia->client_id,
                    'client_address_id' => $guia->client_address_id,
                    'codigo_interno' => $codigoInterno,
                    'codigo_tipo_documento' => $data['codigo_tipo_documento'] ?? '01',
                    'serie_documento' => $data['serie_documento'],
                    'numero_documento' => $data['numero_documento'] ?? null,
                    'fecha_emision' => now()->toDateString(),
                    'hora_emision' => now()->toTimeString(),
                    'fecha_vencimiento' => $fechaVencimiento,
                    'codigo_moneda' => $data['codigo_moneda'] ?? 'PEN',
                    'forma_pago' => $data['forma_pago'] ?? '1',
                    'lista_cuotas' => $data['forma_pago'] === '2' ? $listaCuotas : null,
                    'lista_guias' => $this->buildListaGuiasFromGuia($guia),
                    'estado' => InvoiceStatusEnum::DRAFT->value,
                    'estado_feasy' => FeasyStatusEnum::PENDING->value,
                    'accounting_status' => AccountingStatusEnum::PENDIENTE->value,
                ]);

                // COPIAR ITEMS CON VALIDACIÓN
                $total_base = 0;
                $total_igv = 0;

                foreach ($data['items'] as $index => $item_data) {
                    $purchase_item = PurchaseItem::find($item_data['purchase_item_id']);

                    if (!$purchase_item) {
                        throw new \Exception("Item {$item_data['purchase_item_id']} no encontrado");
                    }

                    // VALIDACIÓN CRÍTICA: No sobrevender
                    $quantity_to_invoice = (float) $item_data['quantity'];
                    $remaining = $purchase_item->remaining_quantity();

                    if ($quantity_to_invoice > $remaining) {
                        throw new \Exception(
                            "Cantidad excede disponible para '{$purchase_item->descripcion}'. " .
                            "Solicitado: {$quantity_to_invoice}, Disponible: {$remaining}"
                        );
                    }

                    // Calcular montos del item (SUNAT requiere separar valor e IGV)
                    $precio_unitario_sin_igv = (float) $item_data['unit_price'];
                    
                    // El precio viene sin IGV, REDONDEADO A 2 DECIMALES PARA SUNAT
                    $valor_unitario = round($precio_unitario_sin_igv, 2);
                    $igv_rate = 0.18; // 18% por defecto
                    
                    // Precio unitario CON IGV (para SUNAT en AlternativeConditionPrice)
                    $precio_unitario_con_igv = round($valor_unitario * (1 + $igv_rate), 2);
                    
                    $valor_total_sin_igv = round($quantity_to_invoice * $valor_unitario, 2);
                    $igv_total_linea = round($valor_total_sin_igv * $igv_rate, 2);
                    $precio_total_con_igv = round($valor_total_sin_igv + $igv_total_linea, 2);

                    // Generar código interno del item
                    $codigoInternoItem = $invoice->codigo_interno . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                    // CREAR ITEM EN FACTURA
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'company_id' => $purchase->company_id,
                        'correlativo' => $item_data['correlativo'] ?? ($index + 1),
                        'codigo_interno' => $codigoInternoItem,
                        'tipo' => 'P', // Producto
                        'codigo_unidad_medida' => $purchase_item->unidad_medida,
                        'descripcion' => $purchase_item->descripcion,
                        'cantidad' => $quantity_to_invoice,
                        'monto_valor_unitario' => $valor_unitario, // Ya redondeado a 2 decimales
                        'monto_precio_unitario' => round($precio_unitario_con_igv, 2), // SUNAT requiere 2 decimales
                        'monto_valor_total' => $valor_total_sin_igv,
                        'codigo_indicador_afecto' => '10', // Afecto a IGV
                        'monto_igv' => $igv_total_linea,
                        'monto_total' => $precio_total_con_igv,
                    ]);

                    // ACTUALIZAR INVOICED_QUANTITY EN COMPRA
                    $purchase_item->increment('invoiced_quantity', $quantity_to_invoice);

                    $total_base += $valor_total_sin_igv;
                    $total_igv += $igv_total_linea;
                }

                // Actualizar montos en factura
                $invoice->update([
                    'monto_total_gravado' => $total_base,
                    'monto_total_igv' => $total_igv,
                    'monto_total' => round($total_base + $total_igv, 2),
                ]);

                // APLICAR RETENCIÓN (si cliente es agente retenedor)
                // SUNAT/Feasy valida la retención contra el total del comprobante, no contra el gravado sin IGV.
                $this->retentionService->apply($invoice, round($total_base + $total_igv, 2));
                $invoice->save();

                // ACTUALIZAR STATUS DE COMPRA
                $purchase->update_status_based_on_items();

                // MARCAR GUÍA COMO FACTURADA
                $guia->update([
                    'estado' => 'invoiced',
                    'invoice_id' => $invoice->id,
                ]);

                Log::info("Factura {$invoice->serie_documento} creada desde guía {$guia->numero}", [
                    'invoice_id' => $invoice->id,
                    'guia_id' => $guia->id,
                    'purchase_id' => $purchase->id,
                    'company_id' => $purchase->company_id,
                    'purchase_status' => $purchase->status,
                ]);

                return $invoice;
            });

            return $invoice->load('client', 'guia', 'clientAddress', 'items');
        } catch (\Exception $e) {
            Log::error("Error al crear factura desde guía {$guia->numero}: " . $e->getMessage(), [
                'guia_id' => $guia->id,
                'purchase_id' => $purchase->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Adjunta la GRE interna como referencia de guía para el payload Feasy de factura.
     */
    private function buildListaGuiasFromGuia(GuiaRemision $guia): ?array
    {
        $payload = is_array($guia->gre_payload) ? $guia->gre_payload : [];
        $documento = $payload['informacion_documento'] ?? [];

        $serie = $documento['serie_documento'] ?? null;
        $numero = $documento['numero_documento'] ?? null;

        if (! $serie || ! $numero) {
            return null;
        }

        return [[
            'codigo_tipo_documento' => $documento['codigo_tipo_documento'] ?? '09',
            'serie_documento' => $serie,
            'numero_documento' => $numero,
        ]];
    }

    /**
     * Obtiene sugerencias para crear una nueva factura.
     * Devuelve el próximo número de factura disponible.
     */
    public function getDocumentSuggestions(): array
    {
        $companyId = session('company_id');

        // CAST numérico evita ordenación lexicográfica incorrecta de VARCHAR
        // (sin esto '00000010' > '00000009' en string pero '10' > '9' en número)
        $lastNum = Invoice::where('company_id', $companyId)
            ->where('codigo_tipo_documento', '01')
            ->where('serie_documento', 'F001')
            ->max(\Illuminate\Support\Facades\DB::raw('CAST(numero_documento AS UNSIGNED)'));

        $nextNumber = ($lastNum ?? 0) + 1;

        return [
            'serie'  => 'F001',
            'numero' => str_pad($nextNumber, 8, '0', STR_PAD_LEFT),
        ];
    }
}

