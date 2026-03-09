<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyUserAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Se requiere la extensión pdo_sqlite para ejecutar pruebas con RefreshDatabase.');
        }

        parent::setUp();
    }

    public function test_client_login_redirects_to_client_dashboard(): void
    {
        $company = Company::query()->create([
            'ruc' => '20999888777',
            'name' => 'Empresa Cliente Test',
            'status' => 'active',
            'facturador_enabled' => false,
        ]);

        $client = User::query()->create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@test.local',
            'password' => 'password123',
            'role' => RoleEnum::CLIENT,
        ]);

        $client->companies()->attach($company->id, [
            'role' => RoleEnum::CLIENT,
            'status' => 'active',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $client->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.cliente', absolute: false));
        $response->assertSessionHas('company_id', $company->id);
    }

    public function test_client_cannot_access_company_users_module(): void
    {
        $company = Company::query()->create([
            'ruc' => '20999111222',
            'name' => 'Empresa Seguridad Test',
            'status' => 'active',
            'facturador_enabled' => true,
        ]);

        $client = User::query()->create([
            'name' => 'Cliente Restringido',
            'email' => 'cliente-restringido@test.local',
            'password' => 'password123',
            'role' => RoleEnum::CLIENT,
        ]);

        $client->companies()->attach($company->id, [
            'role' => RoleEnum::CLIENT,
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($client)
            ->withSession(['company_id' => $company->id])
            ->get(route('users.index'));

        $response->assertForbidden();
    }
}
