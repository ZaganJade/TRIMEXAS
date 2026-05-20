<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'title',
    'category',
    'level',
    'rank',
    'year',
    'score',
    'verified_by_admin',
    'verified_by',
    'verified_at',
    'verification_note',
])]
class StudentAchievement extends Model
{
    protected $casts = [
        'verified_by_admin' => 'boolean',
        'verified_at' => 'datetime',
        'score' => 'decimal:2',
        'year' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
