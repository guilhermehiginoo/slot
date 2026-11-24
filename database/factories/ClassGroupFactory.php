<?php

namespace Database\Factories;

use App\Models\{ClassGroup, Course};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassGroup>
 */
class ClassGroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'semester'  => '2026.2',
            'number'    => 'T01',
            'schedule'  => '35M12',
        ];
    }
}
