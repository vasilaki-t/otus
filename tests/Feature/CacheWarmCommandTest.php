<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Services\StatisticsCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheWarmCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_warm_command_populates_managed_cache_keys(): void
    {
        Page::factory()->create(['is_published' => true]);

        $this->assertFalse(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));

        $this->artisan('cache:warm')->assertExitCode(0);

        $this->assertTrue(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
        $this->assertTrue(Cache::has(StatisticsCache::KEY_PUBLISHED_PAGES));
    }

    public function test_warm_command_force_option_runs(): void
    {
        $this->artisan('cache:warm', ['--force' => true])->assertExitCode(0);

        $this->assertTrue(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
    }
}
