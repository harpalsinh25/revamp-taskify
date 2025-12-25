<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->company() . ' Workspace',
            'user_id' => User::factory(),
            'is_primary' => 0,
        ];
    }

    /**
     * Mark as primary workspace
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => 1,
        ]);
    }
}
