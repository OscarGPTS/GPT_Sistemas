<?php

namespace App\Console\Commands;

use App\Models\Access;
use App\Models\OtpToken;
use App\Models\Role;
use App\Models\User;
use App\Notifications\StalePasswordsAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckStalePasswords extends Command
{
    protected $signature = 'gpt:check-stale-passwords
        {--days=90 : Días desde la última rotación para considerar una contraseña stale}';

    protected $description = 'Detecta contraseñas del vault sin rotar en N días y notifica a security_admin/it';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $stale = Access::with('type')
            ->where('is_active', true)
            ->where(function ($q) use ($days) {
                $q->whereNull('last_rotated_at')
                    ->orWhere('last_rotated_at', '<', now()->subDays($days));
            })
            ->get();

        $this->line("Contraseñas stale (>{$days} días): {$stale->count()}");

        // Cleanup expired OTP tokens (housekeeping)
        $deleted = OtpToken::where('expires_at', '<', now()->subDay())->delete();
        $this->line("OTP tokens expirados eliminados: {$deleted}");

        if ($stale->isEmpty()) {
            $this->info('✓ No hay contraseñas stale.');
            return Command::SUCCESS;
        }

        $roleIds = Role::whereIn('name', ['admin', 'security_admin', 'it'])->pluck('id');
        $recipients = User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $roleIds))
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new StalePasswordsAlert($stale));
            $this->info("✓ Alerta enviada a {$recipients->count()} usuario(s) de seguridad.");
        }

        return Command::SUCCESS;
    }
}
