<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialSetupSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => env('INITIAL_ADMIN_EMAIL', 'admin@mendieta.test')],
            [
                'name' => env('INITIAL_ADMIN_NAME', 'Administrador Mendieta'),
                'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD', 'Admin12345!')),
                'role' => RoleEnum::ADMIN,
            ],
        );

        $company = Company::query()->updateOrCreate(
            ['ruc' => env('DEMO_COMPANY_RUC', '20123456789')],
            [
                'name' => env('DEMO_COMPANY_NAME', 'Empresa Demo Mendieta'),
                'status' => 'active',
                'facturador_enabled' => true,
            ],
        );

        $admin->companies()->syncWithoutDetaching([
            $company->id => ['role' => RoleEnum::ADMIN->value, 'status' => 'active']
        ]);

        // Crear Cliente Demo
        $cliente = User::query()->updateOrCreate(
            ['email' => 'cliente@mendieta.test'],
            [
                'name' => 'Cliente Demo',
                'password' => Hash::make('Cliente123!'),
                'role' => RoleEnum::CLIENT,
            ],
        );

        $cliente->companies()->syncWithoutDetaching([
            $company->id => ['role' => RoleEnum::CLIENT->value, 'status' => 'active']
        ]);

        // Crear Supervisor Demo (El supervisor es global, no se asienta a una empresa en particular)
        User::query()->updateOrCreate(
            ['email' => 'supervisor@mendieta.test'],
            [
                'name' => 'Supervisor Demo',
                'password' => Hash::make('Supervisor123!'),
                'role' => RoleEnum::SUPERVISOR,
            ],
        );

        // Crear Auxiliar Demo
        $auxiliar = User::query()->updateOrCreate(
            ['email' => 'auxiliar@mendieta.test'],
            [
                'name' => 'Auxiliar Demo',
                'password' => Hash::make('Auxiliar123!'),
                'role' => RoleEnum::AUXILIAR,
            ],
        );

        $auxiliar->companies()->syncWithoutDetaching([
            $company->id => ['role' => RoleEnum::AUXILIAR->value, 'status' => 'active']
        ]);
    }
}