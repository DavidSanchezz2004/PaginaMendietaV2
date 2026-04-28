<?php

namespace App\Http\Controllers\Facturador;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminIssuedInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->user()?->role instanceof RoleEnum
            ? $request->user()->role->value
            : (string) $request->user()?->role;

        abort_if(! in_array($role, ['admin', 'supervisor'], true), 403);

        $filters = [
            'month'      => $this->validMonth((string) $request->input('month')) ?? now()->format('Y-m'),
            'company_id' => $request->input('company_id'),
            'estado'     => $request->input('estado'),
            'tipo'       => $request->input('tipo'),
            'search'     => trim((string) $request->input('search')),
        ];

        $period = Carbon::createFromFormat('Y-m', $filters['month'])->startOfMonth();
        $periodStart = $period->toDateString();
        $periodEnd = $period->copy()->endOfMonth()->toDateString();

        $baseQuery = Invoice::query()
            ->with(['company', 'client'])
            ->whereBetween('fecha_emision', [$periodStart, $periodEnd]);

        if (! empty($filters['company_id'])) {
            $baseQuery->where('company_id', (int) $filters['company_id']);
        }

        if (! empty($filters['estado'])) {
            $baseQuery->where('estado', $filters['estado']);
        }

        if (! empty($filters['tipo'])) {
            $baseQuery->where('codigo_tipo_documento', $filters['tipo']);
        }

        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $baseQuery->where(function ($query) use ($search): void {
                $query->where('serie_documento', 'like', $search)
                    ->orWhere('numero_documento', 'like', $search)
                    ->orWhereRaw("CONCAT(serie_documento, '-', numero_documento) LIKE ?", [$search])
                    ->orWhereHas('client', function ($clientQuery) use ($search): void {
                        $clientQuery->where('nombre_razon_social', 'like', $search)
                            ->orWhere('numero_documento', 'like', $search);
                    })
                    ->orWhereHas('company', function ($companyQuery) use ($search): void {
                        $companyQuery->where('name', 'like', $search)
                            ->orWhere('razon_social', 'like', $search)
                            ->orWhere('ruc', 'like', $search);
                    });
            });
        }

        $invoices = (clone $baseQuery)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate(25)
            ->appends($request->query());

        $quotaLimit = (int) config('facturador.monthly_document_limit', 500);
        $quotaUsed = Invoice::query()
            ->whereBetween('fecha_emision', [$periodStart, $periodEnd])
            ->whereIn('estado', ['sent', 'consulted'])
            ->count();

        $stats = [
            'period'            => $period->format('Y-m'),
            'period_label'      => $period->locale('es')->translatedFormat('F Y'),
            'quota_limit'       => $quotaLimit,
            'quota_used'        => $quotaUsed,
            'quota_remaining'   => max($quotaLimit - $quotaUsed, 0),
            'quota_percent'     => $quotaLimit > 0 ? min(round(($quotaUsed / $quotaLimit) * 100, 1), 100) : 0,
            'total_period'      => (clone $baseQuery)->count(),
            'issued_period'     => (clone $baseQuery)->whereIn('estado', ['sent', 'consulted'])->count(),
            'voided_period'     => (clone $baseQuery)->where('estado', 'voided')->count(),
            'amounts_by_currency' => (clone $baseQuery)
                ->where('estado', '!=', 'voided')
                ->selectRaw('codigo_moneda, SUM(monto_total) as total')
                ->groupBy('codigo_moneda')
                ->orderBy('codigo_moneda')
                ->pluck('total', 'codigo_moneda')
                ->map(fn ($total) => (float) $total)
                ->toArray(),
        ];

        $companies = Company::query()
            ->where('facturador_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'razon_social', 'ruc']);

        return view('facturador.admin-issued-invoices.index', compact('invoices', 'filters', 'stats', 'companies'));
    }

    private function validMonth(string $month): ?string
    {
        return preg_match('/^\d{4}-\d{2}$/', $month) ? $month : null;
    }
}
