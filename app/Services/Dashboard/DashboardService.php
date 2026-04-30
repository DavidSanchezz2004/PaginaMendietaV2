<?php

namespace App\Services\Dashboard;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\News;
use App\Models\Purchase;
use App\Models\Report;
use App\Models\Ticket;
use App\Models\User;

class DashboardService
{
    /**
     * Get the appropriate dashboard data based on user role
     */
    public function getDashboardData(User $user, int $year = 0): array
    {
        if ($year <= 0) $year = now()->year;

        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        $latestNews = News::published()->latest('published_at')->first();

        if (in_array($userRole, ['admin', 'supervisor'], true)) {
            $data = $this->getGlobalDashboard($user);
        } elseif ($userRole === 'auxiliar') {
            $data = $this->getAuxiliarDashboard($user);
        } else {
            $data = $this->getClientDashboard($user, $year);
        }

        return array_merge(['latestNews' => $latestNews, 'selectedYear' => $year], $data);
    }

    /**
     * Global panorama for Admin and Supervisor
     */
    private function getGlobalDashboard(User $user): array
    {
        $companyQuery = Company::query();
        $currentMonthInvoices = Invoice::whereYear('fecha_emision', now()->year)
            ->whereMonth('fecha_emision', now()->month);

        return [
            'metrics' => [
                'total_companies' => (clone $companyQuery)->count(),
                'active_companies' => (clone $companyQuery)->where('status', 'active')->count(),
                'facturador_enabled' => (clone $companyQuery)->where('facturador_enabled', true)->count(),
                'sunat_credentials' => (clone $companyQuery)
                    ->whereNotNull('usuario_sol')
                    ->whereNotNull('clave_sol')
                    ->count(),
                'active_users' => User::where('status', 'active')->count(),
                'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'total_reports' => Report::count(),
                'month_invoices' => (clone $currentMonthInvoices)->count(),
                'month_errors' => (clone $currentMonthInvoices)->where('estado', 'error')->count(),
                'month_accepted' => (clone $currentMonthInvoices)->whereIn('estado', ['sent', 'consulted'])->count(),
                'unread_reports' => 0,
            ],
            'recentTickets' => Ticket::with('company')->latest('updated_at')->take(5)->get(),
            'recentReports' => Report::with('company')->latest()->take(5)->get(),
            'financial' => null,
            'analytics'  => null,
        ];
    }

    /**
     * Auxiliar Dashboard: ONLY data from assigned companies
     */
    private function getAuxiliarDashboard(User $user): array
    {
        $assignedCompanyIds = $user->companies()
            ->wherePivot('status', 'active')
            ->pluck('companies.id')
            ->toArray();

        return [
            'metrics' => [
                'total_companies' => count($assignedCompanyIds),
                'open_tickets' => Ticket::whereIn('company_id', $assignedCompanyIds)
                                        ->whereIn('status', ['open', 'in_progress'])
                                        ->count(),
                'total_reports' => Report::whereIn('company_id', $assignedCompanyIds)->count(),
                'unread_reports' => 0,
            ],
            'financial' => null,
            'analytics'  => null,
            'recentTickets' => Ticket::with('company')
                                     ->whereIn('company_id', $assignedCompanyIds)
                                     ->latest('updated_at')
                                     ->take(5)
                                     ->get(),
            'recentReports' => Report::with('company')
                                     ->whereIn('company_id', $assignedCompanyIds)
                                     ->latest()
                                     ->take(5)
                                     ->get(),
        ];
    }

    /**
     * Client Dashboard: ONLY data from active company
     */
    private function getClientDashboard(User $user, int $year): array
    {
        $activeCompanyId = session('company_id') ?? $user->companies()->wherePivot('status', 'active')->first()?->id;

        $metrics = [
            'total_companies' => 0,
            'open_tickets' => 0,
            'total_reports' => 0,
            'unread_reports' => 0,
        ];

        $recentTickets = collect();
        $recentReports = collect();

        if ($activeCompanyId) {
            $metrics['open_tickets'] = Ticket::where('company_id', $activeCompanyId)
                                             ->whereIn('status', ['open', 'in_progress'])
                                             ->count();

            $companyReports = Report::where('company_id', $activeCompanyId)
                                    ->where('status', 'published')
                                    ->get();

            $metrics['total_reports'] = $companyReports->count();

            $readReportIds = $user->readReports()->pluck('reports.id')->toArray();
            $metrics['unread_reports'] = $companyReports->whereNotIn('id', $readReportIds)->count();

            $recentTickets = Ticket::where('company_id', $activeCompanyId)->latest('updated_at')->take(4)->get();
            $recentReports = Report::where('company_id', $activeCompanyId)->where('status', 'published')->latest()->take(4)->get();
        }

        return [
            'metrics' => $metrics,
            'recentTickets' => $recentTickets,
            'recentReports' => $recentReports,
            'financial' => $activeCompanyId ? $this->getFinancialSummary((int) $activeCompanyId) : null,
            'analytics'  => $activeCompanyId ? $this->getAnalytics((int) $activeCompanyId, $year) : null,
        ];
    }

