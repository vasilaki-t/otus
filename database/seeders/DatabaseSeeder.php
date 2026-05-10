<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate([
            'username' => 'test_user',
        ]);

        $this->call([
            MessengerSeeder::class,
            DialogSeeder::class,
            RequestHistorySeeder::class,
        ]);
    }
}
