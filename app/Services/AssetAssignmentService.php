<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Notifications\AssetAssigned;
use App\Notifications\AssetReleased;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AssetAssignmentService
{
    public function __construct(private AuditService $audit)
    {
    }

    public function assign(Asset $asset, User $user, array $data = []): AssetAssignment
    {
        return DB::transaction(function () use ($asset, $user, $data) {
            if ($asset->status === 'retired') {
                throw new RuntimeException('El activo está dado de baja y no puede asignarse.');
            }
            $previous = $asset->currentAssignment()->first();
            if ($previous) {
                $previous->update([
                    'is_active' => false,
                    'returned_at' => now(),
                    'return_reason' => $data['return_reason'] ?? 'Reasignación',
                    'condition_in' => $data['condition_in'] ?? null,
                ]);
                // Notify the previous user of the release
                if ($previous->user) {
                    $previous->user->notify(new AssetReleased($asset, 'Reasignación a otro colaborador'));
                }
            }

            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id,
                'user_id' => $user->id,
                'previous_user_id' => $previous?->user_id,
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
                'assignment_reason' => $data['assignment_reason'] ?? null,
                'assignment_location' => $data['assignment_location'] ?? $asset->location,
                'condition_out' => $data['condition_out'] ?? $asset->condition,
                'is_active' => true,
            ]);

            $asset->update(['status' => 'assigned']);

            $this->audit->log('asset.assigned', $asset, [], [
                'user_id' => $user->id,
                'assignment_id' => $assignment->id,
            ]);

            // Notify the new user
            $user->notify(new AssetAssigned($asset, $assignment));

            return $assignment;
        });
    }

    public function release(Asset $asset, array $data = []): void
    {
        DB::transaction(function () use ($asset, $data) {
            $current = $asset->currentAssignment()->first();
            if (! $current) {
                throw new RuntimeException('El activo no tiene una asignación activa.');
            }
            $user = $current->user;
            $reason = $data['return_reason'] ?? 'Liberación';
            $current->update([
                'is_active' => false,
                'returned_at' => now(),
                'return_reason' => $reason,
                'condition_in' => $data['condition_in'] ?? null,
            ]);
            $asset->update(['status' => 'available']);
            $this->audit->log('asset.released', $asset, ['assignment_id' => $current->id], []);

            if ($user) {
                $user->notify(new AssetReleased($asset, $reason));
            }
        });
    }
}
