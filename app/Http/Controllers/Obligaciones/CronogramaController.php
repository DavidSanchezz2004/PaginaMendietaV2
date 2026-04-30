<?php

namespace App\Http\Controllers\Obligaciones;

use App\Enums\RoleEnum;
use App\Exports\OperationObligationsExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Obligation;
use App\Models\ObligationDeclaration;
use App\Services\Company\CompanyManagementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CronogramaController extends Controller
{
    private const SCHEDULE_YEAR = 2026;

    private const GROUPS = [
        'digit_0' => ['label' => 'Digito 0', 'digits' => ['0']],
        'digit_1' => ['label' => 'Digito 1', 'digits' => ['1']],
        'digits_2_3' => ['label' => 'Digitos 2 y 3', 'digits' => ['2', '3']],
        'digits_4_5' => ['label' => 'Digitos 4 y 5', 'digits' => ['4', '5']],
        'digits_6_7' => ['label' => 'Digitos 6 y 7', 'digits' => ['6', '7']],
        'digits_8_9' => ['label' => 'Digitos 8 y 9', 'digits' => ['8', '9']],
        'good_taxpayers' => ['label' => 'Buenos contribuyentes', 'digits' => []],
    ];

    private const MONTH_LABELS = [
        1 => 'Ene-26',
        2 => 'Feb-26',
        3 => 'Mar-26',
        4 => 'Abr-26',
        5 => 'May-26',
        6 => 'Jun-26',
        7 => 'Jul-26',
        8 => 'Ago-26',
        9 => 'Set-26',
        10 => 'Oct-26',
        11 => 'Nov-26',
        12 => 'Dic-26',
    ];

    private const SCHEDULE_2026 = [
        1 => ['digit_0' => '2026-02-16', 'digit_1' => '2026-02-17', 'digits_2_3' => '2026-02-18', 'digits_4_5' => '2026-02-19', 'digits_6_7' => '2026-02-20', 'digits_8_9' => '2026-02-23', 'good_taxpayers' => '2026-02-24'],
        2 => ['digit_0' => '2026-03-16', 'digit_1' => '2026-03-17', 'digits_2_3' => '2026-03-18', 'digits_4_5' => '2026-03-19', 'digits_6_7' => '2026-03-20', 'digits_8_9' => '2026-03-23', 'good_taxpayers' => '2026-03-24'],
        3 => ['digit_0' => '2026-04-17', 'digit_1' => '2026-04-20', 'digits_2_3' => '2026-04-21', 'digits_4_5' => '2026-04-22', 'digits_6_7' => '2026-04-23', 'digits_8_9' => '2026-04-24', 'good_taxpayers' => '2026-04-27'],
        4 => ['digit_0' => '2026-05-18', 'digit_1' => '2026-05-19', 'digits_2_3' => '2026-05-20', 'digits_4_5' => '2026-05-21', 'digits_6_7' => '2026-05-22', 'digits_8_9' => '2026-05-25', 'good_taxpayers' => '2026-05-26'],
        5 => ['digit_0' => '2026-06-15', 'digit_1' => '2026-06-16', 'digits_2_3' => '2026-06-17', 'digits_4_5' => '2026-06-18', 'digits_6_7' => '2026-06-19', 'digits_8_9' => '2026-06-22', 'good_taxpayers' => '2026-06-23'],
        6 => ['digit_0' => '2026-07-15', 'digit_1' => '2026-07-16', 'digits_2_3' => '2026-07-17', 'digits_4_5' => '2026-07-20', 'digits_6_7' => '2026-07-21', 'digits_8_9' => '2026-07-22', 'good_taxpayers' => '2026-07-24'],
        7 => ['digit_0' => '2026-08-18', 'digit_1' => '2026-08-19', 'digits_2_3' => '2026-08-20', 'digits_4_5' => '2026-08-21', 'digits_6_7' => '2026-08-24', 'digits_8_9' => '2026-08-25', 'good_taxpayers' => '2026-08-26'],
        8 => ['digit_0' => '2026-09-15', 'digit_1' => '2026-09-16', 'digits_2_3' => '2026-09-17', 'digits_4_5' => '2026-09-18', 'digits_6_7' => '2026-09-21', 'digits_8_9' => '2026-09-22', 'good_taxpayers' => '2026-09-23'],
        9 => ['digit_0' => '2026-10-16', 'digit_1' => '2026-10-19', 'digits_2_3' => '2026-10-20', 'digits_4_5' => '2026-10-21', 'digits_6_7' => '2026-10-22', 'digits_8_9' => '2026-10-23', 'good_taxpayers' => '2026-10-26'],
        10 => ['digit_0' => '2026-11-16', 'digit_1' => '2026-11-17', 'digits_2_3' => '2026-11-18', 'digits_4_5' => '2026-11-19', 'digits_6_7' => '2026-11-20', 'digits_8_9' => '2026-11-23', 'good_taxpayers' => '2026-11-24'],
        11 => ['digit_0' => '2026-12-17', 'digit_1' => '2026-12-18', 'digits_2_3' => '2026-12-21', 'digits_4_5' => '2026-12-22', 'digits_6_7' => '2026-12-23', 'digits_8_9' => '2026-12-24', 'good_taxpayers' => '2026-12-28'],
        12 => ['digit_0' => '2027-01-18', 'digit_1' => '2027-01-19', 'digits_2_3' => '2027-01-20', 'digits_4_5' => '2027-01-21', 'digits_6_7' => '2027-01-22', 'digits_8_9' => '2027-01-25', 'good_taxpayers' => '2027-01-26'],
    ];

    private const OPERATION_COLUMNS = [
        'movement' => ['label' => 'Movimiento SI/NO', 'aliases' => ['MOVIMIENTO']],
        'invoice' => ['label' => 'Facturar', 'aliases' => ['FACTURAR', 'FACTURACION']],
        'sire_comp' => ['label' => 'SIRE COMP', 'aliases' => ['SIRE COMP', 'SIRE COMPRAS', 'REGISTRO DE COMPRAS']],
        'sire_vta' => ['label' => 'SIRE VTA', 'aliases' => ['SIRE VTA', 'SIRE VENTAS', 'REGISTRO DE VENTAS']],
        'pdt_621' => ['label' => 'PDT 621', 'aliases' => ['PDT 621', '621']],
        'pdt_617' => ['label' => 'PDT 617', 'aliases' => ['PDT 617', '617']],
        'plame' => ['label' => 'PLAME', 'aliases' => ['PLAME']],
        'ple_diario' => ['label' => 'PLE Libro Diario', 'aliases' => ['PLE DIARIO', 'LIBRO DIARIO']],
        'ple_mayor' => ['label' => 'PLE Libro Mayor', 'aliases' => ['PLE MAYOR', 'LIBRO MAYOR']],
        'kardex' => ['label' => 'Kardex', 'aliases' => ['KARDEX']],
        'activos_fijos' => ['label' => 'Activos Fijos', 'aliases' => ['ACTIVOS FIJOS', 'ACTIVO FIJO']],
        'diario_fisico' => ['label' => 'Diario Físico', 'aliases' => ['DIARIO FISICO', 'DIARIO FÍSICO']],
        'mayor_fisico' => ['label' => 'Mayor Físico', 'aliases' => ['MAYOR FISICO', 'MAYOR FÍSICO']],
        'simplificado_fisico' => ['label' => 'Simplificado Físico', 'aliases' => ['SIMPLIFICADO FISICO', 'SIMPLIFICADO FÍSICO']],
        'invent_balances' => ['label' => 'Invent. y Balances', 'aliases' => ['INVENT', 'BALANCES', 'INVENTARIO']],
    ];

    public function index(Request $request, CompanyManagementService $companyManagementService): View
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorizeAccess($user);

        $companies = $this->visibleCompanies($user, $companyManagementService);
        $companies->each(fn (Company $company) => $company->loadMissing('users'));

        $declarations = ObligationDeclaration::query()
            ->whereIn('company_id', $companies->pluck('id'))
            ->where('period_year', self::SCHEDULE_YEAR)
            ->with('declaredByUser')
            ->get()
            ->keyBy(fn (ObligationDeclaration $declaration) => $declaration->company_id.'-'.$declaration->period_month);
        $obligationsByCompany = Obligation::query()
            ->whereIn('company_id', $companies->pluck('id'))
            ->get(['company_id', 'title'])
            ->groupBy('company_id');
        $operationSettings = Schema::hasTable('company_operation_obligations')
            ? DB::table('company_operation_obligations')
                ->whereIn('company_id', $companies->pluck('id'))
                ->get(['company_id', 'operation_key', 'applies'])
                ->keyBy(fn (object $row): string => $row->company_id.'-'.$row->operation_key)
            : collect();

        $groupedCompanies = $this->groupCompanies($companies);
        $scheduleRows = $this->scheduleRows($declarations);
        $companyOptions = $this->companyOptions($companies);
        $operationMatrix = $this->operationMatrix($companies, $obligationsByCompany, $operationSettings);

        return view('obligaciones.cronograma.index', [
            'scheduleYear' => self::SCHEDULE_YEAR,
            'groups' => self::GROUPS,
            'monthLabels' => self::MONTH_LABELS,
            'groupedCompanies' => $groupedCompanies,
            'scheduleRows' => $scheduleRows,
            'companyOptions' => $companyOptions,
            'operationColumns' => self::OPERATION_COLUMNS,
            'operationMatrix' => $operationMatrix,
            'declarations' => $declarations,
            'today' => Carbon::today()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorizeAccess($user);

        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'due_group' => ['required', 'string', 'in:'.implode(',', array_keys(self::GROUPS))],
            'presentation_date' => ['nullable', 'date'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $visibleCompanyIds = $this->visibleCompanies($user, app(CompanyManagementService::class))->pluck('id');
        abort_if(! $visibleCompanyIds->contains($company->id), 403);

        $periodMonth = (int) $validated['period_month'];
        $dueGroup = (string) $validated['due_group'];
        abort_if(! in_array($dueGroup, $this->groupsForCompany($company), true), 422);

        $dueDate = Carbon::parse(self::SCHEDULE_2026[$periodMonth][$dueGroup])->toDateString();
        $presentationDate = $validated['presentation_date'] ?? null;
        $status = $this->resolveStatus($dueDate, $presentationDate);

        ObligationDeclaration::updateOrCreate(
            [
                'company_id' => $company->id,
                'period_year' => self::SCHEDULE_YEAR,
                'period_month' => $periodMonth,
            ],
            [
                'due_group' => $dueGroup,
                'due_date' => $dueDate,
                'presentation_date' => $presentationDate,
                'status' => $status,
                'observation' => $validated['observation'] ?? null,
                'declared_by' => $user->id,
                'declared_at' => $presentationDate ? now() : null,
            ]
        );

        return back()->with('status', 'Registro mensual actualizado correctamente.');
    }

    public function destroy(Request $request, ObligationDeclaration $declaration): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorizeAccess($user);

        $declaration->delete();

        return back()->with('status', 'Registro mensual eliminado.');
    }

    public function storeOperationMatrix(Request $request, CompanyManagementService $companyManagementService): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorizeAccess($user);

        $visibleCompanyIds = $this->visibleCompanies($user, $companyManagementService)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);
        $input = $request->input('operations', []);
        abort_if(! is_array($input), 422);

        $allowedKeys = array_keys(self::OPERATION_COLUMNS);
        $updates = 0;

        if (! Schema::hasTable('company_operation_obligations')) {
            return back()->with('status', 'La tabla de matriz operativa aún no existe. Ejecuta las migraciones e intenta nuevamente.');
        }

        foreach ($input as $companyId => $operations) {
            $companyId = (int) $companyId;
            if (! $visibleCompanyIds->contains($companyId) || ! is_array($operations)) {
                continue;
            }

            foreach ($operations as $key => $value) {
                if (! in_array($key, $allowedKeys, true) || $value === null || $value === '') {
                    continue;
                }

                DB::table('company_operation_obligations')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'operation_key' => $key,
                    ],
                    [
                        'applies' => (string) $value === '1',
                        'updated_by' => $user->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $updates++;
            }
        }

        return back()->with('status', $updates > 0
            ? "Matriz operativa actualizada ({$updates} cambio(s))."
            : 'No se seleccionaron cambios para guardar.');
    }

    public function exportOperationMatrix(Request $request, CompanyManagementService $companyManagementService): BinaryFileResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorizeAccess($user);

        $companies = $this->visibleCompanies($user, $companyManagementService);
        $obligationsByCompany = Obligation::query()
            ->whereIn('company_id', $companies->pluck('id'))
            ->get(['company_id', 'title'])
            ->groupBy('company_id');
        $operationSettings = Schema::hasTable('company_operation_obligations')
            ? DB::table('company_operation_obligations')
                ->whereIn('company_id', $companies->pluck('id'))
                ->get(['company_id', 'operation_key', 'applies'])
                ->keyBy(fn (object $row): string => $row->company_id.'-'.$row->operation_key)
            : collect();

        $matrix = collect($this->operationMatrix($companies, $obligationsByCompany, $operationSettings));

        return Excel::download(
            new OperationObligationsExport($matrix, self::OPERATION_COLUMNS),
            'matriz-obligaciones-operativas.xlsx'
        );
    }

    private function authorizeAccess($user): void
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        abort_if(! in_array($userRole, ['admin', 'supervisor', 'auxiliar'], true), 403);
    }

    /**
     * @return Collection<int, Company>
     */
    private function visibleCompanies($user, CompanyManagementService $companyManagementService): Collection
    {
        $companies = $companyManagementService->listUserCompanies($user);
        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        if (! in_array($role, ['admin', 'supervisor'], true)) {
            return $companies->values();
        }

        $hiddenCompanyIds = CompanyUser::query()
            ->where('user_id', $user->id)
            ->where('hidden_in_dashboard', true)
            ->pluck('company_id');

        return $companies
            ->reject(fn (Company $company) => $hiddenCompanyIds->contains($company->id))
            ->values();
    }

    /**
     * @param Collection<int, Company> $companies
     * @return array<string, Collection<int, Company>>
     */
    private function groupCompanies(Collection $companies): array
    {
        $grouped = [];

        foreach (array_keys(self::GROUPS) as $groupKey) {
            $grouped[$groupKey] = collect();
        }

        foreach ($companies as $company) {
            $primaryGroup = $this->groupForCompany($company);
            $grouped[$primaryGroup]->push($company);

            if ((bool) ($company->is_good_taxpayer ?? false)) {
                $grouped['good_taxpayers']->push($company);
            }
        }

        return $grouped;
    }

    private function groupForCompany(Company $company): string
    {
        $lastDigit = substr((string) $company->ruc, -1);

        foreach (self::GROUPS as $groupKey => $group) {
            if (in_array($lastDigit, $group['digits'], true)) {
                return $groupKey;
            }
        }

        return 'digits_8_9';
    }

    /**
     * @return array<int, string>
     */
    private function groupsForCompany(Company $company): array
    {
        $groups = [$this->groupForCompany($company)];

        if ((bool) ($company->is_good_taxpayer ?? false)) {
            $groups[] = 'good_taxpayers';
        }

        return $groups;
    }

    /**
     * @param Collection<string, ObligationDeclaration> $declarations
     * @return array<int, array<string, mixed>>
     */
    private function scheduleRows(Collection $declarations): array
    {
        $rows = [];

        foreach (self::SCHEDULE_2026 as $month => $datesByGroup) {
            $cells = [];

            foreach ($datesByGroup as $groupKey => $date) {
                $cells[$groupKey] = [
                    'date' => $date,
                    'label' => $this->formatShortDate($date),
                    'tone' => $this->dateTone($date),
                ];
            }

            $rows[] = [
                'month' => $month,
                'period' => self::MONTH_LABELS[$month],
                'cells' => $cells,
            ];
        }

        return $rows;
    }

    /**
     * @param Collection<int, Company> $companies
     * @return array<int, array<string, mixed>>
     */
    private function companyOptions(Collection $companies): array
    {
        return $companies->map(function (Company $company): array {
            $groups = $this->groupsForCompany($company);

            return [
                'id' => $company->id,
                'name' => $company->name,
                'ruc' => $company->ruc,
                'last_digit' => substr((string) $company->ruc, -1),
                'groups' => $groups,
                'is_good_taxpayer' => (bool) ($company->is_good_taxpayer ?? false),
            ];
        })->values()->all();
    }

    /**
     * @param Collection<int, Company> $companies
     * @param Collection<int, Collection<int, Obligation>> $obligationsByCompany
     * @param Collection<string, object> $operationSettings
     * @return array<int, array<string, mixed>>
     */
    private function operationMatrix(Collection $companies, Collection $obligationsByCompany, Collection $operationSettings): array
    {
        return $companies
            ->sortBy('name')
            ->map(function (Company $company) use ($obligationsByCompany, $operationSettings): array {
                $titles = $obligationsByCompany
                    ->get($company->id, collect())
                    ->pluck('title')
                    ->map(fn (string $title): string => $this->normalizeText($title))
                    ->values();

                $values = [];
                foreach (self::OPERATION_COLUMNS as $key => $column) {
                    $settingKey = $company->id.'-'.$key;
                    $default = match ($key) {
                        'movement' => false,
                        'invoice' => (bool) $company->facturador_enabled,
                        default => $this->hasMatchingObligation($titles, $column['aliases']),
                    };

                    $values[$key] = $operationSettings->has($settingKey)
                        ? (bool) $operationSettings[$settingKey]->applies
                        : $default;
                }

                return [
                    'company' => $company,
                    'short_name' => $this->shortCompanyName($company),
                    'values' => $values,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, string> $titles
     * @param array<int, string> $aliases
     */
    private function hasMatchingObligation(Collection $titles, array $aliases): bool
    {
        $normalizedAliases = collect($aliases)->map(fn (string $alias): string => $this->normalizeText($alias));

        return $titles->contains(function (string $title) use ($normalizedAliases): bool {
            return $normalizedAliases->contains(fn (string $alias): bool => str_contains($title, $alias));
        });
    }

    private function normalizeText(string $value): string
    {
        $value = str($value)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', ' ')
            ->squish()
            ->toString();

        return $value;
    }

    private function shortCompanyName(Company $company): string
    {
        $name = trim((string) $company->name);
        $first = preg_split('/\s+/', $name)[0] ?? 'Empresa';

        return str($first)->limit(18, '')->toString().' !';
    }

    private function resolveStatus(string $dueDate, ?string $presentationDate): string
    {
        if ($presentationDate) {
            return Carbon::parse($presentationDate)->lte(Carbon::parse($dueDate))
                ? 'presented_on_time'
                : 'presented_late';
        }

        return Carbon::today()->gt(Carbon::parse($dueDate)) ? 'overdue' : 'pending';
    }

    private function dateTone(string $date): string
    {
        $today = Carbon::today();
        $dueDate = Carbon::parse($date);

        if ($today->gt($dueDate)) {
            return 'overdue';
        }

        if ($today->diffInDays($dueDate, false) <= 5) {
            return 'soon';
        }

        return 'future';
    }

    private function formatShortDate(string $date): string
    {
        $monthNames = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Set',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        $parsed = Carbon::parse($date);

        return $parsed->format('d').' '.$monthNames[$parsed->month].($parsed->year !== self::SCHEDULE_YEAR ? ' '.$parsed->year : '');
    }
}
