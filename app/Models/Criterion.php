<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'domain_min', 'domain_max', 'unit', 'display_order'])]
class Criterion extends Model
{
    protected $table = 'criteria';

    protected $casts = [
        'domain_min' => 'decimal:2',
        'domain_max' => 'decimal:2',
        'display_order' => 'integer',
    ];

    public function fuzzySets(): HasMany
    {
        return $this->hasMany(FuzzySet::class, 'criterion_id');
    }
}
