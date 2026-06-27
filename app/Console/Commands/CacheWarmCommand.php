<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StatisticsCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Warms up the logic-level statistics cache so the first user request is served
 * from a hot cache instead of paying the cold-miss query cost.
 */
class CacheWarmCommand extends Command
{
    protected $signature = 'cache:warm {--force : Flush managed keys before warming them up}';

    protected $description = 'Warm up the logic-level statistics cache (dashboard counts & published pages)';

    public function handle(StatisticsCache $cache): int
    {
        if ($this->option('force')) {
            $cache->flush();
            $this->line('Flushed managed cache keys before warm-up.');
        }

        $counts = $cache->dashboardCounts();
        $pages = $cache->publishedPages();

        $this->info(sprintf(
            'Cache warmed (TTL %ds): %d entity counters, %d published pages.',
            $cache->ttl(),
            array_sum($counts),
            $pages->count(),
        ));

        $rows = [];
        foreach ($cache->keys() as $key) {
            $rows[] = [$key, Cache::has($key) ? 'warm' : 'missing'];
        }

        $this->table(['Cache key', 'State'], $rows);

        return self::SUCCESS;
    }
}
