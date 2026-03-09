<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(! $request->user()?->can('manageCompanies', Company::class), 403);

        $query = Activity::with('causer', 'subject')
            ->latest();

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search): void {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', fn ($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($log = $request->input('log')) {
            $query->where('log_name', $log);
        }

        $logs = $query->paginate(50)->withQueryString();

        $logNames = Activity::select('log_name')->distinct()->orderBy('log_name')->pluck('log_name');

        return view('admin.audit-log.index', compact('logs', 'logNames'));
    }
}
