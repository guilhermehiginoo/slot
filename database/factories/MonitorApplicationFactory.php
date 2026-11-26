<?php

namespace Database\Factories;

use App\Enums\MonitorApplicationStatus;
use App\Models\{ClassGroup, MonitorApplication, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitorApplication>
 */
class MonitorApplicationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_group_id' => ClassGroup::factory(),
            'user_id'        => User::factory(),
            'message'        => fake()->sentence(),
            'status'         => MonitorApplicationStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => MonitorApplicationStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => ['status' => MonitorApplicationStatus::Rejected]);
    }
}
