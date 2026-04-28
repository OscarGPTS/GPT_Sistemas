<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetCamera extends Model
{
    protected $fillable = [
        'asset_id', 'protocol', 'stream_url', 'mjpeg_url', 'snapshot_url', 'nvr_url',
        'resolution', 'fps', 'has_audio', 'has_motion_detection', 'has_ptz', 'access_id',
    ];

    protected $casts = [
        'has_audio' => 'boolean',
        'has_motion_detection' => 'boolean',
        'has_ptz' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function access(): BelongsTo
    {
        return $this->belongsTo(Access::class);
    }

    /**
     * Returns the best URL for live preview in a browser.
     * Priority: MJPEG (works in <img>) > snapshot (refresh) > none.
     */
    public function previewUrl(): ?string
    {
        return $this->mjpeg_url ?: $this->snapshot_url;
    }

    public function hasLivePreview(): bool
    {
        return ! empty($this->mjpeg_url) || ! empty($this->snapshot_url);
    }
}
