<?php

namespace App\Services\Facturador;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

/**
 * Servicio de gestión de cotizaciones (quotes).
 * 
 * Funcionalidades:
 * - Crear cotizaciones con versionado automático
 * - Generar URL pública para compartir
 * - Convertir a facturas
 * - Recalcular totales
 */
class QuoteService
{
    /**
     * Crea una nueva cotización.
     * 
     * @param int $companyId
     * @param int $userId
     * @param int|null $clientId
     * @param array $data Datos básicos (fecha_emision, observacion, etc.)
     * @param array $items Items con estructura [{descripcion, cantidad, monto_valor_unitario, ...}]
     * @return Quote
     * 
     * @throws \Exception
     */
    public function create(
        int $companyId,
        int $userId,
        ?int $clientId,
        array $data,
        array $items = []
    ): Quote {
        // Generar número de cotización único por empresa
        $lastNumber = Quote::where('company_id', $companyId)
            ->max('numero_cotizacion') ?? 0;
        $numero = str_pad((int) $lastNumber + 1, 8, '0', STR_PAD_LEFT);
        $codigoInterno = "01C{$numero}";

        // Crear cotización
        $quote = new Quote();
        $quote->company_id = $companyId;
        $quote->user_id = $userId;
        $quote->client_id = $clientId;
        $quote->numero_cotizacion = $numero;
        $quote->codigo_interno = $codigoInterno;
        $quote->share_token = (string) Str::uuid();
        $quote->version = 1;

        // Asignar datos
        foreach ($data as $key => $value) {
            if (in_array($key, $quote->fillable)) {
                $quote->$key = $value;
            }
        }

        // Guardar
        $quote->save();

        // Agregar items
        if (!empty($items)) {
            $this->addItems($quote, $items);
        }

        // Recalcular totales
        $this->recalculate($quote);

        return $quote->fresh('items');
    }

    /**
     * Crea una nueva versión de una cotización existente.
     * 
     * @param Quote $original Cotización original
     * @param array $data Datos modificados
     * @param array $items Items modificados (o null para copiar originales)
     * @return Quote Nueva versión
     */
    public function createVersion(
        Quote $original,
        array $data = [],
        ?array $items = null
    ): Quote {
        // Crear cotización con los mismos datos base pero nueva versión
        $newData = $original->only([
            'fecha_emision',
            'fecha_vencimiento',
            'codigo_moneda',
            'porcentaje_igv',
            'observacion',
            'correo',
            'numero_orden_compra',
        ]);

        // Mergear cambios
        $newData = array_merge($newData, $data);

        $version = new Quote();
        $version->company_id = $original->company_id;
        $version->user_id = auth()->id();
        $version->client_id = $original->client_id;
        $version->numero_cotizacion = $original->numero_cotizacion;
        $version->codigo_interno = $original->codigo_interno;
        $version->share_token = (string) Str::uuid(); // Nuevo token para nueva versión
        $version->version = $original->version + 1;

        foreach ($newData as $key => $value) {
            if (in_array($key, $version->fillable)) {
                $version->$key = $value;
            }
        }

        $version->save();

        // Agregar items (copiar originales o usar nuevos)
        $itemsToAdd = $items ?? $original->items->toArray();
        if (!empty($itemsToAdd)) {
            $this->addItems($version, $itemsToAdd);
        }

        // Recalcular
        $this->recalculate($version);

        return $version->fresh('items');
    }

    /**
     * Agrega items a una cotización.
     * 
     * @param Quote $quote
     * @param array $items
     */
    public function addItems(Quote $quote, array $items): void
    {
        foreach ($items as $item) {
            $quoteItem = new QuoteItem();
            $quoteItem->quote_id = $quote->id;
            $quoteItem->company_id = $quote->company_id;

            foreach ($item as $key => $value) {
                if (in_array($key, $quoteItem->fillable)) {
                    $quoteItem->$key = $value;
                }
            }

            // Calcular montos si no vienen
            if (empty($item['monto_valor_total'])) {
                $quoteItem->monto_valor_total = round(
                    ($quoteItem->cantidad ?? 0) * ($quoteItem->monto_valor_unitario ?? 0),
                    2
                );
            }

            if (empty($item['monto_igv']) && $quoteItem->codigo_indicador_afecto === '10') {
                $quoteItem->monto_igv = round(
                    $quoteItem->monto_valor_total * ($quote->porcentaje_igv / 100),
                    2
                );
            }

            if (empty($item['monto_total'])) {
                $quoteItem->monto_total = round(
                    $quoteItem->monto_valor_total + ($quoteItem->monto_igv ?? 0),
                    2
                );
            }

            $quoteItem->save();
        }
    }

