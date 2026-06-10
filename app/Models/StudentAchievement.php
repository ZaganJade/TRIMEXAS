<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
    'certificate_path',
    'certificate_original_name',
    'certificate_size',
])]
class StudentAchievement extends Model
{
    /** @use HasFactory<\Database\Factories\StudentAchievementFactory> */
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'category', 'level', 'rank', 'year', 'score', 'verified_by_admin'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('student_achievement');
    }
    public const CATEGORY_AKADEMIS = 'akademis';
    public const CATEGORY_NON_AKADEMIS = 'non_akademis';

    protected $casts = [
        'year' => 'integer',
        'score' => 'float',
        'verified_by_admin' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
