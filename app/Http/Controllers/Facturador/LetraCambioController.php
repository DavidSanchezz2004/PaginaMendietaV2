<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\LetraCambio;
use App\Models\Provider;
use App\Models\Purchase;
use App\Services\Facturador\LetterCompensationService;
use App\Services\Facturador\LetraCambioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LetraCambioController extends Controller
{
    public function __construct(
        private readonly LetraCambioService $letraService,
        private readonly LetterCompensationService $letterCompensationService,
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
            'monto_pagado'    => 'required|numeric|min:0.01|max:' . $letraCambio->saldo,
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

    public function compensationCandidates(Request $request, LetraCambio $letraCambio): JsonResponse
    {
        $this->ensureOwnership($letraCambio);

        $validated = $request->validate([
            'provider_id' => 'required|integer|exists:providers,id',
        ]);

        $provider = Provider::forActiveCompany()->findOrFail($validated['provider_id']);

        $purchases = Purchase::forActiveCompany()
            ->where('provider_id', $provider->id)
            ->where('codigo_moneda', $letraCambio->codigo_moneda)
            ->orderBy('fecha_emision')
            ->get()
            ->filter(fn (Purchase $purchase) => $purchase->saldo_pendiente_pago > 0)
            ->values()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'serie_numero' => $purchase->serie_numero,
                'fecha_emision' => $purchase->fecha_emision?->format('Y-m-d'),
                'moneda' => $purchase->codigo_moneda,
                'total' => $purchase->monto_pagable,
                'saldo' => $purchase->saldo_pendiente_pago,
                'estado' => $purchase->estado_pago_label,
            ]);

        return response()->json([
            'ok' => true,
            'provider' => [
                'id' => $provider->id,
                'name' => $provider->nombre_display,
            ],
            'purchases' => $purchases,
        ]);
    }

    public function compensationSuppliers(LetraCambio $letraCambio): JsonResponse
    {
        $this->ensureOwnership($letraCambio);

        $suppliers = Purchase::forActiveCompany()
            ->with('provider')
            ->whereNotNull('provider_id')
            ->where('codigo_moneda', $letraCambio->codigo_moneda)
            ->orderBy('razon_social_proveedor')
            ->get()
            ->filter(fn (Purchase $purchase) => $purchase->saldo_pendiente_pago > 0 && $purchase->provider !== null)
            ->groupBy('provider_id')
            ->map(function ($purchases) {
                /** @var \App\Models\Purchase $first */
                $first = $purchases->first();

                return [
                    'id' => $first->provider_id,
                    'name' => $first->provider?->nombre_display ?? $first->razon_social_proveedor,
                    'document' => $first->provider?->numero_documento ?? $first->numero_doc_proveedor,
                    'pending_invoices' => $purchases->count(),
                    'pending_balance' => round($purchases->sum('saldo_pendiente_pago'), 2),
                    'currency' => $first->codigo_moneda,
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json([
            'ok' => true,
            'suppliers' => $suppliers,
        ]);
    }

    public function compensate(Request $request, LetraCambio $letraCambio): JsonResponse
    {
        $this->ensureOwnership($letraCambio);

        $validated = $request->validate([
            'compensation_date' => 'required|date',
            'supplier_id' => 'required|integer|exists:providers,id',
            'details' => 'required|array|min:1',
            'details.*.purchase_invoice_id' => 'required|integer|exists:purchases,id',
            'details.*.amount' => 'required|numeric|min:0.01',
            'observation' => 'nullable|string|max:500',
        ]);

        try {
            $compensation = $this->letterCompensationService->compensate($letraCambio, $validated);
            $letraCambio->refresh();

            return response()->json([
                'ok' => true,
                'message' => 'Compensación registrada correctamente.',
                'compensation_id' => $compensation->id,
                'saldo' => $letraCambio->saldo,
                'monto_pagado' => $letraCambio->monto_pagado,
                'estado' => $letraCambio->estado,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function ensureOwnership(LetraCambio $letra): void
    {
        if ((int) $letra->company_id !== (int) session('company_id')) {
            abort(403, 'Esta letra pertenece a otra empresa.');
        }
    }
}
