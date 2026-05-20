<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->mahasiswa(),
            'nim' => fake()->unique()->numerify('21##########'),
            'full_name' => fake()->name(),
            'semester' => fake()->numberBetween(1, 8),
            'status' => 'aktif',
            'ipk' => fake()->randomFloat(2, 3.0, 4.0),
            'penghasilan_ortu' => fake()->numberBetween(2_000_000, 10_000_000),
            'tanggungan' => fake()->numberBetween(1, 5),
        ];
    }
}
