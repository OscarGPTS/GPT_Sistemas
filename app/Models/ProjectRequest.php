<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'requester_id', 'name', 'description', 'justification',
        'impact', 'priority', 'budget_estimate', 'expected_area',
        'desired_start_date', 'desired_end_date', 'status',
        'workflow_instance_id', 'project_id', 'submitted_at', 'decided_at',
    ];

    protected $casts = [
        'budget_estimate' => 'decimal:2',
        'desired_start_date' => 'date',
        'desired_end_date' => 'date',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->code)) {
                $year = now()->year;
                $last = static::whereYear('created_at', $year)->max('id') ?? 0;
                $model->code = sprintf('PR-%d-%03d', $year, $last + 1);
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
