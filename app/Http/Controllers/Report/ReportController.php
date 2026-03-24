<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Requests\Report\UpdateReportRequest;
use App\Models\Report;
use App\Services\Company\ActiveCompanyService;
use App\Services\Report\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly ActiveCompanyService $activeCompanyService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $activeCompany = $this->activeCompanyService->ensureValidOrInitialize($user, $request->session());
        abort_if(! $activeCompany, 403, 'No tienes una empresa activa válida.');

        $this->authorize('viewAny', [Report::class, $activeCompany]);

        $reportsQuery = Report::with('uploader')
            ->where('company_id', $activeCompany->id)
            ->latest('period_year')
            ->latest('period_month');

        $userRole = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;

        if (in_array($userRole, ['client', 'cliente'], true)) {
            $reportsQuery->where('status', 'published');
        }

        return view('admin.reports.index', [
            'reports' => $reportsQuery->paginate(20),
            'activeCompany' => $activeCompany,
            'isCliente' => in_array($userRole, ['client', 'cliente'], true),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Report::class);
        return view('admin.reports.create');
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        $this->authorize('create', Report::class);

        $user = $request->user();
        $activeCompany = $this->activeCompanyService->ensureValidOrInitialize($user, $request->session());
        abort_if(! $activeCompany, 403);

        $this->reportService->createReport(
            company: $activeCompany,
            uploader: $user,
            data: $request->validated(),
            file: $request->file('file')
        );

        return redirect()->route('reports.index')->with('status', 'Reporte creado correctamente.');
    }

    public function edit(Report $report): View
    {
        $this->authorize('update', $report);
        return view('admin.reports.edit', compact('report'));
    }

    public function update(UpdateReportRequest $request, Report $report): RedirectResponse
    {
        $this->authorize('update', $report);

        $this->reportService->updateReport(
            report: $report,
            data: $request->validated(),
            file: $request->file('file')
        );

        return redirect()->route('reports.index')->with('status', 'Reporte actualizado correctamente.');
    }

    public function destroy(Report $report): RedirectResponse
    {
        $this->authorize('delete', $report);
        
        $this->reportService->deleteReport($report);

        return redirect()->route('reports.index')->with('status', 'Reporte eliminado correctamente.');
    }

    public function download(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        if ($report->format === 'powerbi') {
            abort(404, 'No hay archivo descargable para reportes PowerBI.');
        }

        abort_if(! $report->file_path || ! Storage::disk('local')->exists($report->file_path), 404, 'El archivo no existe.');

        return response()->download(
            Storage::disk('local')->path($report->file_path),
            basename($report->file_path)
        );
    }

    public function publish(Report $report): RedirectResponse
    {
        $this->authorize('publish', $report);
        
        $this->reportService->publishReport($report);

        return back()->with('status', 'Reporte publicado.');
    }

    public function unpublish(Report $report): RedirectResponse
    {
        $this->authorize('publish', $report);
        
        $this->reportService->unpublishReport($report);

        return back()->with('status', 'Reporte des-publicado.');
    }

    public function trackRead(Request $request, Report $report)
    {
        $this->authorize('view', $report);
        $this->reportService->markAsRead($report, $request->user());
        return response()->json(['success' => true]);
    }

    public function trackValued(Request $request, Report $report)
    {
        $this->authorize('view', $report);
        $this->reportService->markAsValued($report, $request->user());
        return response()->json(['success' => true]);
    }
}
