<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit log for credential interactions.
 * NEVER allow updates or deletes — these records are compliance-critical.
 */
class AccessLog extends Model
{
    public $timestamps = false; // We use only created_at via DEFAULT CURRENT_TIMESTAMP

    protected $fillable = ['user_id', 'access_id', 'action', 'field', 'reason', 'ip_address', 'user_agent'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public const ACTIONS = [
        'create' => 'Creación',
        'view_metadata' => 'Vista de metadatos',
        'reveal_request' => 'Solicitud de revelar',
        'reveal' => 'Revelado',
        'copy' => 'Copiado',
        'update' => 'Edición',
        'rotate' => 'Rotación de contraseña',
        'delete' => 'Eliminación',
        'failed_otp' => 'OTP fallido',
    ];

    protected static function booted(): void
    {
        // Hard guard: prevent updates / deletes through Eloquent
        static::updating(function (): bool {
            throw new \LogicException('AccessLog records are immutable.');
        });
        static::deleting(function (): bool {
            throw new \LogicException('AccessLog records cannot be deleted.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function access(): BelongsTo
    {
        return $this->belongsTo(Access::class);
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }
}