    /**
     * Recalcula los totales de una cotización basado en sus items.
     */
    public function recalculate(Quote $quote): void
    {
        $quote->load('items');

        $totalGravado = 0;
        $totalIgv = 0;
        $totalDescuento = 0;
        $totalFinal = 0;

        foreach ($quote->items as $item) {
            $totalGravado += $item->monto_valor_total ?? 0;
            $totalIgv += $item->monto_igv ?? 0;
            $totalDescuento += $item->monto_descuento ?? 0;
            $totalFinal += $item->monto_total ?? 0;
        }

        $quote->monto_total_gravado = round($totalGravado, 2);
        $quote->monto_total_igv = round($totalIgv, 2);
        $quote->monto_total_descuento = round($totalDescuento, 2);
        $quote->monto_total = round($totalFinal, 2);

        $quote->save();
    }

    /**
     * Marca una cotización como enviada.
     */
    public function markAsSent(Quote $quote): void
    {
        $quote->estado = 'sent';
        $quote->sent_at = now();
        $quote->save();
    }

    /**
     * Marca como aceptada.
     */
    public function accept(Quote $quote): void
    {
        $quote->estado = 'accepted';
        $quote->accepted_at = now();
        $quote->save();
    }

    /**
     * Marca como rechazada.
     */
    public function reject(Quote $quote): void
    {
        $quote->estado = 'rejected';
        $quote->rejected_at = now();
        $quote->save();
    }

    /**
     * Convierte una cotización aceptada en factura.
     * 
     * @param Quote $quote
     * @param array $overrideData Campos a sobrescribir en la factura
     * @return Invoice Factura creada
     * 
     * @throws \Exception Si la cotización no puede convertirse
     */
    public function convertToInvoice(Quote $quote, array $overrideData = []): Invoice
    {
        if (!$quote->canBeConvertedToInvoice()) {
            throw new \Exception(
                'La cotización no puede convertirse a factura. ' .
                'Debe estar aceptada y sin factura asociada.'
            );
        }

        // Datos base de la cotización
        $invoiceData = [
            'company_id' => $quote->company_id,
            'user_id' => auth()->id(),
            'client_id' => $quote->client_id,
            'codigo_interno' => $quote->codigo_interno,
            'fecha_emision' => $quote->fecha_emision,
            'fecha_vencimiento' => $quote->fecha_vencimiento,
            'codigo_tipo_documento' => '01', // Factura por defecto
            'series_documento' => 'F001',
            'numero_documento' => '00000001', // Se debe generar secuencia real
            'estado' => 'draft',
            'codigo_moneda' => $quote->codigo_moneda,
            'porcentaje_igv' => $quote->porcentaje_igv,
            'monto_tipo_cambio' => $quote->monto_tipo_cambio,
            'monto_total_gravado' => $quote->monto_total_gravado,
            'monto_total_igv' => $quote->monto_total_igv,
            'monto_total' => $quote->monto_total,
            'forma_pago' => '01', // Contado por defecto
            'observacion' => $quote->observacion,
            'correo' => $quote->correo,
            'numero_orden_compra' => $quote->numero_orden_compra,
        ];

        // Sobrescribir con datos proporcionados
        $invoiceData = array_merge($invoiceData, $overrideData);

        // Crear factura
        $invoice = new Invoice();
        foreach ($invoiceData as $key => $value) {
            if (in_array($key, $invoice->fillable)) {
                $invoice->$key = $value;
            }
        }
        $invoice->save();

        // Copiar items
        foreach ($quote->items as $quoteItem) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->company_id = $quote->company_id;
            $invoiceItem->descripcion = $quoteItem->descripcion;
            $invoiceItem->cantidad = $quoteItem->cantidad;
            $invoiceItem->monto_valor_unitario = $quoteItem->monto_valor_unitario;
            $invoiceItem->codigo_unidad_medida = $quoteItem->codigo_unidad_medida;
            $invoiceItem->monto_valor_total = $quoteItem->monto_valor_total;
            $invoiceItem->monto_igv = $quoteItem->monto_igv;
            $invoiceItem->monto_total = $quoteItem->monto_total;
            $invoiceItem->save();
        }

        // Vincular cotización a factura
        $quote->invoice_id = $invoice->id;
        $quote->save();

        return $invoice->fresh('items');
    }
}
