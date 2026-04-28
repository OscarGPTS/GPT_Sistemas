<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpToken extends Model
{
    protected $fillable = ['user_id', 'purpose', 'access_id', 'code_hash', 'expires_at', 'used_at', 'ip_address'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function access(): BelongsTo
    {
        return $this->belongsTo(Access::class);
    }

    public function isUsable(): bool
    {
        return ! $this->used_at && $this->expires_at && $this->expires_at->isFuture();
    }
}
