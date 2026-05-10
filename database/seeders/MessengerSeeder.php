<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class MessengerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('messengers')->insert([
            ['name' => 'telegram', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'max', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'slack', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
