<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Location extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'parent_id', 'description', 'address', 'is_active', 'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'site' => 'Sitio',
        'building' => 'Edificio',
        'floor' => 'Piso',
        'area' => 'Área',
        'room' => 'Sala',
        'warehouse' => 'Almacén',
        'other' => 'Otro',
    ];

    public const TYPE_COLORS = [
        'site' => '#0ea5e9',
        'building' => '#6366f1',
        'floor' => '#8b5cf6',
        'area' => '#10b981',
        'room' => '#f59e0b',
        'warehouse' => '#94a3b8',
        'other' => '#64748b',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if (empty($model->slug)) {
                $base = Str::slug($model->name);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $model->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $model->slug = $slug;
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('name');
    }

    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    public function deviceUsers(): HasMany
    {
        return $this->hasMany(DeviceUser::class);
    }

    public function scopeRoots(Builder $q): Builder
    {
        return $q->whereNull('parent_id');
    }

    /**
     * Returns the breadcrumb path: [root, ..., self]
     */
    public function path(): Collection
    {
        $chain = collect();
        $node = $this;
        while ($node) {
            $chain->prepend($node);
            $node = $node->parent;
        }
        return $chain;
    }

    public function fullName(string $separator = ' › '): string
    {
        return $this->path()->pluck('name')->implode($separator);
    }

    /**
     * Recursively gather IDs of self + all descendants.
     */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->descendantIds());
        }
        return $ids;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function typeColor(): string
    {
        return self::TYPE_COLORS[$this->type] ?? '#64748b';
    }
}
