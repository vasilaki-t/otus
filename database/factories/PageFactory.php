<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => fake()->unique()->slug(3),
            'content' => fake()->paragraphs(3, true),
            'is_published' => fake()->boolean(70),
            'published_at' => fake()->optional(0.7)->dateTimeBetween('-1 month'),
        ];
    }
}
