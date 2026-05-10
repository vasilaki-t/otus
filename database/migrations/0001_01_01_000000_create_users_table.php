<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
