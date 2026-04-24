<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreClientRequest;
use App\Http\Requests\Facturador\UpdateClientRequest;
use App\Models\Client;
use App\Services\External\RucLookupService;
use App\Services\Facturador\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD de Clientes del Facturador.
 * Controller delgado: toda la lógica en ClientService.
 */
class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService    $service,
        private readonly RucLookupService $rucLookupService,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->service->paginate();

        return view('facturador.clients.index', compact('clients'));
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('facturador.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse|JsonResponse
    {
        $client = $this->service->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Cliente creado correctamente.',
                'client'  => [
                    'id'                    => $client->id,
                    'codigo_tipo_documento' => $client->codigo_tipo_documento,
                    'numero_documento'      => $client->numero_documento,
                    'nombre_razon_social'   => $client->nombre_razon_social,
                    'correo'                => $client->correo,
                    'is_retainer_agent'     => (bool) $client->is_retainer_agent,
                ],
            ], 201);
        }

        return redirect()->route('facturador.clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('facturador.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();

        // No sobrescribir la clave_sol existente si el campo llegó vacío
        if (empty($data['clave_sol'])) {
            unset($data['clave_sol']);
        }

        $this->service->update($client, $data);

        return redirect()->route('facturador.clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $this->service->deactivate($client);

        return redirect()->route('facturador.clients.index')
            ->with('success', 'Cliente desactivado.');
    }

    /**
     * Consulta RUC o DNI en la API de AQPFact y devuelve datos en JSON.
     * GET /facturador/clients/lookup-doc?type=6&number=20613427784
     */
    public function lookupDoc(Request $request): JsonResponse
    {
        $type   = (string) $request->input('type', ''); // '6'=RUC, '1'=DNI
        $number = trim((string) $request->input('number', ''));

        if ($number === '') {
            return response()->json(['ok' => false, 'error' => 'Número de documento requerido.'], 422);
        }

        try {
            if ($type === '6') {
                $data = $this->rucLookupService->lookup($number);
                return response()->json([
                    'ok'        => true,
                    'nombre'    => $data['name'],
                    'direccion' => $data['direccion'],
                ]);
            }

            if ($type === '1') {
                $data = $this->rucLookupService->lookupDni($number);
                return response()->json([
                    'ok'     => true,
                    'nombre' => $data['nombre'],
                ]);
            }

            return response()->json(['ok' => false, 'error' => 'Tipo de documento no soportado para búsqueda automática (solo RUC y DNI).'], 422);

        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
