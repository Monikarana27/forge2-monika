<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Ticket;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['open', 'in_progress', 'resolved']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'tenant_id' => \App\Models\Tenant::factory(),
            'user_id' => \App\Models\User::factory(),
            'assigned_agent_id' => \App\Models\User::factory()->agent(),
        ];
    }

    public function forTenant($tenantId)
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }

    public function forUser($userId)
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    public function assignedTo($agentId)
    {
        return $this->state(fn (array $attributes) => [
            'assigned_agent_id' => $agentId,
        ]);
    }
}