<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\LetraCambio;
use App\Models\Purchase;
use App\Services\Facturador\LetraCambioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetraCambioController extends Controller
{
    public function __construct(
        private readonly LetraCambioService $letraService,
    ) {}

    // ── Lista ─────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $filters = $request->only(['estado', 'from', 'to', 'search']);

        $query = LetraCambio::forActiveCompany()
            ->with(['purchase', 'purchase.provider'])
            ->orderByDesc('purchase_id')
            ->orderBy('fecha_vencimiento');

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['from'])) {
            $query->where('fecha_vencimiento', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('fecha_vencimiento', '<=', $filters['to']);
        }

        if (! empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($s) {
                $q->where('numero_letra', 'like', $s)
                  ->orWhere('aceptante_nombre', 'like', $s)
                  ->orWhere('aceptante_ruc', 'like', $s);
            });
        }

        $letras = $query->paginate(20)->withQueryString();

        $stats = [
            'total_pendiente'  => LetraCambio::forActiveCompany()->where('estado', 'pendiente')->sum('monto'),
            'count_pendiente'  => LetraCambio::forActiveCompany()->where('estado', 'pendiente')->count(),
            'count_vencidas'   => LetraCambio::forActiveCompany()->vencidas()->count(),
            'total_cobrado_mes'=> LetraCambio::forActiveCompany()->where('estado', 'cobrado')
                ->whereYear('updated_at', now()->year)
                ->whereMonth('updated_at', now()->month)
                ->sum('monto'),
        ];

        return view('facturador.letras.index', compact('letras', 'filters', 'stats'));
    }

    // ── Detalle / impresión ───────────────────────────────────────────────

    public function show(LetraCambio $letraCambio): View
    {
        $this->ensureOwnership($letraCambio);
        $letraCambio->load(['purchase', 'pagos.user', 'user']);

        return view('facturador.letras.show', ['letra' => $letraCambio]);
    }

    public function imprimir(LetraCambio $letraCambio): View
    {
        $this->ensureOwnership($letraCambio);
        $letraCambio->load('purchase');

        return view('facturador.letras.imprimir', ['letra' => $letraCambio]);
    }

    // ── Canje desde compra (web) ──────────────────────────────────────────

    /**
     * Formulario de canje: muestra la compra y permite configurar cuotas.
     */
    public function canjeForm(Purchase $purchase): View
    {
        if ((int) $purchase->company_id !== (int) session('company_id')) {
            abort(403);
        }

        $purchase->load(['provider', 'letras']);

        return view('facturador.letras.canje', compact('purchase'));
    }

    /**
     * Procesa el canje y genera las letras.
     */
    public function canjear(Request $request, Purchase $purchase): RedirectResponse
    {
        if ((int) $purchase->company_id !== (int) session('company_id')) {
            abort(403);
        }

        $validated = $request->validate([
            'cuotas'             => 'required|array|min:1|max:36',
            'cuotas.*.dias'      => 'required|integer|min:1|max:1080',
            'cuotas.*.porcentaje'=> 'required|numeric|min:0.01|max:100',
            'lugar_giro'         => 'nullable|string|max:100',
            'banco'              => 'nullable|string|max:100',
            'banco_cuenta'       => 'nullable|string|max:50',
        ]);

        $letras = $this->letraService->canjear(
            $purchase,
            $validated['cuotas'],
            $validated
        );

        return redirect()
            ->route('facturador.letras.index')
            ->with('success', "Se generaron {$letras->count()} letra(s) por un total de {$purchase->codigo_moneda} " . number_format($letras->sum('monto'), 2) . '.');
    }

    // ── Registrar pago ────────────────────────────────────────────────────

    public function registrarPago(Request $request, LetraCambio $letraCambio): JsonResponse
    {
        $this->ensureOwnership($letraCambio);

        $validated = $request->validate([
            'fecha_pago'      => 'required|date',
            'monto_pagado'    => 'required|numeric|min:0.01',
            'medio_pago'      => 'required|in:efectivo,transferencia,cheque,yape,plin',
            'referencia_pago' => 'nullable|string|max:100',
            'observaciones'   => 'nullable|string|max:500',
        ]);

        $pago = $this->letraService->registrarPago($letraCambio, $validated);
        $letraCambio->refresh();

        return response()->json([
            'ok'          => true,
            'saldo'        => $letraCambio->saldo,
            'monto_pagado' => $letraCambio->monto_pagado,
            'estado'       => $letraCambio->estado,
            'pago_id'      => $pago->id,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function ensureOwnership(LetraCambio $letra): void
    {
        if ((int) $letra->company_id !== (int) session('company_id')) {
            abort(403, 'Esta letra pertenece a otra empresa.');
        }
    }
}
