<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRequestDocument extends Model
{
    protected $fillable = [
        'equipment_request_id', 'uploaded_by', 'kind',
        'original_name', 'path', 'mime_type', 'size',
    ];

    public const KINDS = [
        'invoice' => 'Factura',
        'receipt' => 'Ticket de compra',
        'delivery_evidence' => 'Evidencia de entrega',
        'other' => 'Otro',
    ];

    public function equipmentRequest(): BelongsTo
    {
        return $this->belongsTo(EquipmentRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
