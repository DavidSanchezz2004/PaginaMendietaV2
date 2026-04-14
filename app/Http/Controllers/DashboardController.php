<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Display the dashboard view with relevant metrics based on user role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $year = (int) $request->query('year', now()->year);

        $data = $this->dashboardService->getDashboardData($user, $year);

        return view('dashboard', $data);
    }
}

