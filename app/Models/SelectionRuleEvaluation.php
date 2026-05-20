<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'student_id',
    'rule_code',
    'consequent',
    'alpha',
    'z',
])]
class SelectionRuleEvaluation extends Model
{
    protected $casts = [
        'alpha' => 'float',
        'z' => 'float',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SelectionBatch::class, 'batch_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
