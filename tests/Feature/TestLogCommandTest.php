<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TestLogCommandTest extends TestCase
{
    public function test_command_runs_successfully_with_default_level(): void
    {
        Log::spy();

        $this->artisan('log:test')
            ->assertExitCode(0);
    }

    public function test_command_accepts_explicit_level_argument(): void
    {
        Log::spy();

        $this->artisan('log:test', ['level' => 'warning'])
            ->assertExitCode(0);
    }
}
