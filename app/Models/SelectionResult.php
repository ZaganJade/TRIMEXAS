<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'student_id',
    'eligible',
    'ineligibility_reasons',
    'input_snapshot',
    'score',
    'category',
    'rank',
])]
class SelectionResult extends Model
{
    protected $casts = [
        'eligible' => 'boolean',
        'ineligibility_reasons' => 'array',
        'input_snapshot' => 'array',
        'score' => 'float',
        'rank' => 'integer',
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
