<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentationVideo extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'stored_path',
        'video_path',
        'slide_count',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'slide_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'failed' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
