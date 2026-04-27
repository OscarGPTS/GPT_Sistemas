<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRequestItem extends Model
{
    protected $fillable = [
        'equipment_request_id', 'type', 'description', 'suggested_model', 'purchase_link',
        'quantity', 'approved_model', 'estimated_unit_cost', 'actual_unit_cost',
        'asset_id', 'is_inventoriable',
    ];

    protected $casts = [
        'is_inventoriable' => 'boolean',
        'estimated_unit_cost' => 'decimal:2',
        'actual_unit_cost' => 'decimal:2',
    ];

    public function equipmentRequest(): BelongsTo
    {
        return $this->belongsTo(EquipmentRequest::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function effectiveModel(): string
    {
        return $this->approved_model ?: ($this->suggested_model ?? '—');
    }
}
