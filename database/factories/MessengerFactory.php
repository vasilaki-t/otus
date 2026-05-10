<?php

namespace Database\Factories;

use App\Models\Messenger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Messenger>
 */
class MessengerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'telegram',
                'max',
                'slack',
                'web',
                'whatsapp',
                'vk',
            ]),
        ];
    }
}
