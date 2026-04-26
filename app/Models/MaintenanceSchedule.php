<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'asset_id', 'type', 'title', 'description', 'frequency',
        'interval_days', 'next_due_at', 'last_run_at', 'responsible_id', 'is_active',
    ];

    protected $casts = [
        'next_due_at' => 'date',
        'last_run_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'schedule_id');
    }

    public function isDue(): bool
    {
        return $this->is_active && $this->next_due_at && $this->next_due_at->isPast();
    }

    public function daysUntilDue(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->next_due_at, false);
    }
}
