<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Policies\ClientPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\ProductPolicy;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Contracts\CompanyManagementRepositoryInterface;
use App\Repositories\Contracts\CompanyMembershipRepositoryInterface;
use App\Repositories\Contracts\CompanyUserManagementRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ClientRepository;
use App\Repositories\Eloquent\CompanyManagementRepository;
use App\Repositories\Eloquent\CompanyMembershipRepository;
use App\Repositories\Eloquent\CompanyUserManagementRepository;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\UserProfileRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ── Repositorios base ────────────────────────────────────────────
        $this->app->bind(CompanyManagementRepositoryInterface::class, CompanyManagementRepository::class);
        $this->app->bind(CompanyMembershipRepositoryInterface::class, CompanyMembershipRepository::class);
        $this->app->bind(CompanyUserManagementRepositoryInterface::class, CompanyUserManagementRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserProfileRepositoryInterface::class, UserProfileRepository::class);

        // ── Repositorios Facturador ──────────────────────────────────────
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usar paginador Bootstrap (no Tailwind) ya que el proyecto no usa Tailwind CSS
        Paginator::useBootstrap();

        // EasyPanel suele terminar TLS en proxy inverso. Esto evita Mixed Content
        // al generar asset()/url() en HTTP cuando el sitio público va en HTTPS.
        if (app()->environment('production') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        // ── Policies base ────────────────────────────────────────────────
        Gate::policy(Company::class, CompanyPolicy::class);

        // ── Policies Facturador ──────────────────────────────────────────
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}
