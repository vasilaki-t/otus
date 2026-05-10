<?php

namespace Database\Factories;

use App\Models\Dialog;
use App\Models\Messenger;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dialog_id' => Dialog::factory(),
            'messenger_id' => Messenger::factory(),
            'request_text' => fake()->sentence(8),
            'response_text' => fake()->optional(0.8)->paragraph(),
        ];
    }
}
