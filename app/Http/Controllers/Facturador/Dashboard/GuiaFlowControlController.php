<?php

namespace App\Http\Controllers\Facturador\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class GuiaFlowControlController extends Controller
{
    /**
     * Dashboard de control del flujo de compras a facturas.
     */
    public function index()
    {
        $query = Purchase::query()
            ->where('company_id', session('company_id'))
            ->orderByDesc('created_at');

        // Filtros opcionales
        if ($status = request('status')) {
            $query->where('status', $status);
        }

        $purchases = $query->paginate(20);

        // Estadísticas
        $stats = [
            'total' => Purchase::where('company_id', session('company_id'))->count(),
            'pending' => Purchase::where('company_id', session('company_id'))
                ->where(fn($q) => $q->whereNull('client_id')->orWhere('status', 'registered'))
                ->count(),
            'assigned' => Purchase::where('company_id', session('company_id'))
                ->where('status', 'assigned')
                ->count(),
            'guided' => Purchase::where('company_id', session('company_id'))
                ->where('status', 'guided')
                ->count(),
            'invoiced' => Purchase::where('company_id', session('company_id'))
                ->whereIn('status', ['partially_invoiced', 'invoiced'])
                ->count(),
        ];

        return view('facturador.dashboard.guia-flow-control', [
            'purchases' => $purchases,
            'stats' => $stats,
        ]);
    }
}
