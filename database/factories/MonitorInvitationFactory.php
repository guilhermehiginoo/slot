<?php

namespace Database\Factories;

use App\Enums\MonitorInvitationStatus;
use App\Models\{ClassGroup, MonitorInvitation, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitorInvitation>
 */
class MonitorInvitationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_group_id' => ClassGroup::factory(),
            'user_id'        => User::factory(),
            'invited_by'     => User::factory()->professor(),
            'status'         => MonitorInvitationStatus::Pending,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => ['status' => MonitorInvitationStatus::Accepted]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => ['status' => MonitorInvitationStatus::Declined]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => ['status' => MonitorInvitationStatus::Revoked]);
    }
}
