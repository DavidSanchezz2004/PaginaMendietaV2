<?php

namespace App\Http\Controllers\Obligaciones;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ObligationDeclaration;
use App\Services\Company\CompanyManagementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CronogramaController extends Controller
{
    /**
     * Mapa último dígito RUC → día de vencimiento.
     */
    private const DIGIT_MAP = [
        '0' => 16,
        '1' => 17,
        '2' => 18,
        '3' => 18,
        '4' => 19,
        '5' => 19,
        '6' => 20,
        '7' => 20,
        '8' => 23,
        '9' => 23,
    ];

    public function index(Request $request, CompanyManagementService $companyManagementService): View
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        abort_if($userRole === 'client', 403);

        // Período seleccionado (default: mes y año actuales)
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);
        $month = max(1, min(12, $month));

        // Filtros adicionales
        $filterSearch = (string) $request->get('q', '');
        $filterStatus = (string) $request->get('status', '');

        // Empresas visibles según rol (reutiliza la lógica existente)
        $companies = $companyManagementService->listUserCompanies($user);

        // Aplicar filtro de búsqueda
        if ($filterSearch !== '') {
            $companies = $companies->filter(fn ($c) =>
                str_contains(mb_strtolower($c->name), mb_strtolower($filterSearch)) ||
                str_contains($c->ruc, $filterSearch)
            );
        }

        // Cargar declaraciones ya confirmadas para el período
        $companyIds    = $companies->pluck('id');
        $declarations  = ObligationDeclaration::query()
            ->whereIn('company_id', $companyIds)
            ->where('period_year',  $year)
            ->where('period_month', $month)
            ->with('declaredByUser')
            ->get()
            ->keyBy('company_id');

        // Fecha de vencimiento para cada empresa y filtro de estado
        $rows = $companies->map(function (Company $company) use ($declarations, $year, $month) {
            $lastDigit   = substr($company->ruc, -1);
            $dueDay      = self::DIGIT_MAP[$lastDigit] ?? 24;

            // El vencimiento es el mes siguiente al período
            $dueDate = Carbon::createFromDate($year, $month, 1)
                ->addMonth()
                ->setDay($dueDay);

            $declaration = $declarations->get($company->id);

            return [
                'company'     => $company,
                'last_digit'  => $lastDigit,
                'due_date'    => $dueDate,
                'declaration' => $declaration,
                'declared'    => $declaration !== null,
            ];
        });

        // Filtro por estado
        if ($filterStatus === 'declarado') {
            $rows = $rows->filter(fn ($r) => $r['declared']);
        } elseif ($filterStatus === 'pendiente') {
            $rows = $rows->filter(fn ($r) => ! $r['declared']);
        }

        $rows = $rows->values();

        return view('obligaciones.cronograma.index', [
            'rows'          => $rows,
            'year'          => $year,
            'month'         => $month,
            'filterSearch'  => $filterSearch,
            'filterStatus'  => $filterStatus,
            'totalDeclared' => $rows->where('declared', true)->count(),
            'totalPending'  => $rows->where('declared', false)->count(),
        ]);
    }

    public function confirm(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        abort_if(! in_array($userRole, ['admin', 'supervisor', 'auxiliar'], true), 403);

        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);
        $month = max(1, min(12, $month));

        ObligationDeclaration::updateOrCreate(
            [
                'company_id'   => $company->id,
                'period_year'  => $year,
                'period_month' => $month,
            ],
            [
                'declared_by' => $user->id,
                'declared_at' => now(),
            ]
        );

        return back()->with('status', "Declaración de {$company->name} confirmada para el período.");
    }

    public function revert(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        abort_if(! in_array($userRole, ['admin', 'supervisor', 'auxiliar'], true), 403);

        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);
        $month = max(1, min(12, $month));

        ObligationDeclaration::where('company_id',   $company->id)
            ->where('period_year',  $year)
            ->where('period_month', $month)
            ->delete();

        return back()->with('status', "Declaración de {$company->name} revertida a Pendiente.");
    }
}
