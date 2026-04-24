<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\LetraCambio;
use App\Services\Facturador\LetraService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestión de Letras de Cambio.
 * 
 * Solo para letras de VENTA (por cobrar).
 * Las letras por pagar (compras) se gestionan en compras.
 */
class LetraController extends Controller
{
    public function __construct(
        private readonly LetraService $letraService,
    ) {}

    /**
     * GET /facturador/letras
     * Listado paginado de letras por cobrar (de ventas).
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $this->authorize('viewAny', LetraCambio::class);

        $perPage = $request->input('per_page', 15);
        $filters = [
            'search'  => $request->input('search'),
            'estado'  => $request->input('estado'),
            'from'    => $request->input('from'),
            'to'      => $request->input('to'),
        ];

        $letras = $this->getLetrasQuery($filters)
            ->paginate($perPage);

        // Calcular estadísticas
        $allLetras = $this->getLetrasQuery([])->get();
        $thisMonth = $allLetras->where('estado', 'cobrado')
            ->filter(fn($l) => now()->isSameMonth($l->updated_at ?? $l->created_at));
        
        $stats = [
            'total_pendiente'   => $allLetras->where('estado', 'pendiente')->sum('monto'),
            'count_pendiente'   => $allLetras->where('estado', 'pendiente')->count(),
            'count_vencidas'    => $allLetras->where('estado', 'vencida')->count(),
            'total_cobrado_mes' => $thisMonth->sum('monto'),
            'count_cobrado'     => $allLetras->where('estado', 'cobrado')->count(),
        ];

        return view('facturador.letras.index', compact('letras', 'filters', 'stats'));
    }

    /**
     * GET /facturador/letras/{letra}
     * Detalle de una letra con historial de pagos.
     */
    public function show(LetraCambio $letra): \Illuminate\View\View
    {
        $this->authorize('view', $letra);

        // Verificar que sea letra de venta (invoice_id != null)
        if (!$letra->invoice_id) {
            abort(403, 'Esta letra es de compra, no de venta.');
        }

        $pagos = $letra->pagos()->orderBy('fecha_pago', 'desc')->get();

        return view('facturador.letras.show', compact('letra', 'pagos'));
    }

    /**
     * POST /facturador/letras/{letra}/mark-paid (AJAX)
     * Registrar pago de una letra.
     */
    public function markPaid(Request $request, LetraCambio $letra)
    {
        $this->authorize('update', $letra);

        $validated = $request->validate([
            'monto'      => 'nullable|numeric|min:0|max:' . $letra->saldo,
            'medio_pago' => 'in:transferencia,efectivo,cheque,yape|default:transferencia',
            'referencia' => 'nullable|string|max:100',
        ]);

        try {
            $this->letraService->markAsPaid(
                $letra,
                monto: (float) ($validated['monto'] ?? $letra->saldo),
                medio: $validated['medio_pago'],
                referencia: $validated['referencia'] ?? null
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago registrado correctamente.',
                    'letra'   => [
                        'id'           => $letra->id,
                        'estado'       => $letra->fresh()->estado,
                        'saldo'        => $letra->fresh()->saldo,
                        'monto_pagado' => $letra->fresh()->monto_pagado,
                    ],
                ]);
            }

            return back()->with('success', 'Pago registrado correctamente.');
        } catch (\Exception $e) {
            Log::error('[LetraController] Error al registrar pago', [
                'letra_id' => $letra->id,
                'error'    => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar pago: ' . $e->getMessage(),
                ], 400);
            }

            return back()->with('error', 'Error al registrar pago: ' . $e->getMessage());
        }
    }

    /**
     * DELETE /facturador/letras/{letra} (AJAX)
     * Eliminar una letra (solo si está en estado pendiente y sin pagos).
     */
    public function destroy(LetraCambio $letra)
    {
        $this->authorize('delete', $letra);

        if ($letra->estado !== 'pendiente' || $letra->monto_pagado > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una letra con pagos registrados o que no esté pendiente.',
            ], 403);
        }

        $letra->delete();

        return response()->json([
            'success' => true,
            'message' => 'Letra eliminada correctamente.',
        ]);
    }

    /**
     * GET /facturador/letras/export-pdf (AJAX)
     * Descargar PDF de letra para presentarla a banco.
     */
    public function exportPdf(LetraCambio $letra)
    {
        $this->authorize('view', $letra);

        // TODO: Implementar generación de PDF con DOMPDF
        // Por ahora, retornar placeholder
        return response()->json([
            'success' => false,
            'message' => 'PDF export no implementado aún.',
        ], 501);
    }

    // ──────────────────────────────────────────────────────────────────

    /**
     * Construir query base de letras con filtros.
     */
    private function getLetrasQuery(array $filtros): \Illuminate\Database\Eloquent\Builder
    {
        $query = LetraCambio::where('company_id', session('company_id'))
            ->whereNotNull('invoice_id') // Solo letras de venta
            ->with(['invoice', 'company', 'pagos']);

        // Filtro por búsqueda
        if (!empty($filtros['search'])) {
            $search = '%' . $filtros['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('numero_letra', 'like', $search)
                  ->orWhere('referencia', 'like', $search)
                  ->orWhere('aceptante_nombre', 'like', $search)
                  ->orWhereHas('invoice', fn ($q) => $q->where('serie_numero', 'like', $search));
            });
        }

        // Filtro por estado
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        // Filtro por rango de fechas de vencimiento
        if (!empty($filtros['from'])) {
            $query->where('fecha_vencimiento', '>=', $filtros['from']);
        }
        if (!empty($filtros['to'])) {
            $query->where('fecha_vencimiento', '<=', $filtros['to']);
        }

        return $query->orderBy('fecha_vencimiento', 'asc');
    }
}
