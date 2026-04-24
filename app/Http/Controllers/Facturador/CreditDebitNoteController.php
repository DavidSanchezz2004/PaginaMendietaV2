<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreCreditDebitNoteRequest;
use App\Models\CreditDebitNote;
use App\Models\Invoice;
use App\Services\Facturador\CreditDebitNoteService;
use App\Services\Facturador\FeasyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditDebitNoteController extends Controller
{
    public function __construct(
        private readonly CreditDebitNoteService $noteService,
        private readonly FeasyService $feasyService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CreditDebitNote::class);

        $filters = $request->only(['estado', 'tipo', 'search']);
        $notes   = $this->noteService->paginate(15, $filters);

        $stats = [
            'total_credito' => CreditDebitNote::forActiveCompany()
                ->creditos()
                ->where('estado', 'sent')
                ->sum('monto_total'),
            'total_debito' => CreditDebitNote::forActiveCompany()
                ->debitos()
                ->where('estado', 'sent')
                ->sum('monto_total'),
            'pendientes' => CreditDebitNote::forActiveCompany()
                ->where('estado', 'draft')
                ->count(),
            'errores' => CreditDebitNote::forActiveCompany()
                ->where('estado', 'error')
                ->count(),
        ];

        return view('facturador.credit_debit_notes.index', compact('notes', 'filters', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', CreditDebitNote::class);

        // Obtener facturas/boletas emitidas
        $invoices = Invoice::forActiveCompany()
            ->whereIn('estado', ['sent', 'consulted'])
            ->selectRaw("id, CONCAT(serie_documento, '-', numero_documento) as display")
            ->orderBy('fecha_emision', 'desc')
            ->get()
            ->pluck('display', 'id');

        $suggestions = $this->noteService->getDocumentSuggestions();

        // Tipos de notas según SUNAT
        $notaTypes = [
            '01' => '01 - Descuento',
            '02' => '02 - Devolución',
            '03' => '03 - Bonificación',
            '04' => '04 - Otros conceptos',
        ];

        return view('facturador.credit_debit_notes.create', compact('invoices', 'suggestions', 'notaTypes'));
    }

    public function store(StoreCreditDebitNoteRequest $request): RedirectResponse
    {
        $this->authorize('create', CreditDebitNote::class);

        $validated = $request->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        // Inyectar user_id
        $validated['user_id'] = $request->user()->id;

        // Crear la nota
        $note = $this->noteService->create($validated, $items);

        return redirect()->route('facturador.credit_debit_notes.show', $note)
                        ->with('success', 'Nota de ' . ($note->isCreditNote() ? 'crédito' : 'débito') . ' creada exitosamente.');
    }

    public function show(CreditDebitNote $note): View
    {
        $this->authorize('view', $note);

        $invoice = $note->invoice;

        return view('facturador.credit_debit_notes.show', compact('note', 'invoice'));
    }

    public function emit(CreditDebitNote $note): RedirectResponse
    {
        $this->authorize('update', $note);

        if ($note->estado !== 'draft') {
            return back()->with('error', 'Solo se pueden enviar notas en estado borrador.');
        }

        try {
            // Enviar a Feasy
            $response = $this->feasyService->sendCreditDebitNote($note);

            if ($response['success']) {
                $note->update([
                    'estado' => 'sent',
                    'codigo_respuesta_feasy' => $response['data']['codigo_respuesta'] ?? null,
                    'url_pdf_feasy' => $response['data']['url_pdf'] ?? null,
                ]);

                return back()->with('success', 'Nota enviada a SUNAT exitosamente.');
            } else {
                $note->update([
                    'estado' => 'error',
                    'mensaje_respuesta_feasy' => $response['message'] ?? 'Error desconocido',
                ]);

                return back()->with('error', 'Error al enviar: ' . $response['message']);
            }
        } catch (\Exception $e) {
            $note->update([
                'estado' => 'error',
                'mensaje_respuesta_feasy' => $e->getMessage(),
            ]);

            return back()->with('error', 'Excepción: ' . $e->getMessage());
        }
    }

    public function consult(CreditDebitNote $note): RedirectResponse
    {
        $this->authorize('update', $note);

        if ($note->estado !== 'sent') {
            return back()->with('error', 'Solo se pueden consultar notas enviadas.');
        }

        try {
            $response = $this->feasyService->consultCreditDebitNote($note);

            if ($response['success']) {
                $note->update([
                    'estado' => 'consulted',
                    'codigo_respuesta_feasy' => $response['data']['codigo_respuesta'] ?? null,
                ]);

                return back()->with('success', 'Nota consultada en SUNAT.');
            } else {
                return back()->with('error', 'Error al consultar: ' . $response['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Excepción: ' . $e->getMessage());
        }
    }

    public function downloadXml(CreditDebitNote $note)
    {
        $this->authorize('view', $note);

        // TODO: Implementar descarga del XML desde Feasy
        return back()->with('info', 'Descarga de XML no disponible aún.');
    }
}
