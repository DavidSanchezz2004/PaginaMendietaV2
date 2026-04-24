<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\Obligation;
use Illuminate\Http\Request;

class ObligationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Obligation::class);
        $user = $request->user();

        $query = Obligation::with('company');

        if ($user->role === RoleEnum::CLIENT || $user->role === RoleEnum::AUXILIAR) {
            $activeCompanyId = session('company_id') ?? $user->companies()->first()?->id;
            abort_if(!$activeCompanyId, 403, 'Aún no perteneces a ninguna empresa.');
            
            $query->where('company_id', $activeCompanyId);
        } else {
            // Si es un admin y filtra por empresa
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        }

        // Determinar mes y año actual o solicitado
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $date = \Carbon\Carbon::createFromDate($year, $month, 1);

        $startOfCalendar = $date->copy()->startOfMonth()->startOfWeek(\Carbon\CarbonInterface::SUNDAY);
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(\Carbon\CarbonInterface::SATURDAY);

        // Limitar consulta al rango visible en el calendario para rendimiento
        $query->whereBetween('due_date', [$startOfCalendar, $endOfCalendar]);
        
        $obligations = $query->orderBy('due_date', 'asc')->get();

        // Agrupar obligaciones por fecha (Y-m-d)
        $obligationsByDate = $obligations->groupBy(function($ob) {
            return $ob->due_date->format('Y-m-d');
        });

        // Construir la grilla del calendario (Semanas -> Días)
        $calendar = [];
        $currentDay = $startOfCalendar->copy();
        while($currentDay <= $endOfCalendar) {
            $dateString = $currentDay->format('Y-m-d');
            $calendar[] = [
                'date' => $currentDay->copy(),
                'isCurrentMonth' => $currentDay->month == $month,
                'isToday' => $currentDay->isToday(),
                'obligations' => $obligationsByDate->get($dateString, collect())
            ];
            $currentDay->addDay();
        }

        // Obtener clientes para el selector de filtro/creación si es admin
        $companies = collect();
        if ($user->role === RoleEnum::ADMIN || $user->role === RoleEnum::SUPERVISOR) {
            $companies = Company::orderBy('name')->get();
        }

        return view('admin.obligations.index', compact('calendar', 'date', 'companies', 'month', 'year'));
    }

    public function create()
    {
        $this->authorize('create', Obligation::class);
        $companies = Company::orderBy('name')->get();
        return view('admin.obligations.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Obligation::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,completed,expired'
        ]);

        Obligation::create($validated);

        return redirect()->route('obligations.index')
            ->with('status', '✅ Obligación agregada exitosamente.');
    }

    public function edit(Obligation $obligation)
    {
        $this->authorize('update', $obligation);
        $companies = Company::orderBy('name')->get();
        return view('admin.obligations.edit', compact('obligation', 'companies'));
    }

    public function update(Request $request, Obligation $obligation)
    {
        $this->authorize('update', $obligation);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,completed,expired'
        ]);

        $obligation->update($validated);

        return redirect()->route('obligations.index')
            ->with('status', '🔄 Obligación actualizada.');
    }

    public function destroy(Obligation $obligation)
    {
        $this->authorize('delete', $obligation);
        $obligation->delete();

        return redirect()->route('obligations.index')
            ->with('status', '🗑️ Obligación eliminada permanentemente.');
    }

    public function markAsCompleted(Obligation $obligation)
    {
        $this->authorize('update', $obligation);
        
        $obligation->update(['status' => 'completed']);

        return redirect()->back()->with('status', '✅ Obligación marcada como completada.');
    }
}
