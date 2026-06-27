<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Services\StatisticsCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheFlushCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_flush_stats_command_clears_managed_keys(): void
    {
        Page::factory()->create(['is_published' => true]);

        $this->artisan('cache:warm')->assertExitCode(0);
        $this->assertTrue(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
        $this->assertTrue(Cache::has(StatisticsCache::KEY_PUBLISHED_PAGES));

        $this->artisan('cache:flush-stats')->assertExitCode(0);

        $this->assertFalse(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
        $this->assertFalse(Cache::has(StatisticsCache::KEY_PUBLISHED_PAGES));
    }

    public function test_flush_stats_command_with_all_flushes_whole_store(): void
    {
        Cache::put('unmanaged.key', 'value', 600);

        $this->artisan('cache:flush-stats', ['--all' => true])->assertExitCode(0);

        $this->assertFalse(Cache::has('unmanaged.key'));
    }

    public function test_refresh_command_flushes_and_warms(): void
    {
        Page::factory()->create(['is_published' => true]);

        $this->artisan('cache:refresh')->assertExitCode(0);

        $this->assertTrue(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
        $this->assertTrue(Cache::has(StatisticsCache::KEY_PUBLISHED_PAGES));
    }
}
