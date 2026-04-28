<?php

namespace App\Http\Controllers\Obligaciones;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\ObligationDeclaration;
use App\Services\Company\CompanyManagementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

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

        $groupedCompanies = $this->groupCompanies($companies);
        $scheduleRows = $this->scheduleRows($declarations);
        $companyOptions = $this->companyOptions($companies);

        return view('obligaciones.cronograma.index', [
            'scheduleYear' => self::SCHEDULE_YEAR,
            'groups' => self::GROUPS,
            'monthLabels' => self::MONTH_LABELS,
            'groupedCompanies' => $groupedCompanies,
            'scheduleRows' => $scheduleRows,
            'companyOptions' => $companyOptions,
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
