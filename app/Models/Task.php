<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'project_id', 'board_id', 'column_id', 'title', 'description',
        'assignee_id', 'created_by', 'priority', 'due_at', 'started_at', 'completed_at',
        'position', 'tags', 'ticket_id', 'asset_id',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'tags' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code)) {
                $year = now()->year;
                $last = static::whereYear('created_at', $year)->max('id') ?? 0;
                $model->code = sprintf('T-%d-%06d', $year, $last + 1);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(BoardColumn::class, 'column_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('position');
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->orderBy('created_at', 'desc');
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast() && ! $this->completed_at;
    }
}
