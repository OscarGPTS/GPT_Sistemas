<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'category_id', 'type', 'concept', 'notes', 'amount', 'expense_date',
        'supplier', 'invoice_number', 'payment_method',
        'project_id', 'asset_id', 'equipment_request_id', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code)) {
                $date = $model->expense_date ?? now();
                if (is_string($date)) {
                    $date = \Carbon\Carbon::parse($date);
                }
                $month = $date->format('Ym');
                $last = static::where('code', 'like', 'EXP-'.$month.'-%')->count();
                $model->code = sprintf('EXP-%s-%04d', $month, $last + 1);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function equipmentRequest(): BelongsTo
    {
        return $this->belongsTo(EquipmentRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ExpenseDocument::class);
    }
}
