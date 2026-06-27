<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            Role::ADMIN => 'Administrator',
            Role::USER => 'User',
        ])->each(fn (string $name, string $slug) => Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name],
        ));
    }
}
