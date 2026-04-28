<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessDocument extends Model
{
    protected $fillable = ['access_id', 'uploaded_by', 'original_name', 'path', 'mime_type', 'size'];

    public function access(): BelongsTo
    {
        return $this->belongsTo(Access::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
