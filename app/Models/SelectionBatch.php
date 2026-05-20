<?php

namespace App\Models;

use Database\Factories\SelectionBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'label',
    'triggered_by',
    'status',
    'total_candidates',
    'total_eligible',
    'total_ineligible',
    'processed_count',
    'snapshot_fuzzy_sets',
    'snapshot_rules',
    'snapshot_thresholds',
    'error_summary',
    'started_at',
    'completed_at',
])]
class SelectionBatch extends Model
{
    /** @use HasFactory<SelectionBatchFactory> */
    use HasFactory;

    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $casts = [
        'snapshot_fuzzy_sets' => 'array',
        'snapshot_rules' => 'array',
        'snapshot_thresholds' => 'array',
        'error_summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_candidates' => 'integer',
        'total_eligible' => 'integer',
        'total_ineligible' => 'integer',
        'processed_count' => 'integer',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(SelectionResult::class, 'batch_id');
    }

    public function ruleEvaluations(): HasMany
    {
        return $this->hasMany(SelectionRuleEvaluation::class, 'batch_id');
    }
}
