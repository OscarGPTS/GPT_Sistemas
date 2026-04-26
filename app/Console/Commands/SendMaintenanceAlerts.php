<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Role;
use App\Models\User;
use App\Notifications\MaintenanceDue;
use App\Notifications\WarrantyExpiring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendMaintenanceAlerts extends Command
{
    protected $signature = 'gpt:send-alerts
        {--days-due=7 : Días de anticipación para alertar mantenimientos próximos}
        {--days-warranty=60 : Días de anticipación para alertar garantías próximas}';

    protected $description = 'Envía alertas por email y sistema sobre mantenimientos próximos, vencidos y garantías por expirar';

    public function handle(): int
    {
        $daysDue = (int) $this->option('days-due');
        $daysWarranty = (int) $this->option('days-warranty');

        $this->maintenanceUpcoming($daysDue);
        $this->maintenanceOverdue();
        $this->warrantyExpiring($daysWarranty);

        $this->info('✓ Alertas procesadas.');
        return Command::SUCCESS;
    }

    private function maintenanceUpcoming(int $days): void
    {
        $records = MaintenanceRecord::where('status', 'scheduled')
            ->whereDate('scheduled_date', '<=', now()->addDays($days))
            ->whereDate('scheduled_date', '>=', now())
            ->with(['asset', 'responsible'])
            ->get();

        $this->line("Mantenimientos próximos ({$days} días): {$records->count()}");

        foreach ($records as $record) {
            $recipients = collect();
            if ($record->responsible) {
                $recipients->push($record->responsible);
            }
            // Also notify IT team
            $recipients = $recipients->merge($this->itTeam())->unique('id');

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new MaintenanceDue($record, false));
            }
        }
    }

    private function maintenanceOverdue(): void
    {
        $records = MaintenanceRecord::where('status', 'scheduled')
            ->whereDate('scheduled_date', '<', now())
            ->with(['asset', 'responsible'])
            ->get();

        $this->line("Mantenimientos vencidos: {$records->count()}");

        foreach ($records as $record) {
            $recipients = collect();
            if ($record->responsible) {
                $recipients->push($record->responsible);
            }
            $recipients = $recipients->merge($this->itTeam())->unique('id');

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new MaintenanceDue($record, true));
            }
        }
    }

    private function warrantyExpiring(int $days): void
    {
        $assets = Asset::whereDate('warranty_until', '>=', now())
            ->whereDate('warranty_until', '<=', now()->addDays($days))
            ->where('status', '!=', 'retired')
            ->get();

        $this->line("Garantías próximas a vencer ({$days} días): {$assets->count()}");

        if ($assets->isEmpty()) {
            return;
        }
        $recipients = $this->itTeam();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new WarrantyExpiring($assets));
        }
    }

    private function itTeam()
    {
        $roleIds = Role::whereIn('name', ['admin', 'it'])->pluck('id');
        return User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $roleIds))
            ->get();
    }
}
