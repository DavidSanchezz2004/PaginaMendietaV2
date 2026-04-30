<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sunat\StoreSunatApiCredentialRequest;
use App\Http\Requests\Sunat\ValidarComprobanteSunatRequest;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\SunatApiCredential;
use App\Models\SunatComprobanteValidacion;
use App\Services\SunatComprobanteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SunatComprobanteController extends Controller
{
    public function __construct(private readonly SunatComprobanteService $service)
    {
    }

    public function index(Request $request): View
    {
        $company = $this->activeCompany($request);
        $credential = SunatApiCredential::where('empresa_id', $company->id)->first();
        $lastValidation = SunatComprobanteValidacion::where('empresa_id', $company->id)
            ->latest()
            ->first();
        $prefill = $this->prefillFromInvoice($request, $company);

        return view('sunat.comprobantes.validar', compact('company', 'credential', 'lastValidation', 'prefill'));
    }

    public function validar(ValidarComprobanteSunatRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $validation = $this->service->validarComprobante(
                (int) $validated['empresa_id'],
                $validated
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('sunat.comprobantes.show', $validation)
            ->with('success', 'Consulta SUNAT registrada correctamente.');
    }

    public function historial(Request $request): View
    {
        $company = $this->activeCompany($request);
        $filters = $request->only(['from', 'to', 'num_ruc_emisor', 'cod_comp', 'estado_cp']);

        $validaciones = SunatComprobanteValidacion::with(['user', 'empresa'])
            ->where('empresa_id', $company->id)
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['num_ruc_emisor'] ?? null, fn ($q, $v) => $q->where('num_ruc_emisor', 'like', "%{$v}%"))
            ->when($filters['cod_comp'] ?? null, fn ($q, $v) => $q->where('cod_comp', $v))
            ->when($filters['estado_cp'] ?? null, fn ($q, $v) => $q->where('estado_cp', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('sunat.comprobantes.historial', compact('company', 'validaciones', 'filters'));
    }

    public function show(Request $request, SunatComprobanteValidacion $validacion): View
    {
        $company = $this->activeCompany($request);
        abort_if((int) $validacion->empresa_id !== (int) $company->id, 403);

        $validacion->load(['user', 'empresa', 'credential']);

        return view('sunat.comprobantes.show', compact('company', 'validacion'));
    }

    public function configurarCredenciales(Request $request): View
    {
        $company = $this->activeCompany($request);
        $this->authorize('updateSunatCredentials', $company);

        $credential = SunatApiCredential::where('empresa_id', $company->id)->first();

        return view('sunat.comprobantes.credenciales', compact('company', 'credential'));
    }

    public function guardarCredenciales(StoreSunatApiCredentialRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['ruc_consultante'] = preg_replace('/\D/', '', (string) $data['ruc_consultante']);
        $data['client_id'] = trim((string) $data['client_id']);
        $data['client_secret'] = isset($data['client_secret']) ? trim((string) $data['client_secret']) : null;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['scope'] = config('sunat.scope');
        $data['token_url'] = config('sunat.token_url');
        $data['consulta_url'] = config('sunat.consulta_url');

        $credential = SunatApiCredential::firstOrNew(['empresa_id' => (int) $data['empresa_id']]);
        $credential->fill(collect($data)->except('client_secret')->all());

        if (! empty($data['client_secret'])) {
            $credential->client_secret = $data['client_secret'];
        } elseif (! $credential->exists) {
            return back()->withInput()->with('error', 'Ingresa el client_secret para crear la configuración.');
        }

        $credential->save();

        return redirect()
            ->route('sunat.comprobantes.credenciales')
            ->with('success', 'Credenciales SUNAT guardadas para la empresa activa.');
    }

    public function probarConexion(Request $request): RedirectResponse
    {
        $company = $this->activeCompany($request);
        $this->authorize('updateSunatCredentials', $company);

        try {
            $this->service->refreshToken($this->service->getCredentialForEmpresa($company->id));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Conexión SUNAT correcta. Token generado para esta empresa.');
    }

    private function activeCompany(Request $request): Company
    {
        $company = Company::findOrFail((int) $request->session()->get('company_id'));
        $this->authorize('view', $company);

        return $company;
    }

    private function prefillFromInvoice(Request $request, Company $company): array
    {
        $invoiceId = (int) $request->query('invoice_id');

        if ($invoiceId <= 0) {
            return [];
        }

        $invoice = Invoice::withTrashed()
            ->with(['company', 'client'])
            ->where('company_id', $company->id)
            ->find($invoiceId);

        if (! $invoice) {
            return [];
        }

        return [
            'invoice_id' => $invoice->id,
            'serie_numero' => $invoice->serie_numero,
            'client_name' => $invoice->client?->nombre_razon_social,
            'client_document' => $invoice->client?->numero_documento,
            'numRuc' => preg_replace('/\D/', '', (string) $invoice->company?->ruc),
            'codComp' => $invoice->codigo_tipo_documento,
            'numeroSerie' => $invoice->serie_documento,
            'numero' => (int) $invoice->numero_documento,
            'fechaEmision' => $invoice->fecha_emision?->format('d/m/Y'),
            'monto' => number_format((float) $invoice->monto_total, 2, '.', ''),
        ];
    }
}
