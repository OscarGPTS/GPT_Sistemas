<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Access extends Model
{
    use SoftDeletes;

    protected $table = 'accesses';

    protected $fillable = [
        'code', 'type_id', 'name', 'description',
        'hostname', 'ip', 'port', 'url',
        'username', 'password', 'notes',
        'location_id', 'asset_id', 'owner_id', 'created_by',
        'last_rotated_at', 'expires_at', 'is_active',
    ];

    /**
     * Critical: encrypted casts ensure password/notes/username are AES-256 encrypted at rest.
     * Laravel uses APP_KEY to derive the key.
     */
    protected $casts = [
        'username' => 'encrypted',
        'password' => 'encrypted',
        'notes' => 'encrypted',
        'is_active' => 'boolean',
        'last_rotated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Hide the password from default array/JSON serialization.
     * It must be requested explicitly via the reveal flow.
     */
    protected $hidden = ['password'];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code)) {
                $year = now()->year;
                $last = static::withTrashed()->whereYear('created_at', $year)->max('id') ?? 0;
                $model->code = sprintf('ACC-%d-%04d', $year, $last + 1);
            }
            if (empty($model->last_rotated_at)) {
                $model->last_rotated_at = now();
            }
        });
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AccessType::class, 'type_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function extraFields(): HasMany
    {
        return $this->hasMany(AccessExtraField::class)->orderBy('position');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AccessLog::class)->orderBy('created_at', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AccessDocument::class);
    }

    public function maskedPassword(): string
    {
        return $this->password ? str_repeat('•', 10) : '—';
    }

    public function maskedUsername(): string
    {
        if (! $this->username) {
            return '—';
        }
        $u = $this->username;
        return strlen($u) <= 4 ? str_repeat('•', strlen($u)) : substr($u, 0, 2).str_repeat('•', max(0, strlen($u) - 4)).substr($u, -2);
    }

    public function isStale(int $days = 90): bool
    {
        if (! $this->last_rotated_at) {
            return true;
        }
        return $this->last_rotated_at->diffInDays(now()) > $days;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
