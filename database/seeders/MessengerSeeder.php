<?php

namespace Database\Seeders;

use App\Models\Messenger;
use Illuminate\Database\Seeder;

class MessengerSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'telegram',
            'max',
            'slack',
            'web',
        ])->each(fn (string $name) => Messenger::query()->firstOrCreate([
            'name' => $name,
        ]));
    }
}
