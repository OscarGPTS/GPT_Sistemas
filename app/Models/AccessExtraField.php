<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class AccessExtraField extends Model
{
    protected $fillable = ['access_id', 'field_name', 'field_label', 'field_value', 'is_sensitive', 'position'];

    protected $casts = [
        'is_sensitive' => 'boolean',
    ];

    protected $hidden = ['field_value'];

    /**
     * Conditionally encrypt: only when is_sensitive=true.
     * We can't use the 'encrypted' cast because it's all-or-nothing per column.
     */
    protected function fieldValue(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->is_sensitive && $value
                ? rescue(fn () => Crypt::decryptString($value), $value, false)
                : $value,
            set: fn ($value) => $this->is_sensitive && $value
                ? Crypt::encryptString($value)
                : $value,
        );
    }

    public function access(): BelongsTo
    {
        return $this->belongsTo(Access::class);
    }

    public function maskedValue(): string
    {
        if (! $this->field_value) {
            return '—';
        }
        return $this->is_sensitive ? str_repeat('•', 8) : (string) $this->field_value;
    }
}
