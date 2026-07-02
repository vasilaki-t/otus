<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheBenchmarkCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_benchmark_command_runs_successfully(): void
    {
        Page::factory()->count(3)->create();

        $this->artisan('cache:benchmark', ['--iterations' => 5])
            ->assertExitCode(0);
    }
}
