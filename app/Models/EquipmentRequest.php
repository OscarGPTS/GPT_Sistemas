<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'requester_id', 'title', 'justification', 'priority', 'status',
        'workflow_instance_id',
        'it_validator_id', 'it_validated_at', 'it_notes', 'preferred_supplier', 'estimated_cost',
        'buyer_id', 'purchased_at', 'actual_supplier', 'actual_cost', 'invoice_number',
        'delivered_by', 'delivered_to', 'delivered_at', 'delivery_notes',
        'project_id',
    ];

    protected $casts = [
        'it_validated_at' => 'datetime',
        'purchased_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public const STATUSES = [
        'draft' => 'Borrador',
        'in_review' => 'En aprobación',
        'approved' => 'Aprobada · Pendiente compra',
        'rejected' => 'Rechazada',
        'purchasing' => 'En compra',
        'purchased' => 'Comprada · Pendiente entrega',
        'delivered' => 'Entregada',
        'cancelled' => 'Cancelada',
    ];

    public const STATUS_COLORS = [
        'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
        'in_review' => 'bg-amber-50 text-amber-700 border-amber-200',
        'approved' => 'bg-blue-50 text-blue-700 border-blue-200',
        'rejected' => 'bg-red-50 text-red-700 border-red-200',
        'purchasing' => 'bg-violet-50 text-violet-700 border-violet-200',
        'purchased' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
        'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'cancelled' => 'bg-slate-100 text-slate-500 border-slate-200',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code)) {
                $year = now()->year;
                $last = static::whereYear('created_at', $year)->max('id') ?? 0;
                $model->code = sprintf('ER-%d-%04d', $year, $last + 1);
            }
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function itValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'it_validator_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function deliveredTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_to');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EquipmentRequestItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EquipmentRequestDocument::class);
    }

    public function totalEstimated(): float
    {
        return (float) $this->items->sum(fn ($i) => (float) ($i->estimated_unit_cost ?? 0) * $i->quantity);
    }

    public function totalActual(): float
    {
        return (float) $this->items->sum(fn ($i) => (float) ($i->actual_unit_cost ?? 0) * $i->quantity);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-slate-100 text-slate-600';
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['rejected', 'cancelled', 'delivered'], true);
    }
}