    /**
     * Resumen financiero del mes actual.
     */
    private function getFinancialSummary(int $companyId): array
    {
        $year  = now()->year;
        $month = now()->month;

        $ingresos = (float) Invoice::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->whereMonth('fecha_emision', $month)
            ->whereNotIn('estado', ['voided', 'draft'])
            ->sum('monto_total');

        $gastos = (float) Purchase::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->whereMonth('fecha_emision', $month)
            ->sum('monto_total');

        $resultado = $ingresos - $gastos;

        $prevYear  = $month > 1 ? $year : $year - 1;
        $prevMonth = $month > 1 ? $month - 1 : 12;

        $mesAnteriorIngresos = (float) Invoice::where('company_id', $companyId)
            ->whereYear('fecha_emision', $prevYear)
            ->whereMonth('fecha_emision', $prevMonth)
            ->whereNotIn('estado', ['voided', 'draft'])
            ->sum('monto_total');

        $variacion = $mesAnteriorIngresos > 0
            ? round((($ingresos - $mesAnteriorIngresos) / $mesAnteriorIngresos) * 100, 1)
            : null;

        return [
            'ingresos'  => $ingresos,
            'gastos'    => $gastos,
            'resultado' => $resultado,
            'variacion' => $variacion,
            'mes'       => now()->locale('es')->translatedFormat('F Y'),
        ];
    }

    /**
     * Analítica avanzada tipo Power BI para el año seleccionado.
     */
    private function getAnalytics(int $companyId, int $year): array
    {
        $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        
        // Determinar el mes máximo a mostrar: si es el año actual, solo hasta el mes actual
        $maxMes = ($year === now()->year) ? now()->month : 12;

        // ── Ingresos y gastos por mes ─────────────────────────────────────
        $ingresosRaw = Invoice::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->whereNotIn('estado', ['voided', 'draft'])
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(monto_total) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $gastosRaw = Purchase::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(monto_total) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $ingresosMes = [];
        $gastosMes   = [];
        for ($m = 1; $m <= $maxMes; $m++) {
            $ingresosMes[] = round((float)($ingresosRaw[$m] ?? 0), 2);
            $gastosMes[]   = round((float)($gastosRaw[$m] ?? 0), 2);
        }

        // ── IGV pagado vs cobrado por mes ─────────────────────────────────
        $igvCobraRaw = Invoice::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->whereNotIn('estado', ['voided', 'draft'])
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(monto_total_igv) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $igvPagaRaw = Purchase::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->selectRaw('MONTH(fecha_emision) as mes, SUM(igv_gravadas) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $igvCobrado = [];
        $igvPagado  = [];
        for ($m = 1; $m <= $maxMes; $m++) {
            $igvCobrado[] = round((float)($igvCobraRaw[$m] ?? 0), 2);
            $igvPagado[]  = round((float)($igvPagaRaw[$m] ?? 0), 2);
        }

        // ── Top 5 proveedores por gasto ───────────────────────────────────
        $topProveedores = Purchase::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->selectRaw('razon_social_proveedor, SUM(monto_total) as total, COUNT(*) as cantidad')
            ->groupBy('razon_social_proveedor')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => ['nombre' => $r->razon_social_proveedor, 'total' => round($r->total, 2), 'cantidad' => $r->cantidad]);

        // ── Distribución gastos por tipo de comprobante ───────────────────
        $tipoMap = ['01'=>'Facturas','03'=>'Boletas','07'=>'N.Crédito','08'=>'N.Débito','00'=>'DUA'];
        $porTipo = Purchase::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->selectRaw('codigo_tipo_documento, SUM(monto_total) as total')
            ->groupBy('codigo_tipo_documento')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn($r) => [$tipoMap[$r->codigo_tipo_documento] ?? $r->codigo_tipo_documento => round($r->total, 2)]);

