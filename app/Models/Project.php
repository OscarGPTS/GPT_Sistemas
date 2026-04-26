<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'slug', 'description', 'area', 'manager_id',
        'status', 'priority', 'start_date', 'end_date', 'budget',
        'color', 'created_by', 'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code)) {
                $year = now()->year;
                $last = static::whereYear('created_at', $year)->max('id') ?? 0;
                $model->code = sprintf('PRJ-%d-%03d', $year, $last + 1);
            }
            if (empty($model->slug)) {
                $base = Str::slug($model->name);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $model->slug = $slug;
            }
        });
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class)->orderBy('position');
    }

    public function defaultBoard(): HasOne
    {
        return $this->hasOne(Board::class)->where('is_default', true);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function request(): HasOne
    {
        return $this->hasOne(ProjectRequest::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function userRole(?User $user): ?string
    {
        if (! $user) {
            return null;
        }
        if ($user->isAdmin()) {
            return 'admin';
        }
        if ($this->manager_id === $user->id || $this->created_by === $user->id) {
            return 'manager';
        }
        $member = $this->members()->where('users.id', $user->id)->first();
        return $member?->pivot->role;
    }

    public function canEdit(?User $user): bool
    {
        return in_array($this->userRole($user), ['admin', 'manager'], true);
    }

    public function canView(?User $user): bool
    {
        return $this->userRole($user) !== null;
    }
}
