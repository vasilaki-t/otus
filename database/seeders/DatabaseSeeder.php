<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $adminRole = Role::query()->where('slug', Role::ADMIN)->firstOrFail();
        $userRole = Role::query()->where('slug', Role::USER)->firstOrFail();

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('password'),
            ],
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $testUser = User::query()->firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'username' => 'test_user',
                'password' => Hash::make('password'),
            ],
        );
        $testUser->roles()->syncWithoutDetaching([$userRole->id]);

        $this->call([
            MessengerSeeder::class,
            DialogSeeder::class,
            RequestHistorySeeder::class,
            PageSeeder::class,
        ]);
    }
}
