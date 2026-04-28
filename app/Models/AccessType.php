<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AccessType extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'color', 'default_fields', 'is_active'];

    protected $casts = [
        'default_fields' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(Access::class, 'type_id');
    }
}
