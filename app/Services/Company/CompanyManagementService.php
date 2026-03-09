<?php

namespace App\Services\Company;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Repositories\Contracts\CompanyManagementRepositoryInterface;
use App\Services\External\RucLookupService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyManagementService
{
    public function __construct(
        private readonly CompanyManagementRepositoryInterface $companyManagementRepository,
        private readonly RucLookupService $rucLookupService,
    ) {
    }

    /**
     * @return Collection<int, Company>
     */
    public function listUserCompanies(User $user): Collection
    {
        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        if (in_array($role, ['admin', 'supervisor'], true)) {
            return $this->companyManagementRepository->allCompanies();
        }

        return $this->companyManagementRepository->activeCompaniesByUser($user->id);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function createCompany(User $ownerUser, array $validated): Company
    {
        $company = $this->companyManagementRepository->create([
            'ruc' => (string) $validated['ruc'],
            'name' => (string) $validated['name'],
            'status' => (string) ($validated['status'] ?? 'active'),
            'facturador_enabled' => (bool) ($validated['facturador_enabled'] ?? false),
        ]);

        $ownerUser->companies()->syncWithoutDetaching([
            $company->id => [
                'role' => RoleEnum::ADMIN,
                'status' => 'active',
            ],
        ]);

        return $company;
    }

    public function updateCompany(Company $company, array $validated): Company
    {
        return $this->companyManagementRepository->update($company, [
            'ruc' => (string) $validated['ruc'],
            'name' => (string) $validated['name'],
            'status' => (string) ($validated['status'] ?? 'active'),
            'facturador_enabled' => (bool) ($validated['facturador_enabled'] ?? false),
        ]);
    }

    public function deleteCompany(Company $company): bool
    {
        DB::transaction(function () use ($company) {
            // 1. Hijos de facturas
            $invoiceIds = $company->invoices()->pluck('id');
            if ($invoiceIds->isNotEmpty()) {
                InvoiceItem::whereIn('invoice_id', $invoiceIds)->delete();
                InvoicePayment::whereIn('invoice_id', $invoiceIds)->delete();
            }

            // 2. Facturas e ítems directos de la empresa
            $company->invoices()->delete();
            $company->invoiceItems()->delete();

            // 3. Clientes, productos
            $company->clients()->delete();
            $company->products()->delete();

            // 4. Hijos de tickets y reports antes de eliminarlos
            $ticketIds = DB::table('tickets')->where('company_id', $company->id)->pluck('id');
            if ($ticketIds->isNotEmpty()) {
                DB::table('ticket_messages')->whereIn('ticket_id', $ticketIds)->delete();
            }
            DB::table('tickets')->where('company_id', $company->id)->delete();

            $reportIds = DB::table('reports')->where('company_id', $company->id)->pluck('id');
            if ($reportIds->isNotEmpty()) {
                DB::table('report_user_status')->whereIn('report_id', $reportIds)->delete();
            }
            DB::table('reports')->where('company_id', $company->id)->delete();

            // 5. Resto de tablas con company_id
            DB::table('credentials')->where('company_id', $company->id)->delete();
            DB::table('final_documents')->where('company_id', $company->id)->delete();
            DB::table('obligations')->where('company_id', $company->id)->delete();
            DB::table('news')->where('company_id', $company->id)->delete();

            // 6. Tabla pivote usuarios-empresa
            $company->companyUsers()->delete();

            // 7. Empresa
            $company->delete();
        });

        return true;
    }

    /**
     * @return array<string, string>
     */
    public function lookupRuc(string $ruc): array
    {
        $data = $this->rucLookupService->lookup($ruc);

        return [
            'ruc' => $data['ruc'],
            'name' => $data['name'],
            'direccion' => $data['direccion'],
            'ubicacion' => implode(' - ', array_filter([
                $data['departamento'],
                $data['provincia'],
                $data['distrito'],
            ])),
            'status' => mb_strtoupper($data['estado']) === 'ACTIVO' ? 'active' : 'inactive',
        ];
    }
}
