<?php

namespace App\Models;

use App\Domain\Achievement\AchievementScorer;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id',
    'nim',
    'full_name',
    'semester',
    'status',
    'ipk',
    'penghasilan_ortu',
    'tanggungan',
    'phone',
    'address',
])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nim', 'full_name', 'semester', 'status', 'ipk', 'penghasilan_ortu', 'tanggungan'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('student');
    }

    protected $casts = [
        'ipk' => 'decimal:2',
        'semester' => 'integer',
        'penghasilan_ortu' => 'integer',
        'tanggungan' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(StudentAchievement::class);
    }

    /**
     * Agregat skor prestasi akademis (cap 50).
     */
    protected function agregatAkademis(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->aggregateAchievements()['akademis'],
        )->shouldCache();
    }

    /**
     * Agregat skor prestasi non-akademis (cap 50).
     */
    protected function agregatNonAkademis(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->aggregateAchievements()['non_akademis'],
        )->shouldCache();
    }

    /**
     * @return array{akademis: float, non_akademis: float}
     */
    private function aggregateAchievements(): array
    {
        $entries = $this->relationLoaded('achievements')
            ? $this->achievements
            : $this->achievements()->get(['id', 'student_id', 'category', 'score']);

        return AchievementScorer::aggregateByCategory(
            $entries->map(fn (StudentAchievement $a) => [
                'category' => $a->category,
                'score' => (float) $a->score,
            ])->all(),
        );
    }
}
