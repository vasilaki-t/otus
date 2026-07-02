<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Dialog;
use App\Models\Messenger;
use App\Models\Page;
use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Logic-level cache for dashboard statistics and public page data.
 *
 * Wraps expensive aggregate queries in `Cache::remember()` with a configurable
 * TTL (see config/cache_warm.php) and provides explicit invalidation through an
 * authoritative key registry, so it works on any cache driver (array, database,
 * memcached) regardless of tag support.
 */
class StatisticsCache
{
    public const KEY_DASHBOARD_COUNTS = 'stats.dashboard_counts';

    public const KEY_PUBLISHED_PAGES = 'stats.published_pages';

    /**
     * Cache lifetime (seconds) for the managed entries.
     */
    public function ttl(): int
    {
        return (int) config('cache_warm.ttl', 600);
    }

    /**
     * All cache keys owned by this service.
     *
     * @return list<string>
     */
    public function keys(): array
    {
        /** @var list<string> $keys */
        $keys = config('cache_warm.keys', [
            self::KEY_DASHBOARD_COUNTS,
            self::KEY_PUBLISHED_PAGES,
        ]);

        return $keys;
    }

    /**
     * Cached entity counters used by the admin dashboard.
     *
     * @return array<string, int>
     */
    public function dashboardCounts(): array
    {
        return Cache::remember(
            self::KEY_DASHBOARD_COUNTS,
            $this->ttl(),
            static fn (): array => [
                'pages' => Page::query()->count(),
                'published_pages' => Page::query()->where('is_published', true)->count(),
                'messengers' => Messenger::query()->count(),
                'dialogs' => Dialog::query()->count(),
                'request_histories' => RequestHistory::query()->count(),
                'users' => User::query()->count(),
            ],
        );
    }

    /**
     * Cached list of published pages.
     *
     * @return Collection<int, Page>
     */
    public function publishedPages(): Collection
    {
        return Cache::remember(
            self::KEY_PUBLISHED_PAGES,
            $this->ttl(),
            static fn (): Collection => Page::query()
                ->where('is_published', true)
                ->orderByDesc('published_at')
                ->get(),
        );
    }

    /**
     * Eagerly recompute and store every managed cache entry.
     */
    public function warm(): void
    {
        $this->flush();

        $this->dashboardCounts();
        $this->publishedPages();
    }

    /**
     * Invalidate every managed cache entry.
     */
    public function flush(): void
    {
        foreach ($this->keys() as $key) {
            Cache::forget($key);
        }
    }
}
