<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['threshold_1', 'threshold_2', 'is_active'])]
class OutputThreshold extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['threshold_1', 'threshold_2', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('output_threshold');
    }
    protected $casts = [
        'threshold_1' => 'float',
        'threshold_2' => 'float',
        'is_active' => 'boolean',
    ];
}
