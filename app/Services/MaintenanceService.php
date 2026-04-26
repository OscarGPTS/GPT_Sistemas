<?php

namespace App\Services;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function generateDueRecords(): int
    {
        $schedules = MaintenanceSchedule::where('is_active', true)
            ->whereDate('next_due_at', '<=', now()->addDays(7))
            ->get();

        $created = 0;
        DB::transaction(function () use ($schedules, &$created) {
            foreach ($schedules as $schedule) {
                $exists = MaintenanceRecord::where('schedule_id', $schedule->id)
                    ->where('status', 'scheduled')
                    ->whereDate('scheduled_date', $schedule->next_due_at)
                    ->exists();
                if ($exists) {
                    continue;
                }
                MaintenanceRecord::create([
                    'asset_id' => $schedule->asset_id,
                    'schedule_id' => $schedule->id,
                    'type' => $schedule->type,
                    'status' => 'scheduled',
                    'title' => $schedule->title,
                    'description' => $schedule->description,
                    'scheduled_date' => $schedule->next_due_at,
                    'responsible_id' => $schedule->responsible_id,
                ]);
                $created++;
            }
        });

        return $created;
    }

    public function markCompleted(MaintenanceRecord $record, array $data): void
    {
        DB::transaction(function () use ($record, $data) {
            $record->update([
                'status' => 'completed',
                'performed_date' => $data['performed_date'] ?? now(),
                'provider' => $data['provider'] ?? null,
                'cost' => $data['cost'] ?? null,
                'result_notes' => $data['result_notes'] ?? null,
            ]);

            if ($record->schedule_id && $record->schedule) {
                $schedule = $record->schedule;
                $schedule->update([
                    'last_run_at' => $record->performed_date,
                    'next_due_at' => $this->nextDueFor($schedule, Carbon::parse($record->performed_date)),
                ]);
            }
        });
    }

    private function nextDueFor(MaintenanceSchedule $schedule, Carbon $from): Carbon
    {
        return match ($schedule->frequency) {
            'monthly' => $from->copy()->addMonth(),
            'quarterly' => $from->copy()->addMonths(3),
            'biannual' => $from->copy()->addMonths(6),
            'annual' => $from->copy()->addYear(),
            'custom' => $from->copy()->addDays((int) ($schedule->interval_days ?: 30)),
        };
    }
}