        // ── KPIs acumulados del año ───────────────────────────────────────
        $totalIngresosAnio = array_sum($ingresosMes);
        $totalGastosAnio   = array_sum($gastosMes);
        $margenAnio        = $totalIngresosAnio > 0
            ? round(($totalIngresosAnio - $totalGastosAnio) / $totalIngresosAnio * 100, 1)
            : 0;

        // Facturas emitidas y compras del año
        $totalFacturas = Invoice::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->whereNotIn('estado', ['voided', 'draft'])
            ->count();

        $totalCompras = Purchase::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->count();

        // Cuentas por cobrar (facturas crédito sin fecha vencimiento pasada)
        $cxc = (float) Invoice::where('company_id', $companyId)
            ->whereYear('fecha_emision', $year)
            ->whereNotIn('estado', ['voided', 'draft'])
            ->where('forma_pago', '2')
            ->sum('monto_total');

        $ventasUltimosSeisMeses = collect(range(5, 0))->map(function (int $monthsBack) use ($companyId): array {
            $month = now()->subMonthsNoOverflow($monthsBack)->startOfMonth();
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();

            return [
                'label' => $month->locale('es')->translatedFormat('M Y'),
                'month' => $month->format('Y-m'),
                'total' => round((float) Invoice::where('company_id', $companyId)
                    ->whereBetween('fecha_emision', [$start, $end])
                    ->where('estado', '!=', 'voided')
                    ->sum('monto_total'), 2),
                'count' => Invoice::where('company_id', $companyId)
                    ->whereBetween('fecha_emision', [$start, $end])
                    ->where('estado', '!=', 'voided')
                    ->count(),
            ];
        })->values();

        $estadoMesActual = Invoice::where('company_id', $companyId)
            ->whereYear('fecha_emision', now()->year)
            ->whereMonth('fecha_emision', now()->month)
            ->selectRaw("
                SUM(CASE WHEN estado IN ('sent', 'consulted') THEN 1 ELSE 0 END) as aceptados,
                SUM(CASE WHEN estado IN ('draft', 'ready') THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'error' THEN 1 ELSE 0 END) as errores,
                SUM(CASE WHEN estado = 'voided' THEN 1 ELSE 0 END) as anulados
            ")
            ->first();

        // Años disponibles para filtro
        $aniosIngresos = Invoice::where('company_id', $companyId)->selectRaw('YEAR(fecha_emision) as y')->distinct()->pluck('y');
        $aniosGastos   = Purchase::where('company_id', $companyId)->selectRaw('YEAR(fecha_emision) as y')->distinct()->pluck('y');
        $aniosDisponibles = $aniosIngresos->merge($aniosGastos)->unique()->sort()->values()->toArray();
        if (!in_array($year, $aniosDisponibles)) $aniosDisponibles[] = $year;
        sort($aniosDisponibles);

        // Truncar labels a solo los meses mostrados
        $labelsFinales = array_slice($meses, 0, $maxMes);

        return [
            'labels'            => $labelsFinales,
            'ingresos_mes'      => $ingresosMes,
            'gastos_mes'        => $gastosMes,
            'igv_cobrado'       => $igvCobrado,
            'igv_pagado'        => $igvPagado,
            'top_proveedores'   => $topProveedores,
            'por_tipo'          => $porTipo,
            'total_ingresos'    => $totalIngresosAnio,
            'total_gastos'      => $totalGastosAnio,
            'margen'            => $margenAnio,
            'total_facturas'    => $totalFacturas,
            'total_compras'     => $totalCompras,
            'cxc'               => $cxc,
            'ventas_ultimos_6_meses' => $ventasUltimosSeisMeses,
            'ventas_ultimos_6_meses_max' => max(1, (float) $ventasUltimosSeisMeses->max('total')),
            'estado_mes_actual' => [
                'aceptados' => (int) ($estadoMesActual->aceptados ?? 0),
                'pendientes' => (int) ($estadoMesActual->pendientes ?? 0),
                'errores' => (int) ($estadoMesActual->errores ?? 0),
                'anulados' => (int) ($estadoMesActual->anulados ?? 0),
            ],
            'anios'             => $aniosDisponibles,
        ];
    }
}
