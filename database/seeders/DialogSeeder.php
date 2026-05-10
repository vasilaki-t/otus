<?php

namespace Database\Seeders;

use App\Models\Dialog;
use App\Models\User;
use Illuminate\Database\Seeder;

class DialogSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate([
            'username' => 'test_user',
        ]);

        Dialog::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);
    }
}
