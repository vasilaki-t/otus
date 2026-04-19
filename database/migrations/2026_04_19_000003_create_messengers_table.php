<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messengers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('messengers')->insert([
            ['name' => 'telegram', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'max', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'slack', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messengers');
    }
};
