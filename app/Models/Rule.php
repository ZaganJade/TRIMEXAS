<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'antecedents', 'consequent', 'description', 'active'])]
class Rule extends Model
{
    public const CONSEQUENT_LAYAK = 'layak';

    public const CONSEQUENT_DIPERTIMBANGKAN = 'dipertimbangkan';

    public const CONSEQUENT_TIDAK_LAYAK = 'tidak_layak';

    protected $casts = [
        'antecedents' => 'array',
        'active' => 'boolean',
    ];
}
