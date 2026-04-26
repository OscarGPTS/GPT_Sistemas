<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(private TicketService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Ticket::with(['requester', 'assignee', 'asset']);

        if (! $user->isAdmin() && ! $user->hasRole(['it', 'auditor'])) {
            $query->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('assignee_id', $user->id);
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                    ->orWhere('subject', 'like', "%$search%");
            });
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();
        return view('tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'type', 'q']),
        ]);
    }

    public function create(): View
    {
        return view('tickets.form', [
            'ticket' => new Ticket(),
            'assets' => Asset::orderBy('internal_code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:incident,request,maintenance'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'subject' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'due_at' => ['nullable', 'date'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ]);
        $data['requester_id'] = $request->user()->id;
        $data['status'] = 'open';
        $ticket = Ticket::create($data);

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('tickets/'.$ticket->id);
            $ticket->attachments()->create([
                'uploaded_by' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $this->service->notifyCreated($ticket);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket '.$ticket->code.' creado.');
    }

    public function show(Ticket $ticket): View
    {
        $this->authorizeView($ticket);
        $ticket->load(['requester', 'assignee', 'asset', 'comments.user', 'attachments', 'history.user']);
        return view('tickets.show', [
            'ticket' => $ticket,
            'itUsers' => User::whereHas('roles', fn ($q) => $q->whereIn('name', ['it', 'admin']))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeManage($ticket);
        $data = $request->validate([
            'status' => ['nullable', 'in:open,in_progress,on_hold,resolved,closed,cancelled'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);
        if (! empty($data['status'])) {
            $this->service->changeStatus($ticket, $data['status']);
        }
        if (! empty($data['assignee_id'])) {
            $this->service->assign($ticket, User::findOrFail($data['assignee_id']));
        }
        if (! empty($data['priority']) && $data['priority'] !== $ticket->priority) {
            $ticket->update(['priority' => $data['priority']]);
        }
        return back()->with('success', 'Ticket actualizado.');
    }

    public function comment(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeView($ticket);
        $data = $request->validate([
            'body' => ['required', 'string'],
            'is_internal' => ['boolean'],
        ]);
        $this->service->addComment(
            $ticket,
            $data['body'],
            (bool) ($data['is_internal'] ?? false)
        );
        return back()->with('success', 'Comentario añadido.');
    }

    public function downloadAttachment(int $id)
    {
        $attachment = \App\Models\TicketAttachment::findOrFail($id);
        $this->authorizeView($attachment->ticket);
        return Storage::download($attachment->path, $attachment->original_name);
    }

    private function authorizeView(Ticket $ticket): void
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->hasRole(['it', 'auditor'])) {
            return;
        }
        abort_unless(
            $ticket->requester_id === $user->id || $ticket->assignee_id === $user->id,
            403
        );
    }

    private function authorizeManage(Ticket $ticket): void
    {
        $user = auth()->user();
        abort_unless(
            $user->isAdmin() || $user->hasRole(['it']) || $ticket->assignee_id === $user->id,
            403
        );
    }
}
