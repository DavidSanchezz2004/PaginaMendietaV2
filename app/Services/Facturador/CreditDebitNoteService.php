<?php

namespace App\Services\Facturador;

use App\Models\CreditDebitNote;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CreditDebitNoteService
{
    /**
     * Crear una nota de crédito o débito.
     *
     * @param array $validated Datos validados del request
     * @param array $items     Items de la nota
     * @return CreditDebitNote
     */
    public function create(array $validated, array $items): CreditDebitNote
    {
        // Obtener factura original
        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // Calcular totales basados en los items
        $totales = $this->calcularTotales($items, $validated['porcentaje_igv'] ?? 18);

        // Preparar documento referencia
        $docRef = [
            'codigo_tipo_documento_referencia' => $invoice->codigo_tipo_documento,
            'serie_documento_referencia' => $invoice->serie_documento,
            'numero_documento_referencia' => $invoice->numero_documento,
        ];

        // Crear la nota
        $note = CreditDebitNote::create([
            'company_id' => session('company_id'),
            'user_id' => $validated['user_id'],
            'invoice_id' => $invoice->id,
            'codigo_tipo_documento' => $validated['codigo_tipo_documento'],
            'codigo_tipo_nota' => $validated['codigo_tipo_nota'],
            'serie_documento' => $validated['serie_documento'],
            'numero_documento' => $validated['numero_documento'],
            'codigo_interno' => $validated['codigo_interno'],
            'fecha_emision' => $validated['fecha_emision'],
            'hora_emision' => $validated['hora_emision'] ?? '00:00:00',
            'observacion' => $validated['observacion'] ?? null,
            'correo' => $validated['correo'] ?? null,
            'monto_total_gravado' => $totales['monto_total_gravado'],
            'monto_total_inafecto' => $totales['monto_total_inafecto'],
            'monto_total_exonerado' => $totales['monto_total_exonerado'],
            'monto_total_igv' => $totales['monto_total_igv'],
            'monto_total' => $totales['monto_total'],
            'porcentaje_igv' => $validated['porcentaje_igv'] ?? 18,
            'lista_items' => $items,
            'informacion_documento_referencia' => $docRef,
            'estado' => 'draft',
        ]);

        Log::channel('stack')->info('[CreditDebitNoteService] Nota creada', [
            'note_id' => $note->id,
            'tipo' => $note->getTypeLabel(),
            'invoice_id' => $invoice->id,
        ]);

        return $note;
    }

    /**
     * Calcular totales de la nota basados en los items.
     */
    private function calcularTotales(array $items, float $igvRate): array
    {
        $igvRate = $igvRate / 100;
        $totales = [
            'monto_total_gravado' => 0,
            'monto_total_inafecto' => 0,
            'monto_total_exonerado' => 0,
            'monto_total_igv' => 0,
            'monto_total' => 0,
        ];

        foreach ($items as $item) {
            $monto = (float) ($item['monto_total'] ?? 0);
            $afecto = $item['codigo_indicador_afecto'] ?? '10';

            if ($afecto === '10') {
                // Gravado
                $totales['monto_total_gravado'] += $monto;
                $valor = $monto / (1 + $igvRate);
                $igv = $monto - $valor;
                $totales['monto_total_igv'] += $igv;
            } elseif ($afecto === '30') {
                // Inafecto
                $totales['monto_total_inafecto'] += $monto;
            } elseif ($afecto === '40') {
                // Exonerado
                $totales['monto_total_exonerado'] += $monto;
            }

            $totales['monto_total'] += $monto;
        }

        // Redondear a 2 decimales
        foreach ($totales as &$valor) {
            $valor = round($valor, 2);
        }

        return $totales;
    }

    /**
     * Obtener listado paginado de notas para la empresa activa.
     */
    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = CreditDebitNote::forActiveCompany();

        // Filtros
        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['tipo'])) {
            $query->where('codigo_tipo_documento', $filters['tipo']);
        }

        if (! empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $query->where(function ($q) use ($search) {
                $q->where('serie_documento', 'like', $search)
                  ->orWhere('numero_documento', 'like', $search)
                  ->orWhere('codigo_interno', 'like', $search);
            });
        }

        return $query->orderBy('fecha_emision', 'desc')->paginate($perPage);
    }

    /**
     * Obtener sugerencia para próximas notas (serie y número).
     */
    public function getDocumentSuggestions(): array
    {
        $company = session('company_id');

        $last = CreditDebitNote::where('company_id', $company)
            ->orderBy('numero_documento', 'desc')
            ->first();

        $serie = $last?->serie_documento ?? 'C001';
        $numero = $last ? ((int) $last->numero_documento + 1) : 1;

        return [
            'serie' => $serie,
            'numero' => str_pad($numero, 8, '0', STR_PAD_LEFT),
            'codigo_interno' => "07C00100000{$numero}",
        ];
    }
}
