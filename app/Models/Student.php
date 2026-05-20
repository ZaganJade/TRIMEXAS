<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    use SoftDeletes;

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
}
