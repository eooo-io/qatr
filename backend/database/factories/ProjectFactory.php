<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' App',
            'description' => fake()->sentence(),
            'settings' => [],
            'owner_id' => User::factory(),
        ];
    }
}
