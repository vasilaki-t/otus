<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Services\StatisticsCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatisticsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_are_cached_with_ttl_key(): void
    {
        Page::factory()->count(2)->create();

        $cache = app(StatisticsCache::class);

        $this->assertFalse(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));

        $counts = $cache->dashboardCounts();

        $this->assertSame(2, $counts['pages']);
        $this->assertTrue(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
    }

    public function test_creating_a_page_invalidates_cache_via_observer(): void
    {
        $cache = app(StatisticsCache::class);

        // Prime the cache.
        $first = $cache->dashboardCounts();
        $this->assertSame(0, $first['pages']);
        $this->assertTrue(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));

        // Observer must flush the cache on model change.
        Page::factory()->create();
        $this->assertFalse(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));

        // Recomputed value reflects the new state.
        $second = $cache->dashboardCounts();
        $this->assertSame(1, $second['pages']);
    }

    public function test_deleting_a_page_invalidates_cache(): void
    {
        $page = Page::factory()->create();
        $cache = app(StatisticsCache::class);

        $cache->dashboardCounts();
        $this->assertTrue(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));

        $page->delete();

        $this->assertFalse(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
    }

    public function test_published_pages_are_cached(): void
    {
        Page::factory()->create(['is_published' => true]);
        Page::factory()->create(['is_published' => false]);

        $cache = app(StatisticsCache::class);
        $pages = $cache->publishedPages();

        $this->assertCount(1, $pages);
        $this->assertTrue(Cache::has(StatisticsCache::KEY_PUBLISHED_PAGES));
    }

    public function test_flush_removes_all_managed_keys(): void
    {
        $cache = app(StatisticsCache::class);
        $cache->dashboardCounts();
        $cache->publishedPages();

        $cache->flush();

        $this->assertFalse(Cache::has(StatisticsCache::KEY_DASHBOARD_COUNTS));
        $this->assertFalse(Cache::has(StatisticsCache::KEY_PUBLISHED_PAGES));
    }
}
