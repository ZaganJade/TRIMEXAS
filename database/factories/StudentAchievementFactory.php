<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAchievement>
 */
class StudentAchievementFactory extends Factory
{
    protected $model = StudentAchievement::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'title' => fake()->sentence(3),
            'category' => fake()->randomElement(['akademis', 'non_akademis']),
            'level' => fake()->randomElement(['internasional', 'nasional', 'provinsi', 'kabupaten']),
            'rank' => fake()->randomElement(['juara_1', 'juara_2', 'juara_3', 'partisipasi']),
            'year' => fake()->numberBetween(2020, (int) date('Y')),
            'score' => 5.0,
            'verified_by_admin' => false,
        ];
    }
}
