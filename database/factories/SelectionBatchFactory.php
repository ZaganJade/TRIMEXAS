<?php

namespace Database\Factories;

use App\Models\SelectionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SelectionBatch>
 */
class SelectionBatchFactory extends Factory
{
    protected $model = SelectionBatch::class;

    public function definition(): array
    {
        return [
            'label' => 'Batch '.fake()->bothify('##??'),
            'triggered_by' => User::factory()->admin(),
            'status' => SelectionBatch::STATUS_QUEUED,
            'total_candidates' => 0,
            'total_eligible' => 0,
            'total_ineligible' => 0,
            'processed_count' => 0,
        ];
    }
}
