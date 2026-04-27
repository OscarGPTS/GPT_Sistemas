<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\EquipmentRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Notifications\EquipmentRequestApproved;
use App\Notifications\EquipmentRequestPurchased;
use App\Notifications\EquipmentRequestSubmitted;
use App\Notifications\EquipmentRequestDelivered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class EquipmentRequestService
{
    public function __construct(
        private WorkflowEngine $engine,
        private AuditService $audit,
        private AssetAssignmentService $assignments,
    ) {
    }

    public function submit(EquipmentRequest $request): EquipmentRequest
    {
        if (! in_array($request->status, ['draft', 'rejected'], true)) {
            throw new \RuntimeException('Esta solicitud ya fue enviada.');
        }
        if ($request->items()->count() === 0) {
            throw new \RuntimeException('La solicitud requiere al menos un equipo.');
        }

        $workflow = Workflow::where('slug', 'equipment-request')->where('is_active', true)->first();
        if (! $workflow) {
            throw new \RuntimeException('No hay flujo activo para solicitudes de equipo.');
        }

        return DB::transaction(function () use ($request, $workflow) {
            $instance = $this->engine->start($workflow, $request->requester, $request, [
                'equipment_request_id' => $request->id,
                'title' => $request->title,
                'priority' => $request->priority,
                'estimated_total' => $request->totalEstimated(),
                'item_count' => $request->items()->count(),
            ]);
            $request->update([
                'status' => 'in_review',
                'workflow_instance_id' => $instance->id,
            ]);
            $this->audit->log('equipment_request.submitted', $request);
            return $request->fresh();
        });
    }

    /**
     * Called by WorkflowEngine when the workflow instance reaches a final state.
     */
    public function syncFromInstance(WorkflowInstance $instance): void
    {
        if ($instance->subject_type !== EquipmentRequest::class) {
            return;
        }
        $request = EquipmentRequest::find($instance->subject_id);
        if (! $request) {
            return;
        }

        if ($instance->status === 'rejected') {
            $request->update(['status' => 'rejected']);
            $this->audit->log('equipment_request.rejected', $request);
            return;
        }

        if ($instance->status === 'approved') {
            $request->update(['status' => 'approved']);
            $this->audit->log('equipment_request.approved', $request);
            $this->notifyOnApproved($request);
        }
    }

    public function validateByIt(EquipmentRequest $request, User $validator, array $data, array $items): EquipmentRequest
    {
        if ($request->status !== 'approved') {
            throw new \RuntimeException('La solicitud debe estar aprobada para validar.');
        }

        return DB::transaction(function () use ($request, $validator, $data, $items) {
            $request->update([
                'it_validator_id' => $validator->id,
                'it_validated_at' => now(),
                'it_notes' => $data['it_notes'] ?? null,
                'preferred_supplier' => $data['preferred_supplier'] ?? null,
                'estimated_cost' => $data['estimated_cost'] ?? $request->estimated_cost,
                'status' => 'purchasing',
            ]);

            // Update items
            foreach ($items as $itemId => $itemData) {
                $item = $request->items()->find($itemId);
                if (! $item) {
                    continue;
                }
                $item->update(array_filter([
                    'approved_model' => $itemData['approved_model'] ?? null,
                    'estimated_unit_cost' => $itemData['estimated_unit_cost'] ?? null,
                    'is_inventoriable' => isset($itemData['is_inventoriable']),
                ], fn ($v) => $v !== null));
            }

            $this->audit->log('equipment_request.validated', $request);
            $this->notifyPettyCash($request);
            return $request->fresh('items');
        });
    }

    public function recordPurchase(EquipmentRequest $request, User $buyer, array $data, array $items): EquipmentRequest
    {
        if (! in_array($request->status, ['purchasing', 'approved'], true)) {
            throw new \RuntimeException('La solicitud no está en estado de compra.');
        }

        return DB::transaction(function () use ($request, $buyer, $data, $items) {
            $request->update([
                'buyer_id' => $buyer->id,
                'purchased_at' => $data['purchased_at'] ?? now(),
                'actual_supplier' => $data['actual_supplier'] ?? null,
                'actual_cost' => $data['actual_cost'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'status' => 'purchased',
            ]);

            foreach ($items as $itemId => $itemData) {
                $item = $request->items()->find($itemId);
                if (! $item) {
                    continue;
                }
                if (isset($itemData['actual_unit_cost'])) {
                    $item->update(['actual_unit_cost' => $itemData['actual_unit_cost']]);
                }
            }

            $this->audit->log('equipment_request.purchased', $request);
            $this->notifyOnPurchased($request);
            return $request->fresh('items');
        });
    }

    public function recordDelivery(EquipmentRequest $request, User $delivererBy, array $data, bool $createAssets = true): EquipmentRequest
    {
        if ($request->status !== 'purchased') {
            throw new \RuntimeException('La solicitud debe estar comprada para entregar.');
        }

        return DB::transaction(function () use ($request, $delivererBy, $data, $createAssets) {
            $deliveredTo = isset($data['delivered_to'])
                ? User::findOrFail($data['delivered_to'])
                : $request->requester;

            $request->update([
                'delivered_by' => $delivererBy->id,
                'delivered_to' => $deliveredTo->id,
                'delivered_at' => $data['delivered_at'] ?? now(),
                'delivery_notes' => $data['delivery_notes'] ?? null,
                'status' => 'delivered',
            ]);

            if ($createAssets) {
                $this->createAssetsForInventoriableItems($request, $deliveredTo);
            }

            $this->audit->log('equipment_request.delivered', $request);
            $this->notifyOnDelivered($request);
            return $request->fresh('items.asset');
        });
    }

    public function cancel(EquipmentRequest $request, ?string $reason = null): void
    {
        if ($request->status === 'delivered') {
            throw new \RuntimeException('No se puede cancelar una solicitud entregada.');
        }
        $request->update(['status' => 'cancelled']);
        $this->audit->log('equipment_request.cancelled', $request, [], ['reason' => $reason]);
    }

    private function createAssetsForInventoriableItems(EquipmentRequest $request, User $user): void
    {
        // Try to map each item type to a category by name (case-insensitive)
        $categories = AssetCategory::all()->keyBy(fn ($c) => Str::lower($c->name));

        foreach ($request->items as $item) {
            if (! $item->is_inventoriable || $item->asset_id) {
                continue;
            }

            $categoryId = $categories->get(Str::lower($item->type))?->id
                ?? $categories->get('otros')?->id;

            // Create one asset per quantity unit (could be > 1)
            for ($q = 0; $q < $item->quantity; $q++) {
                $code = sprintf('GPT-%s-%d', strtoupper(Str::random(3)), $request->id * 1000 + $item->id * 10 + $q);
                $asset = Asset::create([
                    'internal_code' => $code,
                    'category_id' => $categoryId,
                    'type' => $item->type,
                    'description' => $item->description,
                    'brand' => null,
                    'model' => $item->effectiveModel(),
                    'status' => 'available',
                    'condition' => 'new',
                    'purchase_date' => $request->purchased_at?->format('Y-m-d'),
                    'purchase_cost' => $item->actual_unit_cost ?? $item->estimated_unit_cost,
                    'supplier' => $request->actual_supplier ?? $request->preferred_supplier,
                    'created_by' => Auth::id(),
                ]);

                if ($q === 0) {
                    $item->update(['asset_id' => $asset->id]);
                }

                // Auto-assign to the recipient
                $this->assignments->assign($asset, $user, [
                    'assignment_reason' => 'Entrega por solicitud '.$request->code,
                ]);
            }
        }
    }

    // ========== Notifications ==========

    public function notifyOnSubmit(EquipmentRequest $request): void
    {
        $itTeam = $this->itTeam();
        if ($itTeam->isNotEmpty()) {
            Notification::send($itTeam, new EquipmentRequestSubmitted($request));
        }
    }

    private function notifyOnApproved(EquipmentRequest $request): void
    {
        // IT team needs to validate
        $recipients = $this->itTeam()->merge([$request->requester])->unique('id');
        Notification::send($recipients, new EquipmentRequestApproved($request));
    }

    private function notifyPettyCash(EquipmentRequest $request): void
    {
        $pettyCash = $this->roleUsers('petty_cash');
        if ($pettyCash->isNotEmpty()) {
            Notification::send($pettyCash, new EquipmentRequestApproved($request));
        }
    }

    private function notifyOnPurchased(EquipmentRequest $request): void
    {
        $recipients = $this->itTeam()->merge([$request->requester])->unique('id');
        Notification::send($recipients, new EquipmentRequestPurchased($request));
    }

    private function notifyOnDelivered(EquipmentRequest $request): void
    {
        $recipients = collect([$request->requester, $request->deliveredTo])->filter()->unique('id');
        Notification::send($recipients, new EquipmentRequestDelivered($request));
    }

    private function itTeam()
    {
        $roleIds = Role::whereIn('name', ['admin', 'it'])->pluck('id');
        return User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $roleIds))
            ->get();
    }

    private function roleUsers(string $name)
    {
        return User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', $name))
            ->get();
    }
}
