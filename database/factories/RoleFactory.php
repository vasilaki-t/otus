<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Administrator',
            'slug' => Role::ADMIN,
        ]);
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'User',
            'slug' => Role::USER,
        ]);
    }
}
