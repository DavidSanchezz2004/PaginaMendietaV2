<?php

namespace App\Http\Controllers\Facturador;

use App\Enums\FeasyStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreGRERequest;
use App\Models\Company;
use App\Models\CompanyGrePreset;
use App\Models\Invoice;
use App\Services\Facturador\InvoiceService;
use App\Services\Facturador\OpenAiGrePdfExtractorService;
use App\Services\Facturador\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controlador exclusivo para Guías de Remisión Electrónica (tipo 09).
 * Delega toda la lógica de negocio en InvoiceService.
 */
class GREController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly ProductService  $productService,
    ) {
    }

    // ──────────────────────────────────────────────────────────────────────
    // CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $filters = $request->only(['estado', 'serie', 'search']);

        // Solo listar GREs
        $gres = Invoice::forActiveCompany()
            ->where('codigo_tipo_documento', '09')
            ->when($filters['estado'] ?? null, fn ($q, $v) => $q->where('estado', $v))
            ->when($filters['serie']  ?? null, fn ($q, $v) => $q->where('serie_documento', 'LIKE', "%{$v}%"))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(function ($q2) use ($v) {
                $q2->where('numero_documento', 'LIKE', "%{$v}%")
                   ->orWhere('observacion', 'LIKE', "%{$v}%");
            }))
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('facturador.gre.index', compact('gres', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        $suggestions = $this->invoiceService->getDocumentSuggestions();

        // Sugerencia de serie y número para tipo 09
        $suggestion09 = (object) [
            'serie_documento' => $suggestions['09']['serie'] ?? 'T001',
            'numero_documento' => $suggestions['09']['numero'] ?? 1,
        ];
        $products     = $this->productService->allActive();
        $company      = Company::findOrFail((int) session('company_id'));
        $recentRelatedInvoices = $this->relatedInvoiceOptions($company);
        $grePresets = CompanyGrePreset::where('company_id', $company->id)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('facturador.gre.create', compact('suggestion09', 'products', 'company', 'recentRelatedInvoices', 'grePresets'));
    }

    public function store(StoreGRERequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        // Forzar tipo de documento GRE
        $validated['codigo_tipo_documento'] = '09';
        $validated['codigo_moneda']         = 'PEN';
        $validated['porcentaje_igv']        = 18;
        $validated['forma_pago']            = null;
        $validated['client_id']             = null;
        $validated['monto_total_gravado']   = 0;
        $validated['monto_total_igv']       = 0;
        $validated['monto_total']           = 0;
        $validated['lista_guias']           = null;
        $validated['user_id']               = $request->user()->id;

        // Limpiar transportista/vehiculos según modalidad
        $modalidad = $validated['codigo_modalidad_traslado'];
        if ($modalidad === '01') {
            $validated['gre_vehiculos']  = null;
            $validated['gre_conductores'] = null;
        } else {
            $validated['gre_transportista'] = null;
        }

        $company = Company::findOrFail((int) session('company_id'));

        $validated['gre_documentos_relacionados'] = collect($validated['gre_documentos_relacionados'] ?? [])
            ->filter(fn (array $document): bool =>
                ! empty($document['codigo_tipo_documento'])
                && ! empty($document['serie_documento'])
                && ! empty($document['numero_documento'])
            )
            ->values()
            ->map(function (array $document, int $index) use ($company): array {
                $tipo = (string) ($document['codigo_tipo_documento'] ?? '01');
                $tipoEmisor = (string) ($document['codigo_tipo_documento_emisor'] ?? '6');
                $numeroEmisor = preg_replace('/\D/', '', (string) ($document['numero_documento_emisor'] ?? ''));

                return [
                    'correlativo' => $index + 1,
                    'codigo_tipo_documento' => $tipo,
                    'descripcion_tipo_documento' => $document['descripcion_tipo_documento'] ?? $this->documentTypeLabel($tipo),
                    'serie_documento' => strtoupper(trim((string) ($document['serie_documento'] ?? ''))),
                    'numero_documento' => trim((string) ($document['numero_documento'] ?? '')),
                    'codigo_tipo_documento_emisor' => $tipoEmisor,
                    'numero_documento_emisor' => $numeroEmisor !== '' ? $numeroEmisor : (string) $company->ruc,
                ];
            })
            ->all() ?: null;

        // Ítems GRE sin montos financieros
        $items = array_values(array_map(fn (array $item): array => array_merge([
            'correlativo'             => 1,
            'monto_valor_unitario'    => 0,
            'monto_precio_unitario'   => 0,
            'monto_valor_total'       => 0,
            'monto_igv'               => 0,
            'monto_total'             => 0,
            'codigo_indicador_afecto' => '10',
            'tipo'                    => 'P',
        ], array_intersect_key($item, array_flip([
            'correlativo', 'codigo_interno', 'codigo_unidad_medida', 'descripcion', 'cantidad',
        ]))), $items));

        // Enumerar correlativos
        foreach ($items as $i => &$item) {
            $item['correlativo'] = $i + 1;
        }
        unset($item);

        $invoice = $this->invoiceService->create($validated, $items);

        return redirect()
            ->route('facturador.gre.show', $invoice)
            ->with('success', "Guía de Remisión {$invoice->serie_numero} creada como borrador.");
    }

    private function documentTypeLabel(string $code): string
    {
        return [
            '01' => 'Factura',
            '03' => 'Boleta',
            '07' => 'Nota de crédito',
            '08' => 'Nota de débito',
        ][$code] ?? 'Documento';
    }

    public function extractPdf(Request $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        try {
            $data = app(OpenAiGrePdfExtractorService::class)->extract($request->file('pdf'));

            return response()->json([
                'ok' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'error' => 'No se pudo extraer la GRE del PDF. Revisa que el archivo tenga texto seleccionable.',
            ], 422);
        }
    }

    public function relatedInvoices(Request $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $company = Company::findOrFail((int) session('company_id'));
        $search = trim((string) $request->query('search', ''));

        $invoices = Invoice::forActiveCompany()
            ->with('client:id,nombre_razon_social,numero_documento')
            ->whereIn('codigo_tipo_documento', ['01', '03', '07', '08'])
            ->whereIn('estado', ['sent', 'consulted'])
            ->when($search !== '', function ($query) use ($search): void {
                $like = "%{$search}%";
                $query->where(function ($q) use ($like): void {
                    $q->where('serie_documento', 'like', $like)
                        ->orWhere('numero_documento', 'like', $like)
                        ->orWhereRaw("CONCAT(serie_documento, '-', numero_documento) LIKE ?", [$like])
                        ->orWhereHas('client', function ($clientQuery) use ($like): void {
                            $clientQuery->where('nombre_razon_social', 'like', $like)
                                ->orWhere('numero_documento', 'like', $like);
                        });
                });
            })
            ->latest('fecha_emision')
            ->latest('id')
            ->limit(12)
            ->get();

        return response()->json([
            'items' => $invoices->map(fn (Invoice $invoice): array => $this->relatedInvoiceOption($invoice, $company))->values(),
        ]);
    }

    private function relatedInvoiceOptions(Company $company): array
    {
        return Invoice::forActiveCompany()
            ->with('client:id,nombre_razon_social,numero_documento')
            ->whereIn('codigo_tipo_documento', ['01', '03', '07', '08'])
            ->whereIn('estado', ['sent', 'consulted'])
            ->latest('fecha_emision')
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn (Invoice $invoice): array => $this->relatedInvoiceOption($invoice, $company))
            ->values()
            ->all();
    }

    private function relatedInvoiceOption(Invoice $invoice, Company $company): array
    {
        $date = $invoice->fecha_emision->isToday()
            ? 'Hoy'
            : ($invoice->fecha_emision->isYesterday() ? 'Ayer' : $invoice->fecha_emision->format('d/m/Y'));

        return [
            'id' => $invoice->id,
            'label' => "{$date} · {$invoice->serie_numero} · {$invoice->client?->nombre_razon_social}",
            'codigo_tipo_documento' => $invoice->codigo_tipo_documento,
            'descripcion_tipo_documento' => $this->documentTypeLabel($invoice->codigo_tipo_documento),
            'serie_documento' => $invoice->serie_documento,
            'numero_documento' => $invoice->numero_documento,
            'codigo_tipo_documento_emisor' => '6',
            'numero_documento_emisor' => (string) $company->ruc,
            'cliente' => $invoice->client?->nombre_razon_social,
            'monto_total' => (float) $invoice->monto_total,
            'moneda' => $invoice->codigo_moneda,
        ];
    }

    public function show(Invoice $gre): View
    {
        $this->authorize('view', $gre);

        abort_if($gre->codigo_tipo_documento !== '09', 404);

        $invoice = $this->invoiceService->findWithItems($gre->id);

        return view('facturador.gre.show', compact('invoice'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Acciones Feasy
    // ──────────────────────────────────────────────────────────────────────

    public function emit(Invoice $invoice): RedirectResponse
    {
        $this->authorize('emit', $invoice);
        abort_if($invoice->codigo_tipo_documento !== '09', 404);

        try {
            $invoice = $this->invoiceService->emit($invoice);

            if ($invoice->estado_feasy === FeasyStatusEnum::TICKET) {
                $flashKey = 'info';
                $message  = "GRE {$invoice->serie_numero}: Ticket generado (A01). " .
                            "Usa \"Consultar SUNAT\" para obtener el resultado definitivo.";
            } elseif ($invoice->estado_feasy->isAccepted()) {
                $flashKey = 'success';
                $message  = "GRE {$invoice->serie_numero} aceptada por SUNAT.";
            } else {
                $flashKey = 'warning';
                $message  = "GRE {$invoice->serie_numero}: {$invoice->mensaje_respuesta_sunat}";
            }

        } catch (RuntimeException $e) {
            return redirect()
                ->route('facturador.gre.show', $invoice)
                ->with('error', 'Error al emitir: ' . $e->getMessage());
        }

        return redirect()
            ->route('facturador.gre.show', $invoice)
            ->with($flashKey, $message);
    }

    public function consult(Invoice $invoice): RedirectResponse
    {
        $this->authorize('consult', $invoice);
        abort_if($invoice->codigo_tipo_documento !== '09', 404);

        try {
            $invoice = $this->invoiceService->consult($invoice);

            if ($invoice->estado_feasy->isAccepted()) {
                $flash = 'success';
                $msg   = "GRE {$invoice->serie_numero} aceptada por SUNAT. " .
                         ($invoice->mensaje_respuesta_sunat ?? '');
            } else {
                $flash = 'info';
                $msg   = 'Consulta completada: ' . ($invoice->mensaje_respuesta_sunat ?? 'OK');
            }
        } catch (RuntimeException $e) {
            return redirect()
                ->route('facturador.gre.show', $invoice)
                ->with('error', 'Error al consultar: ' . $e->getMessage());
        }

        return redirect()
            ->route('facturador.gre.show', $invoice)
            ->with($flash, $msg);
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        $this->authorize('void', $invoice);
        abort_if($invoice->codigo_tipo_documento !== '09', 404);

        $motivo = trim(request()->input('motivo', 'Anulado'));
        if (empty($motivo)) {
            $motivo = 'Anulado';
        }

        try {
            $invoice = $this->invoiceService->void($invoice, $motivo);
            $status  = $invoice->estado->value === 'voided' ? 'success' : 'error';
            $msg     = $status === 'success'
                ? "GRE {$invoice->serie_numero} anulada correctamente."
                : 'Error al anular: ' . ($invoice->last_error ?? 'Error desconocido.');
        } catch (RuntimeException $e) {
            $status = 'error';
            $msg    = $e->getMessage();
        }

        return redirect()
            ->route('facturador.gre.show', $invoice)
            ->with($status, $msg);
    }

    public function downloadXml(Invoice $invoice): StreamedResponse
    {
        $this->authorize('downloadXml', $invoice);
        abort_if($invoice->codigo_tipo_documento !== '09', 404);

        if (! $invoice->xml_path || ! Storage::disk('local')->exists($invoice->xml_path)) {
            abort(404, 'El archivo XML no está disponible.');
        }

        return Storage::disk('local')->download(
            $invoice->xml_path,
            $invoice->nombre_archivo_xml ?? 'gre.xml',
            ['Content-Type' => 'application/xml']
        );
    }

    public function destroy(Invoice $gre): RedirectResponse
    {
        $this->authorize('delete', $gre);
        abort_if($gre->codigo_tipo_documento !== '09', 404);

        $gre->items()->delete();
        $gre->delete();

        return redirect()
            ->route('facturador.gre.index')
            ->with('success', 'Guía de Remisión eliminada correctamente.');
    }
}
