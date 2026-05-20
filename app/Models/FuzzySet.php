<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['criterion_id', 'name', 'shape', 'a', 'b', 'c'])]
class FuzzySet extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['a', 'b', 'c'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('fuzzy_set');
    }
    public const SHAPE_LINEAR_TURUN = 'linear_turun';

    public const SHAPE_SEGITIGA = 'segitiga';

    public const SHAPE_LINEAR_NAIK = 'linear_naik';

    protected $casts = [
        'a' => 'float',
        'b' => 'float',
        'c' => 'float',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }
}
