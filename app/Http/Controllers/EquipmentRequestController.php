<?php

namespace App\Http\Controllers;

use App\Models\EquipmentRequest;
use App\Models\EquipmentRequestDocument;
use App\Models\Project;
use App\Models\User;
use App\Services\EquipmentRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EquipmentRequestController extends Controller
{
    public function __construct(private EquipmentRequestService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = EquipmentRequest::with('requester', 'items', 'project');

        // Restrict view by role
        if (! $user->isAdmin()
            && ! $user->hasRole(['it', 'auditor', 'petty_cash', 'manager', 'hr'])) {
            $query->where('requester_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%$search%")
                    ->orWhere('title', 'like', "%$search%");
            });
        }

        return view('equipment_requests.index', [
            'requests' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['status', 'q']),
            'statuses' => EquipmentRequest::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('equipment_requests.form', [
            'request' => new EquipmentRequest(['priority' => 'medium']),
            'projects' => Project::whereIn('status', ['planning', 'in_progress'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRequest($request);
        $items = $data['items'] ?? [];
        unset($data['items']);

        $data['requester_id'] = $request->user()->id;
        $data['status'] = 'draft';
        $eqRequest = EquipmentRequest::create($data);

        foreach ($items as $item) {
            if (empty($item['type']) || empty($item['description'])) {
                continue;
            }
            $eqRequest->items()->create([
                'type' => $item['type'],
                'description' => $item['description'],
                'suggested_model' => $item['suggested_model'] ?? null,
                'purchase_link' => $item['purchase_link'] ?? null,
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'estimated_unit_cost' => $item['estimated_unit_cost'] ?? null,
                'is_inventoriable' => ! empty($item['is_inventoriable']),
            ]);
        }

        if ($request->input('action') === 'submit') {
            $this->service->submit($eqRequest);
            return redirect()->route('equipment_requests.show', $eqRequest)
                ->with('success', 'Solicitud enviada a aprobación.');
        }

        return redirect()->route('equipment_requests.show', $eqRequest)
            ->with('success', 'Borrador guardado.');
    }

    public function show(EquipmentRequest $equipmentRequest): View
    {
        $this->authorizeView($equipmentRequest);
        $equipmentRequest->load([
            'requester', 'items.asset', 'documents.uploader',
            'workflowInstance.workflow.steps', 'workflowInstance.approvals.approver', 'workflowInstance.approvals.step',
            'itValidator', 'buyer', 'deliveredBy', 'deliveredTo', 'project',
        ]);
        return view('equipment_requests.show', [
            'request' => $equipmentRequest,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'me' => auth()->user(),
        ]);
    }

    public function submit(EquipmentRequest $equipmentRequest): RedirectResponse
    {
        abort_unless(
            $equipmentRequest->requester_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );
        $this->service->submit($equipmentRequest);
        return back()->with('success', 'Solicitud enviada a aprobación.');
    }

    public function validateIt(Request $request, EquipmentRequest $equipmentRequest): RedirectResponse
    {
        abort_unless(
            auth()->user()->isAdmin() || auth()->user()->hasPermission('equipment_requests.validate'),
            403
        );

        $data = $request->validate([
            'preferred_supplier' => ['nullable', 'string', 'max:200'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'it_notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.approved_model' => ['nullable', 'string', 'max:200'],
            'items.*.estimated_unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.is_inventoriable' => ['nullable'],
        ]);

        $items = $data['items'] ?? [];
        unset($data['items']);

        $this->service->validateByIt($equipmentRequest, auth()->user(), $data, $items);
        return back()->with('success', 'Solicitud validada por TI · enviada a caja chica.');
    }

    public function purchase(Request $request, EquipmentRequest $equipmentRequest): RedirectResponse
    {
        abort_unless(
            auth()->user()->isAdmin() || auth()->user()->hasPermission('equipment_requests.purchase'),
            403
        );

        $data = $request->validate([
            'actual_supplier' => ['required', 'string', 'max:200'],
            'actual_cost' => ['required', 'numeric', 'min:0'],
            'purchased_at' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:80'],
            'invoice' => ['nullable', 'file', 'max:10240'],
            'items' => ['array'],
            'items.*.actual_unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);
        $items = $data['items'] ?? [];
        unset($data['items'], $data['invoice']);

        $this->service->recordPurchase($equipmentRequest, auth()->user(), $data, $items);

        // Upload invoice if provided
        if ($request->hasFile('invoice')) {
            $this->storeDocument($equipmentRequest, $request->file('invoice'), 'invoice');
        }

        return back()->with('success', 'Compra registrada · pendiente de entrega.');
    }

    public function deliver(Request $request, EquipmentRequest $equipmentRequest): RedirectResponse
    {
        abort_unless(
            auth()->user()->isAdmin()
                || auth()->user()->hasPermission('equipment_requests.deliver')
                || auth()->user()->hasPermission('equipment_requests.purchase'),
            403
        );

        $data = $request->validate([
            'delivered_to' => ['nullable', 'exists:users,id'],
            'delivered_at' => ['required', 'date'],
            'delivery_notes' => ['nullable', 'string'],
            'evidence' => ['nullable', 'file', 'max:10240'],
            'create_assets' => ['nullable'],
        ]);

        $createAssets = ! empty($data['create_assets']);
        unset($data['evidence'], $data['create_assets']);

        $this->service->recordDelivery($equipmentRequest, auth()->user(), $data, $createAssets);

        if ($request->hasFile('evidence')) {
            $this->storeDocument($equipmentRequest, $request->file('evidence'), 'delivery_evidence');
        }

        return back()->with('success', 'Entrega registrada.');
    }

    public function uploadDocument(Request $request, EquipmentRequest $equipmentRequest): RedirectResponse
    {
        $this->authorizeView($equipmentRequest);
        $request->validate([
            'kind' => ['required', 'in:invoice,receipt,delivery_evidence,other'],
            'file' => ['required', 'file', 'max:10240'],
        ]);
        $this->storeDocument($equipmentRequest, $request->file('file'), $request->input('kind'));
        return back()->with('success', 'Documento adjuntado.');
    }

    public function downloadDocument(int $id)
    {
        $doc = EquipmentRequestDocument::with('equipmentRequest')->findOrFail($id);
        $this->authorizeView($doc->equipmentRequest);
        return Storage::download($doc->path, $doc->original_name);
    }

    public function cancel(Request $request, EquipmentRequest $equipmentRequest): RedirectResponse
    {
        abort_unless(
            $equipmentRequest->requester_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );
        $this->service->cancel($equipmentRequest, $request->input('reason'));
        return back()->with('success', 'Solicitud cancelada.');
    }

    private function storeDocument(EquipmentRequest $request, $file, string $kind): EquipmentRequestDocument
    {
        $path = $file->store('equipment_requests/'.$request->id);
        return $request->documents()->create([
            'uploaded_by' => auth()->id(),
            'kind' => $kind,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    private function authorizeView(EquipmentRequest $request): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return;
        }
        if ($user->hasRole(['it', 'auditor', 'petty_cash', 'hr', 'manager'])) {
            return;
        }
        abort_unless($request->requester_id === $user->id, 403);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'justification' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'items' => ['array', 'min:1'],
            'items.*.type' => ['required_with:items', 'string', 'max:80'],
            'items.*.description' => ['required_with:items', 'string'],
            'items.*.suggested_model' => ['nullable', 'string', 'max:200'],
            'items.*.purchase_link' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.estimated_unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.is_inventoriable' => ['nullable'],
        ]);
    }
}
