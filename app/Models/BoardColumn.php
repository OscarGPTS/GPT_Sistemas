<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BoardColumn extends Model
{
    protected $fillable = [
        'board_id', 'name', 'slug', 'color', 'position', 'wip_limit', 'is_done_column',
    ];

    protected $casts = [
        'is_done_column' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'column_id')->orderBy('position');
    }
}
