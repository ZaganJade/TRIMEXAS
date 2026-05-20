<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['threshold_1', 'threshold_2', 'is_active'])]
class OutputThreshold extends Model
{
    protected $casts = [
        'threshold_1' => 'float',
        'threshold_2' => 'float',
        'is_active' => 'boolean',
    ];
}
