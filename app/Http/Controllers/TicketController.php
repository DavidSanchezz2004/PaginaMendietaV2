<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\TicketStatusEnum;
use App\Http\Requests\Ticket\StoreTicketMessageRequest;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', Ticket::class);

        $query = Ticket::with(['client', 'company'])->latest('updated_at');

        if ($user->role === RoleEnum::CLIENT || $user->role === RoleEnum::AUXILIAR) {
            $activeCompanyId = session('company_id') ?? $user->companies()->first()?->id;
            abort_if(!$activeCompanyId, 403, 'Aún no perteneces a ninguna empresa.');
            
            $query->where('company_id', $activeCompanyId);
        }

        // Filtering by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(15)->withQueryString();
        
        return view('admin.tickets.index', compact('tickets'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Ticket::class);
        
        $user = $request->user();
        if ($user->role === RoleEnum::CLIENT || $user->role === RoleEnum::AUXILIAR) {
            $activeCompanyId = session('company_id') ?? $user->companies()->first()?->id;
            abort_if(!$activeCompanyId, 403, 'Debes tener una empresa activa para crear una consulta.');
        }

        return view('admin.tickets.create');
    }

    public function store(StoreTicketRequest $request)
    {
        $this->authorize('create', Ticket::class);
        $user = $request->user();
        
        $activeCompanyId = session('company_id') ?? $user->companies()->first()?->id;
        
        if (!$activeCompanyId) {
            throw ValidationException::withMessages(['general' => 'No se detectó empresa activa para asociar el ticket.']);
        }

        $ticket = Ticket::create([
            'subject' => $request->subject,
            'status' => TicketStatusEnum::OPEN,
            'company_id' => $activeCompanyId,
            'client_id' => $user->id,
        ]);

        $this->storeMessageData($request, $ticket, $user);

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Consulta enviada exitosamente.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        
        $ticket->load(['messages.user', 'client', 'company']);
        
        return view('admin.tickets.show', compact('ticket'));
    }

    public function storeMessage(StoreTicketMessageRequest $request, Ticket $ticket)
    {
        $this->authorize('createMessage', $ticket);
        $user = $request->user();

        // If ticket was closed, reopen it if client replies
        if ($ticket->status === TicketStatusEnum::CLOSED && ($user->role === RoleEnum::CLIENT || $user->role === RoleEnum::AUXILIAR)) {
            $ticket->update(['status' => TicketStatusEnum::OPEN]);
        }

        $this->storeMessageData($request, $ticket, $user);

        // Touch the ticket to update its updated_at column for sorting
        $ticket->touch();

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Mensaje enviado.');
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket)
    {
        $this->authorize('updateStatus', $ticket);

        $ticket->update([
            'status' => $request->status
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Estado del ticket actualizado.');
    }

    private function storeMessageData($request, Ticket $ticket, $user)
    {
        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            // Store explicitly in tickets directory inside public disk
            $attachmentPath = $file->store('tickets', 'public');
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message ?? '', // Fallback for attachment-only messages
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);
    }
}
