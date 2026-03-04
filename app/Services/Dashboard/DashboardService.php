<?php

namespace App\Services\Dashboard;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\News;
use App\Models\Report;
use App\Models\Ticket;
use App\Models\User;

class DashboardService
{
    /**
     * Get the appropriate dashboard data based on user role
     */
    public function getDashboardData(User $user): array
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        $latestNews = News::published()->latest('published_at')->first();

        if (in_array($userRole, ['admin', 'supervisor'], true)) {
            $data = $this->getGlobalDashboard($user);
        } elseif ($userRole === 'auxiliar') {
            $data = $this->getAuxiliarDashboard($user);
        } else {
            $data = $this->getClientDashboard($user);
        }

        return array_merge(['latestNews' => $latestNews], $data);
    }

    /**
     * Global panorama for Admin and Supervisor
     */
    private function getGlobalDashboard(User $user): array
    {
        return [
            'metrics' => [
                'total_companies' => Company::count(),
                'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'total_reports' => Report::count(),
                'unread_reports' => 0, // No aplica globalmente de la misma forma
            ],
            'recentTickets' => Ticket::with('company')->latest('updated_at')->take(5)->get(),
            'recentReports' => Report::with('company')->latest()->take(5)->get(),
        ];
    }

    /**
     * Auxiliar Dashboard: ONLY data from assigned companies
     */
    private function getAuxiliarDashboard(User $user): array
    {
        // Obtener IDs de empresas asignadas al auxiliar
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
    private function getClientDashboard(User $user): array
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
            
            // Unread reports (user hasn't opened them)
            $readReportIds = $user->readReports()->pluck('reports.id')->toArray();
            $metrics['unread_reports'] = $companyReports->whereNotIn('id', $readReportIds)->count();

            $recentTickets = Ticket::where('company_id', $activeCompanyId)->latest('updated_at')->take(4)->get();
            $recentReports = Report::where('company_id', $activeCompanyId)->where('status', 'published')->latest()->take(4)->get();
        }

        return [
            'metrics' => $metrics,
            'recentTickets' => $recentTickets,
            'recentReports' => $recentReports,
        ];
    }
}
